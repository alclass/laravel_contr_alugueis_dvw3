<?php
namespace App\Models\Immeubles;

// To import class Contract elsewhere in the Laravel App
// use App\Models\Immeubles\ContractMora

use App\Models\Finance\CorrMonet;
use App\Models\Utils\DateFunctions;
use App\Models\Utils\FinancialFunctions;
use Carbon\Carbon;

class ContractMora  {

  /*

    Explanation of how this class is designed.

    The mora calculation is based on 'ref' months and days until payment.

    [1]
    Let us see a more general example:

    If month_ref May is not paid on dueday-June, mora on it will begin counting on Jun-01 until payment.
    Because payment is not known, the system outputs the adjusted value everyday.

    [1]
    Now let us give a more specific example:

    Suppose:
      month_ref May  is not paid on the 10th of June
      month_ref June is not paid on the 10th of July
      month_ref July is late until the 15th of August
    => all three months are paid on the 15th of August

    There are 3 moras above.  They are:

    mora(May):
      interest applies from Jun/01 to Aug/15
      monthfractions_tuplelist will be: [[Jun,1],[Jul,1],[Aug,15/31]]
      corrmonetmonthfractions_tuplelist will be: [[corrmonet-May,1],[corrmonet-Jun,1],[corrmonet-Jul,15/31]]
      ie, the corrmonetmonthfractions_tuplelist take the corr.monet. M - 1 (*)
      fraction 15/31 = 0.48387096774194

    mora(Jun):
      interest applies from Jul/01 to Aug/15
      monthfractions_tuplelist will be: [[Jul,1],[Aug,15/31]]

    mora(Jul):
      interest applies from Aug/01 to Aug/15
      monthfractions_tuplelist will be: [[Aug,15/31]]

    (*) Corr. Monet. is M-1
    ie, M minus 1. Eg, the Jun applied corr. monet. index is, in fact, May corr. monet.)

    ***
    This class deals with ONLY one mora.  The class that compounds the 3 moras is MoraDebito.
    MoraDebito is a model class linked to db-table moradebitos.

    The 3 moras in the example will be 3 rows in the db-table. MoraDebito, in this sense,
      depends on this class to make the calculations mora by mora.


    The basic calculation will be held in static methods (probably in App\Utils\FinancialFunctions)
      that will be supported with unit tests.

  */

  private $contract = null; // if null, raise/throw exception
  private $monthrefdate_ini = null; // if null, default to conventional $monthrefdate
  private $monthrefdate_fim = null; // if null, default to conventional $monthrefdate
  private $begin_interest_on_date  = null; // if null, defaults to $monthrefdate_ini->copy()->addMonths(1)
  private $finish_interest_on_date = null; // if null, defaults to $monthrefdate_ini->copy()->addMonths(1)->addDays(duedate)
  private $monthrefdates_list  = null; // to be derived from $monthrefdate_ini & $monthrefdate_fim
  private $composedmorafraction_list = null;
  private $corrmonetfraction_list = null;

  public function __construct(
      $contract,
      $monthrefdate_ini,
      $monthrefdate_fim = null,
      $begin_interest_on_date = null,
      $finish_interest_on_date = null
    ) {
    $this->contract = $contract;
    $this->set_monthrefdate_ini($monthrefdate_ini);
    $this->set_monthrefdate_fim($monthrefdate_fim);
    $this->set_begin_interest_on_date($begin_interest_on_date);
    $this->set_finish_interest_on_date($finish_interest_on_date);
    $this->generate_once_monthrefdates_list();
    $this->generate_once_corrmonetfraction_list();
    $this->generate_once_composedmorafraction_list();
  }

  public function set_monthrefdate_ini($monthrefdate_ini) {
    $this->monthrefdate_ini = $monthrefdate_ini;
    if ($this->monthrefdate_ini == null) {
      $this->monthrefdate_ini = DateFunctions::find_conventional_monthrefdate_with_date_n_dueday();
      return;
    }
    $this->monthrefdate_ini->day(1);
    $this->monthrefdate_ini->setTime(0,0,0);
    $today = Carbon::today();
    if ($this->monthrefdate_ini > $today) {
      $error_msg = "Error: monthrefdate_ini=$this->monthrefdate_ini > today=$today";
      throw new Exception($error_msg, 1);
    }
  } // ends set_monthrefdate_ini()

