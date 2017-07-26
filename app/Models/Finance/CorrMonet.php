<?php
namespace App\Models\Finance;

// To import class CorrMonet elsewhere in the Laravel App
// use App\Models\Finance\CorrMonet;

use App\Models\Finance\MercadoIndice;
use App\Models\Utils\FinancialFunctions;
use Illuminate\Database\Eloquent\Model;

class CorrMonet extends Model {

  /*
    =================================
      Beginning of Static Methods
    =================================
  */

  public static function try_to_find_conventional_average_corrmonet(
    $corrmonet_indice4char
    ) {
    /*

    *** TO YET IMPLEMENT ***

    */
    return 0.005;

  } // ends [static] fetch_monthly_corrmonet_fraction_index_array()

  public static function get_corrmonet_month_n_index_tuplelist_with_index4charid_n_daterange(
      $corrmonet_indice4char
      $ini_date,
      $end_date
    ) {
    /*

    */
    // get $monthyeardaterefs from DateFunctions
    $corrmonet_month_n_fractionindex_tuplelist = array();
    $monthyeardaterefs = DateFunctions::get_ini_fim_monthyeardaterefs_list($ini_date, $end_date);
    $corrmonet_monthly_indices = self
      ::where('corrmonet_indice4char', $corrmonet_indice4char)
      ->whereIn('monthyeardateref', 'in', $monthyeardaterefs)
      ->get();
    foreach ($corrmonet_monthly_indices as $$corrmonet_monthly_index_obj) {
      $corrmonet_month_n_index_tuple = array();
      // Assign monthyeardateref
      $corrmonet_month_n_index_tuple[]     = $corrmonet_monthly_index_obj->monthyeardateref;
      // Assign corrmonet_fraction
      $corrmonet_month_n_index_tuple[]     = $corrmonet_monthly_index_obj->corrmonet_fraction;
      // Push tuple (monthyeardateref, corrmonet_fraction) into tuplelist
      $corrmonet_month_n_fractionindex_tuplelist[] = $corrmonet_month_n_index_tuple;
    }
    return $corrmonet_month_n_fractionindex_tuplelist;

  } // ends [static] get_corrmonet_month_n_index_tuplelist_with_index4charid_n_daterange()

  public static function calc_latervalue_from_inivalue_w_ini_end_dates_n_corrmonet4charid(
      $initial_montant,
      $ini_date,
      $end_date = null,
      $corrmonet4charid = null
    ){
    /*

      IMPORTANT: this method uses CorrMonet
      This method
        calc_latervalue_from_inivalue_w_ini_end_dates_n_corrmonet4charid()
      calculates the time monetary-corrected value of an initial_montant.

      It reuses other static methods in here, they are:
        -> a method to gather the corrmonet monthly indices
        -> a method to get the month-fractions and fuse them into the corrmonet monthly indices
        -> a method to calculate the final_montant

    */
    if ($initial_montant == null || $initial_montant == 0) {
      return 0;
    }
    if ($ini_date == null) {
      return $initial_montant;
    }
    $end_date = ( $end_date != null ? $end_date : Carbon::today() );
    if ($ini_date >= $end_date) {
      return $initial_montant;
    }
    if ($corrmonet4charid == null) {
      $corrmonet4charid = env('DEFAULT_CORRMONET_4CHARID', CorrMonet::DEFAULT_CORRMONET_4CHARID);
      // if it's null...
      if ($corrmonet4charid == null) {
        return $initial_montant;
      }
    }
    $corrmonet_month_n_index_tuplelist = self
      ::get_corrmonet_month_n_index_tuplelist_with_index4charid_n_daterange(
        $corrmonet4charid,
        $ini_date,
        $end_date
      );
    $interest_array = DateFunctions
      ::correct_for_proportional_first_n_last_months_n_return_fractionarray(
        $corrmonet_month_n_index_tuplelist,
        $ini_date,
        $end_date
      );
    $final_montant = FinancialFunctions
      ::calc_fmontant_from_imontant_n_interest_array(
        $initial_montant,
        $interest_array
      );
    return $final_montant;
  } // ()


  /*
    =================================
      End of Static Methods
    =================================
  */

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
     'fraction_value',
     'monthyeardateref',
   ];


   public function mercadoindice() {
     return $this->belongsTo('App\Models\Finance\MercadoIndice');
   }

}  // ends class CorrMonet extends Model
