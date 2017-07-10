<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImoveisTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('imoveis', function(Blueprint $table)
		{
			// ['logradouro', 'tipo_lograd', 'numero', 'complemento', 'cep', 'tipo_imov'];
			$table->increments('id');
			$table->string('apelido');
			$table->string('logradouro');
			$table->string('tipo_lograd');
			$table->integer('numero');
			$table->string('complemento');
			$table->string('cep');
			$table->string('tipo_imov');
			$table->boolean('is_rentable');

			// $table->timestamps();
			$table->nullableTimestamps(); // alternative to problematic line above
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('imoveis');
	}

}
