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
			$table->integer('cobranca_id')->unsigned(); // foreign-key
			$table->integer('carried_from_cobranca_id')->unsigned()->nullable(); // foreign-key

			// eg. ALUG, COND, IPTU, CARR, CRED etc
			$table->tinyInteger('cobrancatipo_id')->unsigned(); // foreign-key
			$table->string('brief_description', 30)->nullable();

			$table->date('monthrefdate')->nullable();
			$table->boolean('use_partnumber')->default(false);
			$table->tinyInteger('partnumber')->unsigned()->nullable();
			$table->tinyInteger('totalparts')->unsigned()->nullable();

			$table->decimal('value', 9, 3);

			$table->tinyInteger('variation_in_perc_if_any')->unsigned()->nullable();
			$table->decimal('variation_value_if_any', 8, 3)->unsigned()->nullable();
			$table->decimal('origvalue', 9, 3)->nullable();

			$table->boolean('was_original_value_modified')->default(false);
			$table->string('brief_description_for_modifier', 30)->nullable();
			$table->decimal('original_value', 9, 3)->nullable();
			// The field below is smallint because it may be negative
			$table->smallInteger('modifying_percent')->nullable();
			$table->decimal('modifying_amount', 8, 3)->nullable();
			$table->text('obsinfo')->nullable();
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
		Schema::drop('billingitems');
	}

}
