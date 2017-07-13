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


  public function gerar_cobranca() {
    $today = Carbon::today();
    $monthyeardateref = $today->addMonth(-1);
    $cobranca = new Cobranca;
    $billingitem = new BillingItem;
    $billingitem->cobrancatipo_id = $this->get_cobrancatipo_id('name'=>'aluguel');
    $billingitem->value = $this->current_rent_value;
    $billingitem->dateref = $monthyeardateref;
    $cobranca->billingitems->add($billingitem);
    foreach ($this->contractbillingrules() as $contractbillingrule) {
      $billingitem = new BillingItem;
      $billingitem->monthyeardateref = $monthyeardateref;
      $billingitem->cobrancatipo_id = $contractbillingrule->cobrancatipo_id;



    }

  }

  public function imovel() {
    return $this->belongsTo('App\Imovel');
  }

  public function cobrancas() {
    return $this->hasMany('App\Cobranca');
  }

  public function contractbillingrules() {
    return $this->hasMany('App\contractBillingRule');
  }

  public function users() {
    return $this->belongsToMany('App\User');
  }

}
