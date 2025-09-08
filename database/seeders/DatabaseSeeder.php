<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create roles
        Role::create(['name' => 'superadmin']);
        Role::create(['name' => 'admin']);

        // Create superadmin user
        $superAdmin = User::factory()->create([
            'name' => 'Boomdevs',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('password'),
        ]);
        $superAdmin->assignRole('superadmin');

        // Create 9 admin users
        User::factory()->count(9)->create()->each(function ($user) {
            $user->assignRole('admin');
        });
    }
}
