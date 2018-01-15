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
			$table->date('monthyeardateref');
			$table->tinyInteger('n_seq_from_dateref')->default(1);
			$table->date('duedate')->nullable();
			$table->decimal('discount', 9, 2)->nullable();
			$table->decimal('price_increase_if_any', 9, 2)->nullable();
			$table->string('lineinfo_discount_or_increase', 144)->nullable();
			$table->decimal('tot_adic_em_tribs', 9, 2)->nullable();
			$table->tinyInteger('n_items')->nullable();

			$table->integer('contract_id')->nullable();
			//  foreign key

			$table->integer('bankaccount_id')->nullable();
			//  foreign key

			$table->tinyInteger('n_parcelas')->unsigned()->default(1);
			$table->boolean('are_parcels_monthly')->nullable();
			$table->smallInteger('parcel_n_days_interval')->unsigned()->nullable();
			$table->boolean('has_been_paid')->default(0);
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
