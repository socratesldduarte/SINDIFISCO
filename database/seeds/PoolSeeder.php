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
        \DB::table('polls')->insert(
            [
                'poll_type_id' => 1,
                'code' => 'SINDIFISCO-RS-2022',
                'name' => 'Eleição Diretoria SINDIFISCO-RS 2022',
                'start' => '2022-04-16',
                'end' => '2022-04-20 23:59',
                'created_at' => now(),
                'active' => true,
            ]
        );
    }
}
