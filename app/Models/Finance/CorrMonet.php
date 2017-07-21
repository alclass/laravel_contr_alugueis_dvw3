<?php
namespace App\Models\Finance;

// To import class CorrMonet elsewhere in the Laravel App
// use App\Models\Finance\CorrMonet;

use App\Models\Finance\MercadoIndice;
use Illuminate\Database\Eloquent\Model;

class CorrMonet extends Model {


  public static function generate_monthly_interest_array_fetching_SELIC_indices(
    $monthly_interest_rate,
    $monthyeardateref_ini,
    $monthyeardateref_fim
  ){

    $key_selic_indice4char = env('SELIC_INDICE4CHAR', MercadoIndice::K_INDICE4CHAR_SELIC);
    $mercadoindice_obj = MercadoIndice::where('indice4char', $key_selic_indice4char);
    if ($mercadoindice_obj == null) {
      // Before returning null, try secondly table corrmonets
      $does_indice4char_for_selic_exist = MercadoIndice::where('indice4char',$key_selic_indice4char)->exists();
      if ($does_indice4char_for_selic_exist == false) {
        return null;
      }
    }
    $selic_indice4char = $mercadoindice_obj->indice4char;

    return generate_monthly_interest_array_fetching_indices(
      $selic_indice4char,
      $monthly_interest_rate,
      $monthyeardateref_ini,
      $monthyeardateref_fim
    );

  } // ends [static] generate_monthly_interest_array_fetching_SELIC_indices()

  public static function generate_monthly_interest_array_fetching_indices(
      $indice_reajuste_4char,
      $monthly_interest_rate,
      $monthyeardateref_ini,
      $monthyeardateref_fim
    ) {

    $monthly_interest_array = array();

    $diff_date_in_months = $monthyeardateref_ini->diffInMonths($monthyeardateref_fim);
    // because they are ref-dates, ie conventioned to be day=1, 1 (one) must be added to the diff
    $diff_date_in_months += 1;
    $ongoing_month_ref = $monthyeardateref_ini->copy();
    for ($i=0; $i < $diff_date_in_months; $i++) {
      // if it's not found in database, it'll be zero
      // if it's found and is negative, let it be zero (see below)
      $corr_monet_fraction = 0;
      $corr_monet = self::where('monthyeardateref', $ongoing_month_ref)
        ->where('indice4char', $indice_reajuste_4char)
        ->first();
      if ($corr_monet!=null) {
        $corr_monet_fraction = $corr_monet->fraction_value;
        // if $corr_monet_fraction < 0, then let it be 0, not negative
        $corr_monet_fraction = ($corr_monet_fraction < 0 ? 0 : $corr_monet_fraction);
      }
      $monthly_interest_array[] = $monthly_interest_rate + $corr_monet_fraction;
      $ongoing_month_ref->addMonths(1);
    } // ends for ($i=0; $i < $diff_date_in_months; $i++)

    return $monthly_interest_array;

  } // ends [static] generate_monthly_interest_array_fetching_indices()

  public static function calculate_final_montant_with_initial_montant_within_date_range(
      $indice_reajuste_4char,
      $monthly_interest_rate,
      $initial_montant,
      $monthyeardateref_ini,
      $monthyeardateref_fim,
      $first_month_n_days = null,
      $last_month_n_days = null
    ) {

    /*
        docstring
    */

    $monthly_interest_array = self
      ::generate_monthly_interest_array_fetching_indices(
        $indice_reajuste_4char,
        $monthly_interest_rate,
        $monthyeardateref_ini,
        $monthyeardateref_fim
    );

    $first_month_as_partial_interest_fraction = null;
    if ($first_month_n_days != null) {
      $first_month_as_partial_interest_fraction = $first_month_n_days / 30;
    }

    $last_month_as_partial_interest_fraction = null;
    if ($last_month_n_days != null) {
      $last_month_as_partial_interest_fraction = $last_month_n_days / 30;
    }

    return FinantialFunctions::calculate_final_montant_with_monthly_interest_array(
      $initial_montant,
      $monthly_interest_array, // eg. [[0]=>0.04, [1]=>0.015, ...]
      $first_month_as_partial_interest_fraction, // eg. 14 days / 31 days = 0.45161290322581
      $last_month_as_partial_interest_fraction // eg. 15 days / 30 days = 0.5
    );
  } // ends [static] calculate_final_montant_with_monthly_interest_array()


  protected $table = 'corrmonets';

  protected $dates = [
    'monthyeardateref',
  ];

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
   protected $fillable = [
     'mercado_indicador_id',
     'indice4char',
     'tarifa_valor',
     'monthyeardateref',
   ];

   public function mercadoindice() {
     return $this->belongsTo('App\Models\Finance\MercadoIndice');
   }

}  // ends class CorrMonet extends Model
