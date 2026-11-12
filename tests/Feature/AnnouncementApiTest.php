<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AnnouncementApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'app.debug' => false,
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.foreign_key_constraints' => false,
            'logging.default' => 'null',
        ]);
        DB::purge('sqlite');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });
        Schema::create('sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('token');
        });
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });
        Schema::create('role_user', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('role_id');
        });
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('employment_id')->nullable();
            $table->boolean('is_active')->default(true);
        });
        Schema::create('employments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('job_level_id')->nullable();
            $table->unsignedBigInteger('job_position_id')->nullable();
        });
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->boolean('all_employees');
            $table->timestamp('publish_at');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
        });
        foreach ([
            'branches' => 'branch_id',
            'organizations' => 'organization_id',
            'job_levels' => 'job_level_id',
            'positions' => 'position_id',
        ] as $tableName => $foreignKey) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->id();
                $table->string('name');
            });
            Schema::create('announcement_' . $tableName, function (Blueprint $table) use ($foreignKey) {
                $table->unsignedBigInteger('announcement_id');
                $table->unsignedBigInteger($foreignKey);
                $table->timestamps();
            });
        }

        DB::table('users')->insert(['id' => 1, 'name' => 'Test User']);
        DB::table('sessions')->insert(['user_id' => 1, 'token' => 'bearer test-token']);
    }

    public function test_missing_or_unrecognized_token_returns_forbidden(): void
    {
        $this->getJson('/api/announcement')
            ->assertForbidden()
            ->assertJson(['message' => 'Forbidden']);

        $this->getJson('/api/announcement', ['Authorization' => 'bearer invalid-token'])
            ->assertForbidden()
            ->assertJson(['message' => 'Forbidden']);
    }

    public function test_inactive_employee_gets_forbidden_for_announcement_requests(): void
    {
        DB::table('employees')->insert([
            'user_id' => 1,
            'is_active' => false,
        ]);

        $this->getJson('/api/announcement', ['Authorization' => 'bearer test-token'])
            ->assertForbidden()
            ->assertJson(['message' => 'Forbidden']);
    }

    public function test_authenticated_user_receives_only_published_global_announcements(): void
    {
        DB::table('announcements')->insert([
            ['title' => 'Published', 'all_employees' => true, 'publish_at' => now()->subDay()],
            ['title' => 'Future', 'all_employees' => true, 'publish_at' => now()->addDay()],
            ['title' => 'Targeted', 'all_employees' => false, 'publish_at' => now()->subDay()],
        ]);

        $this->getJson('/api/announcement', ['Authorization' => 'bearer test-token'])
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.title', 'Published');
    }

    public function test_admin_receives_all_announcements_and_can_limit_results(): void
    {
        DB::table('roles')->insert(['id' => 1, 'name' => 'admin']);
        DB::table('role_user')->insert(['user_id' => 1, 'role_id' => 1]);
        DB::table('announcements')->insert([
            ['title' => 'Published', 'all_employees' => true, 'publish_at' => now()->subDay()],
            ['title' => 'Future', 'all_employees' => false, 'publish_at' => now()->addDay()],
        ]);

        $this->getJson('/api/announcement?limit=1', ['Authorization' => 'bearer test-token'])
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.title', 'Future');
    }

    public function test_targeted_announcements_match_the_employees_branch(): void
    {
        DB::table('branches')->insert([
            ['id' => 1, 'name' => 'Branch A'],
            ['id' => 2, 'name' => 'Branch B'],
        ]);
        DB::table('employments')->insert(['id' => 1, 'branch_id' => 1]);
        DB::table('employees')->insert(['user_id' => 1, 'employment_id' => 1]);
        DB::table('announcements')->insert([
            ['id' => 1, 'title' => 'Matching', 'all_employees' => false, 'publish_at' => now()->subDay()],
            ['id' => 2, 'title' => 'Other branch', 'all_employees' => false, 'publish_at' => now()->subDay()],
        ]);
        DB::table('announcement_branches')->insert([
            ['announcement_id' => 1, 'branch_id' => 1],
            ['announcement_id' => 2, 'branch_id' => 2],
        ]);

        $this->getJson('/api/announcement', ['Authorization' => 'bearer test-token'])
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.title', 'Matching');
    }

    public function test_database_failure_returns_an_error_instead_of_a_success_response(): void
    {
        Schema::drop('announcements');

        $this->getJson('/api/announcement', ['Authorization' => 'bearer test-token'])
            ->assertStatus(500)
            ->assertJsonStructure(['message']);
    }
}
