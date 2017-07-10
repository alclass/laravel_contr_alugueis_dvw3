<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserImovelTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('user_imovel', function(Blueprint $table)
		{
			$table->integer('imovel_id')->unsigned()->nullable();
      $table->foreign('imovel_id')->references('id')
            ->on('imoveis')->onDelete('cascade');

      $table->integer('user_id')->unsigned()->nullable();
      $table->foreign('user_id')->references('id')
            ->on('users')->onDelete('cascade');

			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('user_imovel');
	}

}
