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
			$table->decimal('paid_amount', 9, 2);
			$table->date('paydate');
			$table->date('monthrefdate');
			$table->tinyInteger('monthseqnumber')->unsigned()->default(1);
			$table->integer('contract_id', 3)->unsigned()->nullable();
			// add foreign key to bankaccount_id
			// $table->tinyInteger('bankdeposit_id', 3)->nullable();

			// $table->integer('user_id')->unsigned()->index();
			// add foreign key to user_id

			$table->integer('contract_id')->unsigned()->index()->nullable();
			// add foreign key to contract_id

			$table->nullableTimestamps();

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
