<?php
namespace App\Models\Billing;

use Carbon\Carbon;

class RefForBillingItem {

  const K_REFTYPE_DATE   = 'D';
  const K_REFTYPE_PARCEL = 'P';
  const K_REF_IS_MONTHLY = 'M';
  const K_REF_IS_YEARLY  = 'Y';

  const KEY_REF_TYPE        = 'ref_type';
  const KEY_REF_FREQ_USED   = 'ref_freq_used';
  const KEY_DATE_REF        = 'date_ref';
  const KEY_N_COTA_REF      = 'n_cota_ref';
  const KEY_TOTAL_COTAS_REF = 'total_cotas_ref';
  const KEY_BRIEF_INFO      = 'brief_info';


  public $ref_type; // informs whether ref is dateful (D) or parcelful (P) (ie, N cotas each monthly)
  public $date_ref;  // date is still a full date variable (ie, 'Y-m-d H:M:S) but only Y-m or Y is to be considered
  public $n_cota; // 1, 2, 3, ..., N parcels
  public $total_cotas; // N parcels
  public $freq_used_in_ref; // 'M' (monthly) or 'Y' (yearly)
  public $brief_info;

  private static function fill_in_ref_freq_used($ref_obj, $is_yearly=false) {
    if ($is_yearly == true) {
      $ref_obj->ref_freq_used = Ref::K_REF_IS_YEARLY;
    } else {
      $ref_obj->ref_freq_used = Ref::K_REF_IS_MONTHLY;
    }
    return $ref_obj;
  }

  public static function make_ref_obj_with_date($monthrefdate, $is_yearly=false) {
    $ref_obj = new Ref;
    $ref_obj->ref_type = Ref::K_REFTYPE_DATE;
    $ref_obj->date_ref = $monthrefdate;
    return self::fill_in_ref_freq_used($ref_obj, $is_yearly);
  }

  public static function make_ref_obj_with_parcels($n_cota_ref, $total_cotas_ref, $is_yearly=false) {
    $ref_obj = new Ref;
    $ref_obj->ref_type        = Ref::K_REFTYPE_PARCEL;
    $ref_obj->n_cota_ref      = $n_cota_ref;
    $ref_obj->total_cotas_ref = $total_cotas_ref;
    return self::fill_in_ref_freq_used($ref_obj, $is_yearly);
  }

  public function __construct() {
    // default to monthy date ref. type
    $this->type = self::K_REFTYPE_DATE;
    $this->freq_used_in_ref = self::K_REF_IS_MONTHLY;
    $this->date = Carbon::today();

    // default parcel type to null
    $this->n_cotas = null;
    $this->total_cotas = null;
    $this->cota_freq_used = null;
  } // ends public function __construct()
} // ends class RefForBillingItem
