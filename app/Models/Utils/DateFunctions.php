<?php
namespace App\Models\Utils;

class DateFunctions {

  public static function find_next_anniversary_date_with_triple_start_inbetween_end(
    $start_date,
    $inbetween_date,
    $end_date
  ) {
    /*
    Explanation with an example:

    Let
      $start_date be a contract's start date
      $inbetween_date be the relative point-date from which one wants to know a next price yearly reajust
      $end_date be the contract's end date

    Suppose:
      $start_date = 2015-01-01
      $inbetween_date = 2015-10-01
      $end_date = 2017-05-31 (a 30-month contract)

      One wants to know when the next price yearly reajust will happen from 2015-10-01
        The answer is, in this simple, 2016-01-01 (the next anniversary from 2015-10-01)

      *** This function does the calculation above exemplified. ***

    Another example:
      $start_date = 2014-04-04
      $inbetween_date = 2016-01-10
      $end_date = 2016-10-03 (a 30-month contract)
        Answer should be 2016-04-04

      Examples with edge dates (out of contract date range):
      * Example 1 with edge dates
          $start_date = 2014-04-04
          $inbetween_date = 2013-01-10  (NOTICE that this will not raise an exception)
          $end_date = 2016-10-03 (a 30-month contract)
            Answer should be = 2015-04-04 (simple the first anniversary)
      * Example 2 with edge dates
          $since_date = 2014-04-04
          $inbetween_date = 2018-01-10  (NOTICE that this will not raise an exception)
          $end_date = 2016-10-03 (a 30-month contract)
            Answer should be null
            (because the last reajust 2016-04-04 is before $inbetween_date, there will not be another one til the end of contract)
    */
    // 1st logical case resulting in a right-away return
    if ($inbetween_date > $end_date) {
      // $inbetween_date > $end_date, there will not be a next anniversary in this case
      return null;
    }
    $next_yearly_pointdate = $start_date->copy()->addYears(1);
    // 2nd logical case resulting in a right-away return
    if ($next_yearly_pointdate > $end_date) {
      // A supposedly next anniversary is beyond the end of contract
      return null;
    }
    // 3rd logical case resulting in a right-away return
    if ($inbetween_date <= $next_yearly_pointdate) {
      // At this point, next anniversary is $next_yearly_pointdate which is before the end of contract
      return $next_yearly_pointdate;
    }
    // 4th logical case resulting in recursion
    // Here $inbetween_date > $next_yearly_pointdate
    // So, let $start_date be $next_yearly_pointdate and recurse away
    return self::find_next_anniversary_date_with_triple_start_inbetween_end(
      $next_yearly_pointdate,
      $inbetween_date,
      $end_date
    );
  } // ends find_next_anniversary_date_with_triple_start_inbetween_end()

  public static function find_rent_monthyeardateref_under_convention(
    $date,
    $pay_day_when_monthly
  ) {
    /*

    */
    if ($date->day > 0 && $date->day < $pay_day_when_monthly + 1) {
      // pick up last month and return
      $monthyeardateref = $date->copy()->addMonth(-1);
      $monthyeardateref->day = 0;
      return $monthyeardateref;
    }
    // pick up this month and return
    $monthyeardateref = $date->copy();
    $monthyeardateref->day = 1;
    return $monthyeardateref;
  } // ends find_rent_monthyeardateref_under_convention()

  public static function calculate_monthly_duedate_under_convention(
    $date,
    $pay_day_when_monthly
  ) {
    if ($date->day > 0 && $date->day < $pay_day_when_monthly + 1) {
      // pick up same month and ajust day
      $duedate = $date->copy();
      $duedate->day = $pay_day_when_monthly;
      return $duedate;
    }
    // pick up next month and ajust day
    $duedate = $date->copy()->addMonth(1);
    $duedate->day = $pay_day_when_monthly;
    return $duedate;
  } // ends calculate_monthly_duedate_under_convention()

  public static function format_monthyeardateref_as_m_slash_y($monthyeardateref) {
    if ($monthyeardateref == null) {
      return 'n/a';
    }
    /*  This was needed before using the accessors & mutators technique to force Carbon dates (see above protected $dates)
    if (gettype($this->monthyeardateref)==gettype('s')) {
      $this->monthyeardateref = Carbon::createFromFormat('Y-m-d', $this->monthyeardateref);
    }
    */
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
  } // ends format_monthyeardateref_as_m_slash_y()


} // ends class DateFunctions
