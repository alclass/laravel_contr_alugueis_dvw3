<?php

use Carbon\Carbon;

namespace App;

use Illuminate\Database\Eloquent\Model;

class MoraDebito extends Model {
    //
  $fillable = [
    'monthyeardateref',
    'countinterestfromdate',
    'originaldebtvalue',
    'lineinfo',
  ];


  }

  public function calculate_debt_time_increase($target_date = null) {
    $today = Carbon::today();
    if ($target_date == null) {
      $target_date = $today;
    }
    $carbonmonthyeardateref = Carbon::fromFomat('Y-m-d', $this->monthyeardateref);
    $diff_date = $target_date->diffDate($carbonmonthyeardateref);
    $n_months = $diff_date->months;
    $n_days = $diff_date->days;
    return $this->calculate_debt_with_months_days($n_months, $n_days)

  }


  public function contract() {
    return $this->belongsTo('App\Contract');
  }

}
