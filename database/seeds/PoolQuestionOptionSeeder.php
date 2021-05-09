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
                'poll_question_id' => 5,
                'order' => 1,
                'option' => '01',
                'description' => 'Nome 1',
            ]
        );
        \DB::table('poll_question_options')->insert(
            [
                'poll_question_id' => 5,
                'order' => 2,
                'option' => '02',
                'description' => 'Nome 2',
            ]
        );
        \DB::table('poll_question_options')->insert(
            [
                'poll_question_id' => 5,
                'order' => 3,
                'option' => '03',
                'description' => 'Nome 3',
            ]
        );
        \DB::table('poll_question_options')->insert(
            [
                'poll_question_id' => 5,
                'order' => 4,
                'option' => '04',
                'description' => 'Nome 4',
            ]
        );
    }
}
