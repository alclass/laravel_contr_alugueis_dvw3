<?php
namespace App\Models\Finance;
// To import class AmortizationPaymentTester elsewhere in the Laravel App
// use App\Models\Finance\AmortizationPaymentTester;


use App\Models\Billing\AmortizationParcelTimeEvolver;
use App\Models\Persons\Borrower;
// use App\Models\Utils\DateFunctions;
// use App\Models\Utils\FinancialFunctions;
use Carbon\Carbon;

class AmortizationPaymentTester {

  public $borrower = null;
  public $amortization_evolver = null;


  public function __construct($borrower_id) {
    $this->borrower = Borrower::find($borrower_id);
    $this->borrower->init_n_load_data();
    // $this->amortization_evolver = $this->borrower->get_amortization_parcel_time_evolver();
  }

  public function run_amortization_report() {


  } // ends run_amortization_report()

} // ends class AmortizationPaymentTester
