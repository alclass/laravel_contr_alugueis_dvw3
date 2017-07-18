<?php
namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Model;

class CobrancaTipo extends Model {

  const K_4CHAR_ALUG = 'ALUG';
  const K_4CHAR_COND = 'COND';
  const K_4CHAR_IPTU = 'IPTU';
  const K_4CHAR_MORA = 'MORA';
  const K_4CHAR_CRED = 'CRED';

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

} // class CobrancaTipo extends Model
