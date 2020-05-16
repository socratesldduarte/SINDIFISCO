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
                'email' => 'socrates@swge.com.br',
                'mobile' => '+55(34)99670-6509',
                'administrator' => true,
                'committee' => false,
                'password' => bcrypt('senha'),
            ]
        );
    }
}
