<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('users', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('first_name');
			$table->string('middle_name');
			$table->string('last_name');
			$table->string('cpf');
			$table->string('email')->unique();
			$table->string('password', 60);
			$table->rememberToken();
			// $table->timestamps(); // doesn't work from MySQL v.5.7 onwards with strict mode turned on (the default) 
			$table->nullableTimestamps(); // alternative to problematic line above
			// $table->timestamp()->useCurrent();
			// $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));						
			
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('users');
	}

}
