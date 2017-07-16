<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;

class CorrMonet extends Model
{
  const K_IGPM = 'IGP-M';
  const K_IPCA = 'IPCA';

  protected $table = 'corrmonets';

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'indicador',
    'tarifa_valor',
    'monthyeardateref',
  ];

}
