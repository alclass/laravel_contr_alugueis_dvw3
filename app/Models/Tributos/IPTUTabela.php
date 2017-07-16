<?php

namespace App\Models\Tributos;

use Illuminate\Database\Eloquent\Model;

class IPTUTabela extends Model {

  protected $table = 'iptutabelas';

  protected $fillable = [
		'optado_por_cota_unica', 'ano', 'ano_quitado',
    'n_cota_quitada_ate_entao', 'valor_parcela_unica', 'valor_parcela_10x',
	];

  public function imovel() {
    return $this->belongsTo('App\Models\Immeubles\Imovel');
  }

}
