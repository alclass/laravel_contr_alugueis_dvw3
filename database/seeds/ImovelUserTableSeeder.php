<?php

use Illuminate\Database\Seeder;

class ImovelUserTableSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {

      $user = User::where('first_name', 'Elizabeth')->get();
      $imovel = Imovel::where('apelido', 'Jacum')->get();
      $user->imovel = $imovel;

      $user = User::where('first_name', 'Lucio')->get();
      $imovel = Imovel::where('apelido', 'HLobo')->get();
      $user->imovel = $imovel;

      $user = User::where('first_name', 'Munzer')->get();
      $imovel = Imovel::where('apelido', 'CDutra')->get();
      $user->imovel = $imovel;

  }
}
