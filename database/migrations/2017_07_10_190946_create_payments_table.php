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

			$table->tinyInteger('bankaccount_id', 3)->nullable();
			// add foreign key to bankaccount_id

			$table->date('deposit_date');
			$table->string('bankrefstring', 50)->nullable();

			$table->integer('user_id')->unsigned()->index();
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
