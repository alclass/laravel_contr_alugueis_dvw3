<?php
namespace App\Models\Billing;

// To import class MoraBillingItemToCobrancaAdder elsewhere in the Laravel App
// use App\Models\Billing\MoraBillingItemToCobrancaAdder;

use App\Models\Billing\BillingItem;
use App\Models\Billing\Cobranca;
use App\Models\Billing\CobrancaGerador;
use App\Models\Billing\MoraDebito;

class MoraBillingItemToCobrancaAdder {

  public $cobranca   = null;
  public $moradebito = null;

  public function __construct($cobranca_id) {
    $this->cobranca = Cobranca::find($cobranca_id);

  } // ends __construct()

  public function addMoraDebito($moradebito_id) {
    print ("addMoraDebito($moradebito_id)\n");
    $moradebito        = MoraDebito::find($moradebito_id);
    // $this->$moradebito = $moradebito;
    // check existence
    $ini_debt_date_as_ref = $moradebito->ini_debt_date->copy();
    $ini_debt_date_as_ref->day(1)->setTime(0,0,0);
    $billingitem_if_any = BillingItem
      ::where('cobranca_id',              $this->cobranca->id)
      ->where('cobrancatipo_id',          5) // 5 is MORA
      ->where('original_value_if_needed', $moradebito->ini_debt_value)
      ->where('monthrefdate',         $moradebito->monthrefdate)
      ->first();
    if ($billingitem_if_any != null) {
      print ("Billing Item for MoraDebito already exists" . '\n');
      return false;
    }
    print ("create_billingitem_for_mora...\n");
    $cobranca_gerador = new CobrancaGerador($this->cobranca);
    $cobranca_gerador->create_billingitem_for_mora($moradebito);
    print ("finished: check db\n");
    $this->moradebito = $moradebito;
    return true;
  } // ends addMoraDebito()

} // ends class MoraBillingItemToCobrancaAdder
