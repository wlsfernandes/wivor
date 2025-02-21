<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class RoleSeederPhoto extends Seeder
{
    public function run()
    {
        DB::beginTransaction();

        try {
            // Create Admin Role if not exists
            $adminRole = Role::updateOrCreate(
                ['id' => 2],
                ['name' => 'photographer']
            );

            DB::commit();
            echo "Users and roles seeded successfully.\n";

        } catch (\Exception $e) {
            DB::rollBack();
            echo "Seeding failed: " . $e->getMessage() . "\n";
        }
    }
}
