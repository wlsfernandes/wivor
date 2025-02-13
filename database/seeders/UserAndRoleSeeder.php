<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class UserAndRoleSeeder extends Seeder
{
    public function run()
    {
        DB::beginTransaction();

        try {
            // Create Admin Role if not exists
            $adminRole = Role::updateOrCreate(
                ['id' => 1],
                ['name' => 'admin']
            );

            // Create Users
            $users = [
                [
                    'name' => 'Junior',
                    'email' => 'wlsfernandes@gmail.com',
                    'password' => Hash::make('adminWivor'),
                ],
                [
                    'name' => 'Vitor',
                    'email' => 'vhandrade90@gmail.com',
                    'password' => Hash::make('adminWivor'),
                ],
            ];

            foreach ($users as $userData) {
                $user = User::updateOrCreate(
                    ['email' => $userData['email']], // Prevent duplicate users
                    $userData
                );

                // Attach the admin role
                if (!$user->roles()->where('role_id', $adminRole->id)->exists()) {
                    $user->roles()->attach($adminRole->id);
                }
            }

            DB::commit();
            echo "Users and roles seeded successfully.\n";

        } catch (\Exception $e) {
            DB::rollBack();
            echo "Seeding failed: " . $e->getMessage() . "\n";
        }
    }
}
