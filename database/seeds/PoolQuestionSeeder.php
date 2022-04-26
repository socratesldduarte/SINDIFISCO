<?php

use Illuminate\Database\Seeder;

class PoolQuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('poll_questions')->insert(
            [
                'poll_id' => 1,
                'question' => 'Passo 1',
                'description' => 'Diretoria Executiva e Delegados Representantes',
                'selection_number' => 1,
            ]
        );
        \DB::table('poll_questions')->insert(
            [
                'poll_id' => 1,
                'question' => 'Passo 2',
                'description' => 'Conselheiro Fiscal',
                'selection_number' => 1,
            ]
        );
    }
}
