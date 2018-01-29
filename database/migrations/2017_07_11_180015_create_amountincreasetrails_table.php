<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAmountIncreaseTrailsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('amountincreasetrails', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('cobranca_id')->unsigned();
			$table->decimal('montant_ini', 7, 2)->nullable();
			$table->date('monthrefdate');
			$table->tinyInteger('monthseqnumber')->default(1);
			$table->date('restart_timerange_date');
			$table->date('end_timerange_date');
			$table->decimal('interest_rate', 6, 4)->nullable();
			$table->decimal('corrmonet_in_month', 6, 4)->nullable();
			$table->decimal('paid_amount', 7, 2)->nullable();
			$table->decimal('finevalue', 7, 2)->nullable();
			$table->integer('contract_id')->unsigned();
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
		Schema::drop('amountincreasetrails');
	}

}
