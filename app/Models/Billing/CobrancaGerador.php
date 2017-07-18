<?php
namespace App\Models\Billing;

// use App\Models\Billing\BillingItemsForJson;
// use App\Models\Billing\BillingItemObjToAssocArray as BItem;
use App\Models\Billing\BillingItem;
use App\Models\Billing\Cobranca;
use App\Models\Billing\CobrancaTipo;
// use App\Models\Billing\RefForBillingItem as Ref;
use App\Models\Finance\BankAccount;
use App\Models\Immeubles\Contract;
use App\Models\Immeubles\CondominioTarifa;
use App\Models\Tributos\IPTUTabela;
use App\Models\Utils\DateFunctions;
use App\User;
use Carbon\Carbon;

class CobrancaGerador {

  public static function createOrRetrieveCobrancaWithTripleContractRefSeq(
      $contract,
      $monthyeardateref=null,
      $n_seq_from_dateref=1
    ) {
    // [1] Treat $contract_id
    if ($contract == null) {
      $error = 'Error: Contract is null when instanting a CobrancaGerador object.  Cannot create Cobranca, raise/throw exception.';
      throw new Exception($error);
    }
    // [2] Treat $monthyeardateref
    if ($monthyeardateref==null) {
      $monthyeardateref = DateFunctions
        ::find_rent_monthyeardateref_under_convention(
          Carbon::today(),
          $this->contract->pay_day_when_monthly
        );
    }
    $cobranca = Cobranca
      ::where('contract_id',        $contract->id)
      ->where('monthyeardateref',   $monthyeardateref)
      ->where('n_seq_from_dateref', $n_seq_from_dateref)
      ->first();
    if ($cobranca != null) {
      // ie, cobranca was found, it exists
      return $cobranca;
    }
    // ie, cobranca wasn't found, create a new one
    $cobranca = new Cobranca;
    $cobranca->contract_id        = $contract->id;
    $cobranca->contract()->associate($contract);
    $cobranca->monthyeardateref   = $monthyeardateref;
    $cobranca->duedate            = $monthyeardateref->copy()->addMonths(1);
    $cobranca->duedate->day       = $contract->pay_day_when_monthly;
    $cobranca->n_seq_from_dateref = $n_seq_from_dateref;

    $gerador = new CobrancaGerador($cobranca);
    $gerador->gerar_itens_contratuais();
    $cobranca->save();
    return $cobranca;
  } // ends [static] createOrRetrieveCobrancaWithTripleContractRefSeq()

  private $cobranca         = null;
  private function __construct($cobranca) {

    $this->cobranca = $cobranca;
    /*
    // CobrancaGerador must have ONLY ONE attribute (perhaps another 2 related to db-saving to be established in the future)
    $this->itens_contratuais_gerados = false;
    $this->cobranca_resaved = false;
    */

  } // ends [private] __construct()

  public function get_cobranca() {
    return $this->cobranca;
  }

  public function set_monthyeardateref_relative_to_today() {
    // basically this function will never be called, due to the correction of dateref in the static instantiator function
    $this->cobranca->monthyeardateref = DateFunctions
      ::find_rent_monthyeardateref_under_convention(
        Carbon::today(),
        $this->cobranca->contract->pay_day_when_monthly
      );
  }

  public function set_cobranca_duedate_based_on_monthyeardateref() {
    // basically this 'if' will never happed, due to the correction of dateref in the static instantiator function
    if ($this->cobranca->monthyeardateref==null) {
      $this->set_monthyeardateref_relative_to_today();
    }
    $this->cobranca->duedate      = $this->cobranca->monthyeardateref->copy()->addMonths(1);
    $this->cobranca->duedate->day = $this->cobranca->contract->pay_day_when_monthly;
  }

