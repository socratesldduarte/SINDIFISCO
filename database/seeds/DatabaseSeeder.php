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
                'name' => 'Eleição SINDIFISCO',
            ]
        );

//        $this->call(PoolSeeder::class);
//        $this->call(PoolQuestionSeeder::class);
//        $this->call(PoolQuestionOptionSeeder::class);
        $this->call(UserSeeder::class);
    }
}
