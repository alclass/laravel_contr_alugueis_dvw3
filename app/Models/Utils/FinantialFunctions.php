<?php
namespace App\Models\Utils;

class FinantialFunctions {


  public static function calculate_debt_with_months_days($firstmonthdate, $n_months, $n_days) {
    $corr_monet = CorrMonet::find('monthdate', '=', $firstmonthdate);
    $percent_to_apply = $this->contract->percentual_multa + $this->contract->percentual_juros + $corr_monet;
    $n_months -= 1;
    $ongoingdebt = $this->originaldebtvalue * (1 + $percent_to_apply);
    if ($n_months == 0) {
      return $ongoingdebt;
    }
    $month_date = $firstmonthdate->copy()->addMonth();
    for ($i; $i < $n_months; $i++) {
      $corr_monet = CorrMonet::find('monthdate', '=', $firstmonthdate);
      $percent_to_apply = $this->contract->percentual_juros + $corr_monet;
      $ongoingdebt = $this->originaldebtvalue * (1 + $percent_to_apply);
    }

    $today = Carbon::today();
    if ($target_date == null) {
      $target_date = $today;
    }
    $carbonmonthyeardateref = Carbon::fromFomat('Y-m-d', $this->monthyeardateref);
    $diff_date = $target_date->diffDate($carbonmonthyeardateref);
    $n_months = $diff_date->months;
    $n_days = $diff_date->days;
    return $this->calculate_debt_with_months_days($n_months, $n_days);
  }


  public static function deux($firstmonthdate, $n_months, $n_days) {
      $today = Carbon::today();
      if ($target_date == null) {
        $target_date = $today;
      }
      $carbonmonthyeardateref = Carbon::fromFomat('Y-m-d', $this->monthyeardateref);
      $diff_date = $target_date->diffDate($carbonmonthyeardateref);
      $n_months = $diff_date->months;
      $n_days = $diff_date->days;
      return $this->calculate_debt_with_months_days($n_months, $n_days);
    }

}
