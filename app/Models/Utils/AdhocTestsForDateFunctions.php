<?php
namespace App\Models\Utils;

use App\Models\Utils\DateFunctions;
use Carbon\Carbon;

class TestDateFunctions {

  public static function test_next_anniversary($inbetween_date_str) {

    $start_date     = Carbon::createFromFormat('Y-m-d', '2015-01-01');
    $inbetween_date = Carbon::createFromFormat('Y-m-d', $inbetween_date_str);
    $end_date       = Carbon::createFromFormat('Y-m-d', '2017-05-31');

    $result = DateFunctions::find_next_anniversary_date_with_triple_start_end_n_from(
      $start_date,
      $inbetween_date,
      $end_date
    );
    return $result;
  } // ends test_next_anniversary()

} // ends class TestDateFunctions

//$result = TestDateFunctions::test_next_anniversary();
// echo $result;
