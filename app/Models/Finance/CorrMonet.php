<?php
namespace App\Models\Finance;

// To import class CorrMonet elsewhere in the Laravel App
// use App\Models\Finance\CorrMonet;

use App\Models\Finance\MercadoIndice;
use App\Models\Utils\FinancialFunctions;
use Illuminate\Database\Eloquent\Model;

class CorrMonet extends Model {

  /*
    =================================
      Beginning of Static Methods
    =================================
  */

  public static function try_to_find_conventional_average_corrmonet(
    $corrmonet_indice4char
    ) {
    /*

    *** TO YET IMPLEMENT ***

    */
    return 0.005;

  } // ends [static] fetch_monthly_corrmonet_fraction_index_array()

  /*
    =================================
      End of Static Methods
    =================================
  */

  protected $table = 'corrmonets';

  protected $dates = [
    'monthyeardateref',
  ];

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
   protected $fillable = [
     'mercado_indicador_id',
     'indice4char',
     'fraction_value',
     'monthyeardateref',
   ];


   public function mercadoindice() {
     return $this->belongsTo('App\Models\Finance\MercadoIndice');
   }

}  // ends class CorrMonet extends Model
