<?php
namespace App\Models\Billing;
// use App\Models\Billing\CobrancaTester;

use App\Models\Billing\CobrancaGerador;
use App\Models\Immeubles\Contract;
use App\Models\Immeubles\Imovel;
use Carbon\Carbon;

class CobrancaTester {

  private $contract = null;
  public $cobranca_gerador = null;

  public function __construct($imovel_apelido) {
    $this->imovel = Imovel::where('apelido', $imovel_apelido)->first();
    $this->contract = Contract
      ::where('imovel_id', $this->imovel->id)
      ->where('is_active', 1)
      ->first();
    $this->cobranca_gerador = new CobrancaGerador($this->contract);
  } // ends __construct()

  public function g1() {
    $monthrefdate = new Carbon('2018-3-1');
    $contract_id = 3;
    $cobranca = CobrancaGerador::create_or_retrieve_cobranca_with_keys(
      $contract_id,
      $monthrefdate,
      $monthseqnumber = 1
    );
    return $cobranca;
  }

  public function imv1() {
    // $this->monthrefdate
    $monthrefdate = new Carbon('2018-3-1');
    $iptuanoimovel = $this->imovel
      ->get_iptuanoimovel_with_refmonth_or_default($monthrefdate);
    $is_refmonth_billable = $iptuanoimovel->is_refmonth_billable($monthrefdate);
    print('is_refmonth_billable = ');
    print_r($is_refmonth_billable);
    print('mesref_de_inicio_repasse = ');
    print_r($iptuanoimovel->mesref_de_inicio_repasse);
    print('mesref_de_fim_repasse = ');
    print_r($iptuanoimovel->mesref_de_fim_repasse);
    print('returning $iptuanoimovel');
    return $iptuanoimovel;
  }

  public function create_billingitempos() {
    $charged_value = 100;
    $monthrefdate = new Carbon('2018-2-1');
    $additionalinfo = 'Additional Info';

    $numberpart=1;
    $totalparts=10;
    $billingitempo = CobrancaGerador::make_billingitempo_for_iptu(
      $charged_value,
      $monthrefdate,
      $additionalinfo,
      $numberpart,
      $totalparts
    );
    print('$billingitempo = ');
    print_r($billingitempo);
    return $billingitempo;
  }

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
