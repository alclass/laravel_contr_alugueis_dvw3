<?php
namespace App;

use Carbon\Carbon;

class RefForBillingItem {

  const K_REFTYPE_DATE          = 'D';
  const K_REFTYPE_PARCEL        = 'P';

  const K_REFTYPE_DATE_MONTHLY = 'DM';
  const K_REFTYPE_DATE_YEARLY  = 'DY';

  const K_REFTYPE_PARCEL_MONTHLY = 'PM';
  const K_REFTYPE_PARCEL_YEARLY  = 'PY';

  public $ref_type; // informs whether ref is dateful (D) or parcelful (P) (ie, N cotas each monthly)
  public $date_freq_used; // 'DM' (monthly) or 'DY' (yearly)
  public $date_ref;  // date is still a full date variable (ie, 'Y-m-d H:M:S) but only Y-m or Y is to be considered
  public $n_cota; // 1, 2, 3, ..., N parcels
  public $total_cotas; // N parcels
  public $cota_freq_used; // 'PM' (monthly) or 'PY' (yearly)

  public function __construct() {
    // default to monthy date ref. type
    $this->type = self::K_REFTYPE_DATE;
    $this->date_freq_used = self::K_REFTYPE_DATE_MONTHLY;
    $this->date = Carbon::today();

    // default parcel type to null
    $this->n_cotas = null;
    $this->total_cotas = null;
    $this->cota_freq_used = null;
  } // ends public function __construct()
} // ends class RefForBillingItem
