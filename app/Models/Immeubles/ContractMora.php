<?php
namespace App\Models\Immeubles;

// To import class Contract elsewhere in the Laravel App
// use App\Models\Immeubles\ContractMora

use App\Models\Utils\DateFunctions;
use Carbon\Carbon;

class ContractMora  {

  private $contract = null;
  private $contract_mora_monthly_interest_array = null;

  public function __construct($contract) {
    $this->contract = $contract;
  }

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
        ::calc_fraction_of_n_days_in_a_specified_month(
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
        ::calc_fraction_of_n_days_in_a_specified_month(
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

} // ends class ContractMora()
