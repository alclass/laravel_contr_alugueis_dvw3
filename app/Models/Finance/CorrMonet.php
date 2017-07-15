<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;

class CorrMonet extends Model
{

  protected $table = 'corrmonets';

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'tarifa_valor',
    'monthyeardateref',
  ];

}
