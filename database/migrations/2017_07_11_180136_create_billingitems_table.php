<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillingitemsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('billingitems', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('cobranca_id');
			$table->integer('monthref')->tinyint()->nullable;
			$table->string('other_ref_if_any', 10)->nullable;
			$table->decimal('value', 5, 3);
			$table->integer('billing_item_type_id');
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
		Schema::drop('billingitems');
	}

}
