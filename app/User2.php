<?php namespace App;
use App\User;

class User2 {

	public $inquilino;
	public $imovel_apelido;
	public $imovel_endereco;
	public $email;
	public $is_pay_on_date;


	public function __construct($inquilino) {
		$this->inquilino = $inquilino;
	}


	public static function get_sample() {

		$users = array();

		$user = new User2('Beth') ;
		$user->imovel_apelido = 'Jacum';
		$user->imovel_endereco = 'Rua Jacumã 76 apt. 202';
		$user->email = 'beth@jacuma.com';
		$user->is_pay_on_date = True;

		// append object to array-list
		$users[] = $user;

		$user = new User2('Lucio') ;
		$user->imovel_apelido = 'HLobo';
		$user->imovel_endereco = 'Rua Haddock Lobo 390 apt. 405';
		$user->email = 'lucio@haddocklobo.com';
		$user->is_pay_on_date = 'True';

		// append object to array-list
		$users[] = $user;

		$users = User::with('imoveis')->get();

		return $users;
	}


}
