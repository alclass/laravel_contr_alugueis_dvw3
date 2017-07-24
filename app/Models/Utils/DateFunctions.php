<?php
namespace App\Models\Utils;

// To import the DateFunctions class in the Laravel app Here
// use App\Models\Utils\DateFunctions;

use Carbon\Carbon;
// use App\Models\Utils\FinancialFunctions;

/*

Summary of [static] methods in here:

[1]
calc_fraction_of_n_days_in_specified_month()
[has unittest]

[1]
is_date_on_last_day_of_month()
[doesn't have unittest]

[2]
find_conventional_cutdate_from_monthyeardateref
[has unittest]

[2 (same as above, just treating null cutday to default)]
find_conventional_duedate_from_monthyeardateref
[wrapper of the one above]

[3]
find_conventional_monthyeardateref_with_date_n_cutday()
[has unittest]

[3 (same as above, just treating null cutday to default)]
find_conventional_monthyeardateref_with_date_n_dueday()
[wrapper of the one above]

[4]
find_next_anniversary_date_with_triple_start_end_n_from()
[has unittest]

[5]
get_default_cutdate_in_month()
[no need for a direct unittest, it fetches the default by env or by const]

[6]
get_ini_fim_months_list()
[has unittest]

[7]
get_ini_fim_monthyeardaterefs_list()
[has unittest]

[8]
get_month_n_monthdays_fraction_tuple_list()
[has unittest]

[9]
get_month_n_monthdays_fraction_tuplelist_borders_can_fraction()
[has unittest]

*/

class DateFunctions {

  const CUT_PAY_IN_MONTH_FOR_FINDING_MONTHYEARDATEREF = 10; // this will hold if env() does not have it
  const LOOP_ITERATION_PROTECTION_COUNTER_FOR_MONTHS_YEARS = 10000;

// old name: find_next_anniversary_date_with_triple_start_end_n_from()

  public static function get_default_cutdate_in_month() {

    $cut_day_in_month = (int) env(
      'PAY_DAY_WHEN_MONTHLY',
      self::CUT_PAY_IN_MONTH_FOR_FINDING_MONTHYEARDATEREF
    );
    return $cut_day_in_month;
  }

  public static function get_total_days_in_specified_month($date) {
    $month = $date->month;
    $year  = $date->year;
    return cal_days_in_month(CAL_GREGORIAN, $month, $year);
  } // ends [static] get_total_days_in_specified_month()

  public static function is_date_on_last_day_of_month($date) {
    if ($date==null) {
      return false;
    }
    $n_days_in_month = $date->day;
    $total_days_in_month = DateFunctions::get_total_days_in_specified_month($date);
    if ($n_days_in_month == $total_days_in_month) {
      return true;
    }
    return false;
  } // ends [static] is_date_on_last_day_of_month()

  public static function calc_fraction_of_n_days_in_specified_month(
      $n_days_considered = null,
      $monthyeardateref  = null
    ) {
    /*

    */
    if ($n_days_considered == null || $monthyeardateref == null) {
      return null;
    }
    if ($n_days_considered < 1) {
      return 0;
    }
    $total_days_in_specified_month = self::get_total_days_in_specified_month($monthyeardateref);
    if ($n_days_considered >= $total_days_in_specified_month) {
      return 1;
    }
    $n_days_as_month_fraction = $n_days_considered / $total_days_in_specified_month;
    return $n_days_as_month_fraction;
  } // ends [static] calc_fraction_of_n_days_in_specified_month()

