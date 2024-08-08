<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class InitialSetupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $userRole = Role::firstOrCreate(['name' => 'user']);

        // Create user
        $user = User::firstOrCreate(
            ['email' => 'lpangm03@me.com'],
            [
                'name' => 'Angelo Marasa',
                'password' => Hash::make('fifa99')
            ]
        );

        // Assign role to user
        $user->roles()->syncWithoutDetaching([$adminRole->id]);
    }
}
