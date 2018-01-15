<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContractsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('contracts', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('imovel_id');
			$table->decimal('rent_value', 8, 3)->nullable();
			$table->integer('pay_day_when_monthly')->smallint()->nullable();
			$table->integer('percentual_multa')->tinyint()->nullable();
			$table->integer('percentual_juros')->tinyint()->nullable();
			$table->boolean('aplicar_corr_monet')->nullable();
			$table->date('signing_date')->nullable();
			$table->date('start_date')->nullable();
			$table->integer('duration_in_months')->tinyint()->nullable();
			$table->integer('duration_in_days')->smallint()->nullable();
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
		Schema::drop('contracts');
	}

}
