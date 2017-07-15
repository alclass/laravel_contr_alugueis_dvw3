<?php
namespace App\Models\Utils;

class DateFunctions {

  public static function get_next_rent_value_reajust_date($firstmonthdate, $n_months, $n_days) {
    $today = Carbon::today();
    $contract_start_date = Carbon::createFromFormat('Y-m-d', $this->start_date);
    $current_year  = $today->year;
    $start_year    = $contract_start_date->year;
    $diff_in_years = $current_year - $start_year;
    // 3 cases / hypotheses below
    if ($diff_in_years < 0) {
      return $contract_start_date->addYear();  // no need to ->copy() because obj. is local
    }
    $projected_date = $contract_start_date->copy()->addYear($diff_in_years);
    if ($projected_date >= $today) {
      return $projected_date;
    }
    return $projected_date->addYear();    
    return 1;
  } // ends get_next_rent_value_reajust_date()

} // ends class
