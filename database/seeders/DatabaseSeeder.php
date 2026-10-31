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
        $this->call([
            RoleSeeder::class,
            PositionSeeder::class,
            RelationshipSeeder::class,
            DocumentCategorySeeder::class,
            DegreeSeeder::class,
            JobRequirementSeeder::class,
        ]);

        $fuad = User::firstOrCreate([
            'email' => 'fuad.r@mutiaraharapan.sch.id',
        ], [
            'name' => 'Fuad Rifai',
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        ]);
        $fuad->roles()->syncWithoutDetaching([1]);

        $salludin = User::firstOrCreate([
            'email' => 'salludin.g@mutiaraharapan.sch.id',
        ], [
            'name' => 'Salludin Gozali',
            'email_verified_at' => now(),
            'password' => '$2y$10$zMzXBaCLSTLnNJnPIYsN6OJHisOlgA/g6LW2kWsYN11Zq4aF2FjDS',
        ]);
        $salludin->roles()->syncWithoutDetaching([1]);
    }
}
