<?php namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model {

	//
  protected $fillable = [
		'initial_rent_value', 'current_rent_value', 'indicador_reajuste',
    'pay_day_when_monthly', 'percentual_multa', 'percentual_juros', 'aplicar_corr_monet',
    'signing_date', 'start_date', 'duration_in_months', 'n_days_adicional', 'is_active',
	];

  public function get_next_rent_value_reajust_date() {
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
  }

  public function imovel() {
    return $this->belongsTo('App\Imovel');
  }

  public function cobrancas() {
    return $this->hasMany('App\Cobranca');
  }

  public function users() {
    return $this->belongsToMany('App\User');
  }

}
