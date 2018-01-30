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
			$table->integer('contract_id')->nullable();
			$table->integer('bankaccount_id')->nullable();
			$table->date('duedate')->nullable();
			$table->decimal('total_amount_paid', 9, 2)->nullable();
			$table->decimal('amount_paid_ontime', 9, 2)->nullable();
			$table->decimal('debt_to_next_bill', 9, 2)->nullable();
			$table->date('lastprocessingdate')->nullable();
			$table->text('amountincreasetrailsjson')->nullable();
			$table->text('obsinfo')->nullable();
			$table->boolean('closed')->default(0);
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
