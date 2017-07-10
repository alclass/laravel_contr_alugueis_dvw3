<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {

      DB::table('users')->insert([
        'first_name' => 'Elizabeth',
        'middle_names' => 'Elionor Elvira',
        'last_name' => 'Silva',
        'cpf' => '12345678909',
        'email' => 'beth@jacuma.com',
        'password' => bcrypt('1234'),
      ]);

      DB::table('users')->insert([
        'first_name' => 'Filipe',
        'middle_names' => 'Marcos',
        'last_name' => 'Teixeira',
        'cpf' => '12345678909',
        'email' => 'filipe@jacuma.com',
        'password' => bcrypt('1234'),
      ]);

      DB::table('users')->insert([
        'first_name' => 'Lucio',
        'middle_names' => 'Mauro Lucas',
        'last_name' => 'Lemes',
        'cpf' => '12345678909',
        'email' => 'lucio@hlobo.com',
        'password' => bcrypt('1234'),
      ]);

      DB::table('users')->insert([
        'first_name' => 'Munzer',
        'middle_names' => 'Syrian Arab',
        'last_name' => 'Isbelle',
        'cpf' => '12345678909',
        'email' => 'munzer@jacuma.com',
        'password' => bcrypt('1234'),
      ]);
  }
}
