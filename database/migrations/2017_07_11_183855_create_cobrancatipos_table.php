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
			/*
				reftype accepts 3 letters, ie: D = Date, P = Parcel, B = Both Date and Parcel
					reftype defaults to D
					Eg. ALUG is D and IPTU is B
				freqtype accepts 3 letters, ie: M = Monthly, Y = Yearly, W = Weekly
					freqtype defaults to M
					Eg. ALUG is M and FUNE is Y
			*/
			$table->increments('id');
			$table->char('char4id', 4);
			$table->string('brief_description', 20)->nullable();
			// $table->boolean('is_repasse')->default(0);
			$table->char('reftype', 1)->default('D')->nullable();
			$table->char('freqtype', 1)->default('M')->nullable();
			$table->text('long_description')->nullable();
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
		Schema::drop('cobrancatipos');
	}
}
