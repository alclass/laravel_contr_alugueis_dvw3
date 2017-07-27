<?php

namespace Tests\Unit;

use App\Models\Finance\CorrMonet;
use App\Models\Finance\MercadoIndice;
use App\Models\Utils\DateFunctions;

// use App\Models\Utils\FinancialFunctions;
// use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Carbon\Carbon;

class CorrMonetTest extends TestCase {
  /**
   * A basic test example.
   *
   * @return void
   */

/*
  public function setUp(){
    parent::setUp();
  }
*/

  public function get_mercado_indicador_id_for_SELIC() {

    $selic_char4indicator = MercadoIndice::get_default_char4indicator_for_corrmonet();

    $mercadoindicador = MercadoIndice
      ::where('indice4char', $selic_char4indicator)
      ->first();
    if ($mercadoindicador != null) {
      return $mercadoindicador->id;
    }
    $mercadoindicador = new MercadoIndice;
    $mercadoindicador->indice4char = $selic_char4indicator;
    $mercadoindicador->sigla = $selic_char4indicator;
    $mercadoindicador->save();
    return $mercadoindicador->id;
  }   // ends get_mercado_indicador_id_for_SELIC()

  public function   check_or_set_corrmonet_fractionindex_for_monthyeardateref(
      $monthyeardateref,
      $corrmonet_fractionindex
    ) {

    $selic_char4indicator = MercadoIndice::get_default_char4indicator_for_corrmonet();

    $corrmonet = CorrMonet
      ::where('indice4char', $selic_char4indicator)
      ->where('monthyeardateref', $monthyeardateref)
      ->first();

    if ($corrmonet!=null) {
      if ($corrmonet->fraction_value != $corrmonet_fractionindex) {
        $error_msg = 'CorrMonet exists in db with a non-expected value: fraction_value'.$corrmonet->fraction_value.'!= $corrmonet_fractionindex='.$corrmonet_fractionindex;
        throw new \Exception($error_msg, 1);
      }
    } else {
      $corrmonet = new CorrMonet;
      $corrmonet->mercado_indicador_id = $this->get_mercado_indicador_id_for_SELIC(); // this is the id for SELIC
      $corrmonet->indice4char = $selic_char4indicator;
      $corrmonet->fraction_value = $corrmonet_fractionindex;
      $corrmonet->monthyeardateref = $monthyeardateref;
    }
  }  // ends check_or_set_corrmonet_fractionindex_for_monthyeardateref()

  public function set_if_corrmonets_are_not_in_db() {

    // 1 of 3
    $monthyeardateref = new Carbon('2017-03-01');
    $corrmonet_fractionindex = 0.0105;

    $this->check_or_set_corrmonet_fractionindex_for_monthyeardateref(
      $monthyeardateref,
      $corrmonet_fractionindex
    );

    // 2 of 3
    $monthyeardateref = new Carbon('2017-04-01');
    $corrmonet_fractionindex = 0.0079;

    $this->check_or_set_corrmonet_fractionindex_for_monthyeardateref(
      $monthyeardateref,
      $corrmonet_fractionindex
    );

    // 3 of 3
    $monthyeardateref = new Carbon('2017-05-01');
    $corrmonet_fractionindex = 0.0093;

    $this->check_or_set_corrmonet_fractionindex_for_monthyeardateref(
      $monthyeardateref,
      $corrmonet_fractionindex
    );

  }  // ends set_if_corrmonets_are_not_in_db()

  public function testget_month_n_fractionindex_tuplelist_w_char4indic_n_daterange() {

    /*
        IMPORTANT:  class CorrMonet is an Eloquent model.
          Because of that, this unit test will fail in database is not
          properly working and approprietely data-fed.
    */
    // Inner test 1

    $this->set_if_corrmonets_are_not_in_db();

    $ini_date = new Carbon('2017-03-01');
    $end_date = new Carbon('2017-05-01');
    $monthyeardaterefs = DateFunctions
      ::get_ini_end_monthyeardaterefs_list($ini_date, $end_date);

    $corrmonet_fractionindex_list = [
      0.0105,
      0.0079,
      0.0093,
    ];

    $expected_corrmonet_month_n_fractionindex_tuplelist = array();
    foreach ($monthyeardaterefs as $i=>$monthyeardateref) {
      $corrmonet_fractionindex = $corrmonet_fractionindex_list[$i];
      $tuple = [$monthyeardateref, $corrmonet_fractionindex];
      $expected_corrmonet_month_n_fractionindex_tuplelist[] = $tuple;
    }

    $corrmonet_char4indicator = MercadoIndice::get_default_char4indicator_for_corrmonet();
    // Reminding that class CorrMonet does read data from a database
    // (see also docstring above)
    $returned_corrmonet_month_n_fractionindex_tuplelist = CorrMonet
      ::get_month_n_fractionindex_tuplelist_w_char4indic_n_daterange(
        $corrmonet_char4indicator,
        $ini_date,
        $end_date
      );

    $this->assertEquals(
      $returned_corrmonet_month_n_fractionindex_tuplelist,
      $expected_corrmonet_month_n_fractionindex_tuplelist
    );



  }  // ends testcalc_fmontant_from_imontant_plus_interest_array_plus_border_proportions()

} // ends class FinancialFunctionsTest extends TestCase
