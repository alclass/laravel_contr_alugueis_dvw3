<?php
namespace App\Models\Finance;

// To import class LMadeiraPagto elsewhere in the Laravel App
// use App\Models\Finance\LMadeiraPagto;
use App\Models\Finance\CorrMonet;
use Illuminate\Database\Eloquent\Model;
// use Carbon\Carbon;

class LMadeiraPagto extends Model {


  protected $table = 'lmadeirapagtos';

  protected $dates = [
    'paydate',
  ];

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
   protected $fillable = [
     'paydate',
     'valor_pago',
   ];

}  // ends class LMadeiraPagto extends Model
