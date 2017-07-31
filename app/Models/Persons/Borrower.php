<?php
namespace App\Models\Persons;
// To import class Borrower elsewhere in the Laravel App
// use App\Models\Persons\Borrower;

use App\Models\Finance\AmortizationPayment;
use App\Models\Finance\AmortizationParcelTimeEvolver;
use App\Models\Persons\Person;
use Illuminate\Database\Eloquent\Model;

class Borrower extends Person {

  public $loan_value = null;
  public $loan_date  = null;
  public $loan_duration_in_months = null;
  public $payback_tuplelist       = null;
  public $payback_querybuilder    = null;
  public $are_data_loaded         = false;

  public function init_n_load_data($do_reload_data = false) {
    if ($this->are_data_loaded == false || $do_reload_data == true) {
      $this->init_data();
      $this->load_data();
      $this->are_data_loaded = true;
    } // ends if
  }// ends init_n_load_data()

  public function init_data() {
    $this->loan_value = null;
    $this->loan_date  = null;
    $this->loan_duration_in_months = null;
    $this->payback_tuplelist       = null;
    $this->payback_querybuilder    = null;
  }

  private function load_data() {
    $amortization_payments = AmortizationPayment
      ::where('payer_person_id', $this->id);
    // the loan itself
    $loan_obj = $amortization_payments
      ->where('is_loan_delivery', true)->first();
    if ($loan_obj == null) {
      return;
    } // ends if
    $this->loan_value = $loan_obj->valor_pago;
    $this->loan_date  = $loan_obj->paydate;
    $this->loan_duration_in_months = $loan_obj->loan_duration_in_months;
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

  public function get_amortization_parcel_time_evolver() {
    $amortization_parcel_time_evolver = new AmortizationParcelTimeEvolver(
        $this->loan_ini_date,
        $this->loan_ini_value,
        $this->loan_duration_in_months,
        $this->payback_querybuilder
        //$this->payback_tuplelist
    );
    return $amortization_parcel_time_evolver;

  } // ends get_amortization_parcel_time_evolver()

} // ends class Borrower extends Person
