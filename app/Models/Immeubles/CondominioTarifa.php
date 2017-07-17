<?php
namespace App\Models\Immeubles;

use App\Models\Utils\DateFunctions;
use Illuminate\Database\Eloquent\Model;

class CondominioTarifa extends Model {
  //


  public static function calcular_media_min_max_das_tarifas($condominio_tarifas) {
    if ($condominio_tarifas == null || $condominio_tarifas->count()==0) {
      $triple_stats = ['media'=>0, 'max'=>0, 'min'=>0];
      return $triple_stats;
    }
    $soma = 0;
    // at least the first exists, for count() > 0
    $condominio_tarifa = $condominio_tarifas->first();
    $min = $condominio_tarifa->tarifa_valor;
    $max = $condominio_tarifa->tarifa_valor;
    foreach ($condominio_tarifas as $condominio_tarifa) {
      $soma += $condominio_tarifa->tarifa_valor;
      if ($condominio_tarifa->tarifa_valor > $max) {
        $max = $condominio_tarifa->tarifa_valor;
      } elseif ($condominio_tarifa->tarifa_valor < $min) {
        $min = $condominio_tarifa->tarifa_valor;
      }
    }
    $media = $soma / $condominio_tarifas->count();
    $triple_stats = ['media'=>$media, 'max'=>$max, 'min'=>$min];
    return $triple_stats;
  }

  public static function calcular_media__min_max_das_ultimas_n_tarifas(
    $n_ultimas=null,
    $max_1_year=true
  ) {
    $n_recs = CondominioTarifa::count();
    if ($n_recs == 0) {
      $triple_stats = ['media'=>0, 'max'=>0, 'min'=>0];
      return $triple_stats;
    }
    if ($n_ultimas == null || $n_ultimas > $n_recs) {
      $n_ultimas=$n_recs;
    }
    if ($n_ultimas > 12 && $max_1_year == true) {
      $n_ultimas = 12;
    }
    $lasts = CondominioTarifa::last($n_ultimas);
    if ($lasts == null || $lasts->count()==0) {
      $triple_stats = ['media'=>0, 'max'=>0, 'min'=>0];
      return $triple_stats;
    }
    return self::calcular_media_min_max_das_tarifas($lasts);
  } // ends static calcular_media_das_ultimas_tarifas()

  protected $dates = [
    'monthyeardateref',
    //'created_at',
    //'updated_at',
  ];

  protected $table = 'condominiotarifas';

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'tarifa_valor',
    'monthyeardateref',
  ];

  public function format_monthyeardateref_as_m_slash_y() {
    return DateFunctions::format_monthyeardateref_as_m_slash_y($this->monthyeardateref);
  }

  public function media_min_max_das_tarifas($condominiotarifas) {
    return self::calcular_media_min_max_das_tarifas($condominiotarifas);
  }

  public function imovel() {
    return $this->belongsTo('App\Models\Immeubles\Imovel');
  }
}
