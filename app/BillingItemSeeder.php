<?php
namespace App;

use JsonSerializable;
use App\RefForBillingItem;
use App\BillingItemForJson;

class BillingItemSeeder {

  public function process () {

    $billingitem = new BillingItemForJson;
    $billingitem->cobrancatipo_id = 1;
    $billingitem->origvalue = 1000;
    $billingitem->ref = new RefForBillingItem; // the default is date type ref. with today's date

    $billingitem->JsonSerialize();

    echo $billingitem;

    return $billingitem;

  }
}
