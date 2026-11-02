<?php

namespace Tests\Unit;

use App\Services\Recruitment\Ai\Implement\OpenAiMatchingProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OpenAiMatchingProviderTest extends TestCase
{
    public function test_standard_model_includes_temperature_and_system_role(): void
    {
        Config::set('ai.provider', 'openai');
        Config::set('ai.openai.api_key', 'test-key');
        Config::set('ai.openai.model', 'gpt-4o-mini');
        Config::set('ai.openai.temperature', null);

        Http::fake([
            '*/chat/completions' => function ($request) {
                $data = $request->data();
                $this->assertEquals('gpt-4o-mini', $data['model']);
                $this->assertEquals('system', $data['messages'][0]['role']);
                $this->assertEquals(0.1, $data['temperature']);

                return Http::response([
                    'choices' => [
                        [
                            'message' => [
                                'content' => json_encode([
                                    'summary' => 'Good candidate',
                                    'requirements' => [
                                        ['id' => 1, 'status' => 'met', 'evidence' => 'Found it', 'reason' => 'Matches'],
                                    ],
                                ]),
                            ],
                        ],
                    ],
                ], 200);
            },
        ]);

        $provider = new OpenAiMatchingProvider();
        $result = $provider->matchCv('Sample CV', [
            ['id' => 1, 'requirement' => 'Teaching experience', 'is_mandatory' => true, 'weight' => 3],
        ]);

        $this->assertEquals('Good candidate', $result['summary']);
        $this->assertEquals('met', $result['requirements'][0]['status']);
    }

    public function test_reasoning_model_omits_temperature_and_uses_developer_role(): void
    {
        Config::set('ai.provider', 'openai');
        Config::set('ai.openai.api_key', 'test-key');
        Config::set('ai.openai.model', 'gpt-6-luna');
        Config::set('ai.openai.temperature', null);

        Http::fake([
            '*/chat/completions' => function ($request) {
                $data = $request->data();
                $this->assertEquals('gpt-6-luna', $data['model']);
                $this->assertEquals('developer', $data['messages'][0]['role']);
                $this->assertArrayNotHasKey('temperature', $data, 'Temperature must be omitted for reasoning models like gpt-6-luna');

                return Http::response([
                    'choices' => [
                        [
                            'message' => [
                                'content' => json_encode([
                                    'summary' => 'Luna screening complete',
                                    'requirements' => [
                                        ['id' => 1, 'status' => 'met', 'evidence' => 'Strong fit', 'reason' => 'Direct match'],
                                    ],
                                ]),
                            ],
                        ],
                    ],
                ], 200);
            },
        ]);

        $provider = new OpenAiMatchingProvider();
        $result = $provider->matchCv('Sample CV with Luna', [
            ['id' => 1, 'requirement' => 'English teacher', 'is_mandatory' => true, 'weight' => 5],
        ]);

        $this->assertEquals('Luna screening complete', $result['summary']);
        $this->assertEquals('met', $result['requirements'][0]['status']);
    }

    public function test_reasoning_model_omits_invalid_temperature_even_if_configured(): void
    {
        Config::set('ai.provider', 'openai');
        Config::set('ai.openai.api_key', 'test-key');
        Config::set('ai.openai.model', 'gpt-6-luna');
        // If user configured OPENAI_TEMPERATURE=0.1, it should still be omitted for gpt-6-luna to avoid API error
        Config::set('ai.openai.temperature', 0.1);

        Http::fake([
            '*/chat/completions' => function ($request) {
                $data = $request->data();
                $this->assertArrayNotHasKey('temperature', $data);

                return Http::response([
                    'choices' => [
                        [
                            'message' => [
                                'content' => json_encode(['summary' => 'OK', 'requirements' => []]),
                            ],
                        ],
                    ],
                ], 200);
            },
        ]);

        $provider = new OpenAiMatchingProvider();
        $result = $provider->matchCv('Sample CV', []);
        $this->assertEquals('OK', $result['summary']);
    }
}

