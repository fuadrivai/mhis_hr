<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.foreign_key_constraints' => false,
        ]);
        DB::purge('sqlite');

        Schema::create('users', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->integer('user_id_talenta')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_resets', function ($table) {
            $table->string('email')->index();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function test_api_can_request_and_complete_a_password_reset(): void
    {
        Notification::fake();
        $user = User::create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => Hash::make('old-password'),
        ]);

        $this->postJson('/api/password/forgot', ['email' => $user->email])
            ->assertOk()
            ->assertJsonStructure(['message']);

        Notification::assertSentTo($user, ResetPasswordNotification::class);

        $token = Password::createToken($user);
        $this->postJson('/api/password/reset', [
            'email' => $user->email,
            'token' => $token,
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ])->assertOk();

        $this->assertTrue(Hash::check('new-secure-password', $user->fresh()->password));

        $this->postJson('/api/password/reset', [
            'email' => $user->email,
            'token' => $token,
            'password' => 'another-secure-password',
            'password_confirmation' => 'another-secure-password',
        ])->assertUnprocessable();
    }

    public function test_web_password_reset_pages_are_available(): void
    {
        $this->get('/password/forgot')->assertOk();
        $this->get('/password/reset/sample-token?email=user%40example.com')->assertOk();
    }

    public function test_web_and_mobile_requests_send_branded_email_with_a_working_reset_link(): void
    {
        $messages = [];
        Event::listen(MessageSent::class, function (MessageSent $event) use (&$messages) {
            $messages[] = $event->message;
        });

        foreach (['web', 'mobile'] as $channel) {
            $user = User::create([
                'name' => 'Test & User',
                'email' => $channel . '@example.com',
                'password' => Hash::make('old-password'),
            ]);

            if ($channel === 'web') {
                $this->post('/password/email', ['email' => $user->email])->assertRedirect();
            } else {
                $this->postJson('/api/password/forgot', ['email' => $user->email])->assertOk();
            }

            $message = end($messages);
            $this->assertCount($channel === 'web' ? 1 : 2, $messages);
            $this->assertSame('Reset Password - MHIS HUB', $message->getSubject());
            $html = $message->getHtmlBody();
            $text = $message->getTextBody();
            $this->assertStringContainsString('Mutiara Harapan Islamic School', $html);
            $this->assertStringContainsString('Test &amp; User', $html);
            $this->assertStringContainsString('expire in 60 minutes', $html);
            $this->assertStringContainsString('If you did not request a password reset', $text);
            $this->assertMatchesRegularExpression('/href="([^"]+\/password\/reset\/[^"]+)"/', $html);
            preg_match('/href="([^"]+\/password\/reset\/[^"]+)"/', $html, $matches);
            $url = html_entity_decode($matches[1], ENT_QUOTES, 'UTF-8');
            $this->assertStringContainsString($url, $text);

            $path = parse_url($url, PHP_URL_PATH);
            parse_str(parse_url($url, PHP_URL_QUERY), $query);
            $this->assertSame($user->email, $query['email']);
            $token = basename($path);
            $this->assertTrue(Password::tokenExists($user, $token));
            $this->get($url)->assertOk()->assertSee('Create a new password');
            $this->post('/password/reset', [
                'email' => $user->email,
                'token' => $token,
                'password' => 'new-secure-password',
                'password_confirmation' => 'new-secure-password',
            ])->assertRedirect('/login');
            $this->assertTrue(Hash::check('new-secure-password', $user->fresh()->password));
            $this->assertFalse(Password::tokenExists($user, $token));
        }
    }
}
