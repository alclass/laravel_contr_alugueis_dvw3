<?php
namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Model;

class CobrancaTipo extends Model {

  const K_TEXT_ID_ALUGUEL    = 'ALUG';
  const K_TEXT_ID_CONDOMINIO = 'COND';
  const K_TEXT_ID_IPTU       = 'IPTU';

  protected $table = 'cobrancatipos';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'char_id', 'brief_description', 'is_repasse ',
    'aplicar_percentual ', 'percentual_a_aplicar ', 'percentual_a_aplicar_descricao',
    'long_description ',
  ];

  public function cobranca() {
    return $this->belongsTo('App\Models\Billing\Cobranca');
  }

}
