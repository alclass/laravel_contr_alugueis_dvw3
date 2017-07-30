<?php
namespace App\Models\Finance;

// To import class CorrMonet elsewhere in the Laravel App
// use App\Models\Finance\CorrMonet;

use App\Models\Finance\MercadoIndice;
use App\Models\Utils\DateFunctions;
use App\Models\Utils\FinancialFunctions;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CorrMonet extends Model {

  /*
    =================================
      Beginning of Static Methods
    =================================
  */

  public static function try_to_find_conventional_average_corrmonet(
    $corrmonet_indice4char = null
    ) {
    /*

    *** TO YET IMPLEMENT ***

    */
    return 0.005;

  } // ends [static] fetch_monthly_corrmonet_fraction_index_array()

  public static function get_month_n_fractionindex_tuplelist_w_char4indic_n_daterange(
      $corrmonet_char4indicator,
      $ini_date,
      $end_date = null
    ) {
    /*

    */
    if ($corrmonet_char4indicator == null) {
      $corrmonet_char4indicator = MercadoIndice
        ::get_default_char4indicator_for_corrmonet();
    }
    // get $monthyeardaterefs from DateFunctions
    $monthyeardaterefs = DateFunctions
      ::get_ini_end_monthyeardaterefs_list($ini_date, $end_date);
    // guard against null/empty (parameters $ini_date & $end_date will be checked in get_ini_end_monthyeardaterefs_list())
    if (empty($monthyeardaterefs)) {
      return null;
    }
    $corrmonet_month_n_fractionindex_tuplelist = array();
    $monthly_corrmonet_indices = self
      ::where('indice4char',    $corrmonet_char4indicator)
      ->whereIn('monthyeardateref', $monthyeardaterefs)
      ->orderBy('monthyeardateref', 'asc')
      ->get();
    foreach ($monthly_corrmonet_indices as $corrmonet_monthly_index_obj) {
      $corrmonet_month_n_index_tuple = array();
      // Assign `monthyeardateref` (the column name & attribute name are the same)
      $corrmonet_month_n_index_tuple[] = $corrmonet_monthly_index_obj->monthyeardateref;
      // Assign `fraction_value` (the column name & attribute name are the same)
      $corrmonet_month_n_index_tuple[] = $corrmonet_monthly_index_obj->fraction_value;
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
  } // ends [static] calc_latervalue_from_inivalue_w_ini_end_dates_n_corrmonet4charid()


  public static function get_corr_monet_for_month_or_average(
      $monthdate,
      $indice4char=null
    ) {

    if ($indice4char == null) {
      $indice4char = env('K_INDICE4CHAR_SELIC', MercadoIndice::K_INDICE4CHAR_SELIC);
    }
    // guarantee $monthdate is a $monthyeardateref
    $monthyeardateref = $monthdate->copy()->day(1)->setTime(0,0,0);
    $corrmonet_obj = self::where('indice4char', $indice4char)
      ->where('indice4char', $monthdate)
      ->first();
    if ($corrmonet_obj == null || $corrmonet_obj->fraction_value) {
      return self::try_to_find_conventional_average_corrmonet();
    }
    return $corrmonet_obj->fraction_value;

  } // ends [static] get_corr_monet_for_month_or_average()


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