  private function create_n_add_billingitem_aluguel() {
    // Check existing before instantiating a new object

    $cobrancatipo = CobrancaTipo
      ::where('char_id', CobrancaTipo::K_4CHAR_ALUG)
      ->first();
    if ($cobrancatipo == null) {
      $error = 'cobrancatipo from CobrancaTipo::K_4CHAR_ALUG was not db-found, raise/throw exception.';
      throw new Exception($error);
    }
    $does_billingitem_exist = $this->cobranca->billingitems()
      ->where('cobrancatipo_id', $cobrancatipo->id)->exists();
    if ($does_billingitem_exist == true) {
      // billingitem exists, no need to recreate it, return
      return false;
    }

    $billingitem  = new BillingItem;

    $billingitem->cobranca_id       = $this->cobranca->id;
    $billingitem->cobrancatipo_id   = $cobrancatipo->id;
    $billingitem->brief_description = $cobrancatipo->brief_description;
    $billingitem->charged_value     = $this->cobranca->contract->current_rent_value;
    $billingitem->ref_type          = BillingItem::K_REF_TYPE_IS_DATE;
    $billingitem->freq_used_ref     = BillingItem::K_FREQ_USED_IS_MONTHLY;
    $billingitem->monthyeardateref  = $this->cobranca->monthyeardateref;
    //$billingitem->save();
    $this->cobranca->billingitems()->save($billingitem);
    return true;
  }

  private function create_n_add_billingitem_iptu() {

    if ($this->cobranca->contract->repassar_iptu==false) {
      return false;
    }
      // imovel is protected against null in Constructor (ie, $this->contract->imovel is not null at this point)
    $iptutabela = IPTUTabela
      ::where('imovel_id', $this->cobranca->contract->imovel->id)
      ->where('ano', $this->cobranca->monthyeardateref->year)
      ->first();
    if ($iptutabela == null) {
      return null;
    }
    // 1st case: entire IPTU has been fully paid
    if ($iptutabela->ano_quitado == true) {
      return null;
    }

    $cobrancatipo = CobrancaTipo
      ::where('char_id', CobrancaTipo::K_4CHAR_IPTU)
      ->first();
    if ($cobrancatipo == null) {
      $error = 'cobrancatipo from CobrancaTipo::K_4CHAR_IPTU was not db-found, raise/throw exception.';
      throw new Exception($error);
    }

    $does_billingitem_exist = $this->cobranca->billingitems()
      ->where('cobrancatipo_id', $cobrancatipo->id)->exists();
    if ($does_billingitem_exist == true) {
      // billingitem exists, no need to recreate it, return
      return false;
    }

    $billingitem  = new BillingItem;
    $billingitem->cobranca_id       = $this->cobranca->id;
    $billingitem->cobrancatipo_id   = $cobrancatipo->id;;
    $billingitem->brief_description = $cobrancatipo->brief_description;
    // 2nd case: cota-única anual a ser repassada em Fevereiro, ref. Janeiro
    if ($iptutabela->optado_por_cota_unica == true && $this->monthyeardateref->month == 1) {
      $billingitem->charged_value = $iptutabela->valor_parcela_unica;
      $billingitem->ref_type          = BillingItem::K_REF_TYPE_IS_BOTH_DATE_N_PARCEL;
      $billingitem->freq_used_ref     = BillingItem::K_FREQ_USED_IS_YEARLY;
      $billingitem->n_cota_ref        = 1;
      $billingitem->total_cotas_ref   = 1;
      $billingitem->monthyeardateref  = $this->cobranca->monthyeardateref;
      // $billingitem->save();
      $this->cobranca->billingitems()->save($billingitem);
      return true;
    }
    // 3rd case: cota-única anual a ser repassada em Fevereiro, ref. Janeiro
    if ($this->cobranca->monthyeardateref->month == 1 || $this->cobranca->monthyeardateref->month == 12) {
      // this case is optado por 10x and the first one starts in March ref. February
      // Billing happens from March to December, ref. Feb to Nov
      return null;
    }
    $billingitem->charged_value     = $iptutabela->valor_parcela_10x;
    $billingitem->ref_type          = BillingItem::K_REF_TYPE_IS_BOTH_DATE_N_PARCEL;
    $billingitem->freq_used_ref     = BillingItem::K_FREQ_USED_IS_MONTHLY;
    $billingitem->n_cota_ref        = $this->cobranca->monthyeardateref->month - 1;
    $billingitem->total_cotas_ref   = 10;  // remove the hardcoded value replacing it with the rules table datum
    $billingitem->monthyeardateref  = $this->cobranca->monthyeardateref;
    // $billingitem->save();
    $this->cobranca->billingitems()->save($billingitem);
    return true;
  }

