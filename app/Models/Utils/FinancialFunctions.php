<?php
namespace App\Models\Utils;

// To import class FinancialFunctions elsewhere in the Laravel App
// use App\Models\Utils\FinancialFunctions

/*

  The following ref-site has a listing of financial formulas.
    [link] www.math4finance.com/financial-formulas.php

Summary of [static] methods in here:

calc_fmontant_from_imontant_n_interest_array()
[has unittest]

calc_fmontant_from_imontant_plus_interest_array_plus_border_proportions()
[has unittest]

calc_monthly_payment_pmt()
[has unittest]

  The method below was refactored to class CorrMonet, it's no longer here
    calc_latervalue_from_inivalue_w_ini_end_dates_n_corrmonet4charid()
    [does not have unittest for the time being (this method gets db info)]
*/

class FinancialFunctions {

  public static function calc_fmontant_from_imontant_n_interest_array(
      $initial_montant,
      $interest_array // eg. [[0]=>0.04, [1]=>0.015, ...]
    ){
    /*
      One example will explain this method, ie:
        Suppose $initial_montant = 100 and $interest_array = [0.04, 0.015, 0.027]
        In this example, the final montant will be:
        = 100*(1+0.04)*(1+0.015)*(1+0.027) = 108.41012
    */
    if ($initial_montant == null || $initial_montant == 0) {
      return 0;
    }
    if ($interest_array==null || empty($interest_array)) {
      return $initial_montant;
    }
    $final_montant = $initial_montant;
    foreach ($interest_array as $interest) {
      $final_montant *= (1 + $interest);
    }
    return $final_montant;
  } // ends calc_fmontant_from_imontant_n_interest_array()

  public static function calc_fmontant_from_imontant_plus_interest_array_plus_border_proportions(
      $initial_montant, // ie, not in percent
      $interest_array, // eg. [[0]=>0.04, [1]=>0.015, ...]
      $first_interest_proportion = null, // eg. 14 days / 31 days = 0.45161290322581
      $last_interest_proportion  = null // eg. 15 days / 30 days = 0.5
    ) {
    /*
      This method wraps, if they exist, $first_interest_proportion
        & $last_interest_proportion, to method:
        self::calc_fmontant_from_imontant_n_interest_array()
      In so doing, it updates $interest_array, then issueing the above mentioned
        method with the updated $interest_array

      Two examples will explain this method, ie:

      E.g. 1
        Suppose $initial_montant = 100 and $interest_array = [0.04, 0.015, 0.027]
        Consider also that $first_interest_proportion is
            14 days / 31 days = 0.45161290322581 (let this be 0.45)
        In this example, the final montant will be:
        = 100*(1+0.04)*(1+0.015)*(1+0.027*0.45) = 106.842554

        Notice that the calculation is done by callee:
          self::calc_fmontant_from_imontant_n_interest_array()

        This method just adjusts the 0.027 to 0.027*0.45 in the last array position.
          wrapping it to the forward (callee) method.

      E.g. 3
        Suppose $initial_montant = 100 and $interest_array = [0.04, 0.015, 0.027]
        Consider:
        3-1 -> $first_interest_proportion is
               15 days / 30 days = 0.5
        3-2 -> $last_interest_proportion is
               14 days / 31 days = 0.45161290322581 (let this be 0.45)
        In this example, the final montant will be:
        = 100*(1+0.04*0.5)*(1+0.015)*(1+0.027*0.45) = 104.7878895

      Notice concerning the changing of $interest_array inside this method:
        In PHP arrays are assigned by copy, not by reference.
          Because of that, it's not a problem to change the array in here,
          ie, it will not create a side-effect elsewhere outside this method.

    */
    // First logical cases
    // 1st logical case $initial_montant is null or 0, return immediately
    if ($initial_montant == null || $initial_montant == 0) {
      return 0;
    }
    // 2nd logical case $monthly_interest_array is null or empty, return immediately
    if ($interest_array == null || empty($interest_array)) {
      return $initial_montant;
    }
    // Update $first_interest in array if needed
    if ($first_interest_proportion!=null) {
      $first_interest = $interest_array[0];
      $new_first_interest = $first_interest * $first_interest_proportion;
      $interest_array[0] = $new_first_interest; // see above in the docstring the reference/copy discussion
    }

    // Update $last_interest in array if needed
    $arraylength = count($interest_array);
    if ($last_interest_proportion!=null && $arraylength>1) {
      $last_interest = $interest_array[$arraylength-1];
      $new_last_interest = $last_interest * $last_interest_proportion;
      $interest_array[$arraylength-1] = $new_last_interest; // see above in the docstring the reference/copy discussion
    }

    return self::calc_fmontant_from_imontant_n_interest_array(
      $initial_montant,
      $interest_array
    );
  } // ends [static] function calc_fmontant_from_imontant_plus_interest_array_plus_border_proportions()


  public static function calc_monthly_payment_pmt(
      $initial_montant,
      $n_months, //
      $interest_rate
    ) {
    /*
      This method calculates a PMT, ie, what is the monthly payment parcel (ie, prestacao)
      based on:
        p (initial montant),
        r (interest rate) and
        n (number of monthly payments that amortize initial montant)
    */
    $p = $initial_montant;
    $n = $n_months;
    $r = $interest_rate;
    $numerator = $p * $r * ((1 + $r) ** $n); // ***CHECK*** THIS: maybe the first $r is $i
    $denominator = ((1 + $r) ** $n) - 1;
    $pmt = $numerator / $denominator;
    return $pmt;

  } // ends [static] function calc_fmontant_from_imontant_plus_interest_array_plus_border_proportions()

} // ends class FinancialFunctions
