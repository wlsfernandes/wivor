<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PostType;
use Illuminate\Support\Facades\DB;

class PostTypeSeeder extends Seeder
{
    public function run()
    {
        DB::beginTransaction();

        try {
            // Create PostType "event" with ID = 1
            PostType::updateOrCreate(
                ['id' => 1], // Ensure ID 1 is always used
                ['name' => 'event']
            );

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
        }
    }
}
