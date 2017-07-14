<?php
namespace App;

use JsonSerializable;
use App\RefForBillingItem as Ref;

class BillingItemForJson implements JsonSerializable {

  public $cobrancatipo_id;
  public $origvalue;
  public $finalvalue;
  public $value_modifier_brief_descriptor_if_any;
  public $ref_obj;

  public function __construct($json_obj = null) {
    if ($json_obj != null) {
      $this->extract_json_repr($json_obj);
    }
    else {
      $this->cobrancatipo_id = null;
      $this->origvalue       = null;
      $this->finalvalue      = null;
      $this->value_modifier_brief_descriptor_if_any = null;
      $this->ref_obj = new Ref;
    }
  }

  public function JsonSerialize() {
    $assoc_array = array();
    $assoc_array['cobrancatipo_id'] = $this->cobrancatipo_id;
    $assoc_array['origvalue']       = $this->origvalue;
    $assoc_array['finalvalue']      = $this->finalvalue;
    $assoc_array['value_modifier_brief_descriptor_if_any'] = $this->value_modifier_brief_descriptor_if_any;
    // 1st case: ref type is K_REFTYPE_DATE
    if ($this->ref_obj->ref_type == Ref::K_REFTYPE_DATE) {
      $assoc_array['ref_type'] = Ref::K_REFTYPE_DATE;
      $assoc_array['date_ref'] = $this->ref_obj->date_ref;
      // 1-1st case: date ref is MONTHLY
      if ($this->ref_obj->freq_date_type == Ref::K_REFTYPE_DATE_MONTHLY) {
        $assoc_array['date_freq_used'] = Ref::K_REFTYPE_DATE_MONTHLY;
      // 1-2nd case: date ref is YEARLY
      } else {
        $assoc_array['date_freq_used'] = Ref::K_REFTYPE_DATE_YEARLY;
      }
    // 2-1st case: cota ref is MONTHLY
    } else {  // K_REFTYPE_PARCEL
      $assoc_array['ref_type'] = Ref::K_REFTYPE_PARCEL;
      $assoc_array['n_cota_ref']      = $this->ref_obj->n_cota_ref;
      $assoc_array['total_cotas_ref'] = $this->ref_obj->total_cotas_ref;
      // 2-1st case: cota ref is MONTHLY
      if ($this->ref_obj->cota_freq_used == Ref::K_REFTYPE_PARCEL_MONTHLY) {
        $assoc_array['cota_freq_used'] = Ref::K_REFTYPE_PARCEL_MONTHLY;
      // 2-2nd case: cota ref is YEARLY
      } else {
        $assoc_array['date_freq_used'] = Ref::K_REFTYPE_DATE_YEARLY;
      }
    }
    return $assoc_array;
  }

  public function get_json_repr() {
    return json_encode($this->JsonSerialize());
  }

  public function extract_json_repr($json_obj) {
    $assoc_array = json_decode($json_obj);
    $this->cobrancatipo_id = $assoc_array['cobrancatipo_id'];
    $this->origvalue       = $assoc_array['origvalue'];
    $this->finalvalue      = $assoc_array['finalvalue'];
    $this->value_modifier_brief_descriptor_if_any = $assoc_array['value_modifier_brief_descriptor_if_any'];
    $ref = new RefForBillingItem;
    if ($assoc_array['ref_type'] == Ref::K_REFTYPE_DATE_MONTHLY) {
      $this->ref_obj->ref_type = Ref::K_REFTYPE_DATE_MONTHLY;
      $this->ref_obj->date_ref = $assoc_array['date_ref'];
      if ($this->ref_obj->$date_freq_used == Ref::K_REFTYPE_DATE_MONTHLY) {
        $this->ref_obj->$date_freq_used = BillingItem::K_REFTYPE_DATE_MONTHLY;
      } else {
        $this->ref_obj->$date_freq_used = BillingItem::K_REFTYPE_DATE_YEARLY;
      }
    }
    else {
      $this->ref_obj->n_cota_ref = $assoc_array['n_cota_ref'];
      $this->ref_obj->total_cotas_ref = $assoc_array['total_cotas_ref'];
      $this->ref_obj->cota_freq_used = $assoc_array['cota_freq_used'];
    }
  }
}
