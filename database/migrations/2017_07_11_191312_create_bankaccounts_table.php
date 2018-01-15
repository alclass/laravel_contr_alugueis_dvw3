<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBankaccountsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('bankaccounts', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('banknumber')->smallint()->primary_key();
			$table->string('bankname', 30);
			$table->integer('agency')->smallint();
			$table->string('account', 20);
			$table->string('customer', 40)->nullable();
			$table->string('cpf', 11)->nullable();
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
		Schema::drop('bankaccounts');
	}

}
