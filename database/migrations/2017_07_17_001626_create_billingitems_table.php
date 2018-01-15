<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillingitemsTable extends Migration
{

  public $cobrancatipo_id;
  public $brief_description;
  public $item_value;
  public $modified_value;
  public $value_modifier_brief_descriptor_if_any;
  public $ref_obj;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('billingitems', function (Blueprint $table) {
            $table->increments('id');
            $table->tinyInteger('cobrancatipo_id')->unsigned()->foreign_key('cobrancatipo');
            $table->string('brief_description', 20); // a copy of cobrancatipo.brief_description
            $table->decimal('charged_value', 9, 2);
            $table->char('ref_type', 1)->default('D'); // D = Date; C = Cota (Parcel); B = Both (Date & Parcel)
            $table->char('freq_used_ref', 1)->default('M'); // M = Monthly; Y = Yearly
            $table->date('monthyeardate_ref')->nullable();
            $table->tinyInteger('n_cota_ref')->unsigned()->nullable();
            $table->tinyInteger('total_cotas_ref')->unsigned()->nullable();
            $table->boolean('was_original_value_modified')->default(0);
            $table->string('brief_description_for_modifier_if_any', 20); // a copy of cobrancatipo.brief_description
            $table->decimal('original_value_if_needed', 9, 2)->nullable();
            $table->tinyInteger('percent_in_modifying_if_any')->nullable();
            $table->decimal('money_amount_in_modifying_if_any')->nullable();
            $table->string('obs', 144);
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
        Schema::dropIfExists('billingitems');
    }
}
