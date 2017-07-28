<?php
namespace App\Models\Finance;

// To import class LMadeiraPagto elsewhere in the Laravel App
// use App\Models\Finance\TimeEvolveParcel;
use App\Models\Finance\CorrMonet;
use Illuminate\Database\Eloquent\Model;
// use Carbon\Carbon;

class TimeEvolveParcel {

  const JUROS_FIXOS_AM_CONVENCIONADOS = 0.01;

  /*
    =================================
      Beginning of Static Methods
    =================================
  */
  /*
    =================================
      Beginning of Instance Methods
    =================================
  */

  $load_ini_date            = null;
  $load_durantion_in_months = null;
  $saldo_date               = null;
  $row = null;

  public function __construct($load_ini_date, $load_durantion_in_months=24) {

    $this->load_ini_date = $load_ini_date;
    $this->load_durantion_in_months = $load_durantion_in_months;
    $this->saldo_date = $this->load_ini_date->copy();
    $this->row = array();

    $this->get_time_evolve_parcels_table();
  }

  public function generate_month_position($monthrefdate) {

    // just a protection, monthref's have day=1 by convention
    $monthrefdate->day(1);
    $last_day_in_month_date = DateFunctions::get_total_days_in_specified_month($monthrefdate);
    $lmadeira_pagtos = LMadeiraPagto
      ::where('payday', '>=', $monthrefdate)
      ->where('payday', '<=', $last_day_in_month_date)
      ->get();
    foreach ($lmadeira_pagtos as $lmadeira_pagto) {
      $payday  = $lmadeira_pagto->payday;
      $abatido = $lmadeira_pagto->valor_pago;
      $this->row['abatido'] = $abatido;

      $evolve_row['abatido'] = $abatido;
      $evolve_row['saldo_date'] = $payday;
      // $n_elapsed_days = $monthrefdate->diffInDays($payday);
      $time_amount_in_days = $restart_date->diffInDays($payday);
      $month_fraction = $n_elapsed_days / $last_day_in_month_date;
      $previous_monthdate = $monthrefdate->copy()->addMonths(-1);
      $corrmonet_ref_m_minus_1 = self::get_corr_monet_for_month($previous_monthdate);
      $corr_fraction = $corrmonet_ref_m_minus_1 * $month_fraction;
      $novo_saldo = $this->saldo * (1 + $corr_fraction);
    }

    $lmadeira_pagtos


  }

  public static function get_time_evolve_parcels_table() {

    $msg_or_info = '';

    $column_keys = ['value_ini_month', 'corrmonet_in_perc', 'corrected_value', 'abatido', 'saldo' ];
    $value_ini_month = 2000;
    $loan_ini_date = new Carbon('2017-04-01');
    $monthrefdate = new Carbon('2017-04-01');
    $current_monthyeardateref = $loan_ini_date->copy();
    $rows = array(); $saldo = 0; $n_iterations = 0;
    for ($month=1; $month <= 24; $month++) {
      self::generate_month_position($monthrefdate);
      $monthrefdate->addMonths(1);
    }

    while ($n_iterations < 23) { // while ($saldo < 0) {
      $n_iterations += 1;
      $current_monthyeardateref->addMonths(1);
      $corrmonet_obj = CorrMonet::where('indice4char', 'SELI')
        ->where('monthyeardateref', $current_monthyeardateref)
        ->first();
      if ($corrmonet_obj!=null) {
         $corrmonet_fraction = $corrmonet_obj->fraction_value;
      } else {
        // not found so default it to 0.005;
        $corrmonet_fraction = 0.005;
      }
      $juros_mais_corrmonet = $corrmonet_fraction + self::JUROS_FIXOS_AM_CONVENCIONADOS;

      $corrected_value = $value_ini_month * (1 + $juros_mais_corrmonet);
      $next_monthyeardateref = $current_monthyeardateref->copy()->addMonths(1);
      $lmadeira_pagtos = LMadeiraPagto
        ::where('payday', '>=', $current_monthyeardateref)
        ->where('payday', '<', $next_monthyeardateref)
        ->get();
      $abatido = 0;
      foreach ($lmadeira_pagtos as $lmadeira_pagto) {
        $abatido = $lmadeira_pagto->valor_pago;
      } // ends foreach


      $saldo = $corrected_value - $abatido;
      $row = array();
      // 'value_ini_month', 'corrmonet_in_perc', 'corrected_value', 'abatido', 'saldo'
      $row['value_ini_month']   = $value_ini_month;
      $row['corrmonet_in_perc'] = $corrmonet_fraction * 100;
      $row['corrected_value']   = $corrected_value;
      $row['abatido']           = $abatido;
      $row['saldo']             = $saldo;
      $rows[] = $row;
      $value_ini_month = $saldo;
      // Protection against too long or infinite loop
      if ($n_iterations > 1000) {
        break;
      }
    }
    return view('finance.tabelasacprice', [
      'column_keys'   => $column_keys, 'rows' => $rows,
      'loan_ini_date' => $loan_ini_date,
      'msg_or_info'   => $msg_or_info,
    ]);
  } // ends [static] get_time_evolve_parcels_table() {

  /*
    =================================
      End of Static Methods
    =================================
  */


  protected $table = 'lmadeirapagtos';

  protected $dates = [
    'payday',
  ];

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
   protected $fillable = [
     'payday',
     'valor_pago',
   ];

}  // ends class LMadeiraPagto extends Model
