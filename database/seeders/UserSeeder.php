<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name' => 'Pastor João',
            'email' => 'paranastate@gmail.com',
            'password' => Hash::make('JesusVive'),
            'institution_id' => 1, // Replace with a valid institution ID
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
