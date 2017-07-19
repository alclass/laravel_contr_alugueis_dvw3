<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;

class CorrMonet extends Model {

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
     'tarifa_valor',
     'monthyeardateref',
   ];

   public function mercadoindice() {
     return $this->belongsTo('App\Models\Finance\MercadoIndice');
   }

}  // ends class CorrMonet extends Model
