<?php
namespace App\Models\Billing;

use JsonSerializable;
use App\Models\Billing\RefForBillingItem as Ref;
use App\Models\Billing\BillingItemForJson;

class BillingItemObjToAssocArray {

  public function process () {
    $billingitems = new BillingItemsForJson;
    // 1
    $billingitem = new BillingItemForJson;
    $billingitem->cobrancatipo_id = 1;
    $billingitem->origvalue = 1000;
    $billingitem->ref = new Ref; // the default is date type ref. with today's date
    $billingitems->add($billingitem);

    // 2
    $billingitem = new BillingItemForJson;
    $billingitem->cobrancatipo_id = 2;
    $billingitem->origvalue = 1500;
    $billingitem->ref = new Ref; // the default is date type ref. with today's date
    $billingitems->add($billingitem);

    $billingitemsjson = $billingitems->get_json();

    echo $billingitemsjson;

    return $billingitemsjson;

  }
}
