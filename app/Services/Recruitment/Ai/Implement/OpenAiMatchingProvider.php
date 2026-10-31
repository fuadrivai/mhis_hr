<?php

namespace App\Services\Recruitment\Ai\Implement;

use App\Services\Recruitment\Ai\AiMatchingProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class OpenAiMatchingProvider implements AiMatchingProvider
{
    private string $apiKey;
    private string $model;
    private string $baseUrl;
    private int $timeout;

    public function __construct()
    {
        $this->apiKey = (string) config('ai.openai.api_key', '');
        $this->model = (string) config('ai.openai.model', 'gpt-4o-mini');
        $this->baseUrl = rtrim((string) config('ai.openai.base_url', 'https://api.openai.com/v1'), '/');
        $this->timeout = (int) config('ai.timeout', 45);
    }

    public function matchCv(string $redactedCv, array $requirements, string $promptVersion = 'v1'): array
    {
        // Fallback to mock if provider is explicitly set to mock or API key is not configured
        if (config('ai.provider') === 'mock' || empty($this->apiKey)) {
            return $this->mockMatchResponse($redactedCv, $requirements);
        }

        $promptFile = resource_path("prompts/cv_match/{$promptVersion}.md");
        $systemPrompt = file_exists($promptFile)
            ? (string) file_get_contents($promptFile)
            : "You are an objective screening assistant. Analyze CV against requirements and return JSON.";

        $reqList = [];
        foreach ($requirements as $req) {
            $reqList[] = [
                'id'           => $req['id'] ?? null,
                'text'         => $req['requirement'] ?? ($req['text'] ?? ''),
                'category'     => $req['category'] ?? 'other',
                'is_mandatory' => (bool) ($req['is_mandatory'] ?? false),
                'weight'       => (int) ($req['weight'] ?? 3),
            ];
        }

        $userPayload = "JOB REQUIREMENTS:\n"
            . json_encode($reqList, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
            . "\n\nCANDIDATE CV:\n"
            . $redactedCv;

        try {
            $response = Http::withToken($this->apiKey)
                ->withoutVerifying()
                ->timeout($this->timeout)
                ->retry(2, 600, throw: false)
                ->post("{$this->baseUrl}/chat/completions", [
                    'model'           => $this->model,
                    'messages'        => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $userPayload],
                    ],
                    'response_format' => ['type' => 'json_object'],
                    'temperature'     => 0.1,
                ]);

            if (!$response->successful()) {
                throw new RuntimeException("OpenAI API call failed ({$response->status()}): {$response->body()}");
            }

            $content = $response->json('choices.0.message.content');
            $data = json_decode((string) $content, true);

            if (!is_array($data)) {
                throw new RuntimeException("Failed to decode JSON from AI response: {$content}");
            }

            return $this->normalizeMatchResponse($data, $requirements);
        } catch (Throwable $e) {
            Log::error('OpenAiMatchingProvider::matchCv error: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            throw $e;
        }
    }

    public function parseCvIdentity(string $rawCvText): array
    {
        if (config('ai.provider') === 'mock' || empty($this->apiKey)) {
            return $this->mockIdentityResponse($rawCvText);
        }

        $promptFile = resource_path("prompts/cv_parse/v1.md");
        $systemPrompt = file_exists($promptFile)
            ? (string) file_get_contents($promptFile)
            : "Extract contact info from CV: full_name, email, phone, current_city. Return JSON.";

        try {
            $response = Http::withToken($this->apiKey)
                ->withoutVerifying()
                ->timeout($this->timeout)
                ->retry(2, 500, throw: false)
                ->post("{$this->baseUrl}/chat/completions", [
                    'model'           => $this->model,
                    'messages'        => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => "Extract identity from this CV text:\n\n" . mb_substr($rawCvText, 0, 4000)],
                    ],
                    'response_format' => ['type' => 'json_object'],
                    'temperature'     => 0.0,
                ]);

            if ($response->successful()) {
                $content = $response->json('choices.0.message.content');
                $data = json_decode((string) $content, true);
                if (is_array($data)) {
                    return [
                        'full_name'    => $data['full_name'] ?? null,
                        'email'        => $data['email'] ?? null,
                        'phone'        => $data['phone'] ?? null,
                        'current_city' => $data['current_city'] ?? null,
                    ];
                }
            }
        } catch (Throwable $e) {
            Log::warning('AI CV identity parsing failed: ' . $e->getMessage());
        }

        // Regex heuristic fallback if API fails
        return $this->heuristicIdentityExtraction($rawCvText);
    }

    public function getProviderName(): string
    {
        return 'openai';
    }

    public function getModelName(): string
    {
        return $this->model;
    }

    private function normalizeMatchResponse(array $data, array $requirements): array
    {
        $reqResults = [];
        $rawReqs = (array) ($data['requirements'] ?? []);
        $rawKeyed = [];
        foreach ($rawReqs as $r) {
            if (isset($r['id'])) {
                $rawKeyed[$r['id']] = $r;
            }
        }

        foreach ($requirements as $req) {
            $id = $req['id'] ?? null;
            $match = $rawKeyed[$id] ?? null;

            $status = $match['status'] ?? 'unknown';
            if (!in_array($status, ['met', 'partial', 'not_met', 'unknown'], true)) {
                $status = 'unknown';
            }

            $reqResults[] = [
                'id'       => $id,
                'status'   => $status,
                'evidence' => trim((string) ($match['evidence'] ?? '')),
                'reason'   => trim((string) ($match['reason'] ?? '')),
            ];
        }

        return [
            'summary'             => (string) ($data['summary'] ?? 'Screening evaluation complete.'),
            'red_flags'           => (array) ($data['red_flags'] ?? []),
            'interview_questions' => (array) ($data['interview_questions'] ?? []),
            'requirements'        => $reqResults,
        ];
    }

    private function mockMatchResponse(string $redactedCv, array $requirements): array
    {
        $reqResults = [];
        $lowerCv = strtolower($redactedCv);

        foreach ($requirements as $req) {
            $id = $req['id'] ?? null;
            $text = strtolower((string) ($req['requirement'] ?? ($req['text'] ?? '')));

            // Split requirement into key keywords (words >= 4 chars)
            $keywords = array_filter(
                preg_split('/\W+/u', $text) ?: [],
                fn ($w) => strlen($w) >= 4 && !in_array($w, ['have', 'with', 'must', 'able', 'minimum', 'years'], true)
            );

            $matchedCount = 0;
            $foundWord = '';
            foreach ($keywords as $kw) {
                if (str_contains($lowerCv, $kw)) {
                    $matchedCount++;
                    $foundWord = $kw;
                }
            }

            if ($matchedCount >= 2) {
                $status = 'met';
                $evidence = "Found qualification related to {$foundWord} in candidate profile.";
                $reason = "Candidate matches requirement based on relevant experience.";
            } elseif ($matchedCount === 1) {
                $status = 'partial';
                $evidence = "Mentions {$foundWord} in CV text.";
                $reason = "Partial match observed in candidate background.";
            } else {
                $status = $req['is_mandatory'] ?? false ? 'not_met' : 'unknown';
                $evidence = '';
                $reason = "No direct mention found in CV text.";
            }

            $reqResults[] = [
                'id'       => $id,
                'status'   => $status,
                'evidence' => $evidence,
                'reason'   => $reason,
            ];
        }

        return [
            'summary'             => 'Candidate exhibits foundational qualifications with relevant pedagogical background.',
            'red_flags'           => [],
            'interview_questions' => [
                'Can you elaborate on your experience aligning with our curriculum?',
                'How do you manage classroom engagement in diverse environments?',
            ],
            'requirements'        => $reqResults,
        ];
    }

    private function mockIdentityResponse(string $rawCvText): array
    {
        return $this->heuristicIdentityExtraction($rawCvText);
    }

    private function heuristicIdentityExtraction(string $rawCvText): array
    {
        $fullName = null;
        $email = null;
        $phone = null;
        $city = null;

        // 1. Email
        if (preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $rawCvText, $m)) {
            $email = $m[0];
        }

        // 2. Phone
        if (preg_match('/(?:\+?62|08|\b02\d)\d{8,12}/', preg_replace('/[-.\s]/', '', $rawCvText), $m)) {
            $phone = $m[0];
        }

        // 3. Name (typically first non-empty line with 2-4 capitalized words)
        $lines = preg_split('/[\r\n]+/', trim($rawCvText));
        foreach ($lines as $line) {
            $cleanLine = trim($line);
            if (mb_strlen($cleanLine) > 3 && mb_strlen($cleanLine) < 50) {
                // If line does not look like headers, email, or phone
                if (!preg_match('/(?:resume|curriculum|cv|biodata|profile|contact|email|phone|http)/i', $cleanLine) && !preg_match('/@|\d{4}/', $cleanLine)) {
                    $fullName = $cleanLine;
                    break;
                }
            }
        }

        // 4. City (heuristic search for common Indonesian cities)
        if (preg_match('/\b(Jakarta|Tangerang|Bekasi|Bogor|Depok|Bandung|Surabaya|Semarang|Yogyakarta|Medan|Palembang)\b/i', $rawCvText, $m)) {
            $city = ucfirst(strtolower($m[1]));
        }

        return [
            'full_name'    => $fullName,
            'email'        => $email,
            'phone'        => $phone,
            'current_city' => $city,
        ];
    }

    /**
     * Extract text from a visual/scanned document or image via AI Vision OCR.
     */
    public function extractTextFromVisualDocument(string $filePath): string
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return '';
        }

        if (config('ai.provider') === 'mock' || empty($this->apiKey)) {
            return "Mock OCR text for scanned document at " . basename($filePath);
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $fileBytes = (string) @file_get_contents($filePath);
        if (empty($fileBytes)) {
            return '';
        }

        $base64 = base64_encode($fileBytes);
        $payloadContent = [
            [
                'type' => 'text',
                'text' => 'Extract all text, sections, and qualifications verbatim from this document. Return ONLY the plain extracted text without conversational commentary.'
            ]
        ];

        if ($extension === 'pdf') {
            $rasterizer = new \App\Services\Recruitment\Ai\PdfRasterizer();
            $pages = $rasterizer->rasterize($filePath, 2);
            $ocrChunks = [];
            foreach ($pages as $p) {
                $chunk = $this->extractTextFromVisualDocument($p);
                if (!empty(trim($chunk))) {
                    $ocrChunks[] = $chunk;
                }
                @unlink($p);
            }
            return implode("\n\n", $ocrChunks);
        }

        $mime = match ($extension) {
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            default => (mime_content_type($filePath) ?: 'image/jpeg'),
        };

        if (!str_starts_with($mime, 'image/')) {
            $mime = 'image/png';
        }

        $payloadContent[] = [
            'type' => 'image_url',
            'image_url' => [
                'url' => 'data:' . $mime . ';base64,' . $base64,
            ]
        ];

        try {
            $response = Http::withToken($this->apiKey)
                ->withoutVerifying()
                ->timeout($this->timeout)
                ->retry(2, 600, throw: false)
                ->post("{$this->baseUrl}/chat/completions", [
                    'model'       => $this->model,
                    'messages'    => [
                        [
                            'role'    => 'user',
                            'content' => $payloadContent,
                        ]
                    ],
                    'temperature' => 0.0,
                    'max_tokens'  => 3000,
                ]);

            if ($response->successful()) {
                return (string) $response->json('choices.0.message.content');
            }

            Log::warning("OpenAI Vision OCR API error ({$response->status()}): {$response->body()}");
            return '';
        } catch (\Throwable $e) {
            Log::warning("OpenAI Vision OCR exception: " . $e->getMessage());
            return '';
        }
    }
}

