<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class InstitutionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('institutions')->insert([
            [
                'name' => 'Amid Learning',
                'email' => 'joao@gmail.com',
                'address' => '1240 Power Ferris Rd - Marietta - GA',
                'phone' => '(678) 523-3172',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
