<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class InactiveEmployeeLoginTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.foreign_key_constraints' => false,
            'jwt.secret' => str_repeat('a', 32),
        ]);
        DB::purge('sqlite');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->integer('user_id_talenta')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->boolean('is_active')->default(true);
        });
        Schema::create('sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('token');
            $table->string('device')->nullable();
            $table->string('device_id')->nullable();
            $table->dateTime('login_date')->nullable();
            $table->timestamps();
        });

        User::create([
            'name' => 'Inactive User',
            'email' => 'inactive@example.com',
            'password' => Hash::make('password'),
        ]);
        User::create([
            'name' => 'Active User',
            'email' => 'active@example.com',
            'password' => Hash::make('password'),
        ]);
        DB::table('employees')->insert([
            'user_id' => 1,
            'is_active' => 0,
        ]);
        DB::table('employees')->insert([
            'user_id' => 2,
            'is_active' => 1,
        ]);
    }

    public function test_inactive_employee_cannot_log_in_via_web(): void
    {
        $this->post('/login', [
            'email' => 'inactive@example.com',
            'password' => 'password',
        ])->assertForbidden()
            ->assertViewIs('login.inactive')
            ->assertSee('Your account is currently inactive. Please contact the administrator for assistance.')
            ->assertSee('Return to sign in');

        $this->assertGuest();
        $this->assertDatabaseCount('sessions', 0);
    }

    public function test_inactive_employee_cannot_log_in_via_api(): void
    {
        $this->postJson('/api/login', [
            'email' => 'inactive@example.com',
            'password' => 'password',
            'device' => 'mobile',
            'device_id' => 'test-device',
        ])->assertForbidden()
            ->assertJson([
                'status' => 'error',
                'message' => 'Employee account is inactive.',
            ]);

        $this->assertDatabaseCount('sessions', 0);
    }

    public function test_active_employee_with_an_incorrect_password_is_not_logged_in(): void
    {
        $this->post('/login', [
            'email' => 'active@example.com',
            'password' => 'wrong-password',
        ])->assertRedirect()
            ->assertSessionHas('LoginError', 'Email is not valid');

        $this->assertGuest();
        $this->assertDatabaseCount('sessions', 0);
    }
}