  public function get_monthrefdate_ini() {
    return $this->monthrefdate_ini;
  }

  public function set_monthrefdate_fim($monthrefdate_fim) {
    $this->monthrefdate_fim = $monthrefdate_fim;
    if ($this->monthrefdate_fim == null) {
      $this->monthrefdate_fim = DateFunctions::find_conventional_monthrefdate_with_date_n_dueday();
    }
    if ($this->monthrefdate_ini > $this->monthrefdate_fim) {
      $error_msg = "monthrefdate_ini ($this->monthrefdate_ini) > monthrefdate_fim ($this->monthrefdate_fim)";
      throw new Exception($error_msg, 1);
    }
    $this->monthrefdate_fim->day(1);
    $this->monthrefdate_fim->setTime(0,0,0);
  } // ends set_monthrefdate_fim()

  public function get_monthrefdate_fim() {
    return $this->monthrefdate_fim;
  }

  public function set_begin_interest_on_date($begin_interest_on_date) {
    $this->begin_interest_on_date = $begin_interest_on_date;
    if ($this->begin_interest_on_date==null) {
      // this is the default $begin_interest_on_date, ie, one month after $monthrefdate_ini
      $this->begin_interest_on_date = $this->monthrefdate_ini->copy()->addMonths(1);
    }
  }

  public function get_begin_interest_on_date() {
    return $this->begin_interest_on_date;
  }

  public function is_begin_interest_date_on_future() {
    $today = Carbon::today();
    if ($this->begin_interest_on_date > $today) {
      return true;
    }
    return false;
  }

  public function set_finish_interest_on_date($finish_interest_on_date) {

    $this->finish_interest_on_date = $finish_interest_on_date;
    if ($this->finish_interest_on_date==null) {
      $this->finish_interest_on_date = Carbon::today();
    }
    if ($this->begin_interest_on_date != null) {
      if ($this->finish_interest_on_date < $this->begin_interest_on_date) {
        $this->finish_interest_on_date = $this->begin_interest_on_date->copy();
      } // ends inner if
    } // ends outer if
  } // ends set_finish_interest_on_date()

  public function get_finish_interest_on_date() {
    return $this->finish_interest_on_date;
  }

  public function generate_once_monthrefdates_list() {
    $this->monthrefdates_list = DateFunctions::get_ini_end_monthrefdates_list(
        $this->monthrefdate_ini,
        $this->monthrefdate_fim
      );
  } // ends generate_once_monthrefdates_list()

  public function get_monthrefdates_list() {
    return $this->monthrefdates_list;
  }

  public function is_last_month_full_interest() {
    return DateFunctions::is_date_on_last_day_of_month($this->finish_interest_on_date);
  }

  public function calc_monthfraction_in_last_mora_month() {

    if ( $this->is_last_month_full_interest() ) {
      return 1;
    }
    return DateFunctions::calc_fraction_of_n_days_in_specified_month(
      $this->finish_interest_on_date->day,
      $this->finish_interest_on_date
    );
  }

  public function find_mora_duration_in_tuple_n_months_n_additional_days() {
    $first_mora_month = $this->monthrefdate_ini->copy()->addMonths(1);
    $n_months = $first_mora_month->diffInMonths($this->finish_interest_on_date);
    $n_months += 1; $n_days = 0;
    if ($this->is_last_month_full_interest()) {
      // add yet another 1!
      $n_months += 1; // $n_days will remain 0;
    } else {
      $n_days = $this->finish_interest_on_date->day;
    }
    return [$n_months, $n_days];
  }

  public function find_mora_duration_n_months() {
    $tuple = $this->find_mora_duration_in_tuple_n_months_n_additional_days();
    if (count($tuple)==2) {
      return $tuple[0];
    }
    return null; // would seem a logical error in class, in the constructor init process, where attributes should carry consistency
  }

  public function find_mora_duration_n_additional_days() {
    $tuple = $this->find_mora_duration_in_tuple_n_months_n_additional_days();
    if (count($tuple)==2) {
      return $tuple[1];
    }
    return null; // would seem a logical error... see above find_n_months_mora_duration()
  }

