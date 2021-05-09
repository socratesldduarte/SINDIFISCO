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
                'description' => 'Membro da Mesa',
                'selection_number' => 1,
            ]
        );
        \DB::table('poll_questions')->insert(
            [
                'poll_id' => 2,
                'question' => 'Passo 1',
                'description' => 'Membro da Mesa',
                'selection_number' => 1,
            ]
        );
        \DB::table('poll_questions')->insert(
            [
                'poll_id' => 3,
                'question' => 'Passo 1',
                'description' => 'Membro da Mesa',
                'selection_number' => 1,
            ]
        );
        \DB::table('poll_questions')->insert(
            [
                'poll_id' => 4,
                'question' => 'Passo 1',
                'description' => 'Membro da Mesa',
                'selection_number' => 1,
            ]
        );
        \DB::table('poll_questions')->insert(
            [
                'poll_id' => 5,
                'question' => 'Passo 1',
                'description' => 'Membro da Mesa',
                'selection_number' => 1,
            ]
        );
    }
}
