<?php

use Illuminate\Database\Seeder;

class ImoveisTableSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {

      DB::table('imoveis')->insert([
        'apelido' => 'SenFur',
        'logradouro' => 'Senador Furtado',
        'tipo_lograd' => 'rua',
        'numero' => 109,
        'complemento' => 'casa 2',
        'cep' => '20.000-001',
        'tipo_imov' => 'Casa de Vila',
      ]);

      DB::table('imoveis')->insert([
        'apelido' => 'CDutra',
        'logradouro' => 'Carmela Dutra',
        'tipo_lograd' => 'rua',
        'numero' => 76,
        'complemento' => 'apt. 201',
        'cep' => '20.000-001',
        'tipo_imov' => 'Apartamento',
      ]);

      DB::table('imoveis')->insert([
        'apelido' => 'Jacum',
        'logradouro' => 'Jacumã',
        'tipo_lograd' => 'rua',
        'numero' => 76,
        'complemento' => 'apt. 202',
        'cep' => '20.000-001',
        'tipo_imov' => 'Apartamento',
      ]);

      DB::table('imoveis')->insert([
        'apelido' => 'HLobo',
        'logradouro' => 'Haddock Lobo',
        'tipo_lograd' => 'rua',
        'numero' => 390,
        'complemento' => 'apt. 405',
        'cep' => '20.000-001',
        'tipo_imov' => 'Apartamento',
      ]);

      DB::table('imoveis')->insert([
        'apelido' => 'MFaust',
        'logradouro' => 'Mário Faustino',
        'tipo_lograd' => 'rua',
        'numero' => 390,
        'complemento' => 'apt. 405',
        'cep' => '20.000-001',
        'tipo_imov' => 'Apartamento',
      ]);
  }
}
