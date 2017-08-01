<?php
namespace App\Models\Persons;
// To import class Borrower elsewhere in the Laravel App
// use App\Models\Persons\Borrower;

use App\Models\Finance\AmortizationPayment;
use App\Models\Finance\AmortizationParcelsEvolver;
use App\Models\Persons\Person;
use Illuminate\Database\Eloquent\Model;

class Borrower extends Person {

  public $loan_ini_value = null;
  public $loan_ini_date = null;
  public $loan_duration_in_months = null;
  public $amortization_parcels_evolver = null;
  public $are_data_loaded = false;

  /* THIS DOES NOT WORK : COMMENTED OUT
     (The ideia was to run reprep_n_init_data() at construction time)
  public function __construct(array $attributes = array(), $value = null) {
    parent::__construct($attributes, $value);
    $this->reprep_n_init_data(true);
  }
  */

  public function reprep_n_init_data($do_reload_data = false) {
    if ($this->are_data_loaded == false || $do_reload_data == true) {
      $this->reprep_data();
      $this->init_data();
      $this->are_data_loaded = true;
    } // ends if
  }// ends reprep_n_init_data()

  public function reprep_data() {
    $this->loan_ini_value = null;
    $this->loan_ini_date  = null;
    $this->loan_duration_in_months = null;
    $this->amortization_parcels_evolver == null; // this will be fetched when its set method is issued
  } // ends reprep_data()

  private function init_data() {
    $borrowers_loan = AmortizationPayment
      ::where('payer_person_id', $this->id)
      ->where('is_loan_delivery', true)->first();
    if ($borrowers_loan == null) {
      return;
    } // ends if
    $this->loan_ini_value = $borrowers_loan->valor_pago;
    $this->loan_ini_date  = $borrowers_loan->paydate;
    $this->loan_duration_in_months = $borrowers_loan->loan_duration_in_months;

  } // ends init_data()

  public function set_amortization_parcels_evolver() {

    if  (  $this->loan_ini_value          == null
        || $this->loan_ini_date           == null
        || $this->loan_duration_in_months == null) {
      $this->reprep_n_init_data(true);
    }
    // Setting $borrowers_paybacks_qb QueryBuilder instance
    // This attribute will be sent as param to new AmortizationParcelsEvolver()
    //  from there, it's cloned every time it issues a ->where() or other self-returning QueryBuilder
    $borrowers_paybacks_qb = AmortizationPayment
      ::where('payer_person_id', $this->id)
      ->where('is_loan_delivery', false);
    $this->amortization_parcels_evolver = new AmortizationParcelsEvolver(
        $this->loan_ini_value,
        $this->loan_ini_date,
        $this->loan_duration_in_months,
        $borrowers_paybacks_qb
    );
  } // ends set_amortization_parcels_evolver()

  public function get_amortization_parcels_evolver() {
    if ($this->amortization_parcels_evolver == null) {
      $this->set_amortization_parcels_evolver();
    }
    return $this->amortization_parcels_evolver;
  } // ends get_amortization_parcels_evolver()

  public function generate_payback_date_n_value_tuplelist() {
    $borrowers_paybacks = AmortizationPayment
      ::where('payer_person_id', $this->id)
      ->where('is_loan_delivery', false)->get();
    $payback_date_n_value_tuplelist = array();
    foreach ($borrowers_paybacks as $payback) {
      $payback_date_n_value_tuple[] = $payback->paydate;
      $payback_date_n_value_tuple[] = $payback->valor_pago;
      $payback_date_n_value_tuplelist[] = $payback_date_n_value_tuple;
    } // ends foreach
  } // ends generate_payback_date_n_value_tuplelist()

} // ends class Borrower extends Person
