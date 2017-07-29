<?php

namespace Tests\Unit;

use App\Models\Utils\FinancialFunctions;
// use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class FinancialFunctionsTest extends TestCase {
  /**
   * A basic test example.
   *
   * @return void
   */


  public function setUp(){
    parent::setUp();

  }

  public function testcalc_fmontant_from_imontant_n_interest_array() {

    // Inner test 1
    $initial_montant = 1000;
    $i1=0.04; $i2=0.015; $i3=0.027;
    $interest_array  = [$i1, $i2, $i3];
    // $expected_final_montant = 1000*(1+0.04)*(1+0.015)*(1+0.027*0.45);
    $expected_final_montant = $initial_montant*(1+$i1)*(1+$i2)*(1+$i3);
    $returned_final_montant = FinancialFunctions
      ::calc_fmontant_from_imontant_n_interest_array(
        $initial_montant,
        $interest_array
      );

    $this->assertEquals($returned_final_montant, $expected_final_montant);

  }  // ends testcalc_fmontant_from_imontant_n_interest_array()

  public function testcalc_fmontant_from_imontant_plus_interest_array_plus_border_proportions() {

    $initial_montant = 1000;
    $i1=0.04; $i2=0.015; $i3=0.027;
    $interest_array  = [$i1, $i2, $i3];
    $first_interest_proportion = 0.31;
    $p_i = $first_interest_proportion;
    $last_interest_proportion  = 0.45;
    $p_f = $last_interest_proportion;
    // $expected_final_montant = 1000*(1+0.04*0.31)*(1+0.015)*(1+0.027*0.45);
    $expected_final_montant = $initial_montant*(1+$i1*$p_i)*(1+$i2)*(1+$i3*$p_f);
    $returned_final_montant = FinancialFunctions
      ::calc_fmontant_from_imontant_plus_interest_array_plus_border_proportions(
        $initial_montant,
        $interest_array,
        $first_interest_proportion,
        $last_interest_proportion
      );

    $this->assertEquals($returned_final_montant, $expected_final_montant);

  }  // ends testcalc_fmontant_from_imontant_plus_interest_array_plus_border_proportions()

  public function testcalc_monthly_payment_pmt() {
    /*
    This method calculates a PMT, ie, what is the monthly payment based on P, r and n
    */
    $initial_montant = 101.515; // 1000;
    $n_months = 6;
    $interest_rate = 0.005;

    $p = $initial_montant;
    $n = $n_months;
    $r = $interest_rate;

    $valor_prestacao_ie_the_pmt_aprox = 20; // only for the expected calculation here to be confronted with the function return
    $balance = $initial_montant;
    for ($i=0; $i < $n_months; $i++) {
      $increase = $balance * (1 + $r);
      $balance = $increase - $valor_prestacao_ie_the_pmt_aprox;
    }

    $valor_aprox_prestacao_ie_pmt - $expected_zero;
    $returned_pmt = FinancialFunctions::calc_monthly_payment_pmt($p, $n, $r);

    // because variables are float, the two methods will not equal to the last decimal place
    // then, round them off to two decimal places and assertEquals

    $returned_pmt = round($returned_pmt, 2);
    $expected_pmt = round($expected_pmt, 2);

    $this->assertEquals(0, $valor_aprox_prestacao_ie_pmt);
    $this->assertEquals($returned_pmt, $valor_prestacao_ie_the_pmt_aprox);

    return $pmt;

  } // ends function testcalc_monthly_payment_pmt()

} // ends class FinancialFunctionsTest extends TestCase
