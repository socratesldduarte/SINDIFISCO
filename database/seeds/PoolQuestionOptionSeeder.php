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
                'order' => 1,
                'option' => '01',
                'description' => 'Diretoria Executiva
Presidente: ALTEMIR FELTRIN DA SILVA
Vice-Presidente: CHRISTIAN JESUS SILVA DE AZEVEDO
Diretor de Políticas e Ações Sindicais: CELSO MALHANI DE SOUZA
Diretor Administrativo: PAULO RICARDO MÄHLER
Diretor Financeiro: JORGE RITTER DE ABREU
Diretor de Relações Parlamentares e Institucionais: JOSÉ EDUARDO SESTARI ARGENTON JASNIEVICZ
Diretor de Assuntos Jurídicos e Previdenciários: ADEMAR PETRY
Diretor de Assuntos Técnicos: LUCIANO BARBOZA GARCIA
Diretor de Comunicação e Integração Social: ERONI IZAIAS NUMER
Diretor de Assuntos dos Aposentados e Pensionistas: SILVIA MARIA BENEDETTI TEIXEIRA

Delegados Representantes
I – IVANI BEATRIZ MULLER
II – GILBERTO SANTINI PROCATI',
            ]
        );
        \DB::table('poll_question_options')->insert(
            [
                'poll_question_id' => 2,
                'order' => 1,
                'option' => '01',
                'description' => 'DELMIRIO BRANDT DE SOUZA',
            ]
        );
        \DB::table('poll_question_options')->insert(
            [
                'poll_question_id' => 2,
                'order' => 2,
                'option' => '02',
                'description' => 'DIEGO DEGRAZIA DA SILVEIRA',
            ]
        );
        \DB::table('poll_question_options')->insert(
            [
                'poll_question_id' => 2,
                'order' => 3,
                'option' => '03',
                'description' => 'DONATO LUIZ HUBNER',
            ]
        );
        \DB::table('poll_question_options')->insert(
            [
                'poll_question_id' => 2,
                'order' => 4,
                'option' => '04',
                'description' => 'SALOMÃO ALBERTO LEIZER',
            ]
        );
        \DB::table('poll_question_options')->insert(
            [
                'poll_question_id' => 2,
                'order' => 5,
                'option' => '05',
                'description' => 'SIRLEI TERESINHA WALENCIUK',
            ]
        );
        \DB::table('poll_question_options')->insert(
            [
                'poll_question_id' => 2,
                'order' => 6,
                'option' => '06',
                'description' => 'SUSANA FAGUNDES GARCIA',
            ]
        );
        \DB::table('poll_question_options')->insert(
            [
                'poll_question_id' => 2,
                'order' => 7,
                'option' => '07',
                'description' => 'UBIRAJARA NOGUEIRA DA GAMA MEDINA',
            ]
        );
    }
}
