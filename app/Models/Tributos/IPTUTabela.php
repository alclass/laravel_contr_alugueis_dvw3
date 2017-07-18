<?php

namespace App\Models\Tributos;

use Illuminate\Database\Eloquent\Model;

class IPTUTabela extends Model {


  const K_IPTU_TOTAL_COTAS = 10;

  public static function get_IPTU_N_COTAS_ANO() {
    return self::K_IPTU_TOTAL_COTAS;
  }

  protected $table = 'iptutabelas';

  protected $fillable = [
		'optado_por_cota_unica', 'ano', 'ano_quitado',
    'n_cota_quitada_ate_entao', 'valor_parcela_unica', 'valor_parcela_10x',
	];

  public function imovel() {
    return $this->belongsTo('App\Models\Immeubles\Imovel');
  }

}
