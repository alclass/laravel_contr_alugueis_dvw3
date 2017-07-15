<?php
namespace App\Models\Billing;

use JsonSerializable;
use App\Models\Billing\BillingItemObjToAssocArray;
use App\Models\Billing\RefForBillingItem as Ref;

class BillingItemsForJson implements JsonSerializable {

  public function __construct($json_obj = null) {
    if ($json_obj != null) {
      $this->fill_in_billingitems_from_json($json_obj);
    }
    else {
      $this->billingitems = array();
    }
  }

  public function add($billingitem) {
    $this->billingitems[] = $billingitem;
  }

  public function get_total() {
    $total = 0;
    foreach ($this->billingitems as $billingitem) {
      $value = $billingitem->origvalue;
      if ($billingitem->finalvalue != null) {
        $value = $billingitem->origvalue;
      }
      $total += $billingitem->get_total();
    }
    return $total;
  }

  public function jsonSerialize() {
    /*
    The caller must issue json_encode($array) to get the related json-string
    */
    $billingitems_as_assoc_arrays_for_json_serialize = array();
    foreach ($this->billingitems as $billingitem) {
      $billingitems_as_assoc_arrays_for_json_serialize[] = generate_n_return_assoc_array($assoc_array);
    }
    return $billingitems_as_assoc_arrays_for_json_serialize;
  }

  public function get_json() {
    return json_encode($this->jsonSerialize());
  }

  public function fill_in_billingitems_from_json($json_obj) {
    $list_array = json_decode($json_obj);
    // empty billingitems array
    $this->billingitems = array();
    foreach ($list_array as $assoc_array) {
      $billingitem = new BillingItemObjToAssocArray($assoc_array);
      $this->billingitems[] = $billingitem;
    }
  }

} // ends class BillingItemsForJson
