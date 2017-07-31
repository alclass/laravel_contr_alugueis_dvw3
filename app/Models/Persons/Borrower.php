<?php
namespace App\App\Models\Persons;
// To import class Borrower elsewhere in the Laravel App
// use App\Models\Persons\Borrower;

use App\Models\Finance\TimeEvolveAmortizationParcel;
// use App\Models\Persons\Person;
use Illuminate\Database\Eloquent\Model;

class Borrower extends Person {

  public $loan_value = null;
  public $loan_date  = null;
  public $loan_duration_in_months = null;
  public $payback_tuplelist       = null;
  public $payback_querybuilder    = null;


  private function load_data() {

    $amortization_payments = AmortizationPayment
      ::where('payer_person_id', $this->id);
    // the loan itself
    $loan_obj = $amortization_payments->find('is_loan_delivery', true);
    if ($loan_obj != null) {
      $this->loan_value = $loan_obj->valor_pago;
      $this->loan_date  = $loan_obj->paydate;
      $this->loan_duration_in_months = $loan_obj->loan_duration_in_months;
    } // ends if
    $this->payback_querybuilder = AmortizationPayment
      ::where('payer_person_id', $this->id)
      ->where('is_loan_delivery', false);
    $paybacks = $amortization_payments->where('is_loan_delivery', false)->get();
    $this->payback_tuplelist = array();
    foreach ($paybacks as $payback) {
      $payback_tuple[] = $payback->paydate;
      $payback_tuple[] = $payback->valor_pago;
      $this->payback_tuplelist[] = $payback_tuple;
    } // ends foreach

  } // ends load_data()

  public function get_time_evolve_amortization_parcel_report() {


    $this->load_data();
    $amortization_parcel_reporter = new TimeEvolveAmortizationParcel(
        $this->loan_ini_date,
        $this->loan_ini_value,
        $this->loan_duration_in_months,
        $this->payback_querybuilder
        //$this->payback_tuplelist
    );
    return $amortization_parcel_reporter;

  } // ends get_time_evolve_amortization_parcel_report()

} // ends class Borrower extends Person
