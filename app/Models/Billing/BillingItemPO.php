<?php
namespace App\Models\Billing;
// To import class BillingItem elsewhere in the Laravel App
// use App\Models\Billing\BillingItemPO;

use App\Models\Billing\CobrancaTipo;
use Carbon\Carbon;

class BillingItemPO {
  /**
   * BillingItemPO is a class for temporary instantiation of BillingItem's
   * objects. PO means Plain Object. The idea is to postpone the
   * setAttribute of Cobrança as a relationship for BillingItem.
  */


  var $cobrancatipo;
  var $charged_value;
  var $monthrefdate;
  var $additionalinfo;
  var $numberpart;
  var $totalparts;
  var $reftype;
  var $freqtype;

  public function __construct(
      $cobrancotipo_char4id,
      $charged_value,
      $monthrefdate,
      $additionalinfo='',
      $numberpart=1,
      $totalparts=1
    ) {
    $this->cobrancatipo  = CobrancaTipo::fetch_by_char4id($cobrancotipo_char4id);
    $this->charged_value = $charged_value;
    $this->monthrefdate  = $monthrefdate;
    $this->additionalinfo= $additionalinfo;
    $this->numberpart    = $numberpart;
    $this->totalparts    = $totalparts;
    // When overwrite is needed, these below are to be used
    $this->reftype = null;
    $this->freqtype = null;
  }

  public function get_reftype_attribute() {
    if ($this->reftype == null) {
      return $this->cobrancatipo->reftype;
    }
    return $this->reftype;
  }

  public function get_freqtype_attribute() {
    if ($this->freqtype == null) {
      return $this->cobrancatipo->freqtype;
    }
    return $this->freqtype;
  }

  const DYN_ATTRIBUTES = ['reftype', 'freqtype'];
  public function __get($attri) {

    if (in_array($attri, self::DYN_ATTRIBUTES)) {
      $methodname = 'get_' . $attri . '_attribute';
      return $this->{$methodname}();
    }
    return null;
  }

  public function __set($attri, $value) {
    /*
      For billingitem overwrites: numberpart, totalparts, reftype & freqtype
    */
    if (in_array($attri, self::DYN_ATTRIBUTES)) {
      $this->{$attri} = $value;
    }
  }

  public function generate_billingitem_for_cobranca($cobranca) {
    if ($cobranca == null) {
      return null;
    }
    $billingitem = new BillingItem();
    $billingitem->cobranca = $cobranca;
    $billingitem->cobrancatipo  = $this->cobrancatipo;
    $billingitem->charged_value = $this->charged_value;
    $billingitem->monthrefdate  = $this->monthrefdate;
    $billingitem->numberpart    = $this->numberpart;
    $billingitem->totalparts    = $this->totalparts;
    // reftype firstly belongs to cobrancatipo, but if it's in billingitem, it's overwritten
    if ($this->reftype != null) {
      $billingitem->reftype = $this->reftype;
    }
    // freqtype firstly belongs to cobrancatipo, but if it's in billingitem, it's overwritten
    if ($this->freqtype != null) {
      $billingitem->freqtype = $this->freqtype;
    }
    return $billingitem;
  }

  public function toJson() {
    // TO-DO
    return 'json';
  }

  public function __toString() {
    $outstr = '';
    $char4id = 'n/a';
    if ($this->cobrancatipo != null) {
      $char4id = $this->cobrancatipo->char4id;
    }
    $outstr .= "cobrancatipo => $char4id \n";
    $outstr .= "charged_value => $this->charged_value \n";
    $outstr .= "monthrefdate => $this->monthrefdate \n";
    $outstr .= "additionalinfo => $this->additionalinfo \n";
    $outstr .= "numberpart => $this->numberpart \n";
    $outstr .= "totalparts => $this->totalparts \n";
    return $outstr;
  }


} // ends class BillingItemPO
