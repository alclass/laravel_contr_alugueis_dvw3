<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('payments', function(Blueprint $table)
		{
			$table->increments('id');
			$table->decimal('amount', 8, 2);
			$table->string('bankname', 15)->nullable();
			$table->date('deposited_on');
			$table->integer('user_id')->unsigned()->index();
			$table->integer('imovel_id')->unsigned()->index()->nullable();
			$table->timestamps();

			/*
			$table->foreign('user_id')
				->references('id')->on('users')
				->references('id')->onDelete('cascade');
			*/

		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('payments');
	}

}