  public static function find_next_anniversary_date_with_triple_start_end_n_from(
    $start_date,
    $end_date,
    $from_date = null,
    $cycle_time_in_years = 1
  ) {
    /*
    Explanation with an example:

    Let
      $start_date be a contract's start date
      $from_date be the relative point-date from which one wants to know a next price yearly reajust
      $end_date be the contract's end date

    Suppose:
      $start_date = 2015-01-01
      $end_date = 2017-06-30 (a 30-month contract)
      $from_date = 2015-10-01

      One wants to know when the next price yearly reajust will happen from 2015-10-01
        The answer is, in this simple example, 2016-01-01
          (ie, the next anniversary counting from 2015-10-01)

      *** This function does the calculation above exemplified. ***

    Another example:
      $start_date = 2014-04-04
      $end_date   = 2016-10-03 (a 30-month contract)
      $from_date = 2016-01-10
        Answer should be 2016-04-04

      Examples with edge dates (out of contract date range):
      * Example 1 with edge dates
          $start_date = 2014-04-04
          $end_date = 2016-10-03 (a 30-month contract)
          $from_date = 2013-01-10  (NOTICE that this will not raise an exception)
            Answer should be = 2015-04-04 (simple the first anniversary)
      * Example 2 with edge dates
          $since_date = 2014-04-04
          $from_date = 2018-01-10  (NOTICE that this will not raise an exception)
          $end_date = 2016-10-03 (a 30-month contract)
            Answer should be null
            (because the last reajust 2016-04-04 is before $from_date, there will not be another one til the end of contract)

      The programming plan was to device a recursive method, but later on
        we decided for a while-loop version limiting it to 10000 (or other via config) iterations
        (just for noticing, the runtime did not protect the infinite loop we have
         when still developing the recursive version, crashing the machine...)

    */
    // First off, treat null case for both start and end dates,
    // these two cannot be null, if they are, raise exception
    $null_date_error_msg = "In DateFunctions::find_next_anniversary_date_with_triple_start_end_n_from() -> null case for ";
    if ($start_date==null) {
      $null_date_error_msg .= "start date (=$start_date), it cannot be null";
      throw new Exception($null_date_error_msg, 1);
    }
    if ($end_date==null) {
      $null_date_error_msg .= "end date (=$start_date), it cannot be null";
      throw new Exception($null_date_error_msg, 1);
    }
    // Second off, treat null case for $from_date, if it is, default it to today()
    if ($from_date==null) {
      $from_date = Carbon::today();
    }
    // print ('$from_date             = ' . $from_date->format('Y-m-d') . "\n");
    // 1st logical case resulting in an immediate return
    if ($start_date > $end_date) {
      // $start_date > $end_date, it's not logical, return null, no exception raised
      return null;
    }
    // 2nd logical case resulting in an immediate return
    if ($from_date > $end_date) {
      // $from_date > $end_date, there will not be a next anniversary in this case
      return null;
    }
    // 3rd logical case as seen in the examples in the docstring
    // if $from_date <= $start_date, let it simply equal $start_date,
    // for we are simply interested finding the next anniversary
    // use method copy(), DO NOT simply attribute it with "="...
    if ($from_date <= $start_date) {
      /*
       In this case, force $from_date to be equal to $start_date
       In this specific case, if there's a first anniversary, that will be the result
      */
      $from_date = $start_date->copy();
    }
    $next_yearly_pointdate = $start_date->copy()->addYears($cycle_time_in_years);
    $loop_iteration_protection_counter = 0;
    while (true) {
      // print ('$loop_iteration_protection_counter = ' . $loop_iteration_protection_counter . "\n");
      // print ('$next_yearly_pointdate             = ' . $next_yearly_pointdate->format('Y-m-d') . "\n");
      if ($next_yearly_pointdate > $end_date) {
        // In this case, there won't be a next anniversary, ie, anniversary is beyond end date
        return null;
      }
      if ($from_date <= $next_yearly_pointdate) {
        // In this case, $next_yearly_pointdate is the searched next anniversary, that's the result
        return $next_yearly_pointdate;
      }
      $loop_iteration_protection_counter += 1;
      if ($loop_iteration_protection_counter > self::LOOP_ITERATION_PROTECTION_COUNTER_FOR_MONTHS_YEARS) {
        return null;
      };
      // Cycle to next anniversary and loop on
      $next_yearly_pointdate = $next_yearly_pointdate->addYears($cycle_time_in_years);
    } // ends while ($loop_on)

    $logical_error_msg = "The program should not logically have arrived at this point. Logical Exception in find_next_anniversary_date_with_triple_start_end_n_from()";
    throw new Exception($logical_error_msg, 1);

  } // ends [static] find_next_anniversary_date_with_triple_start_end_n_from()

  public static function find_conventional_monthyeardateref_with_date_n_cutday(
    $date = null,
    $cut_day_in_month
  ) {
    /*
    This method does the following
      if day is within [1,10] monthref is the previous one
      if day is 11 and above monthref is the current one

      That is, the convention is:
        if day is within [1,CUT_DAY] monthref is the previous one
        if day is CUT_DAY+1 and above monthref is the current one

      Example:
        [1]
        Suppose CUT_DAY = 10 and $date=2017-06-07
        Because day (=07) is within [1,10], monthref is the previous one
          to 06 (June), ie, it will be 05 (May)

        Suppose CUT_DAY = 10 and $date=2017-06-17
        Because day (=17) is above CUT_DAY = 10, monthref is the same as date's month,
          it will be 06 (June)
    */

    if ($cut_day_in_month == null) {
      /*
      See also method find_conventional_monthyeardateref_with_date_n_dueday()
      which calls a default for $cut_day_in_month and then wraps to this method
      */
      return null;
    }
    $date = ( $date != null ? $date : Carbon::today() );
    if ($date->day >= 1 && $date->day <= $cut_day_in_month) {
      // pick up previous month and ajust day
      $monthyeardateref = $date->copy()->addMonths(-1);
    } else {
      $monthyeardateref = $date->copy();
    }
    $monthyeardateref->day(1);
    $monthyeardateref->setTime(0,0,0);
    return $monthyeardateref;
  } // ends [static] calc_conventional_monthyeardateref_with_date_n_cutday()

