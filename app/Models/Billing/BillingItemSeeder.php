<?php
namespace App\Models\Billing;

use App\Models\Billing\BillingItemsForJson;
use App\Models\Billing\BillingItemObjToAssocArray as BItem;
use App\Models\Billing\RefForBillingItem as Ref;
use Carbon\Carbon;

class BillingItemSeeder {

  public $billingitems;
  public $bitems;

  public function __construct() {
    $this->billingitems = new BillingItemsForJson;
    $this->bitems = array();
    $this->seed();
  }

  public function seed() {

    // 1
    $billingitem = new BItem;
    $billingitem->cobrancatipo_id = 1;
    $billingitem->item_value      = 1000;
    $monthrefdate           = Carbon::createFromFormat('Y-m-d', '2016-02-02');
    $billingitem->ref_obj = BItem::make_ref_obj_with_date($monthrefdate);
    $this->bitems[] = $billingitem;
    $this->billingitems->add($billingitem);

    // 2
    $billingitem = new BItem;
    $billingitem->cobrancatipo_id = 2;
    $billingitem->item_value      = 1500;
    $n_cota_ref = 3;
    $total_cotas_ref = 10;
    $billingitem->ref_obj = BItem::make_ref_obj_with_parcels($n_cota_ref, $total_cotas_ref);
    $this->bitems[] = $billingitem;
    $this->billingitems->add($billingitem);

  } // ends seed()

  public function json() {
    return $this->billingitems->get_json();
  }

} // ends class BillingItemSeeder
