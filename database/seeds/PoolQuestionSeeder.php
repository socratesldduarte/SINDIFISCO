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
                'questao' => 'Passo 1',
                'descricao' => 'Diretoria',
                'selecao' => 1,
            ]
        );
        \DB::table('poll_questions')->insert(
            [
                'poll_id' => 1,
                'questao' => 'Passo 2',
                'descricao' => 'Conselheiro Titular',
                'selecao' => 2,
            ]
        );
        \DB::table('poll_questions')->insert(
            [
                'poll_id' => 1,
                'questao' => 'Passo 3',
                'descricao' => 'Conselheiro Suplente',
                'selecao' => 2,
            ]
        );
    }
}
