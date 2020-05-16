<?php

use Illuminate\Database\Seeder;

class PoolQuestionOptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('poll_question_options')->insert(
            [
                'poll_question_id' => 1,
                'ordem' => 1,
                'opcao' => '11',
                'descricao' => 'Chapa 1<br>
Fulano 1 - Presidente<br>
Fulano 2 - Vice-Presidente',
            ]
        );
        \DB::table('poll_question_options')->insert(
            [
                'poll_question_id' => 1,
                'ordem' => 2,
                'opcao' => '12',
                'descricao' => 'Chapa 2<br>
Fulano 3 - Presidente<br>
Fulano 4 - Vice-Presidente',
            ]
        );
        \DB::table('poll_question_options')->insert(
            [
                'poll_question_id' => 1,
                'ordem' => 3,
                'opcao' => '13',
                'descricao' => 'Chapa 3<br>
Fulano 5 - Presidente<br>
Fulano 6 - Vice-Presidente',
            ]
        );
        \DB::table('poll_question_options')->insert(
            [
                'poll_question_id' => 2,
                'ordem' => 1,
                'opcao' => '21',
                'descricao' => 'Conselheiro Titular 1',
            ]
        );
        \DB::table('poll_question_options')->insert(
            [
                'poll_question_id' => 2,
                'ordem' => 2,
                'opcao' => '22',
                'descricao' => 'Conselheiro Titular 2',
            ]
        );
        \DB::table('poll_question_options')->insert(
            [
                'poll_question_id' => 2,
                'ordem' => 3,
                'opcao' => '23',
                'descricao' => 'Conselheiro Titular 3',
            ]
        );
        \DB::table('poll_question_options')->insert(
            [
                'poll_question_id' => 2,
                'ordem' => 4,
                'opcao' => '24',
                'descricao' => 'Conselheiro Titular 4',
            ]
        );
        \DB::table('poll_question_options')->insert(
            [
                'poll_question_id' => 2,
                'ordem' => 5,
                'opcao' => '25',
                'descricao' => 'Conselheiro Titular 5',
            ]
        );
        \DB::table('poll_question_options')->insert(
            [
                'poll_question_id' => 3,
                'ordem' => 1,
                'opcao' => '31',
                'descricao' => 'Conselheiro Suplente 1',
            ]
        );
        \DB::table('poll_question_options')->insert(
            [
                'poll_question_id' => 3,
                'ordem' => 2,
                'opcao' => '32',
                'descricao' => 'Conselheiro Suplente 2',
            ]
        );
        \DB::table('poll_question_options')->insert(
            [
                'poll_question_id' => 3,
                'ordem' => 3,
                'opcao' => '33',
                'descricao' => 'Conselheiro Suplente 3',
            ]
        );
        \DB::table('poll_question_options')->insert(
            [
                'poll_question_id' => 3,
                'ordem' => 4,
                'opcao' => '34',
                'descricao' => 'Conselheiro Suplente 4',
            ]
        );
        \DB::table('poll_question_options')->insert(
            [
                'poll_question_id' => 3,
                'ordem' => 5,
                'opcao' => '35',
                'descricao' => 'Conselheiro Suplente 5',
            ]
        );
        \DB::table('poll_question_options')->insert(
            [
                'poll_question_id' => 3,
                'ordem' => 6,
                'opcao' => '36',
                'descricao' => 'Conselheiro Suplente 6',
            ]
        );
        \DB::table('poll_question_options')->insert(
            [
                'poll_question_id' => 3,
                'ordem' => 7,
                'opcao' => '37',
                'descricao' => 'Conselheiro Suplente 7',
            ]
        );
    }
}
