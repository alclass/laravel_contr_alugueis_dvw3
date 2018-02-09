<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBankaccountsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('bankaccounts', function(Blueprint $table) {
			$table->increments('id');
			$table->smallInteger('banknumber')->unsigned();
			$table->char('bank4char', 4);
			$table->string('bankname', 30);
			$table->string('agency', 12);
			$table->string('midaccount', 4);
			$table->string('account', 20);
			$table->string('customer', 50)->nullable();
			$table->string('cpf', 11)->nullable();
			$table->nullableTimestamps();
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
