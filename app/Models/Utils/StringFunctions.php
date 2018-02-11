<?php
namespace App\Models\Utils;
// To import the DateFunctions class in the Laravel app Here
// use App\Models\Utils\StringFunctions;

// use Carbon\Carbon;

class StringFunctions {

  public static function is_classname_at_the_end_of_namepath($namepath, $classname) {

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

      $namepath will return something like
        'App\Models\Folder\ClassNameABC'

      The strpos() below returns the position at which
        ClassNameABC occurs in the namespace path indicated above;
        - if it's not in the string, strpos() returns false.
    */

    // Before issuing get_class(), check that $variable has an object
    if (gettype($variable) != "object") {
      return false;
    }
    $namepath = get_class($variable);
    return self::is_classname_at_the_end_of_namepath($namepath, $classname);

  } // ends static is_var_of_class()

  public static function adhoctest_is_var_of_class() {
    $namepath  = 'App\Models\Folder\ClassNameABC';
    $classname = 'ClassNameABC';
    $bool = self::is_classname_at_the_end_of_namepath($namepath, $classname);
    print "$namepath + $classname => at_end = $bool \n";

    $namepath  = 'App\Models\Folder\ClassNameABC';
    $classname = 'ClassNameABX';
    $bool = self::is_classname_at_the_end_of_namepath($namepath, $classname);
    print "$namepath + $classname => at_end = $bool \n";

    $namepath  = 'App\Models\Folder\ClassNameABC\X';
    $classname = 'ClassNameABC';
    $bool = self::is_classname_at_the_end_of_namepath($namepath, $classname);
    print "$namepath + $classname => at_end = $bool \n";
  }

} // ends class DateFunctions
