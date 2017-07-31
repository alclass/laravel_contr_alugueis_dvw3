<?php
namespace App\Models\Finance;
// To import class AmortizationPayment elsewhere in the Laravel App
// use App\Models\Finance\AmortizationPayment;

// use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class AmortizationPayment extends Model {

  protected $table = 'amortizationpayments';
  protected $dates = [
    'paydate',
  ];
  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
   protected $fillable = [
     'borrower_id',
     'paydate',
     'valor_pago',
   ];

   // Relationship: belongsTo Person (borrower/payer)
   public function person() {
     return $this->belongsTo('App\Persons\Person');
   }

}  // ends class AmortizationPayment extends Model