  public static function find_conventional_monthyeardateref_with_date_n_dueday(
    $date = null,
    $dueday_in_month = null
  ) {
    /*
        Explanation is in the docstring for method
          calc_conventional_monthyeardateref_with_date_n_cutday()

        This method is a wrapper to that one, the only difference is that
          this one resolves a default for null $cut_day_in_month
          whereas the callee doesn't, ie, it there returns null against
          null $cut_day_in_month

    */
    if ($dueday_in_month == null) {
      $dueday_in_month = self::get_default_cutdate_in_month();
    }
    return self::find_conventional_monthyeardateref_with_date_n_cutday(
      $date,
      $dueday_in_month
    );
  } // ends find_rent_monthyeardateref_under_convention()

  public static function find_conventional_cutdate_from_monthyeardateref(
    $monthyeardateref,
    $cut_day_in_month
  ) {
    /*
        $cutdate is the next month to $monthyeardateref on day = $cut_day_in_month
    */
    if ($cut_day_in_month == null) {
      return null;
    }
    if ($monthyeardateref == null) {
      $monthyeardateref = Carbon::today();
      $monthyeardateref->day(1); // all $monthyeardateref's have day=1
      $monthyeardateref->setTime(0,0,0);
    }
    // $cutdate is next month on cut_day
    $cutdate = $monthyeardateref->copy()->addMonths(1);
    $cutdate->day($cut_day_in_month);
    return $cutdate;

  } // ends [static] find_conventional_cutdate_from_monthyeardateref()


  public static function find_conventional_duedate_from_monthyeardateref(
      $date = null,
      $pay_day_when_monthly = null
    ) {
      /*
          This method is a wrapper to
            find_conventional_cutdate_from_monthyeardateref()
      */
    if ($pay_day_when_monthly == null) {
      $pay_day_when_monthly = self::get_default_cutdate_in_month();
    }
    return self::find_conventional_cutdate_from_monthyeardateref(
      $date,
      $pay_day_when_monthly
    );
  } // ends [static] calculate_monthly_duedate_under_convention()


  public static function get_ini_fim_months_list(
      $date_ini = null,
      $date_fim = null
    ) {
    /*
    */
    if ($date_ini == null) {
        throw new Exception(
          "Error: date_ini $date_ini is null in DateFunctions::get_ini_fim_months_list()", 1
        );
    }
    $date_fim = ($date_fim != null ? $date_fim : Carbon::today());
    // If months are the same, set greater date to the 1-element array and return right away
    if ($date_ini->month == $date_fim->month) {
      $month_list = [ $date_ini ];
      if ($date_ini < $date_fim) {
        $month_list = [ $date_fim ];
      }
      return $month_list;
    } // The next if excludes mere month equality, so if it's true, $date_ini has a later month than $date_fim
    if ($date_ini > $date_fim) {
        throw new Exception(
          "Error: date_ini ($date_ini) > date_fim ($date_fim) [in month criterium] is in DateFunctions::get_ini_fim_months_list()", 1
        );
    }
    $month_list = array();
    $n_months = $date_ini->diffInMonths($date_fim);
    $ongoing_month_ref = $date_ini; // this first doesn't need ->copy()

    for ($i=0; $i < $n_months; $i++) {
      $month_list[] = $ongoing_month_ref;
      $ongoing_month_ref = $ongoing_month_ref->copy()->addMonths(1);
    }
    // Now add $date_fim itself
    $month_list[] = $date_fim;
    return $month_list;

  } // ends [static] get_ini_fim_monthrefs_list()

