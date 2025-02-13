<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class anotherModule extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = [
            ['name' => 'TEOLOGIA SISTEMÁTICA', 'institution_id' => 1],
            ['name' => 'HOMILÉTICA', 'institution_id' => 1],
            ['name' => 'TEOLOGIA BÍBLICA', 'institution_id' => 1],
            ['name' => 'TEOLOGIA DO VELHO TESTAMENTO', 'institution_id' => 1],
            ['name' => 'TEOLOGIA DO NOVO TESTAMENTO', 'institution_id' => 1],
            ['name' => 'EXEGESE', 'institution_id' => 1],
            ['name' => 'HERMENÊUTICA', 'institution_id' => 1],
            ['name' => 'TEOLOGIA CONTEMPORÂNEA', 'institution_id' => 1],
            ['name' => 'TEOLOGIA HISTÓRICA', 'institution_id' => 1],
            ['name' => 'LÍNGUAS', 'institution_id' => 1],
            ['name' => 'TEOLOGIA BÍBLICA DE MISSÕES', 'institution_id' => 1],
            ['name' => 'TEOLOGIA DE LIDERANÇA', 'institution_id' => 1],
            ['name' => 'TEOLOGIA PASTORAL', 'institution_id' => 1],
            ['name' => 'TEOLOGIA DE MISSÕES', 'institution_id' => 1],
            ['name' => 'TEOLOGIA PEDAGÓGICA', 'institution_id' => 1],
            ['name' => 'TEOLOGIA DA VIDA CRISTÃ', 'institution_id' => 1],
            ['name' => 'ESTÁGIO', 'institution_id' => 1],
            ['name' => 'CONCLUSÃO DE CURSO', 'institution_id' => 1],
        ];

        DB::table('modules')->insert($modules);
    }
}
