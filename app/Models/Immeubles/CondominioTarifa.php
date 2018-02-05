<?php
namespace App\Models\Immeubles;
// use App\Models\Immeubles\CondominioTarifa;

use App\Models\Utils\DateFunctions;
use Illuminate\Database\Eloquent\Model;

class CondominioTarifa extends Model {
  //

  const K_DEFAULT_CONDOMINIO_GROUP_COSTS  = [
    /* 
      This will be mostly used when current data has not been updated to db
      Notice that this array, if unordered, will be sorted in method,
        so group numbers ascending have ascending costs
      Eg. if array is [800, 600, 700], groups will map to [600, 700, 800]
    */
    1200,
    400,
    700,
  ];

  public static function get_default_condominiotarifa_for_group_n($cond_group_cost_number=-2) {

    // PHP does a full copy of an array by attribution, not a reference as other languages do
    // This 'deep' copy is necessary, because sort() cannot change a const by reference
    $cost_array = self::K_DEFAULT_CONDOMINIO_GROUP_COSTS;
    sort($cost_array);
    $index = $cond_group_cost_number - 1;
    if (array_key_exists($index, $cost_array)) {
      return $cost_array[$index];
    }
    // print_r($cost_array);
    $lowest_tarifa    = $cost_array[0]; // first element, array is sorted
    $highest_tarifa   = $cost_array[count($cost_array)-1]; // last element, array is sorted
    $inbetween_tarifa = ($highest_tarifa + $lowest_tarifa) / 2; // average of min, max
    return $inbetween_tarifa;
  } // ends get_default_condominiotarifa_for_group_n()

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

  public static function calcular_media_min_max_das_ultimas_n_tarifas(
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
  } // ends [static] calcular_media_das_ultimas_tarifas()

  public static function get_valor_tarifa_mesref_ou_alternativa_com_brief_info(
      $imovel_id,
      $monthrefdate
    ) {
    $return_array = array();
    $condominio_tarifa = self::where('imovel_id', $imovel_id)
      ->where('monthrefdate', $monthrefdate)
      ->first();
    $brief_info = null;
    if ($condominio_tarifa == null) {
      $condominio_tarifa_valor = self::calcular_media_das_ultimas_tarifas();
      $brief_info = 'Usada a média das últimas tarifas';
    } else {
      $condominio_tarifa_valor = $condominio_tarifa->tarifa_valor;
    }
    $return_array = ['condominio_tarifa_valor'=>$condominio_tarifa_valor, 'brief_info'=>$brief_info];
    return $return_array;
  } // ends [static] get_valor_tarifa_mesref_ou_alternativa()

  protected $dates = [
    'monthrefdate',
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
    'monthrefdate',
  ];

  public function media_min_max_das_tarifas($condominiotarifas) {
    /*
      This method was a way to give access to the static method above
      with needing to namespace this class in the blade template
      However, the other solution is to call this in the controller.
      To REVISE: when the above mentioned change occurs,
          delete this instance method, leaving only the static one above

    */
    return self::calcular_media_min_max_das_tarifas($condominiotarifas);
  }

  public function imovel() {
    return $this->belongsTo('App\Models\Immeubles\Imovel');
  }
}
