<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('payments', function(Blueprint $table)
		{
			$table->increments('id');
			$table->decimal('paid_amount', 9, 2);
			$table->date('paydate');
			$table->integer('cobranca_id')->unsigned()->nullable(); // foreign key

			/*
			Special Cases
			=============

			[1] More than one payment on a single day
			=========================================
			Because payment-record registers a unique date,
			 	and also because more than one payment might
				be done on the same day, we need a composite field, ie the json
				field below, to register each payment issued (bank or physical).
			Thus, more than one payment will be integrated into one record.

			Example: suppose one payee does two bank deposits on
				the same day, say, one in check, one in species.
				In this particular case, the two deposits will
				be registered in the json data inside the text field
				'bankrecordsjson' below.

			The json struture is as follows:
			{'paydate':<date>, 'paid_amount':<value>, 'bankaccount_id':<bid>,
		   'seqorder_onday': <seq>, 'bankdocline': <banksdocstring>,
		   'payeesname': <name>, 'paytype': <transfer|dep-money|dep-cheque>}
			*/
			$table->text('bankrecordsjson')->nullable();
			$table->string('bankrefstring', 50)->nullable(); // null when bankrecordsjson is used

			/*
			Special Cases
			=============

			[2] One single payment split into two bills
			===========================================
			The other special case is when a single payment must be split
			into TWO bill payments.

			When this happens, the fields below:
				+ is_payment_split_into_two_bills => should be 'true'
				+ complete_paid_amount => should have the full payment value
				+ linked_to_payment_id => should have the id of the complement bill
			*/
			$table->boolean('is_payment_split_into_two_bills')->default(false)->nullable();
			$table->decimal('complete_paid_amount', 9, 2)->nullable();
			$table->integer('linked_to_payment_id')->unsigned()->nullable();

			$table->tinyInteger('bankaccount_id')->unsigned()->nullable(); // foreign key
			$table->integer('user_id')->unsigned()->index(); // payee, foreign key
			$table->integer('contract_id')->unsigned()->nullable(); // foreign key
			$table->nullableTimestamps();
			/*
			$table->foreign('user_id')
				->references('id')->on('users')
				->references('id')->onDelete('cascade');
			*/

		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('payments');
	}

}
