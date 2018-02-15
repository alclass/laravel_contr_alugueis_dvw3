<?php
namespace App\Models\Utils;
// To import the DateFunctions class in the Laravel app Here
// use App\Models\Utils\StringFunctions;

// use Carbon\Carbon;

class StringFunctions {

  public static function is_classname_at_the_end_of_namepath($namepath, $classname) {
    /*
      Classname is checked at the end of the full path string.

      $namepath is the folder path to class, in the form:
        'App\Models\Folder\ClassNameABC'

      The strpos() below returns the position at which
        ClassNameABC occurs in the namespace path indicated above;
        - if it's not in the string, strpos() returns false.
    */

    $strpos_value = strpos($namepath, $classname);
    // 1st check: is substring found?
    if (!$strpos_value) {
      return false;
    }
    $namepath_strlen  = strlen($namepath);
    $classname_strlen = strlen($classname);
    $shouldbe_namepath_strlen = $strpos_value + $classname_strlen;
    // 2nd check: is substring AT THE END of string?
    if ($shouldbe_namepath_strlen != $namepath_strlen) {
      return false;
    }
    return true;
  }

  public static function is_var_of_class($variable, $classname) {
    /*
      The checking here is only for the classname.
      The method DOES NOT check the full namespace + classname.
      Classname is checked at the end of the full path string.
    */

    // Before issuing get_class(), check that $variable has an object
    if (gettype($variable) != "object") {
      return false;
    }
    $namepath = get_class($variable);
    return self::is_classname_at_the_end_of_namepath($namepath, $classname);

  } // ends static is_var_of_class()




  /*
      Adhoc Tests
      ===========
  */

  public static function adhoctest_is_var_of_class() {

    // Test 1
    $namepath  = 'App\Models\Folder\ClassNameABC';
    $classname = 'ClassNameABC';
    $bool = self::is_classname_at_the_end_of_namepath($namepath, $classname);
    print "$namepath + $classname => at_end = $bool \n";

    // Test 2
    $namepath  = 'App\Models\Folder\ClassNameABC';
    $classname = 'ClassNameABX';
    $bool = self::is_classname_at_the_end_of_namepath($namepath, $classname);
    print "$namepath + $classname => at_end = $bool \n";

    // Test 3
    $namepath  = 'App\Models\Folder\ClassNameABC\X';
    $classname = 'ClassNameABC';
    $bool = self::is_classname_at_the_end_of_namepath($namepath, $classname);
    print "$namepath + $classname => at_end = $bool \n";
  } // ends static adhoctest_is_var_of_class()


  public static function parseStrToFloat($strfloat, $dec_point=null) {
    /*
      The snippet in here came from a question in StackOverflow in the url below:
      https://stackoverflow.com/questions/2935906/how-do-i-convert-output-of-number-format-back-to-numbers-in-php

      The code takes into consideration $dec_point (eg. point in English , comma in Portuguese).
      However, float needs 'point' anyway.

      The regexp below also takes care of thousand separators, stripping them out.

      Eg.
      1)
        $strfloat = '1,295.67'
        $float will be 1295.67
      2)
        $strfloat = '1.295,67'
        $float will be 1295.67
    */

    if (empty($dec_point)) {
      $locale = localeconv();
      $dec_point = $locale['decimal_point'];
    }
    return floatval(
      str_replace(
        $dec_point,
        '.',
        preg_replace('/[^\d'.preg_quote($dec_point).']/', '', $strfloat)
      )
    );
  } // ends static parseStrToFloat()

} // ends class DateFunctions
