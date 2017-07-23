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

} // ends class FinancialFunctionsTest extends TestCase
