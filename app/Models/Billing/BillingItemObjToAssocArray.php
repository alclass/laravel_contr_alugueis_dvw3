<?php
namespace App\Models\Billing;

use App\Models\Billing\RefForBillingItem as Ref;

class BillingItemObjToAssocArray {

  private static function fill_in_ref_freq_used($ref_obj, $is_yearly=false) {
    if ($is_yearly == true) {
      $ref_obj->ref_freq_used = Ref::K_REF_IS_YEARLY;
    } else {
      $ref_obj->ref_freq_used = Ref::K_REF_IS_MONTHLY;
    }
    return $ref_obj;
  }

  public static function make_ref_obj_with_date($monthyeardateref, $is_yearly=false) {
    $ref_obj = new Ref;
    $ref_obj->ref_type = Ref::K_REFTYPE_DATE;
    $ref_obj->date_ref = $monthyeardateref;
    return self::fill_in_ref_freq_used($ref_obj, $is_yearly);
  }

  public static function make_ref_obj_with_parcels($n_cota_ref, $total_cotas_ref, $is_yearly=false) {
    $ref_obj = new Ref;
    $ref_obj->ref_type        = Ref::K_REFTYPE_PARCEL;
    $ref_obj->n_cota_ref      = $n_cota_ref;
    $ref_obj->total_cotas_ref = $total_cotas_ref;
    return self::fill_in_ref_freq_used($ref_obj, $is_yearly);
  }

  public $cobrancatipo_id;
  public $item_value;
  public $modified_value;
  public $value_modifier_brief_descriptor_if_any;
  public $ref_obj;

  public function __construct($assoc_array = null) {
    if ($assoc_array == null) {
      $this->set_null_to_attributes();
    } else {
      $this->set_attrs_from_assoc_array($assoc_array);
    }
  }

  public function set_null_to_attributes() {
    $this->cobrancatipo_id = null;
    $this->item_value      = null;
    $this->modified_value  = null;
    $this->value_modifier_brief_descriptor_if_any = null;
    $this->ref_obj = null;
  }

  public function generate_n_return_assoc_array() {
    $assoc_array = array();
    $assoc_array['cobrancatipo_id'] = $this->cobrancatipo_id;
    $assoc_array['item_value']      = $this->item_value;
    $assoc_array['modified_value']  = $this->modified_value;
    $assoc_array['value_modifier_brief_descriptor_if_any'] = $this->value_modifier_brief_descriptor_if_any;
    // Item Ref
    // 1st case: ref type is K_REFTYPE_DATE
    if ($this->ref_obj->ref_type == Ref::K_REFTYPE_DATE) {
      $assoc_array[Ref::KEY_REF_TYPE] = Ref::K_REFTYPE_DATE;
      $assoc_array[Ref::KEY_DATE_REF] = $this->ref_obj->date_ref;
      // 1-1st case: date ref is MONTHLY
      if ($this->ref_obj->ref_freq_used == Ref::K_REF_IS_MONTHLY) {
        $assoc_array[Ref::KEY_REF_FREQ_USED] = Ref::K_REF_IS_MONTHLY;
      // 1-2nd case: date ref is YEARLY
      } else {
        $assoc_array[Ref::KEY_REF_FREQ_USED] = Ref::K_REF_IS_YEARLY;
      }
      // 2nd case: ref type is K_REFTYPE_PARCEL
    } else {
      $assoc_array[Ref::KEY_REF_TYPE]      = Ref::K_REFTYPE_PARCEL;
      $assoc_array[Ref::KEY_N_COTA_REF]      = $this->ref_obj->n_cota_ref;
      $assoc_array[Ref::KEY_TOTAL_COTAS_REF] = $this->ref_obj->total_cotas_ref;
      // 2-1st case: cota ref is MONTHLY
      if ($this->ref_obj->ref_freq_used == Ref::K_REF_IS_MONTHLY) {
        $assoc_array[Ref::KEY_REF_FREQ_USED] = Ref::K_REF_IS_MONTHLY;
      // 2-2nd case: cota ref is YEARLY
      } else {
        $assoc_array[Ref::KEY_REF_FREQ_USED] = Ref::K_REF_IS_YEARLY;
      }
    }
    return $assoc_array;
  } // ends generate_n_return_assoc_array()

  public function set_attrs_from_assoc_array($assoc_array) {
    $this->set_null_to_attributes();
    $this->cobrancatipo_id = $assoc_array['cobrancatipo_id'];
    $this->item_value      = $assoc_array['item_value'];
    $this->modified_value  = $assoc_array['modified_value'];
    $this->value_modifier_brief_descriptor_if_any = $assoc_array['value_modifier_brief_descriptor_if_any'];
    $ref = new Ref;
    // 1st case: ref type is K_REFTYPE_DATE
    if ($assoc_array[Ref::KEY_REF_TYPE] == Ref::K_REFTYPE_DATE) {
      $this->ref_obj->ref_type = Ref::K_REFTYPE_DATE;
      $this->ref_obj->date_ref = $assoc_array[Ref::KEY_DATE_REF];
      // 1-1st case: date ref is MONTHLY
      if ($this->ref_obj->ref_freq_used == Ref::K_REF_IS_MONTHLY) {
        $this->ref_obj->ref_freq_used = Ref::K_REF_IS_MONTHLY;
      // 1-2nd case: date ref is YEARLY
      } else {
        $this->ref_obj->ref_freq_used = Ref::K_REF_IS_YEARLY;
      }
    // 2nd case: ref type is K_REFTYPE_PARCEL
    } else {
      $this->ref_obj->ref_type        = Ref::K_REFTYPE_PARCEL;
      $this->ref_obj->n_cota_ref      = $assoc_array[Ref::KEY_N_COTA_REF];
      $this->ref_obj->total_cotas_ref = $assoc_array[Ref::KEY_TOTAL_COTAS_REF];
      $this->ref_obj->ref_freq_used   = $assoc_array[Ref::KEY_REF_FREQ_USED];
    }
  } // ends set_attrs_from_assoc_array()

} // ends class BillingItemForJson
