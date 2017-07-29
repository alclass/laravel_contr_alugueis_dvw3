<?php
namespace App\Models\Finance;
// To import class LMadeiraPagto elsewhere in the Laravel App
// use App\Models\Finance\TimeEvolveParcel;

use App\Models\Finance\CorrMonet;
use Carbon\Carbon;

class TimeEvolveParcel {

  const JUROS_FIXOS_AM_CONVENCIONADOS = 0.01;

  /*
    =================================
      Beginning of Static Methods
    =================================
  */

  /*
    =================================
      End of Static Methods
    =================================
  */

  public $loan_ini_date           = null;
  public $loan_ini_value          = null;
  public $loan_duration_in_months = null;
  public $saldo                   = null;
  public $restart_date            = null;
  public $rows                    = null;
  public $pmt_prestacao_mensal_aprox_until_payment_end = null;

  public function __construct(
      $loan_ini_date = null,
      $loan_ini_value=2000,
      $loan_duration_in_months=24
    ) {
    $this->loan_ini_date  = $loan_ini_date;
    if ($loan_ini_date == null) {
      $this->loan_ini_date  = new Carbon('2017-04-01');
    }
    $this->loan_ini_value = $loan_ini_value;
    $this->loan_duration_in_months = $loan_duration_in_months;
    // $loan_ini_date = new Carbon('2017-04-01');
    $this->saldo = $this->loan_ini_value;
    $this->restart_date = $this->loan_ini_date->copy();
    $this->rows = array();
    $this->pmt_prestacao_mensal_aprox_until_payment_end = 0;
    $this->generate_amortization_table();
  }

  public function generate_amortization_table() {
    /*
      This method organize the "chain-process" that
        the constructor itself dispatches at instanciation.

      The resulting $this->rows is publicly available,
        ie, $obj->rows
    */

    $this->get_time_evolve_parcels_with_existing_payments();
    $this->complete_amortization_table();

  } // ends generate_amortization_table()

  public function generate_month_row_position($monthrefdate) {

    // just a protection, monthref's have day=1 by convention
    $monthrefdate->day(1);
    $last_day_in_month_date = DateFunctions
      ::get_total_days_in_specified_month($monthrefdate);
    $lmadeira_pagtos = LMadeiraPagto
      ::where('paydate', '>=', $monthrefdate)
      ->where('paydate', '<=', $last_day_in_month_date)
      ->get();
    $this->restart_date = $monthrefdate->copy()->addDays(-1); // ie, last day of previous month
    $row = array();
    foreach ($lmadeira_pagtos as $lmadeira_pagto) {
      $paydate  = $lmadeira_pagto->paydate;
      $abatido = $lmadeira_pagto->valor_pago;
      // $n_elapsed_days = $monthrefdate->diffInDays($payday);
      $n_elapsed_days = $this->restart_date->diffInDays($paydate);
      $this->restart_date = $paydate->copy(); // the new $restart_date
      $month_fraction = $n_elapsed_days / $last_day_in_month_date;
      // convention for corr. monet. is M-1 (M minus one)
      $previous_monthdate = $monthrefdate->copy()->addMonths(-1);
      $corrmonet_fraction = self::get_corr_monet_for_month_or_average($previous_monthdate);
      $correction_fraction = $corrmonet_fraction * $month_fraction;
      $montante_corregido = $this->saldo * (1 + $correction_fraction);
      $novo_saldo = $montante_corregido - $abatido;
      // Fill in $row
      $row['montante']            = $this->saldo;
      $row['corrmaisjuros_perc']  = $this->$correction_fraction * 100;
      $row['montante_corrigido']  = $montante_corregido;
      $row['abatido_data']        = $paydate;
      $row['abatido']             = $abatido;
      $row['saldo']               = $novo_saldo;
      $this->rows[] = $row;
      $this->saldo = $novo_saldo;
    } // ends foreach

    if (count($lmadeira_pagtos)==0) {
      // false means no $lmadeira_pagto was found
      return false;
    }
    // true means some $lmadeira_pagto was found
    return true;
  } // ends generate_month_row_position()

  public static function close_month($monthrefdate) {

    $previous_monthdate = $monthrefdate->copy()->addMonths(-1);
    if ($this->restart_date != $previous_monthdate) {
      $n_elapsed_days = $this->restart_date->diffInDays($monthrefdate);
      $this->restart_date = $monthrefdate->copy(); // the new $restart_date
      $last_day_in_month_date = DateFunctions
        ::get_total_days_in_specified_month($previous_monthdate);
      $month_fraction = $n_elapsed_days / $last_day_in_month_date;
    } else {
      $month_fraction = 1;
    }
    $corrmonet_fraction = self::get_corr_monet_for_month_or_average($previous_monthdate);
    $correction_fraction = $corrmonet_fraction * $month_fraction;
    $montante_corregido = $this->saldo * (1 + $correction_fraction);
    $novo_saldo = $montante_corregido - $abatido;
    // Fill in $row
    $row['montante']            = $this->saldo;
    $row['corrmaisjuros_perc']  = $this->$correction_fraction * 100;
    $row['montante_corrigido']  = $montante_corregido;
    $row['abatido_data']        = $paydate;
    $row['abatido']             = $abatido;
    $row['saldo']               = $novo_saldo;
    $this->rows[] = $row;
    $this->saldo = $novo_saldo;
    $this->restart_date = $monthrefdate->copy();
  } // ends close_month()

  public static function get_time_evolve_parcels_with_existing_payments() {

    $msg_or_info = '';

    $column_keys = ['montante', 'corrmaisjuros_perc', 'montante_corrigido',
                    'abatido_data', 'abatido', 'saldo'];
    $this->saldo    = $this->loan_ini_value;
    $monthrefdate  = $loan_ini_date->copy();
    $n_lmadeira_pagtos = LMadeiraPagto::count();
    for ($pagto_i=1; $month <= $n_lmadeira_pagtos; $pagto_i++) {
      $found_any = self::generate_month_row_position($monthrefdate);
      /*      if ($found_any == false) {
        $this->restart_date = $monthrefdate->copy($monthrefdate);
      }      */
      $this->close_month($monthrefdate);
      $monthrefdate->addMonths(1);
    }

    return view('finance.tabelasacprice', [
      'column_keys'   => $column_keys, 'rows' => $rows,
      'loan_ini_date' => $loan_ini_date,
      'msg_or_info'   => $msg_or_info,
    ]);
  } // ends get_time_evolve_parcels_table()


public function complete_amortization_table() {

  $n_months_used = $this->loan_ini_date->diffInMonths($this->restart_date);
  $n_remaining_months = $this->loan_duration_in_months - $n_months_used;
  $interest_rate_aprox = 1.5;

  $this->pmt_prestacao_mensal_aprox_until_payment_end = FinanceFunctions
    ::calc_monthly_payment_pmt(
      $this->saldo,
      $n_remaining_months,
      $interest_rate_aprox
    );

} // ends complete_amortization_table()

public function f() {

  $n_iterations = 0;
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
  } // ends while
} // ends f()

}  // ends class TimeEvolveParcel
