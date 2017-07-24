<?php
namespace App\Models\Immeubles;

// To import class Contract elsewhere in the Laravel App
// use App\Models\Immeubles\ContractMora

use App\Models\Utils\DateFunctions;
use Carbon\Carbon;
use Tests\Unit\DateFunctionsTest;

class ContractMora  {

  private $contract = null;
  private $monthyeardateref_ini = null;
  public $monthyeardateref_fim = null;
  private $first_day_in_first_month = null;
  public $last_day_in_last_month   = null;
  public $monthyeardaterefs_list   = null;
  public $months_n_monthdays_fraction_tuplelist = null;

  public function __construct(
      $contract,
      $monthyeardateref_ini,
      $monthyeardateref_fim = null,
      $first_day_in_first_month = 1,
      $last_day_in_last_month = null
    ) {
    $this->contract = $contract;
    $this->set_monthyeardateref_ini($monthyeardateref_ini);
    $this->set_monthyeardateref_fim($monthyeardateref_fim);
    $this->set_first_day_in_first_month($first_day_in_first_month);
    $this->set_last_day_in_first_month($last_day_in_first_month);
    $this->set_monthyeardaterefs_list();
    $this->set_months_n_monthdays_fraction_tuplelist();
  }

  public function set_monthyeardateref_ini($monthyeardateref_ini) {
    $this->monthyeardateref_ini = $monthyeardateref_ini;
    if ($this->monthyeardateref_ini == null) {
      $this->monthyeardateref_ini = DateFunctions::find_conventional_monthyeardateref_with_date_n_dueday();
      return;
    }
    $this->monthyeardateref_ini->day(1);
    $this->monthyeardateref_ini->setTime(0,0,0);
  } // ends set_monthyeardateref_ini()

  public function set_monthyeardateref_fim($monthyeardateref_fim) {
    $this->monthyeardateref_fim = $monthyeardateref_fim;
    if ($this->monthyeardateref_fim == null) {
      $this->monthyeardateref_fim = DateFunctions::find_conventional_monthyeardateref_with_date_n_dueday();
      return;
    }
    if ($this->monthyeardateref_ini > $this->monthyeardateref_fim) {
      $this->monthyeardateref_fim = $this->monthyeardateref_ini->copy();
      return;
    }
    $this->monthyeardateref_fim->day(1);
    $this->monthyeardateref_fim->setTime(0,0,0);
  } // ends set_monthyeardateref_fim()

  public function set_first_day_in_first_month($first_day_in_first_month) {
    if ($first_day_in_first_month==null) {
      $this->first_day_in_first_month = 1;
      return;
    }
    if ($first_day_in_first_month==1) {
      $this->first_day_in_first_month = 1;
      return;
    }
    $total_day_in_month = DateFunctions::get_total_days_in_specified_month($date);
    if ($first_day_in_first_month > $total_day_in_month) {
      $this->first_day_in_first_month = $total_day_in_month;
    }
    $this->first_day_in_first_month = $first_day_in_first_month;
  }

  public function set_monthyeardaterefs_list() {
    $this->monthyeardaterefs_list = DateFunctions
      ::get_ini_fim_monthyeardaterefs_list(
        $this->monthyeardateref_ini,
        $this->monthyeardateref_fim
      );
  } // ends set_monthyeardaterefs_list()

  public function set_months_n_monthdays_fraction_tuplelist() {

    if (empty($this->monthyeardaterefs_list)) {
      $this->months_n_monthdays_fraction_tuplelist = array();
      return;
    }

    // arrays are not copied by reference, so it's okay to attribute one to the other below
    $months_list = $this->monthyeardaterefs_list;
    if ($this->first_day_in_first_month != 1) {
      // update first month only if it's not 1, for monthyeardateref's always have day=1
      $date = $months_list[0];
      $total_day_in_month = DateFunctions::get_total_days_in_specified_month($date);
      if ($this->first_day_in_first_month <= $total_day_in_month) {
        $date->day($this->first_day_in_first_month);
        $months_list[0] = $date;
      } // ends inner if
    } // ends outer if
    $n_months = count($months_list);
    // update last month anyway because it's a monthyeardateref having its day always 1
    $date = $months_list[$n_months-1];
    $total_day_in_month = DateFunctions::get_total_days_in_specified_month($date);
    $date->day($total_day_in_month);
    if ($this->$last_day_in_last_month != null &&
        $this->$last_day_in_last_month <= $total_day_in_month) {
      $date->day($last_day_in_last_month);
    } else {
      $this->$last_day_in_last_month = $total_day_in_month;
    }
    $months_list[$n_months-1] = $date;
    // =================== the setting attribute ==========================
    $this->months_n_monthdays_fraction_tuplelist = DateFunctions
      ::get_month_n_monthdays_fraction_tuplelist_borders_can_fraction(
        $months_list
      );
  } // ends set_months_n_monthdays_fraction_tuplelist()

