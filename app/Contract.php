<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Contract extends Model {

	//
  protected $fillable = [
		'initial_rent_value', 'current_rent_value', 'indicador_reajuste',
    'pay_day_when_monthly', 'percentual_multa', 'percentual_juros', 'aplicar_corr_monet',
    'signing_date', 'start_date', 'duration_in_months', 'n_days_adicional', 'is_active',
	];

  public function imovel() {
    return $this->belongsTo('App\Imovel');
  }

  public function cobrancas() {
    return $this->hasMany('App\Cobranca');
  }
}
