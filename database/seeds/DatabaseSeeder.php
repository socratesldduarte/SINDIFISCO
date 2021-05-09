<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('poll_types')->insert(
            [
                'name' => 'Eleição Tradicional AFISVEC',
            ]
        );
        \DB::table('poll_types')->insert(
            [
                'name' => 'Eleição Tipo Mesa AFISVEC (candidatos são os próprios eleitores)',
            ]
        );

//        $this->call(PoolSeeder::class);
//        $this->call(PoolQuestionSeeder::class);
//        $this->call(PoolQuestionOptionSeeder::class);
        $this->call(UserSeeder::class);
    }
}