  public static function get_ini_fim_monthyeardaterefs_list(
    $p_monthyeardateref_ini = null,
    $p_monthyeardateref_fim = null
  ) {
  /*

  */
  if ($p_monthyeardateref_ini == null) {
    $p_monthyeardateref_ini = self::find_conventional_monthyeardateref_with_date_n_dueday();
  }
  if ($p_monthyeardateref_fim == null) {
    $p_monthyeardateref_fim = self::find_conventional_monthyeardateref_with_date_n_dueday();
  }
  // Force convention of day=1 and time(0,0,0)
  // This will guarantee middle months will also have that convention
  $monthyeardateref_ini = $p_monthyeardateref_ini->copy()->day(1);
  $monthyeardateref_ini->setTime(0,0,0);
  $monthyeardateref_fim = $p_monthyeardateref_fim->copy()->day(1);
  $monthyeardateref_fim->setTime(0,0,0);
  $ini_fim_monthyeardaterefs_list = self::get_ini_fim_months_list(
    $monthyeardateref_ini,
    $monthyeardateref_fim
  );
  return $ini_fim_monthyeardaterefs_list;

  } // ends [static] get_ini_fim_monthyeardaterefs_list()

  public static function get_month_n_monthdays_fraction_tuple_list(
    $months_list = null
  ) {
  /*

  */
  if ($months_list == null) {
    return null;
  }
  $month_n_days_in_month_fraction_tuple_list = array();
  foreach ($months_list as $monthdate) {
    $total_days_in_month = self::get_total_days_in_specified_month($monthdate);
    $n_days = $monthdate->day;
    $days_in_month_fraction = $n_days / $total_days_in_month;
    $tuple = [$monthdate, $days_in_month_fraction];
    $month_n_days_in_month_fraction_tuple_list[] = $tuple;
  } // ends foreach
  return $month_n_days_in_month_fraction_tuple_list;

  } // ends [static] get_month_n_monthdays_fraction_tuple_list()

  public static function get_month_n_monthdays_fraction_tuplelist_borders_can_fraction(
      $months_list = null
    ) {
    /*

      Example of a month_n_monthdays_fraction_tuplelist_borders_can_fraction:

    [
      [Carbon('2017-04-10'), 21/30], // 21 days from day 10 (inclusive) to day 30
      [Carbon('2017-05-dd'), 1],
      [Carbon('2017-06-dd'), 1],
      [Carbon('2017-07-dd'), 1],
      [Carbon('2017-08-dd'), 1],
      [Carbon('2017-09-10'), 10/30], // 10 days from day 1 to day 10 (inclusive)
    ]

        IMPORTANT:
        There is a difference between this method and the one above (or below)
          self::get_month_n_monthdays_fraction_tuple_list()
        This difference, besides the fact that middle months all 100% (ie, 1 complete),
         considers the interpretation that the first month fraction is the days
         existing until the end of month, not the number of days elapsed.
        This is seen in the example above:
        ie =>  [Carbon('2017-04-10'), 21/30], // 21 days from day 10 (inclusive) to day 30

        To illustrate this differente, consider the case of:
        1st month is 2017-06-10.
        In the other method, fraction is 10/30 = 1/3 = 0.333...
        In this method, fraction is (30-10+1)/30 = 21/30

    */

    if (empty($months_list)) {
      return null;
    }
    $month_n_days_in_month_fraction_tuple_list = array();
    // Fill up the tuplelist with 1's for the monthdays fraction
    foreach ($months_list as $monthdate) {
      $tuple = [$monthdate, 1];
      $month_n_days_in_month_fraction_tuple_list[] = $tuple;
    } // ends foreach

    $n_months = count($months_list);
    // Adjust the first tuplelist element
    if ($n_months>0) {
      $monthdate = $months_list[0];
      $total_days_in_month = self::get_total_days_in_specified_month($monthdate);
      $n_days = $monthdate->day;
      $fraction_monthdays = ($total_days_in_month - $n_days + 1) / $total_days_in_month;
      $tuple = [$monthdate, $fraction_monthdays];
      $month_n_days_in_month_fraction_tuple_list[0] = $tuple;
    }
    // Adjust the last tuplelist element if any
    if ($n_months>1) {
      $last_element = $n_months-1;
      $monthdate = $months_list[$last_element];
      $total_days_in_month = self::get_total_days_in_specified_month($monthdate);
      $n_days = $monthdate->day;
      $fraction_monthdays = $n_days / $total_days_in_month;
      $tuple = [$monthdate, $fraction_monthdays];
      $month_n_days_in_month_fraction_tuple_list[$last_element] = $tuple;
    }

    return $month_n_days_in_month_fraction_tuple_list;

  } // ends [static] get_month_n_monthdays_fraction_tuplelist_borders_can_fraction()

} // ends class DateFunctions
