<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class UserAndRoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::updateOrCreate(
            ['id' => 1],
            ['name' => 'admin']
        );
    }
}
