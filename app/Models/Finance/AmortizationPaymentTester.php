<?php
namespace App\Models\Finance;
// To import class AmortizationPaymentTester elsewhere in the Laravel App
// use App\Models\Finance\AmortizationPaymentTester;


use App\Models\Billing\AmortizationParcelsEvolver;
use App\Models\Persons\Borrower;
// use App\Models\Utils\DateFunctions;
// use App\Models\Utils\FinancialFunctions;
use Carbon\Carbon;

class AmortizationPaymentTester {

  public $borrower = null;
  public $paybacks_within_range_collection = null;

  public function __construct($borrower_id) {
    $this->borrower = Borrower::find($borrower_id);
    $this->borrower->set_amortization_parcels_evolver();
    $this->runtest1();
  }

  public function runtest1() {
    $paybacks_within_range_qb = $this->borrower
      ->get_amortization_parcels_evolver()->clone_borrowers_paybacks_qb();
    $this->paybacks_within_range_collection = $paybacks_within_range_qb
      ->where('paydate', '>=', new Carbon('2017-05-01'))
      ->where('paydate', '<=', new Carbon('2017-07-31'))
      ->get();

  } // ends run_amortization_report()

} // ends class AmortizationPaymentTester
