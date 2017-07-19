<?php
namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Model;

class CobrancaTipo extends Model {

  const K_4CHAR_ALUG = 'ALUG';
  const K_4CHAR_COND = 'COND';
  const K_4CHAR_IPTU = 'IPTU';
  const K_4CHAR_MORA = 'MORA';
  const K_4CHAR_CRED = 'CRED';

  public static function get_cobrancatipo_with_its_4char_repr($p_4char_repr, $raise_exception_if_null=false) {
    $cobrancatipo = CobrancaTipo::where('char_id', $p_4char_repr)
      ->first();
    if ($cobrancatipo == null && $raise_exception_if_null=true) {
      $error = 'cobrancatipo from CobrancaTipo::'.$p_4char_repr.' was not db-found, raise/throw exception.';
      throw new Exception($error);
    }
    return $cobrancatipo;
  } // ends [static] function get_cobrancatipo_with_its_4char_repr()

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
