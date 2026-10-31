<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $roles = [
            ['name' => 'admin', 'description' => 'Administrator'],
            ['name' => 'editor', 'description' => 'Content editor'],
            ['name' => 'user', 'description' => 'Regular user'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role['name']], ['description' => $role['description']]);
        }
    }
}
