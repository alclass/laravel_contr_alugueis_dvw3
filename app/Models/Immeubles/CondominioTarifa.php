<?php
namespace App\Models\Immeubles;

use App\Models\Utils\DateFunctions;
use Illuminate\Database\Eloquent\Model;

class CondominioTarifa extends Model {
  //


  public static function calcular_media_das_ultimas_tarifas(
    $n_ultimas=null,
    $max_1_year=true
  ) {
    $n_recs = CondominioTarifa::count();
    if ($n_recs == 0) {
      return null;
    }
    if ($n_ultimas == null || $n_ultimas > $n_recs) {
      $n_ultimas=$n_recs;
    }
    if ($n_ultimas > 12 && $max_1_year == true) {
      $n_ultimas = 12;
    }
    $lasts = CondominioTarifa::last($n_ultimas);
    if ($lasts == null) {
      return null;
    }
    $average = 0;
    foreach ($lasts as $condominio_tarifa) {
      $average += $condominio_tarifa->tarifa_valor;
    }
    $average = $average / $lasts->count();
    return $average;
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

  public function imovel() {
    return $this->belongsTo('App\Models\Immeubles\Imovel');
  }
}
