<?php
namespace App\Models\Billing;

// To import class BillingItem elsewhere in the Laravel App
// use App\Models\Billing\BillingItem;

use App\Models\Billing\CobrancaTipo;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class BillingItemPO {
  /**
   * BillingItemPO is a class for temporary instantiation of BillingItem's objects.
   * PO means Plain Object. The idea is to postpone the setAttribute of
   * Cobrança back to the object.
  */

  public function __construct(
      $cobrancotipo_char4id,
      $charged_value,
      $monthrefdate=null,
      $numberpart=1
    ) {
    $this->cobrancotipo  = CobrancaTipo::fetch_by_char4id($cobrancotipo_char4id);
    $this->charged_value = $charged_value;
    $this->monthrefdate  = $monthrefdate;
    $this->numberpart    = $numberpart;
    $this->reftype = null;
  }

  public function get_reftype_attribute() {
    if ($this->reftype == null) {
      return $this->cobrancotipo->reftype;
    }
    return $this->reftype;
  }

  public function get_freqtype_attribute() {
    if ($this->freqtype == null) {
      return $this->cobrancotipo->freqtype;
    }
    return $this->freqtype;
  }

  const DYN_ATTRIBUTES = ['reftype', 'freqtype'];
  public function __get($attri) {

    if (in_array($attri, self::DYN_ATTRIBUTES) {
      $methodname = 'get_' . $attri . '_attribute';
      return $this->{$methodname}();
    }
    return null;
  }

  public function __set($attri, $value) {
    /*
      For billingitem overwrites: numberpart, totalparts, reftype & freqtype
    */
    if (in_array($attri, self::DYN_ATTRIBUTES) {
      $this->{$attri} = $value;
    }
  }

  public function complement_cobranca_n_generate_billingitem($cobranca) {
    $billingitem = new BillingItem();
    $billingitem->cobrancotipo  = $this->cobrancotipo;
    $billingitem->charged_value = $this->charged_value;
    $billingitem->monthrefdate  = $this->monthrefdate;
    $billingitem->numberpart    = $this->numberpart;
    if ($this->reftype != null) {
      $billingitem->reftype = $this->reftype;
    }
    if ($this->freqtype != null) {
      $billingitem->freqtype = $this->freqtype;
    }
    $billingitem->cobranca = $cobranca;
    return $billingitem;
  }

  public function toJson() {
    // TO-DO
    return 'json';
  }




} // ends class BillingItemPO
