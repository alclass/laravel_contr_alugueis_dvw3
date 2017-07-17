<?php
namespace App\Models\Billing;

use App\Models\Billing\Cobranca;

class CobrancaResetTotal {

  public function __construct() {
    $this->process();
  } // ends __construct()

  public function process() {
    $cobrancas = Cobranca::all();
    foreach ($cobrancas as $cobranca) {
      $total = 0; $n_items = 0;
      $total_old = $cobranca->total; $n_items_old = $cobranca->n_items;
      foreach ($cobranca->billingitems()->get() as $billingitem) {
        $total += $billingitem->charged_value;
        $n_items += 1;
      }
      print ('cobranca->total = ' . $cobranca->total . "\n");
      print ('cobranca->n_items = ' . $cobranca->n_items . "\n");
      if ($total_old != $total || $n_items_old != $n_items) {
        $cobranca->total = $total;
        $cobranca->n_items = $n_items;
        print ('Saving them');
        $cobranca->save();
      } else {
        print ('Values are the same, not saving them.' . "\n");
      }
    }
  }
} // ends class CobrancaTester
