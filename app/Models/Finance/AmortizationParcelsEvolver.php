<?php
namespace App\Models\Finance;
// To import class AmortizationParcelsEvolver elsewhere in the Laravel App
// use App\Models\Finance\AmortizationParcelsEvolver;

use App\Models\Finance\CorrMonet;
use App\Models\Finance\MercadoIndice;
use App\Models\Utils\DateFunctions;
use App\Models\Utils\FinancialFunctions;
use Carbon\Carbon;

class AmortizationParcelsEvolver {


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
  public $balance_date            = null;
  public $saldo                   = null;
  public $rows                    = null;
  public $pmt_prestacao_mensal_aprox_until_payment_end = null;
  public $n_remaining_months_on_pmt = null;
  public $interest_rate_pmt_aprox = null;
  public $borrowers_paybacks_qb   = null;
  public $msg_or_info             = null;

  public $column_keys = [
    'balance_date',
    'montante',
    'corrmonet_mes_perc',
    'corrmonet_aplic_dias_perc',
    'cm_n_juros_aplic_dias_perc',
    'montante_corrigido',
    'abatido',
    'saldo',
  ];

  public function __construct(
      $loan_ini_value,
      $loan_ini_date,
      $loan_duration_in_months,
      $borrowers_paybacks_qb
    ) {
    $this->loan_ini_date  = $loan_ini_date;
    if ($this->loan_ini_date == null) {
      $this->loan_ini_date  = new Carbon('2017-04-15');
    }
    $this->loan_ini_value = $loan_ini_value;
    $this->loan_duration_in_months = $loan_duration_in_months;
    // $loan_ini_date = new Carbon('2017-04-01');
    $this->saldo = $this->loan_ini_value;
    $this->balance_date = $this->loan_ini_date->copy();
    $this->rows = array();
    $row['balance_date']        = $this->loan_ini_date;
    $row['montante']            = $this->saldo;
    $row['corrmonet_perc']             = 0;
    $row['corrmonet_aplic_dias_perc']  = 0;
    $row['juros_am_perc']              = 0;
    $row['cm_n_juros_aplic_dias_perc'] = 0;
    $row['montante_corrigido'] = $this->loan_ini_value;
    $row['abatido']            = 0;
    $row['saldo']              = $this->saldo;
    $this->rows[] = $row;
    $this->pmt_prestacao_mensal_aprox_until_payment_end = 0;
    $this->msg_or_info  = 'Cálculo de Amortização de Financiamento';
    // Clone incoming QueryBuilder to avoiding side-effect against both sides that own the reference
    // Clone will also be taken in every use, because the QueryBuilder mutates on chained use
    $this->borrowers_paybacks_qb = clone($borrowers_paybacks_qb);
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

  public function generate_month_row_position($from_day_in_month_date) {

    $last_day_in_month_date = DateFunctions::get_last_day_in_month_date($from_day_in_month_date);
    // Conserve original $this->borrowers_paybacks_qb
    $local_querybuilder = clone($this->borrowers_paybacks_qb);
    $paybacks = $local_querybuilder
      ->where('paydate', '>=', $from_day_in_month_date)
      ->where('paydate', '<=', $last_day_in_month_date)
      ->get();
    $row = array();
    foreach ($paybacks as $payback) {
      $paydate  = $payback->paydate;
      $abatido  = $payback->valor_pago;
      $n_elapsed_days = $this->balance_date->diffInDays($paydate);
      $last_day_in_month = $last_day_in_month_date->day;
      $month_fraction = $n_elapsed_days / $last_day_in_month;
      // convention for corr. monet. is M-1 (M minus one)
      $previous_monthrefdate = $from_day_in_month_date->copy()
        ->addMonths(-1)->day(1)->setTime(0,0,0);
      $corrmonet_month_fraction_index = CorrMonet
        ::get_corr_monet_for_month_or_average($previous_monthrefdate);
      $applied_corrmonet_fraction = $corrmonet_month_fraction_index * $month_fraction;
      $juros_am_perc = MercadoIndice::get_default_juros_fixos_am_in_perc();
      $cm_n_juros_aplic_dias_perc = ($corrmonet_month_fraction_index + $juros_am_perc) * $month_fraction;
      $montante_corrigido = $this->saldo * (1 + $cm_n_juros_aplic_dias_perc);
      $novo_saldo = $montante_corrigido - $abatido;
      // Fill in $row
      $row['balance_date'] = $paydate; // $this->balance_date will receive this also
      $row['montante']     = $this->saldo;
      $row['corrmonet_perc']             = $corrmonet_month_fraction_index * 100;
      $row['corrmonet_aplic_dias_perc']  = $applied_corrmonet_fraction * 100;
      $row['juros_am_perc']              = $juros_am_perc;
      $row['cm_n_juros_aplic_dias_perc'] = $cm_n_juros_aplic_dias_perc * 100;
      $row['montante_corrigido'] = $montante_corrigido;
      $row['abatido']            = $abatido;
      $row['saldo']              = $novo_saldo;
      // Append row
      $this->rows[] = $row;
      $this->saldo = $novo_saldo;
      $this->balance_date = $paydate->copy(); // the new $balance_date
    } // ends foreach

    if (count($paybacks)==0) {
      // false means no $payback was found
      return false;
    }
    // true means some $payback was found
    return true;
  } // ends generate_month_row_position()

  public function close_month($last_day_in_month_date) {

    $n_elapsed_days = $this->balance_date->diffInDays($last_day_in_month_date);
    $total_days_in_month = $last_day_in_month_date->day;
    $month_fraction = 1;
    if ($n_elapsed_days < $total_days_in_month+1) {
      if ($this->balance_date->month == $last_day_in_month_date->month) {
        $month_fraction = $n_elapsed_days / $total_days_in_month;
      } // ends inner if
    } // ends outer if
    $previous_monthrefdate = $last_day_in_month_date->copy()
      ->addMonths(-1)->day(1)->setTime(0,0,0);
    $corrmonet_month_fraction_index = CorrMonet
      ::get_corr_monet_for_month_or_average($previous_monthrefdate);
    $applied_corrmonet_fraction = $corrmonet_month_fraction_index * $month_fraction;
    $juros_am_perc = MercadoIndice::get_default_juros_fixos_am_in_perc();
    $cm_n_juros_aplic_dias_perc = ($corrmonet_month_fraction_index + $juros_am_perc) * $month_fraction;
    $montante_corrigido     = $this->saldo * (1 + $cm_n_juros_aplic_dias_perc);
    $novo_saldo             = $montante_corrigido;

    // Fill in $row
    $row['balance_date']   = $last_day_in_month_date;
    $row['montante']       = $this->saldo;
    $row['corrmonet_perc'] = $corrmonet_month_fraction_index * 100;
    $row['juros_am_perc']  = $juros_am_perc;
    $row['corrmonet_aplic_dias_perc']  = $applied_corrmonet_fraction * 100;
    $row['cm_n_juros_aplic_dias_perc'] = $cm_n_juros_aplic_dias_perc * 100;
    $row['montante_corrigido'] = $montante_corrigido;
    $row['abatido']            = 0;
    $row['saldo']              = $novo_saldo;
    $this->rows[]       = $row;
    $this->saldo        = $novo_saldo;
    $this->balance_date = $last_day_in_month_date;
  } // ends close_month()

  public function get_time_evolve_parcels_with_existing_payments() {

    $this->saldo            = $this->loan_ini_value;
    $this->balance_date     = $this->loan_ini_date->copy();
    $from_day_in_month_date = $this->loan_ini_date->copy();
    // Conserve original $this->borrowers_paybacks_qb
    $local_querybuilder = clone($this->borrowers_paybacks_qb);
    $n_paybacks = $local_querybuilder->get()->count();
    $last_payment = $local_querybuilder
      ->orderBy('paydate', 'desc')
      ->first();
    $up_to_monthdate = $from_day_in_month_date->copy()->addMonths(1);
    if ($last_payment && $last_payment->paydate != null) {
      $up_to_monthdate = $last_payment->paydate->copy()->addMonths(1);
    }
    // To protect against infinite loop logical error
    $n_loop_interactions = 0;
    while ($from_day_in_month_date < $up_to_monthdate) {
      $last_day_in_month_date = DateFunctions::get_last_day_in_month_date($from_day_in_month_date);
      $found_payment = self::generate_month_row_position($from_day_in_month_date);
      /*      if ($found_payment == false) {
        $this->balance_date = $monthrefdate->copy($monthrefdate);
      }      */
      $this->close_month($last_day_in_month_date);
      $from_day_in_month_date->addMonths(1)->day(1);
      // Protect against infinite loop logical error
      $n_loop_interactions += 1;
      if ($n_loop_interactions > 10000) {
        break;
      }
    } // ends while

  } // ends get_time_evolve_parcels_table()

  public function complete_amortization_table() {

    $n_months_used = $this->loan_ini_date->diffInMonths($this->balance_date);
    $this->n_remaining_months_on_pmt = $this->loan_duration_in_months - $n_months_used;
    $this->interest_rate_pmt_aprox = 0.015;

    $this->pmt_prestacao_mensal_aprox_until_payment_end = FinancialFunctions
      ::calc_monthly_payment_pmt(
        $this->saldo,
        $this->n_remaining_months_on_pmt,
        $this->interest_rate_pmt_aprox
      );

    } // ends complete_amortization_table()

  public function clone_borrowers_paybacks_qb() {
    return clone($this->borrowers_paybacks_qb);
  } // ends clone_borrowers_paybacks_qb()

}  // ends class AmortizationParcelsEvolver
