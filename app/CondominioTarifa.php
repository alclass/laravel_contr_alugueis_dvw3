<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CondominioTarifa extends Model
{
  //
  $fillable = [
    'tarifa_valor',
    'monthyeardateref',
  ];

  public function contract() {
    return $this->belongsTo('App\Contract');
  }
}