  public function get_monthdays_fraction_list() {

    $monthdays_fraction_list = array();
    foreach ($this->months_n_monthdays_fraction_tuplelist as $months_n_monthdays_fraction_tuple) {
      $monthdays_fraction_list[] = $months_n_monthdays_fraction_tuple[1];
    }
    return $monthdays_fraction_list;
  }

  public function get_mora_duration_in_complete_months() {
    return $this->monthyeardateref_ini->diffInMonths($this->monthyeardateref_fim);
  }

  public function get_mora_n_days_additional_to_n_months() {
    $n_complete_months_ini_fim = $this->get_mora_duration_in_complete_months();
    $advanced_n_months_date = $this->monthyeardateref_ini->copy()->addMonths($n_complete_months_ini_fim);
    return $advanced_n_months_date->diffInDays($this->monthyeardateref_fim);
  }

  public function verify_n_add_corrmonet_fraction_month_by_month_list() {

    if ($this->contract->apply_corrmonet_am == false) {
      return;
    }

    $item2_contract_fraction_fix_monthly_juros = 0;
    if ($this->contract->apply_juros_fixos_am()) {
      $item2_contract_juros_fixos_ao_mes_fraction =
        $this->contract->get_juros_fixos_ao_mes_fraction();
    }
    $monthly_mora_fraction_index_array = array();
    foreach ($monthly_corrmonet_fraction_index_array as $monthly_corrmonet_fraction_index) {
      $monthly_mora_fraction_index = $monthly_corrmonet_fraction_index + $item2_contract_juros_fixos_ao_mes_fraction;
      $monthly_mora_fraction_index_array[] = $monthly_mora_fraction_index;
    }
    $item1_multa_na_incidencia_de_mora = null;
    if ($this->contract->apply_multa_incid_mora) {
      $item1_contract_fraction_multa_na_incidencia_de_mora =
        $this->contract->get_multa_incid_mora_in_fraction();
      $monthly_mora_fraction_index = $monthly_mora_fraction_index_array[0];
      // Add it up to the first month index
      $monthly_mora_fraction_index += $item1_contract_fraction_multa_na_incidencia_de_mora;
      // Put it back into array()
      $monthly_mora_fraction_index_array[0] = $monthly_mora_fraction_index;
    }
    return $monthly_mora_fraction_index_array;
  } // add_contract_mora_items_to_monthly_corrmonet_fraction_index_array

  public function fetch_n_fill_contract_indice4char_corrmonet_indices_within_daterange(
      $monthyeardateref_ini,
      $monthyeardateref_fim
    ) {
      /*
        This method fetches and fills into an array the corr_monet indices
          within a date range
        The fetcher is a method in class CorrMonet.  It will receive
          from this method the 4char index identifier (eg. IGPM, IPCA, SELI etc),
          enveloped here, and the initial date and end date references.
          It will then return array $contract_mora_monthly_interest_array
          which contains the indices month by month.
      */

    $corr_monet_monthly_indices = CorrMonet
      ::generate_monthly_correction_array_with_specified_corr_monet_index(
        $this->contract->reajuste_indice4char,
        $monthyeardateref_ini,
        $monthyeardateref_fim
    );
    $item1_multa_na_incidencia_de_mora = null;
    if ($this->contract->apply_multa && $this->contract->percentual_multa_na_mora != null) {
        $item1_contract_fraction_multa_na_incidencia_de_mora =
          $this->contract->percentual_multa_na_mora / 100;
    }
    $item2_contract_fraction_fix_monthly_juros = 0;
    if ($this->contract->apply_juros && $this->contract->percentual_juros_fixos_ao_mes != null) {
      $item2_contract_fraction_fix_monthly_juros = $this->contract->percentual_juros_fixos_ao_mes / 100;
    }
    // For REVISION: next operation might be done with a map() kind of functional technique
    //   but it's now here done under a for-loop traditional technique

    $this->contract_mora_monthly_interest_array = array();
    foreach ($corr_monet_monthly_indices as $i=>$corr_monet_monthly_index) {
      $month_mora = $corr_monet_monthly_index;
      if ($i == 0 // ie, only the first month will have "multa"
        && $item1_contract_fraction_multa_na_incidencia_de_mora != null
      ) {
          $month_mora += $item1_contract_fraction_multa_na_incidencia_de_mora;
      } // ends if there must be applied the multa on mora incidence
      // if the fix monthly juros does not apply, okay, it will be logically zero
      $month_mora += $item2_contract_fraction_fix_monthly_juros;
      $this->contract_mora_monthly_interest_array[] = $month_mora;
    } // ends foreach

    // Available from here: $this->contract_mora_monthly_interest_array
    return;

  } // ends fetch_n_fill_contract_indice4char_corrmonet_indices_within_daterange()


