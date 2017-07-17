<?php
namespace App\Models\Billing;

// use App\Models\Billing\Cobranca;
use App\Models\Billing\CobrancaGerador;
use App\Models\Immeubles\Contract;
use App\Models\Immeubles\Imovel;

class CobrancaTester {

  private $contract = null;
  public $cobranca_gerador = null;

  public function __construct($imovel_apelido) {
    $imovel = Imovel::where('apelido', $imovel_apelido)->first();
    $this->contract = Contract::where('imovel_id', $imovel->id)
      ->where('is_active', 1)
      ->first();
    $this->cobranca_gerador = new CobrancaGerador($this->contract);
  } // ends __construct()

  public function gerar() {
    $this->cobranca_gerador->gerar_cobranca_based_on_today();
  }

  public function print() {
    return '=>' . $this->cobranca_gerador->__toString();
  }

  public function save() {
    return $this->cobranca_gerador->save_cobranca();
  }

  public function listitems() {
    $cobranca = $this->cobranca_gerador->get_cobranca();
    $cobranca->convert_from_json_n_set_billingitems();
    foreach ($cobranca->billingitems as $billingitem) {
      print ('brief_description = ' . $billing_item_type->cobranca_id['brief_description']);
      print ('date ref = ' . $billing_item_type->date_ref);
      print ('valor    = ' . $billing_item_type->item_value);
    }
  } // ends public function listitems()
} // ends class CobrancaTester
