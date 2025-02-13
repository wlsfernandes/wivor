<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModulesTableSeeder extends Seeder
{
    public function run()
    {
        $modules = [
            ['name' => 'TEOLOGIA SISTEMÁTICA', 'institution_id' => 1],
            ['name' => 'HOMILÉTICA', 'institution_id' => 1],
            ['name' => 'TEOLOGIA BÍBLICA', 'institution_id' => 1],
            ['name' => 'TEOLOGIA DO VELHO TESTAMENTO', 'institution_id' => 2],
            ['name' => 'TEOLOGIA DO NOVO TESTAMENTO', 'institution_id' => 2],
            ['name' => 'EXEGESE', 'institution_id' => 2],
            ['name' => 'HERMENÊUTICA', 'institution_id' => 3],
            ['name' => 'TEOLOGIA CONTEMPORÂNEA', 'institution_id' => 3],
            ['name' => 'TEOLOGIA HISTÓRICA', 'institution_id' => 3],
            ['name' => 'LÍNGUAS', 'institution_id' => 4],
            ['name' => 'TEOLOGIA BÍBLICA DE MISSÕES', 'institution_id' => 4],
            ['name' => 'TEOLOGIA DE LIDERANÇA', 'institution_id' => 4],
            ['name' => 'TEOLOGIA PASTORAL', 'institution_id' => 5],
            ['name' => 'TEOLOGIA DE MISSÕES', 'institution_id' => 5],
            ['name' => 'TEOLOGIA PEDAGÓGICA', 'institution_id' => 5],
            ['name' => 'TEOLOGIA DA VIDA CRISTÃ', 'institution_id' => 6],
            ['name' => 'ESTÁGIO', 'institution_id' => 6],
            ['name' => 'CONCLUSÃO DE CURSO', 'institution_id' => 6],
        ];

        DB::table('modules')->insert($modules);
    }
}
