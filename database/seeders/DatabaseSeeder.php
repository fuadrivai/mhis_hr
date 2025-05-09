<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        User::create([
            'name' => "Fuad Rifai",
            'email' => "fuad.r@mutiaraharapan.sch.id",
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
        ]);
        User::create([
            'name' => "Salludin Gozali",
            'email' => "salludin.g@mutiaraharapan.sch.id",
            'email_verified_at' => now(),
            'password' => '$2y$10$zMzXBaCLSTLnNJnPIYsN6OJHisOlgA/g6LW2kWsYN11Zq4aF2FjDS', // mutiaraharapan
        ]);
    }
}
