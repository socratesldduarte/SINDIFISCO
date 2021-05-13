<?php

use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('users')->insert(
            [
                'document' => '99999999999',
                'able' => true,
                'name' => 'Administrador do Sistema',
                'email' => 'admin@swge.com.br',
                'birthday' => '2000-12-31',
                'mobile' => '+55(34)99670-6509',
                'administrator' => true,
                'committee' => false,
                'password' => bcrypt('senha'),
            ]
        );
//        \DB::table('users')->insert(
//            [
//                'document' => '39205037168',
//                'poll_id' => 5,
//                'able' => true,
//                'name' => 'Sócrates Duarte',
//                'email' => 'socrates@swge.com.br',
//                'mobile' => '+55(34)99670-6509',
//                'administrator' => false,
//                'committee' => false,
//                'password' => bcrypt('senha'),
//            ]
//        );
    }
}
