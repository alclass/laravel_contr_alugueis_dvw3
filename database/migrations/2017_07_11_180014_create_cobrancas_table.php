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
			$table->integer('mainmonthref')->tinyint()->nullable;
			$table->date('duedate')->nullable;
			$table->integer('user_id')->nullable;
			$table->integer('imovel_id')->nullable;
			$table->integer('contract_id')->nullable;
			$table->integer('bank_account_recipient')->nullable;
			$table->integer('n_parcelas')->tinyint()->nullable;
			$table->boolean('are_parcels_monthly')->nullable;
			$table->integer('parcel_n_days_interval')->tinyint()->nullable;
			$table->boolean('has_been_paid')->default(0);
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
		Schema::drop('cobrancas');
	}

}