  public function get_month_proportion_of_last_month() {
    $tuple = $this->find_mora_duration_in_tuple_n_months_n_additional_days();
    if (count($tuple)==2) {
      return $tuple[1];
    }
    return null; // would seem a logical error... see above find_n_months_mora_duration()
  }

  public function generate_once_corrmonetfraction_list() {

    if ($this->contract->apply_corrmonet_am == false) {
      return null;
    }
    $this->corrmonetfraction_list = array();
    foreach ($this->monthrefdates_list as $monthrefdate) {
      $corrmonetfractionvalue = 0;
      $corr_monet_obj = CorrMonet
        ::where('indice4char', $this->contract->corrmonet_indice4char)
        ->where('monthrefdate', $monthrefdate)
        ->first();
      // if it's not found, try average()
      if ($corr_monet_obj == null) {
        $average_corrmonet_fraction = CorrMonet::try_to_find_conventional_average_corrmonet($this->contract->corrmonet_indice4char);
        if ($average_corrmonet_fraction != null) {
          $corrmonetfractionvalue = $average_corrmonet_fraction;
        }
      } else {
        $corrmonetfractionvalue = $corr_monet_obj->fraction_value;
      }
      $this->corrmonetfraction_list[] = $corrmonetfractionvalue;
    } // ends foreach

  } // ends generate_once_corrmonetfraction_list()

  public function get_corrmonetfraction_list() {
    return $this->corrmonetfraction_list;
  }

  public function generate_once_composedmorafraction_list() {

    $this->composedmorafraction_list = array();
    if (empty($this->corrmonetfraction_list)) {
      return;
    }

    // In PHP, the line below is attribution by copy not by reference, so it's okay!
    $this->composedmorafraction_list = $this->corrmonetfraction_list;
    if ($this->contract->apply_multa_incid_mora) {
      $composedmorafraction = $this->composedmorafraction_list[0];
      $composedmorafraction += $this->contract->get_multa_incid_mora_in_fraction();
      $this->composedmorafraction_list[0] = $composedmorafraction;
    }

    if ($this->contract->apply_juros_fixos_am == false) {
      return;
    }
    $juros_fixos_am_in_fraction = $this->contract->get_juros_fixos_am_in_fraction();
    foreach ($this->composedmorafraction_list as $key=>$composedmorafraction) {
      $composedmorafraction += $juros_fixos_am_in_fraction;
      $this->composedmorafraction_list[$key] = $composedmorafraction;
    } // ends foreach

    $n_months = count($this->composedmorafraction_list);
    if ($n_months>0) {
      $monthfraction_in_last_mora_month = $this->calc_monthfraction_in_last_mora_month();
      if ($monthfraction_in_last_mora_month < 1) {
        $fraction = $this->composedmorafraction_list[$n_months-1];
        $this->composedmorafraction_list[$n_months-1] = $fraction * $monthfraction_in_last_mora_month;
      } // ends inner if
    } // ends outer if
  } // ends generate_once_composedmorafraction_list()

  public function get_composedmorafraction_list() {
    return $this->composedmorafraction_list;
  }

  public function calculate_mora_amount_for_month_i($amount, $i) {

    return;
  }

  public function calculate_mora_with_imontant(
      /*
        This method intends to adjust the first and last elements of
          $this->contract_mora_monthly_interest_array
        if either or both first and last months have a partial "vigência",
        ie, if the mora interest rate is fractional for these months

        There's no return for the array to be adjust belongs to $this
          ie, it's an inner instance attribute

        Eg.
          Suppose mora array is [0.017, 0.012, 0.011, 0.021]
          if first month January took 12 days and last month April took 19 days
          then the new mora array will become:
            [0.017*(12/31), 0.012, 0.011, 0.021*(19/30)]
            ie: [0.0065.., 0.012, 0.011, 0.0133]
      */
      $initial_montant
    ) {

    $fmontant = FinancialFunctions::calc_fmontant_from_imontant_n_interest_array(
      $initial_montant,
      $this->composedmorafraction_list
    );

    return $fmontant;
  } // ends calc_fmontant_from_imontant_n_interest_array()


  public function generate_mora_details($mora_debito) {

    /*

    TODO

    */

  }

  public function calculate_($mora_debito) {


  }



} // ends class ContractMora()
