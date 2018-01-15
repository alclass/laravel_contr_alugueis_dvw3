<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCobrancatiposTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('cobrancatipos', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('billing_type_brief_description', 20);
			$table->boolean('is_repasse')->default(0);
			$table->boolean('aplicar_percentual')->default(0);
			$table->boolean('percentual_a_aplicar')->default(0);
			$table->text('percentual_a_aplicar_descricao')->nullable();
			$table->text('billing_type_long_description')->nullable();
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
		Schema::drop('cobrancatipos');
	}

}
