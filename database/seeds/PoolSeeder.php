<?php

use Illuminate\Database\Seeder;

class PoolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
//        \DB::table('poll_types')->insert(
//            [
//                'name' => 'Eleição Tradicional AFISVEC',
//            ]
//        );
//        \DB::table('poll_types')->insert(
//            [
//                'name' => 'Eleição Tipo Mesa AFISVEC (candidatos são os próprios eleitores)',
//            ]
//        );
        \DB::table('polls')->insert(
            [
                'poll_type_id' => 2,
                'code' => 'Mesa1-2021',
                'name' => 'Primeira Mesa da Eleição 2021',
                'start' => '2021-04-30',
                'end' => '2021-05-31',
                'created_at' => now(),
                'active' => true,
            ]
        );
        \DB::table('polls')->insert(
            [
                'poll_type_id' => 2,
                'code' => 'Mesa2-2021',
                'name' => 'Segunda Mesa da Eleição 2021',
                'start' => '2021-04-30',
                'end' => '2021-05-31',
                'created_at' => now(),
                'active' => true,
            ]
        );
        \DB::table('polls')->insert(
            [
                'poll_type_id' => 2,
                'code' => 'Mesa3-2021',
                'name' => 'Terceira Mesa da Eleição 2021',
                'start' => '2021-04-30',
                'end' => '2021-05-31',
                'created_at' => now(),
                'active' => true,
            ]
        );
        \DB::table('polls')->insert(
            [
                'poll_type_id' => 2,
                'code' => 'Mesa4-2021',
                'name' => 'Quarta Mesa da Eleição 2021',
                'start' => '2021-04-30',
                'end' => '2021-05-31',
                'created_at' => now(),
                'active' => true,
            ]
        );
        \DB::table('polls')->insert(
            [
                'poll_type_id' => 2,
                'code' => 'Mesa5-2021',
                'name' => 'Quinita Mesa da Eleição 2021',
                'start' => '2021-04-30',
                'end' => '2021-05-31',
                'created_at' => now(),
                'active' => true,
            ]
        );
    }
}
