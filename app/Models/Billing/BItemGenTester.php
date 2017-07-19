<?php
namespace App\Models\Billing;

use App\Models\Billing\Cobranca;
use App\Models\Billing\BillingItemGenerator;

use App\Models\Immeubles\Contract;
use App\Models\Immeubles\Imovel;

class BItemGenTester {

  public $cobranca = null;
  public $bitemgen = null;
  public $next_monthyeardateref = null;

  public function __construct($cobranca_id) {
    $this->cobranca = Cobranca::find($cobranca_id);
    $this->bitemgen = new BillingItemGenerator($this->cobranca);
    $this->set_next_monthyeardateref();
  } // ends __construct()

  private function set_next_monthyeardateref() {
    $this->next_monthyeardateref = $this->cobranca->monthyeardateref->copy()->addMonths(1);
  }

  public function gerar($valor_negativo_mora_positivo_credito=null) {
    if ($valor_negativo_mora_positivo_credito==null) {
      $valor_negativo_mora_positivo_credito = 100;
    }
    $this->bitemgen->createIfNeededBillingItemForMoraOrCreditoMonthlyRef(
      $valor_negativo_mora_positivo_credito,
      $this->next_monthyeardateref
    );
  }

  public function print() {
    return '=>' . $this->bitemgen->__toString();
  }

  public function echo() {
    echo $this->print();
  }

  public function listitems() {
    $billingitems = $this->cobranca->billingitems()->get();
    foreach ($billingitems as $billingitem) {
      print ($billingitem->toString());

      // =========================================
      /*
      print ('id                = ' . $billingitem->id);
      print ('brief_description = ' . $billingitem->brief_description);
      print ('date ref          = ' . $billingitem->monthyeardateref);
      print ('charged_value     = ' . $billingitem->charged_value);
      print ('ref type          = ' . $billingitem->ref_type);
      print ('ref type          = ' . $billingitem->ref_type);
      */

    } // ends foreach
  } // ends public function listitems()

} // ends class CobrancaTester