  private function create_n_add_billingitem_condominio() {

    if ($this->cobranca->contract->repassar_condominio == false) {
      return false;
    }
    if ($this->cobranca->contract->imovel == null) {
      $error = '[In CobrancaGerador::create_billingitem_condominio()] Contract object does not have an imovel object attached to it.';
      throw new Exception($error);
    }
    $cobrancatipo = CobrancaTipo
      ::where('char_id', CobrancaTipo::K_4CHAR_COND)
      ->first();
    if ($cobrancatipo == null) {
      $error = 'cobrancatipo from CobrancaTipo::K_4CHAR_COND was not db-found, raise/throw exception.';
      throw new Exception($error);
    }

    $does_billingitem_exist = $this->cobranca->billingitems()
      ->where('cobrancatipo_id', $cobrancatipo->id)->exists();
    if ($does_billingitem_exist == true) {
      // billingitem exists, no need to recreate it, return
      return false;
    }

    $billingitem  = new BillingItem;
    $billingitem->cobranca_id       = $this->cobranca->id;
    $billingitem->cobrancatipo_id   = $cobrancatipo->id;
    $billingitem->brief_description = $cobrancatipo->brief_description;
    // fetch value
    $condominio_tarifa = CondominioTarifa
      ::where('imovel_id',        $this->cobranca->contract->imovel->id)
      ->where('monthyeardateref', $this->cobranca->monthyeardateref)
      ->first();
    $brief_info = null;
    if ($condominio_tarifa == null) {
      $condominio_valor = CondominioTarifa::calcular_media_das_ultimas_tarifas();
      $brief_info = 'Usada a média das últimas tarifas';
    } else {
      $condominio_valor = $condominio_tarifa->tarifa_valor;
    }
    $billingitem->charged_value     = $condominio_valor;
    $billingitem->ref_type          = BillingItem::K_REF_TYPE_IS_DATE;
    $billingitem->freq_used_ref     = BillingItem::K_FREQ_USED_IS_MONTHLY;
    $billingitem->monthyeardateref  = $this->cobranca->monthyeardateref;
    if ($brief_info != null) {
      $billingitem->obs = $brief_info;
    }
    $billingitem->save();
    $this->cobranca->billingitems()->add($billingitem);
    return true;
  }

  private function gerar_itens_contratuais() {
    /*
    DO NOT run $this->set_obj_dates_based_on_today() in here!!!

    // [1] Add Aluguel
    // [2] Add IPTU if applicable
    // [3] Add Condominio if applicable

    TO-DO: implement the algorithm to use the billingitems_rules table
           so that the item generation may become more dynamic and automatic
           instead of pre-fixed here, each one in a corresponding method

    */

    // [1] Add Aluguel
    $result = $this->create_n_add_billingitem_aluguel();
    print ('create_n_add_billingitem_aluguel()s result is ' . $result . "\n");
    // [2] Add IPTU if applicable
    $result = $this->create_n_add_billingitem_iptu();
    print ('create_n_add_billingitem_iptu()s result is ' . $result . "\n");
    // [3] Add Condominio if applicable
    $result = $this->create_n_add_billingitem_condominio();
    print ('create_n_add_billingitem_iptu()s result is ' . $result . "\n");

    $this->cobranca->load('billingitems');
    $this->cobranca->total = 0;
    $this->cobranca->n_items = 0;
    foreach ($this->cobranca->billingitems()->get() as $billingitem) {
      $this->cobranca->total += $billingitem->charged_value;
      $this->cobranca->n_items += 1;
    }
    // $this->cobranca->save();
    $this->cobranca_gerada = true;
  } // ends gerar_itens_contratuais()

  /*
  public function save_cobranca() {
    if ($this->cobranca_dbsaved == true) {
      return null;
    }
    if ($this->cobranca_gerada == true) {
      $this->cobranca->save();
      //$this->cobranca_dbsaved = true;
      return true;
    }
    return false;
  }
  */

  public function __toString() {
    $outstr = 'Gerador\n ';
    $outstr .= $this->cobranca->__toString();
    return $outstr;
  }

} // ends class CobrancaGerador
