<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIptutabelasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('iptutabelas', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('imovel_id')->foreign_key('imoveis');
            $table->boolean('is_parcela_em_10x')->default(true);
            $table->smallInteger('ano')->unsigned();
            $table->decimal('iptu_parcela', 6, 2);
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
        Schema::dropIfExists('create_iptutabelas_table');
    }
}
