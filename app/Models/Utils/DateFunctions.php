<?php
namespace App\Models\Utils;

// To import the DateFunctions class in the Laravel app Here
// use App\Models\Utils\DateFunctions;

use Carbon\Carbon;

class DateFunctions {

  const PAY_DAY_WHEN_MONTHLY_ENVFALLBACK = 10; // this will hold if env() does not have it
  const LOOP_ITERATION_PROTECTION_COUNTER_FOR_MONTHS_YEARS = 10000;

  public static function find_next_anniversary_date_with_triple_start_inbetween_end(
    $start_date,
    $end_date,
    $inbetween_date = null,
    $cycle_time_in_years = 1
  ) {
    /*
    Explanation with an example:

    Let
      $start_date be a contract's start date
      $inbetween_date be the relative point-date from which one wants to know a next price yearly reajust
      $end_date be the contract's end date

    Suppose:
      $start_date = 2015-01-01
      $end_date = 2017-06-30 (a 30-month contract)
      $inbetween_date = 2015-10-01

      One wants to know when the next price yearly reajust will happen from 2015-10-01
        The answer is, in this simple example, 2016-01-01
          (ie, the next anniversary counting from 2015-10-01)

      *** This function does the calculation above exemplified. ***

    Another example:
      $start_date = 2014-04-04
      $end_date   = 2016-10-03 (a 30-month contract)
      $inbetween_date = 2016-01-10
        Answer should be 2016-04-04

      Examples with edge dates (out of contract date range):
      * Example 1 with edge dates
          $start_date = 2014-04-04
          $end_date = 2016-10-03 (a 30-month contract)
          $inbetween_date = 2013-01-10  (NOTICE that this will not raise an exception)
            Answer should be = 2015-04-04 (simple the first anniversary)
      * Example 2 with edge dates
          $since_date = 2014-04-04
          $inbetween_date = 2018-01-10  (NOTICE that this will not raise an exception)
          $end_date = 2016-10-03 (a 30-month contract)
            Answer should be null
            (because the last reajust 2016-04-04 is before $inbetween_date, there will not be another one til the end of contract)

      The programming plan was to device a recursive method, but later on
        we decided for a while-loop version limiting it to 10000 (or other via config) iterations
        (just for noticing, the runtime did not protect the infinite loop we have
         when still developing the recursive version, crashing the machine...)

    */
    // First off, treat null case for both start and end dates,
    // these two cannot be null, if they are, raise exception
    $null_date_error_msg = "In DateFunctions::find_next_anniversary_date_with_triple_start_inbetween_end() -> null case for ";
    if ($start_date==null) {
      $null_date_error_msg .= "start date (=$start_date), it cannot be null";
      throw new Exception($null_date_error_msg, 1);
    }
    if ($end_date==null) {
      $null_date_error_msg .= "end date (=$start_date), it cannot be null";
      throw new Exception($null_date_error_msg, 1);
    }
    // Second off, treat null case for $inbetween_date, if it is, default it to today()
    if ($inbetween_date==null) {
      $inbetween_date = Carbon::today();
    }
    // print ('$inbetween_date             = ' . $inbetween_date->format('Y-m-d') . "\n");
    // 1st logical case resulting in an immediate return
    if ($start_date > $end_date) {
      // $start_date > $end_date, it's not logical, return null, no exception raised
      return null;
    }
    // 2nd logical case resulting in an immediate return
    if ($inbetween_date > $end_date) {
      // $inbetween_date > $end_date, there will not be a next anniversary in this case
      return null;
    }
    // 3rd logical case as seen in the examples in the docstring
    // if $inbetween_date <= $start_date, let it simply equal $start_date,
    // for we are simply interested finding the next anniversary
    // use method copy(), DO NOT simply attribute it with "="...
    if ($inbetween_date <= $start_date) {
      /*
       In this case, force $inbetween_date to be equal to $start_date
       In this specific case, if there's a first anniversary, that will be the result
      */
      $inbetween_date = $start_date->copy();
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
      if ($inbetween_date <= $next_yearly_pointdate) {
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

    $logical_error_msg = "The program should not logically have arrived at this point. Logical Exception in find_next_anniversary_date_with_triple_start_inbetween_end()";
    throw new Exception($logical_error_msg, 1);

  } // ends [static] find_next_anniversary_date_with_triple_start_inbetween_end()

  public static function find_rent_monthyeardateref_under_convention(
    $date = null,
    $pay_day_when_monthly = null
  ) {
    /*
    The convention is:
    if day is within [1,10] monthref is the previous one
    if day is 11 and above monthref is the current one
    */
    if ($date == null) {
      $date = Carbon::today();
    }
    if ($pay_day_when_monthly == null) {
      $pay_day_when_monthly = (int) env('PAY_DAY_WHEN_MONTHLY', self::PAY_DAY_WHEN_MONTHLY_ENVFALLBACK);
    }
    if ($date->day > 0 && $date->day < $pay_day_when_monthly + 1) {
      // pick up last month and return
      $monthyeardateref = $date->copy()->addMonth(-1);
      $monthyeardateref->day(1);
      return $monthyeardateref;
    }
    // pick up this month and return
    $monthyeardateref = $date->copy();
    $monthyeardateref->day(1);
    return $monthyeardateref;
  } // ends find_rent_monthyeardateref_under_convention()

  public static function calculate_monthly_duedate_under_convention(
    $date = null,
    $pay_day_when_monthly = null
  ) {
    if ($date == null) {
      $date = Carbon::today();
    }
    if ($pay_day_when_monthly == null) {
      $pay_day_when_monthly = (int) env('PAY_DAY_WHEN_MONTHLY', self::PAY_DAY_WHEN_MONTHLY_ENVFALLBACK);
    }
    if ($date->day > 0 && $date->day < $pay_day_when_monthly + 1) {
      // pick up same month and ajust day
      $duedate = $date->copy();
      $duedate->day($pay_day_when_monthly);
      return $duedate;
    }
    // pick up next month and ajust day
    $duedate = $date->copy()->addMonth(1);
    $duedate->day($pay_day_when_monthly);
    return $duedate;
  } // ends [static] calculate_monthly_duedate_under_convention()

/*
  // Method DEACTIVATED
  public static function format_monthyeardateref_as_m_slash_y($monthyeardateref) {
    if ($monthyeardateref == null) {
      return 'n/a';
    }
    /*
      This method is no longer needed, for Carbon can do that by:
        $dt->format('m/Y');

      This was needed before using the accessors & mutators technique to force Carbon dates (see above protected $dates)
    if (gettype($this->monthyeardateref)==gettype('s')) {
      $this->monthyeardateref = Carbon::createFromFormat('Y-m-d', $this->monthyeardateref);
    }
    * /
    // toDayDateTimeString() => Thu, Dec 25, 1975 2:15 PM
    $datestring = $monthyeardateref->toDayDateTimeString();
    $pos_for_3letter_month = 5;
    $month_str = substr($datestring, $pos_for_3letter_month, 3); // = "Dec" (December)
    $pos_for_2digit_year = 15;
    if ($monthyeardateref->day < 10) {
      // if day >= 10, position is 15, else it's 14
      $pos_for_2digit_year = 14;
    }
    $year_str  = substr($datestring, $pos_for_2digit_year, 2); // = "75" (of 1975)
    $outstr    = $month_str . "/" . $year_str;
    return $outstr;
  } // ends [static] format_monthyeardateref_as_m_slash_y()
*/

} // ends class DateFunctions
