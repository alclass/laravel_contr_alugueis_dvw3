<?php

namespace Tests\Unit;

use App\Models\Utils\DateFunctions;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class DateFunctionsTest extends TestCase {
  /**
   * A basic test example.
   *
   * @return void
   */
  public function testBasicTest() {
       $this->assertTrue(true);
  }

  public function testcalc_fraction_of_n_days_in_specified_month() {
    $n_days_considered = 15;
    $monthyeardateref  = new Carbon('2017-04-01'); // April has 30 days
    $expected_answer   = 15/30;
    $n_days_as_month_fraction = DateFunctions
      ::calc_fraction_of_n_days_in_specified_month(
        $n_days_considered,
        $monthyeardateref
      );

    $this->assertEquals($n_days_as_month_fraction, $expected_answer);

    $n_days_considered = 7;
    $monthyeardateref  = new Carbon('2017-05-01'); // May has 31 days
    $expected_answer   = 7/31;
    $n_days_as_month_fraction = DateFunctions
      ::calc_fraction_of_n_days_in_specified_month(
        $n_days_considered,
        $monthyeardateref
      );
    $this->assertEquals($n_days_as_month_fraction, $expected_answer);

  }  // ends testcalc_fraction_of_n_days_in_specified_month()

  public function testfind_next_anniversary_date_with_triple_start_end_n_from() {


    /*
      Hypothesis 1:
        when $start_date + $cycle_time_in_years > $end_date,
        there's no anniversary whatsoever, so the answer should be null
        irrespective of $from_date

        Hypothesis 1 subtest 1: with $from_date is in between $start_date &  $end_date
          Answer: null
    */
    $start_date = new Carbon('2015-10-15'); // April has 30 days
    // Next line guarantees that $start_date + $cycle_time_in_years < $end_date
    $one_year_start_to_end = 1;
    $end_date   = $start_date->copy()->addYears($one_year_start_to_end);
    $cycle_time_in_years = 2;
    $mathematical_first_anniversary = $start_date->copy()->addYears($cycle_time_in_years);
    // Hypothesis 1:  when $from_date < $start_date
    $from_date  = $start_date->copy()->addMonths(6);
    // $this->assertLessThan($from_date, $end_date);
    $this->assertLessThan($end_date, $from_date);
    $expected_answer = null;
    $received_answer = DateFunctions
      ::find_next_anniversary_date_with_triple_start_end_n_from(
        $start_date,
        $end_date,
        $from_date,
        $cycle_time_in_years
      );
    $this->assertEquals($received_answer, $expected_answer);

    /*
      Hypothesis 1 subtest 2: with $from_date < $start_date
        Answer: null
    */
    $from_date  = $start_date->copy()->addMonths(-40);
    $this->assertLessThan($start_date, $from_date);
    $expected_answer = null;
    $received_answer = DateFunctions
      ::find_next_anniversary_date_with_triple_start_end_n_from(
        $start_date,
        $end_date,
        $from_date,
        $cycle_time_in_years
      );
    $this->assertEquals($received_answer, $expected_answer);

    /*
      Hypothesis 1 subtest 3: with $from_date after $end_date
        Answer: null
    */
    $from_date       =  $end_date->copy()->addDays(1); // could be anything after
    $expected_answer = null;
    $received_answer = DateFunctions
      ::find_next_anniversary_date_with_triple_start_end_n_from(
        $start_date,
        $end_date,
        $from_date,
        $cycle_time_in_years
      );
    $this->assertEquals($received_answer, $expected_answer);

    /*
      Hypothesis 1 subtest 4: with $from_date picking up today's date
        Answer: null
    */
    $from_date       = null; // it will become Carbon::today(), but whatever it is is unimportant under this Hypothesis
    $expected_answer = null;
    $received_answer = DateFunctions
      ::find_next_anniversary_date_with_triple_start_end_n_from(
        $start_date,
        $end_date,
        $from_date,
        $cycle_time_in_years
      );
    $this->assertEquals($received_answer, $expected_answer);

    /*
      Hypothesis 2:
        when $start_date + $cycle_time_in_years <= $end_date,
        there's at least one anniversary, so the answer should be to $from_date

      Hypothesis 2 subtest 1:
        $start_date < $from_date < $some_anniversary < $end_date
          Answer: $some_anniversary
    */
    $start_date = new Carbon('2015-10-15'); // April has 30 days
    // Next line guarantees that $start_date + $cycle_time_in_years < $end_date
    $from_date  = $start_date->copy()->addMonth(6);
    $this->assertLessThan($from_date, $start_date);
    $cycle_time_in_years = 1;
    $first_anniversary = $start_date->copy()->addYears($cycle_time_in_years);
    $this->assertLessThan($first_anniversary, $from_date);
    $end_date   = $start_date->copy()->addYears(4);
    $this->assertLessThan($end_date, $first_anniversary);
    $expected_answer = $first_anniversary;
    $received_answer = DateFunctions::find_next_anniversary_date_with_triple_start_end_n_from(
      $start_date,
      $end_date,
      $from_date,
      $cycle_time_in_years
    );
    $this->assertEquals($received_answer, $expected_answer);

    /*
      Hypothesis 2:
        when $start_date + $cycle_time_in_years <= $end_date,
        there's at least one anniversary, so the answer should be to $from_date

      Hypothesis 2 subtest 2:
        $from_date < $start_date < < $some_anniversary < $end_date
          Answer: $some_anniversary
    */

    $start_date = new Carbon('2015-10-15'); // April has 30 days
    // Next line guarantees that $start_date + $cycle_time_in_years < $end_date
    $from_date  = $start_date->copy()->addMonth(-6);
    $this->assertLessThan($start_date, $from_date);
    $cycle_time_in_years = 1;
    $first_anniversary = $start_date->copy()->addYears($cycle_time_in_years);
    $this->assertLessThan($first_anniversary, $from_date);
    $end_date   = $start_date->copy()->addYears(4);
    $this->assertLessThan($end_date, $first_anniversary);
    $expected_answer = $first_anniversary;
    $received_answer = DateFunctions::find_next_anniversary_date_with_triple_start_end_n_from(
      $start_date,
      $end_date,
      $from_date,
      $cycle_time_in_years
    );
    $this->assertEquals($received_answer, $expected_answer);

    /*
      Hypothesis 2:
        when $start_date + $cycle_time_in_years <= $end_date,
        there's at least one anniversary, so the answer should be to $from_date

      Hypothesis 2 subtest 3:
        $start_date < $last_anniversary < $from_date < $end_date
          Answer: null
    */

    $start_date = new Carbon('2015-10-15'); // April has 30 days
    // Next line guarantees that $start_date + $cycle_time_in_years < $end_date
    $end_date   = $start_date->copy()->addYears(3)->addMonths(4);
    $cycle_time_in_years = 1;
    $from_date  = $start_date->copy()->addYears(3)->addMonths(3);
    $this->assertLessThan($end_date, $from_date);
    $last_anniversary = $start_date->copy()->addYears(3*$cycle_time_in_years);
    $this->assertLessThan($from_date, $last_anniversary);
    $expected_answer = null;
    $received_answer = DateFunctions
      ::find_next_anniversary_date_with_triple_start_end_n_from(
        $start_date,
        $end_date,
        $from_date,
        $cycle_time_in_years
      );
    $this->assertEquals($received_answer, $expected_answer);

    /*
      Hypothesis 2:
        when $start_date + $cycle_time_in_years <= $end_date,
        there's at least one anniversary, so the answer should be to $from_date

      Hypothesis 2 subtest 4:
        $start_date < < $last_anniversary < $end_date < $from_date
          Answer: null
    */

    $start_date = new Carbon('2015-10-15'); // April has 30 days
    // Next line guarantees that $start_date + $cycle_time_in_years < $end_date
    $end_date   = $start_date->copy()->addYears(4);
    $from_date  = $end_date->copy()->addDays(1);
    $this->assertLessThan($from_date, $end_date);
    $cycle_time_in_years = 1;
    $last_anniversary = $end_date->copy()->addYears(-$cycle_time_in_years);
    $this->assertLessThan($end_date, $last_anniversary);
    $expected_answer = null;
    $received_answer = DateFunctions::find_next_anniversary_date_with_triple_start_end_n_from(
      $start_date,
      $end_date,
      $from_date,
      $cycle_time_in_years
    );
    $this->assertEquals($received_answer, $expected_answer);


    /*
      Hypothesis 3:
        when $last_anniversary coincides with $end_date

      Hypothesis 3 subtest 1:
        when $from_date coincides with $end_date (which is = $last_anniversary )
          Answer: $end_date (all three [$from_date, $last_anniversary, $end_date] are equal)
    */

    $end_date   = $start_date->copy()->addYears(3);
    $cycle_time_in_years = 1;
    $from_date  = $end_date->copy();
    $last_anniversary = $start_date->copy()->addYears(3*$cycle_time_in_years);
    $this->assertEquals($from_date, $last_anniversary, $end_date);
    $expected_answer = $end_date->copy();
    $received_answer = DateFunctions
      ::find_next_anniversary_date_with_triple_start_end_n_from(
        $start_date,
        $end_date,
        $from_date,
        $cycle_time_in_years
      );
    $this->assertEquals($received_answer, $expected_answer);

    /*
      Hypothesis 3:
        when $last_anniversary coincides with $end_date

      Hypothesis 3 subtest 2:
        Borderline test: $from_date is a little before $end_date (which is = $last_anniversary )
          Answer: $end_date (which is = $last_anniversary )
    */

    $from_date  = $end_date->copy()->addDays(-3);
    $this->assertLessThan($last_anniversary, $from_date);
    $received_answer = DateFunctions
      ::find_next_anniversary_date_with_triple_start_end_n_from(
        $start_date,
        $end_date,
        $from_date,
        $cycle_time_in_years
      );
    $this->assertEquals($received_answer, $expected_answer);

    /*
      Hypothesis 3:
        when $last_anniversary coincides with $end_date

      Hypothesis 3 subtest 3:
        Borderline test: $from_date is a little after $end_date (which is = $last_anniversary )
          Answer: null (for $from_date is outside range [$start_date, $end_date])
    */

    $start_date = new Carbon('2015-10-15'); // April has 30 days
    // Next line guarantees that $start_date + $cycle_time_in_years < $end_date
    $end_date   = $start_date->copy()->addYears(3);
    $cycle_time_in_years = 1;
    $from_date  = $end_date->copy()->addDays(3);
    $this->assertLessThan($from_date, $end_date);
    $last_anniversary = $start_date->copy()->addYears(3*$cycle_time_in_years);
    $this->assertEquals($last_anniversary, $end_date);
    $expected_answer = null;
    $received_answer = DateFunctions
      ::find_next_anniversary_date_with_triple_start_end_n_from(
        $start_date,
        $end_date,
        $from_date,
        $cycle_time_in_years
      );
    $this->assertEquals($received_answer, $expected_answer);


  }  // ends testfind_next_anniversary_date_with_triple_start_end_n_from()

  public function testfind_conventional_cutdate_from_monthyeardateref() {

    // Inner test 1
    $monthyeardateref = new Carbon('2017-05-01');
    // $monthyeardateref->setTime(0,0,0);
    $cut_day_in_month = 11;
    $expected_conventional_cutdate_from_monthyeardateref = new Carbon('2017-06-11');
    $returned_conventional_cutdate_from_monthyeardateref = DateFunctions
      ::find_conventional_cutdate_from_monthyeardateref(
        $monthyeardateref,
        $cut_day_in_month
      );
    $this->assertEquals(
      $returned_conventional_cutdate_from_monthyeardateref,
      $expected_conventional_cutdate_from_monthyeardateref
    );

    // Inner test 2
    $monthyeardateref = new Carbon('2016-02-29');
    // $monthyeardateref->setTime(0,0,0);
    $cut_day_in_month = 9;
    $expected_conventional_cutdate_from_monthyeardateref = new Carbon('2016-03-09');
    $returned_conventional_cutdate_from_monthyeardateref = DateFunctions
      ::find_conventional_cutdate_from_monthyeardateref(
        $monthyeardateref,
        $cut_day_in_month
      );
    $this->assertEquals(
      $returned_conventional_cutdate_from_monthyeardateref,
      $expected_conventional_cutdate_from_monthyeardateref
    );

    // Inner test 3
    $monthyeardateref = new Carbon('2016-02-29');
    // $monthyeardateref->setTime(0,0,0);
    $cut_day_in_month = null;
    $expected_conventional_cutdate_from_monthyeardateref = null;
    $returned_conventional_cutdate_from_monthyeardateref = DateFunctions
      ::find_conventional_cutdate_from_monthyeardateref(
        $monthyeardateref,
        $cut_day_in_month
      );
    $this->assertEquals(
      $returned_conventional_cutdate_from_monthyeardateref,
      $expected_conventional_cutdate_from_monthyeardateref
    );

    // Inner test 4
    $monthyeardateref = null;
    // $monthyeardateref->setTime(0,0,0);
    $cut_day_in_month = 3;
    // Simulate the default $monthyeardateref when it's received as null
    $todays_monthyeardateref = Carbon::today();
    $todays_monthyeardateref->day(1);
    $todays_monthyeardateref->setTime(0,0,0);
    $cutdate = $todays_monthyeardateref->copy()->addMonths(1);
    $cutdate->day($cut_day_in_month);
    $expected_conventional_cutdate_from_monthyeardateref = $cutdate;
    $returned_conventional_cutdate_from_monthyeardateref = DateFunctions
      ::find_conventional_cutdate_from_monthyeardateref(
        $monthyeardateref,
        $cut_day_in_month
      );
    $this->assertEquals(
      $returned_conventional_cutdate_from_monthyeardateref,
      $expected_conventional_cutdate_from_monthyeardateref
    );

  }  // ends testfind_conventional_cutdate_from_monthyeardateref()


  public function testfind_conventional_monthyeardateref_with_date_n_cutday() {

    // Inner test 1
    $date = new Carbon('2017-05-05');
    // $monthyeardateref->setTime(0,0,0);
    $cut_day_in_month = 11;
    $expected_conventional_monthyeardateref_with_date_n_cutday = new Carbon('2017-04-01');
    $expected_conventional_monthyeardateref_with_date_n_cutday->setTime(0,0,0);
    $returned_conventional_monthyeardateref_with_date_n_cutday = DateFunctions
      ::find_conventional_monthyeardateref_with_date_n_cutday(
        $date,
        $cut_day_in_month
      );
    $this->assertEquals(
      $returned_conventional_monthyeardateref_with_date_n_cutday,
      $expected_conventional_monthyeardateref_with_date_n_cutday
    );

    // Inner test 2
    $date = new Carbon('2017-05-15');
    // $monthyeardateref->setTime(0,0,0);
    $cut_day_in_month = 11;
    $expected_conventional_monthyeardateref_with_date_n_cutday = new Carbon('2017-05-01');
    $expected_conventional_monthyeardateref_with_date_n_cutday->setTime(0,0,0);
    $returned_conventional_monthyeardateref_with_date_n_cutday = DateFunctions
      ::find_conventional_monthyeardateref_with_date_n_cutday(
        $date,
        $cut_day_in_month
      );
    $this->assertEquals(
      $returned_conventional_monthyeardateref_with_date_n_cutday,
      $expected_conventional_monthyeardateref_with_date_n_cutday
    );

    // Inner test 3
    $date = new Carbon('2017-05-15');
    // $monthyeardateref->setTime(0,0,0);
    $cut_day_in_month = null;
    $expected_conventional_monthyeardateref_with_date_n_cutday = null;
    $returned_conventional_monthyeardateref_with_date_n_cutday = DateFunctions
      ::find_conventional_monthyeardateref_with_date_n_cutday(
        $date,
        $cut_day_in_month
      );
    $this->assertEquals(
      $returned_conventional_monthyeardateref_with_date_n_cutday,
      $expected_conventional_monthyeardateref_with_date_n_cutday
    );

  }  // ends testfind_conventional_cutdate_from_monthyeardateref()

  public function testget_ini_end_months_list() {

    // Inner test 1
    $d1 = new Carbon('2017-01-05');
    $d2 = new Carbon('2017-02-05');
    $d3 = new Carbon('2017-03-05');
    $d4 = new Carbon('2017-04-05');
    $d5 = new Carbon('2017-05-15');
    $expected_ini_fim_months_list = [$d1,$d2,$d3,$d4,$d5];
    $returned_ini_fim_months_list = DateFunctions
      ::get_ini_end_months_list(
        $d1,
        $d5
      );
    $this->assertEquals(
      $expected_ini_fim_months_list,
      $expected_ini_fim_months_list
    );

  }  // ends testget_ini_end_months_list()


  public function testget_ini_end_monthyeardaterefs_list() {

    // Inner test 1
    $d1 = new Carbon('2017-01-01');
    $d2 = new Carbon('2017-02-01');
    $d3 = new Carbon('2017-03-01');
    $d4 = new Carbon('2017-04-01');
    $d5 = new Carbon('2017-05-01');
    $expected_ini_fim_months_list = [$d1,$d2,$d3,$d4,$d5];
    $returned_ini_fim_months_list = DateFunctions
      ::get_ini_end_monthyeardaterefs_list(
        $d1,
        $d5
      );
    $this->assertEquals(
      $returned_ini_fim_months_list,
      $expected_ini_fim_months_list
    );

    // Inner test 2
    $d1 = new Carbon('2017-01-01');
    $d2 = $d1->copy();
    $expected_ini_fim_months_list = [$d1];
    $returned_ini_fim_months_list = DateFunctions
      ::get_ini_end_monthyeardaterefs_list(
        $d1,
        $d2
      );
    $this->assertEquals(
      $returned_ini_fim_months_list,
      $expected_ini_fim_months_list
    );

    // Inner test 3
    $d1 = null;
    $d2 = null;
    $conventional_monthyeardateref = DateFunctions
      ::find_conventional_monthyeardateref_with_date_n_dueday();
    $expected_ini_fim_months_list = [$conventional_monthyeardateref];
    $returned_ini_fim_months_list = DateFunctions
      ::get_ini_end_monthyeardaterefs_list(
        $d1,
        $d2
      );
    $this->assertEquals(
      $returned_ini_fim_months_list,
      $expected_ini_fim_months_list
    );

  }  // ends testget_ini_end_monthyeardaterefs_list()

  public function testget_month_n_monthdays_fraction_tuple_list() {

    // Inner test 1
    $months_list = array();
    $expected_month_n_monthdays_fraction_tuple_list = array();
    $date = new Carbon('2017-01-10');
    $months_list[]=$date;
    $days_in_month_fraction = 10/31;
    $tuple = [$date, $days_in_month_fraction];
    $expected_month_n_monthdays_fraction_tuple_list[] = $tuple;
    $date = new Carbon('2017-02-17');
    $months_list[]=$date;
    $days_in_month_fraction = 17/28;
    $tuple = [$date, $days_in_month_fraction];
    $expected_month_n_monthdays_fraction_tuple_list[] = $tuple;
    $date = new Carbon('2017-03-08');
    $months_list[]=$date;
    $days_in_month_fraction = 8/31;
    $tuple = [$date, $days_in_month_fraction];
    $expected_month_n_monthdays_fraction_tuple_list[] = $tuple;
    $date = new Carbon('2017-04-01');
    $months_list[]=$date;
    $days_in_month_fraction = 1/30;
    $tuple = [$date, $days_in_month_fraction];
    $expected_month_n_monthdays_fraction_tuple_list[] = $tuple;
    $date = new Carbon('2017-05-05');
    $months_list[]=$date;
    $days_in_month_fraction = 5/31;
    $tuple = [$date, $days_in_month_fraction];
    $expected_month_n_monthdays_fraction_tuple_list[] = $tuple;

    $returned_month_n_monthdays_fraction_tuple_list = DateFunctions
      ::get_month_n_monthdays_fraction_tuple_list(
        $months_list
      );
    $this->assertEquals(
      $returned_month_n_monthdays_fraction_tuple_list,
      $returned_month_n_monthdays_fraction_tuple_list
    );

  } // ends testget_month_n_monthdays_fraction_tuple_list()


  public function testget_month_n_monthdays_fraction_tuplelist_borders_can_fraction() {

    // Inner test 1
    $months_list = array();
    $expected_month_n_monthdays_fraction_tuple_list = array();
    $date = new Carbon('2017-01-10');
    $months_list[]=$date;
    $days_in_month_fraction = (31-10+1)/31;
    $tuple = [$date, $days_in_month_fraction];
    $expected_month_n_monthdays_fraction_tuple_list[] = $tuple;
    $date = new Carbon('2017-02-17');
    $months_list[]=$date;
    $tuple = [$date, 1];
    $expected_month_n_monthdays_fraction_tuple_list[] = $tuple;
    $date = new Carbon('2017-03-08');
    $months_list[]=$date;
    $tuple = [$date, 1];
    $expected_month_n_monthdays_fraction_tuple_list[] = $tuple;
    $date = new Carbon('2017-04-01');
    $months_list[]=$date;
    $tuple = [$date, 1];
    $expected_month_n_monthdays_fraction_tuple_list[] = $tuple;
    $date = new Carbon('2017-05-05');
    $months_list[]=$date;
    $days_in_month_fraction = 5/31;
    $tuple = [$date, $days_in_month_fraction];
    $expected_month_n_monthdays_fraction_tuple_list[] = $tuple;

    $returned_month_n_monthdays_fraction_tuple_list = DateFunctions
      ::get_month_n_monthdays_fraction_tuplelist_borders_can_fraction(
        $months_list
      );
    $this->assertEquals(
      $returned_month_n_monthdays_fraction_tuple_list,
      $expected_month_n_monthdays_fraction_tuple_list
    );

  } // ends testget_month_n_monthdays_fraction_tuplelist_borders_can_fraction()


  public function testcorrect_for_proportional_first_n_last_months_n_return_fractionarray() {

    // Inner test 1
    $months_list = array();
    $corrmonet_month_n_index_tuplelist = array();
    $corrmonet_month_n_index_tuplelist[] = [new Carbon('2017-04-01'), 0.0023];
    $corrmonet_month_n_index_tuplelist[] = [new Carbon('2017-05-01'), 0.0031];
    $corrmonet_month_n_index_tuplelist[] = [new Carbon('2017-06-01'), 0.0017];
    $ini_date = new Carbon('2017-04-10'); // monthdays fraction = 21/30
    $end_date = new Carbon('2017-06-10'); // monthdays fraction = 10/30

    $expected_fractionindices_array = [0.0023*(21/30), 0.0031, 0.0017*(10/30)];
    $returned_fractionindices_array = DateFunctions
      ::correct_for_proportional_first_n_last_months_n_return_fractionarray(
        $corrmonet_month_n_index_tuplelist,
        $ini_date,
        $end_date
      );

    $this->assertEquals(
      $returned_fractionindices_array,
      $expected_fractionindices_array
    );

    // Inner test 2
    $months_list = array();
    $corrmonet_month_n_index_tuplelist = array();
    $corrmonet_month_n_index_tuplelist[] = [new Carbon('2017-04-17'), 0.0023];
    $corrmonet_month_n_index_tuplelist[] = [new Carbon('2017-05-01'), 0.0031];
    $corrmonet_month_n_index_tuplelist[] = [new Carbon('2017-06-23'), 0.0017];
    $corrmonet_month_n_index_tuplelist[] = [new Carbon('2017-07-31'), 0.0024];
    $ini_date = new Carbon('2017-04-05'); $ini_monthdays_fraction = (30-5+1)/30;
    $end_date = new Carbon('2017-07-17'); $end_monthdays_fraction = 17/31;

    $expected_fractionindices_array = [
      0.0023 * $ini_monthdays_fraction,
      0.0031,
      0.0017,
      0.0024 * $end_monthdays_fraction,
    ];
    $returned_fractionindices_array = DateFunctions
      ::correct_for_proportional_first_n_last_months_n_return_fractionarray(
        $corrmonet_month_n_index_tuplelist,
        $ini_date,
        $end_date
      );

    $this->assertEquals(
      $returned_fractionindices_array,
      $expected_fractionindices_array
    );

  } // ends correct_for_proportional_first_n_last_months_n_return_fractionarray()

} // ends class DateFunctionsTest extends TestCase
