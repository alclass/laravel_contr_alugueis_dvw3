<?php
namespace App\Models\Immeubles;

use Illuminate\Database\Eloquent\Model;

class CondominioTarifa extends Model {
  //

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

  public function contract() {
    return $this->belongsTo('App\Models\Immeubles\Contract');
  }
}
