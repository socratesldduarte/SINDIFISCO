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
                'nome' => 'Eleição 2020',
                'inicio' => '2020-04-30',
                'termino' => '2020-05-14',
                'created_at' => now(),
                'ativo' => true,
            ]
        );
    }
}