  public function reset_first_last_indices_if_there_are_partial_first_last_months(
      $first_month_took_n_days = null,
      $first_monthyeardateref  = null,
      $last_month_took_n_days  = null,
      $last_monthyeardateref   = null
    ) {
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
    $fraction_first_month_took_n_days = null;
    $fraction_last_month_took_n_days  = null;

    // $days_in_months_assoc_array['taken_n_days_in_the_month'] = $first_month_n_days;
    if ($first_month_took_n_days != null && $first_monthyeardateref != null) {
      $fraction_first_month_took_n_days = DateFunctions
        ::calc_fraction_of_n_days_in_specified_month(
          $first_month_took_n_days,
          $first_monthyeardateref
        );
      $month_interest = $this->contract_mora_monthly_interest_array[0];
      $new_month_interest = $month_interest * $fraction_first_month_took_n_days;
      $new_month_interest = $corr_monet_monthly_indices;
      $this->contract_mora_monthly_interest_array[0] = $new_month_interest;
    }
    if ($fraction_last_month_took_n_days != null && $first_monthyeardateref != null) {
      $fraction_last_month_took_n_days = DateFunctions
        ::calc_fraction_of_n_days_in_specified_month(
          $last_month_took_n_days,
          $last_monthyeardateref
        );
      $arraylength = count($this->contract_mora_monthly_interest_array);
      $month_interest = $this->contract_mora_monthly_interest_array[$arraylength-1];
      $new_month_interest = $month_interest * $fraction_last_month_took_n_days;
      $this->contract_mora_monthly_interest_array[$arraylength-1] = $new_month_interest;
    }

  } // ends reset_first_last_indices_if_there_are_partial_first_last_months()


  public function calc_fmontant_from_imontant_monthdaterange_under_contract_mora(
      $initial_montant,
      $monthyeardateref_ini,
      $monthyeardateref_fim,
      $first_month_took_n_days = null,
      $last_month_took_n_days  = null
    ) {
      /*
        This instance method just wraps up $this->contract->reajuste_indice4char
          into the parameters and then issues static method:
          => CorrMonet::calc_fmontant_from_imontant_daterange_n_interest_per_month()
      */

    $this->contract_mora_monthly_interest_array =
      $this->fetch_n_fill_contract_indice4char_corrmonet_indices_within_daterange(
        $monthyeardateref_ini,
        $monthyeardateref_fim
      );

    $this->reset_first_last_indices_if_there_are_partial_first_last_months(
      $first_month_took_n_days,
      $last_month_took_n_days
    );


    return FinancialFunctions::calc_fmontant_from_imontant_n_monthly_interest_array(
      $initial_montant,
      $monthly_interest_array
    );
  } // ends calc_fmontant_from_imontant_monthdaterange_under_contract_mora()


  public function generate_mora_details($mora_debito) {

    // apply_corrmonet_am?
    $n_months_ref_ini_fim = $mora_debito->get_n_months_ref_ini_fim();
    $n_months_ref_ini_fim = $mora_debito->get_n_days_ref_ini_fim();
    $monthly_corrmonet_fraction_index_array = array($n_months_ref_ini_fim);

    if ($this->contract->apply_corrmonet_am) {
      $monthly_corrmonet_fraction_index_array = CorrMonet
        ::fetch_monthly_corrmonet_fraction_index_array(
          $this->contract->corrmonet_indice4char,
          $mora_debito->monthyeardateref_ini,
          $mora_debito->get_monthyeardateref_fim()
        );
    }

    $monthly_mora_fraction_index_array = $this
      ->add_contract_mora_items_to_monthly_corrmonet_fraction_index_array(
        $monthly_corrmonet_fraction_index_array
    );
    $first_interest_proportion = null;
    if ($n_days_in_monthyeardateref_ini != null) {
      $first_interest_proportion = DateFunctions
        ::calc_fraction_of_n_days_in_specified_month(
          $n_days_in_monthyeardateref_ini,
          $monthyeardateref_ini
        );
    }
    $last_interest_proportion = null;
    if ($n_days_in_monthyeardateref_fim != null) {
      $last_interest_proportion = DateFunctions
        ::calc_fraction_of_n_days_in_specified_month(
          $n_days_in_monthyeardateref_fim,
          $monthyeardateref_ini
        );
    }

    $this->copy_monthly_mora_fraction_index_array = $monthly_mora_fraction_index_array;
    $this->updated_debt_value = FinancialFunctions
      ::calc_fmontant_from_imontant_plus_interest_array_plus_border_proportions(
        $initial_montant, // ie, not in percent
        $monthly_mora_fraction_index_array,
        $first_interest_proportion,
        $last_interest_proportion
      );


  }


} // ends class ContractMora()
