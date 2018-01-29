<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCobrancasTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('cobrancas', function(Blueprint $table)
		{
			$table->increments('id');
			$table->date('monthrefdate');
			$table->tinyInteger('monthseqnumber')->default(1);
			$table->date('duedate')->nullable();
			$table->integer('contract_id')->nullable();
			$table->integer('bankaccount_id')->nullable();
			$table->integer('bankdeposit_id')->nullable();
			$table->boolean('has_been_closed')->default(0);
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
		Schema::drop('cobrancas');
	}

}
