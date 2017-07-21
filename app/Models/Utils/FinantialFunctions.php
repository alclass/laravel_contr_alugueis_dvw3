<?php
namespace App\Models\Utils;

// To import class FinantialFunctions elsewhere in the Laravel App
// use App\Models\Utils\FinantialFunctions

class FinantialFunctions {

  public static function calculate_final_montant_with_monthly_interest_array(
      $initial_montant, // ie, not in percent
      $monthly_interest_array, // eg. [[0]=>0.04, [1]=>0.015, ...]
      $first_month_as_fraction = null, // eg. 14 days / 31 days = 0.45161290322581
      $last_month_as_fraction  = null // eg. 15 days / 30 days = 0.5
    ) {
    /*
      Three examples will explain this method, ie:
      E.g. 1
        Suppose $initial_montant = 100 and $monthly_interest_array = [0.04, 0.015, 0.027]
        In this example, the final montant will be:
        = 100*(1+0.04)*(1+0.015)*(1+0.027) = 108.41012

      E.g. 2
        Suppose $initial_montant = 100 and $monthly_interest_array = [0.04, 0.015, 0.027]
        Consider also that $last_month_as_fraction is
            14 days / 31 days = 0.45161290322581 (let this be 0.45)
        In this example, the final montant will be:
        = 100*(1+0.04)*(1+0.015)*(1+0.027*0.45) = 106.842554

        E.g. 3
          Suppose $initial_montant = 100 and $monthly_interest_array = [0.04, 0.015, 0.027]
          Consider:
          3-1 -> $first_month_as_fraction is
                 15 days / 30 days = 0.5
          3-2 -> $last_month_as_fraction is
                 14 days / 31 days = 0.45161290322581 (let this be 0.45)
          In this example, the final montant will be:
          = 100*(1+0.04*0.5)*(1+0.015)*(1+0.027*0.45) = 104.7878895


      Notice concerning the changing of $monthly_interest_array inside this method:
        In PHP arrays are assigned by copy, not by reference.
          Because of that, the possible array_pop($monthly_interest_array)
          will not create a side-effect elsewhere outside this method.

    */
    // First logical cases
    // 1st logical case $initial_montant is null or 0, return immediately
    if ($initial_montant == null || $initial_montant == 0) {
      return 0;
    }
    // 2nd logical case $monthly_interest_array is null or empty, return immediately
    if ($monthly_interest_array == null || empty($monthly_interest_array)) {
      return $initial_montant;
    }
    $final_montant = $initial_montant;
    if ($first_month_as_fraction!=null) {
      $first_month_interest_fraction = array_shift($monthly_interest_array);
      $final_montant *= (1 + $first_month_interest_fraction * $first_month_as_fraction);
      // If the popping out of first element let the array empty, then return result, no need to go further
      if (emtpy($monthly_interest_array)) {
        return $final_montant;
      } // ends inner if
    } // ends outer if
    // Pop out the last element and check it for month fraction application later
    if ($last_month_as_fraction!=null) {
      $last_month_interest_fraction = array_pop($monthly_interest_array);
    }
    foreach ($monthly_interest_array as $monthly_interest_fraction) {
      $final_montant *= (1 + $monthly_interest_fraction);
    }
    if ($last_month_as_fraction!=null) {
      $final_montant *= (1 + $last_month_interest_fraction * $last_month_as_fraction);
    }
    return $final_montant;
  } // ends [static] function calculate_final_montant_with_monthly_interest_array()


} // ends class FinantialFunctions
