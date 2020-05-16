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
        $this->call(UserSeeder::class);
//        $this->call(PoolSeeder::class);
//        $this->call(PoolQuestionSeeder::class);
//        $this->call(PoolQuestionOptionSeeder::class);
    }
}
