<?php
namespace App\Models\Tributos;
// use App\Models\Tributos\IPTUTabela;

use Illuminate\Database\Eloquent\Model;

class IPTUTabela extends Model {


  const K_DEFAULT_IPTU_TOTAL_COTAS = 10;
  const K_DEFAULT_MESREF_DE_INICIO_REPASSE = 2;
  const K_DEFAULT_IPTU_VALOR_GENERICO_POR_PARCELA = 250;

  public static function get_DEFAULT_IPTU_TOTAL_COTAS() {
    return self::K_DEFAULT_IPTU_TOTAL_COTAS;
  }

  private static function make_default_instance_with_imovel_n_ano($imovel, $ano) {
    $iptu_ano_imovel = new self();
    $iptu_ano_imovel->imovel_id = $imovel->id;
    $iptu_ano_imovel->optado_por_cota_unica = false;
    $iptu_ano_imovel->mesref_de_inicio_repasse = self::K_DEFAULT_MESREF_DE_INICIO_REPASSE;
    $iptu_ano_imovel->ano = $ano;
    $iptu_ano_imovel->ano_quitado = false;
    $iptu_ano_imovel->valor_parcela_unica = null;
    // Provisory, the db-one to be fetched below
    $iptu_ano_imovel->valor_por_parcela = self::K_DEFAULT_IPTU_VALOR_GENERICO_POR_PARCELA;
    $imovel_fallback_iptu_parcela = $imovel->default_valor_parcela_mes_iptu;
    if ($imovel_fallback_iptu_parcela != null) {
      $iptu_ano_imovel->valor_por_parcela = $imovel_fallback_iptu_parcela;
    }
    $iptu_ano_imovel->total_de_parcelas = self::K_DEFAULT_IPTU_TOTAL_COTAS;
    return $iptu_ano_imovel;
  } // ends static make_instance_with_imovel_n_ano_or_null()

  public static function fetch_by_imovel_n_ano_or_return_null($imovel, $ano) {
    if ($imovel == null) {
      return null;
    }
    $iptu_ano_imovel = self
      ::where('imovel_id', $imovel->id)
      ->where('ano', $ano)
      ->first();
    if ($iptu_ano_imovel != null) {
      return $iptu_ano_imovel;
    }
    return null;
  } // ends static fetch_by_imovel_n_ano_or_return_null()

  public static function fetch_by_imovelapelido_n_ano_or_return_null($imovelapelido, $ano) {
    $imovel = Imovel
      ::where('apelido', $imovelapelido)
      ->first();
    return self::fetch_by_imovel_n_ano_or_return_null($imovel, $ano);
  }

  public static function make_instance_with_imovel_n_ano_or_get_default($imovel, $ano) {
    // There is no default if $imovel is null (the default is if $ano does get back a db-record)
    if ($imovel == null) {
      return null;
    }
    $iptu_ano_imovel = self::fetch_by_imovel_n_ano_or_return_null($imovel, $ano);
    if ($iptu_ano_imovel != null) {
      return $iptu_ano_imovel;
    }
    return self::make_default_instance_with_imovel_n_ano($imovel, $ano);
  } // ends static make_instance_with_imovel_n_ano_or_get_default()

  protected $table = 'iptutabelas';

  /*
    Attributes from db-table:
    ====================
      imovel_id & imovel
      optado_por_cota_unica
      mesref_de_inicio_repasse
      ano
      ano_quitado
      valor_parcela_unica
      valor_por_parcela
      total_de_parcelas (THIS SHOULD BE CONSIDERED private; see totalparts below)
      n_guia
      tem_prox_guia

    Dynamic Attributes:
    ====================
      mesref_de_fim_repasse
      totalparts (this is either total_de_parcelas when not cota_unica or 1 if cota_unica)


    protected $fillable = [   	];

  */
  protected $attributes = ['mesref_de_fim_repasse', 'totalparts'];

  public function copytoanewyearinstance($ano) {
    $copied_iptu = new self();
    $copied_iptu->imovel_id = $this->imovel_id;
    $copied_iptu->optado_por_cota_unica    = false;
    $copied_iptu->mesref_de_inicio_repasse = $this->mesref_de_inicio_repasse;
    $copied_iptu->ano = $ano;
    $copied_iptu->ano_quitado = false ;
    $copied_iptu->valor_parcela_unica = $this->valor_parcela_unica;
    $copied_iptu->valor_por_parcela   = $this->valor_por_parcela;
    $copied_iptu->total_de_parcelas   = $this->total_de_parcelas;
    return $copied_iptu;
  }

  public function get_months_repass_value($monthrefdate) {

    $mes = $monthrefdate->month;
    if ($this->optado_por_cota_unica) {
      if ($mes == $this->mesref_de_inicio_repasse) {
        return $this->valor_parcela_unica;
      }
      else {
        // Cota única out of its month
        return 0;
      }
    }
		if (
      $mes >= $this->mesref_de_inicio_repasse &&
      $mes <= $this->mesref_de_fim_repasse
    ) {
      // Time window with IPTU
      return $this->valor_por_parcela;
    }
    // Time window without IPTU
    return 0;
  } // ends get_months_repass_value()

  public function is_refmonth_billable($monthrefdate) {
    $null_is_false = $this->get_numberpart_with_refmonth($monthrefdate);
    if ($null_is_false == null) {
      return false;
    }
    return true;
  } // ends is_refmonth_billable()

  public function get_numberpart_with_refmonth($monthrefdate) {

    $mes = $monthrefdate->month;
    if ($this->optado_por_cota_unica) {
      if ($mes == $this->mesref_de_inicio_repasse) {
        return 1;
      }
      else {
        // Cota única out of its month
        return null;
      }
    }
		if (
      $mes >= $this->mesref_de_inicio_repasse &&
      $mes <= $this->mesref_de_fim_repasse
    ) {
			// Time window with IPTU
      $numberpart = $mes - $this->mesref_de_inicio_repasse + 1;
      return $numberpart;
    }
    // Time window without IPTU
    return null;
  } // ends get_numberpart_with_refmonth()

  public function getTotalpartsAttribute() {
    if ($this->optado_por_cota_unica) {
      return 1;
    }
    return $this->total_de_parcelas;
  }

  // Dynamic Attribute: mesref_de_fim_repasse
  public function getMesrefDeFimRepasseAttribute() {
    /*

     This method is an accessor for the dynamic Attribute:
       mesref_de_fim_repasse

     Notice that the whole IPTU must be enclosed in the year, ie,
     repassing end refmonth may not be greater than 12

     A TO-DO for a 'log an error somewhere' task has been commented in here.
    */
    $n_month = $this->mesref_de_inicio_repasse + $this->total_de_parcelas - 1;
    if ($n_month > 12)  {
      // TO-DO
      // log an error somewhere
    }
    return $n_month;
  }

  public function imovel() {
    return $this->belongsTo('App\Models\Immeubles\Imovel');
  }

}
