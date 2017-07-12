<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class CobrancaTipo extends Model {

	//
  protected $table = 'cobrancatipos';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'brief_description ', 'is_repasse ',
    'aplicar_percentual ', 'percentual_a_aplicar ', 'percentual_a_aplicar_descricao',
    'long_description ',
  ];

  public function cobranca() {
    return $this->belongsTo('App\Cobranca');
  }

}
