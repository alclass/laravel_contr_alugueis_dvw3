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

  /*  The Constructor() is PRIVATE Here
      To generate or retrieve a Cobranca object, use the public static methods:
        ::createOrRetrieveCobrancaWithTripleContractRefSeq()
        ::createOrRetrieveCobrancaWithTripleContractIdRefSeq()
  */

  public static function createOrRetrieveCobrancaWithTripleContractIdRefSeq(
      $contract_id,
      $monthyeardateref=null,
      $n_seq_from_dateref=1
    ) {
    $contract = Contract::find($contract_id);
    return self::createOrRetrieveCobrancaWithTripleContractRefSeq(
      $contract,
      $monthyeardateref,
      $n_seq_from_dateref
    );
  } // ends [static] createAndReturnNewCobrancaWithTripleContractRefSeq()

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
      // The convention is:
      // if day is within [1,duedate] monthref is the previous one
      // if day is duedate+1 and above monthref is the current one
      $monthyeardateref = DateFunctions
        ::find_conventional_monthyeardateref_with_date_n_dueday(
          null, // $p_monthyeardateref
          $contract->pay_day_when_monthly
        );
    }
    $cobranca = Cobranca
      ::where('contract_id',        $contract->id)
      ->where('monthyeardateref',   $monthyeardateref)
      ->where('n_seq_from_dateref', $n_seq_from_dateref)
      ->first();
    if ($cobranca == null) {
      // ie, cobranca wasn't found
      // create a new Cobranca for it does not exist yet
      $cobranca = self::createAndReturnNewCobrancaWithTripleContractRefSeq(
        $contract,
        $monthyeardateref,
        $n_seq_from_dateref
      );
    }
    // From here $cobranca is not null and is of intended class-type
    $gerador = new CobrancaGerador($cobranca);
    $gerador->gerar_itens_contratuais();
    // $cobranca->save(); // now 'id' will be available for the billing items (they'll need it)
    return $cobranca;
  } // ends [static] createOrRetrieveCobrancaWithTripleContractRefSeq()

  private static function createAndReturnNewCobrancaWithTripleContractRefSeq(
      $contract,
      $monthyeardateref,
      $n_seq_from_dateref=1
    ) {
    $cobranca = new Cobranca;
    $cobranca->contract_id        = $contract->id;
    $cobranca->bankaccount_id     = $contract->bankaccount_id;
    $cobranca->monthyeardateref   = $monthyeardateref;
    $cobranca->duedate            = $monthyeardateref->copy()->addMonths(1);
    $cobranca->duedate->day($contract->pay_day_when_monthly);
    $cobranca->n_seq_from_dateref = $n_seq_from_dateref;
    $cobranca->save();
    $cobranca->contract()->associate($contract);
    return $cobranca;
  } // ends [static] createAndReturnNewCobrancaWithTripleContractRefSeq()

  /*--------------------------------------------
    Beginning of AREA for the class' attributes:
    --------------------------------------------*/

  private $cobranca                = null;
  private $cobrancatipo_objs_array = null;

  /*--------------------------------------------
    End of AREA for the class' attributes:
    --------------------------------------------*/


  private function __construct($cobranca) {

    $this->cobranca = $cobranca;
    // --------------------------------------------
    // Buffer the 3 main billing type objects, ie ALUG, IPTU & COND
    // --------------------------------------------
    $this->fill_in_cobrancatipo_objs_array();

  } // ends [private] __construct()

  private function fill_in_cobrancatipo_objs_array() {
    /*
      Buffer the 3 main billing type objects, ie ALUG, IPTU & COND
      Two attributes are used from CobrancaTipo, ie, its 'id' & its 'brief_description'
    */
    $this->cobrancatipo_objs_array = array();
    $do_raise_exception_if_null_cobrancatipo = true;

    // [1] ALUG
    $cobrancatipo = CobrancaTipo::get_cobrancatipo_with_its_4char_repr(
      CobrancaTipo::K_4CHAR_ALUG,
      $do_raise_exception_if_null_cobrancatipo
    );
    $this->cobrancatipo_objs_array[CobrancaTipo::K_4CHAR_ALUG] = $cobrancatipo;
    // [2] IPTU
    $cobrancatipo = CobrancaTipo::get_cobrancatipo_with_its_4char_repr(
      CobrancaTipo::K_4CHAR_IPTU,
      $do_raise_exception_if_null_cobrancatipo
    );
    $this->cobrancatipo_objs_array[CobrancaTipo::K_4CHAR_IPTU] = $cobrancatipo;
    // [3] CONDOMÍNIO
    $cobrancatipo = CobrancaTipo::get_cobrancatipo_with_its_4char_repr(
      CobrancaTipo::K_4CHAR_COND,
      $do_raise_exception_if_null_cobrancatipo
    );
    $this->cobrancatipo_objs_array[CobrancaTipo::K_4CHAR_COND] = $cobrancatipo;

  } // ends fill_in_cobrancatipo_objs_array()

  /*
  The accessor below [get_cobranca()] doesn't make sense anymore,
    due to now private Constructor
  public function get_cobranca() {
    return $this->cobranca;
  }
  */

  public function set_monthyeardateref_relative_to_today($force_change_if_dateref_not_null=false) {
    /*
      Basically this function will never be called, due to the adjustment
       in dateref in the static instantiator function above
       ie, createOrRetrieveCobrancaWithTripleContractRefSeq()
    */
    if ($this->cobranca->monthyeardateref!=null && $force_change_if_dateref_not_null==false) {
      return;
    }
    $this->cobranca->monthyeardateref = DateFunctions
      ::find_conventional_monthyeardateref_with_date_n_dueday(
        null, // $p_monthyeardateref
        $this->cobranca->contract->pay_day_when_monthly
      );
  }

  public function set_cobranca_duedate_based_on_monthyeardateref() {
    /*
      Basically the first 'if' in this method will never solve 'true',
       due to the adjustment
       in dateref in the static instantiator function above
       ie, createOrRetrieveCobrancaWithTripleContractRefSeq()
    */
    if ($this->cobranca->monthyeardateref==null) {
      $this->set_monthyeardateref_relative_to_today();
    }
    $this->cobranca->duedate      = $this->cobranca->monthyeardateref->copy()->addMonths(1);
    $this->cobranca->duedate->day = $this->cobranca->contract->pay_day_when_monthly;
  }

  private function verify_existence_of_billingitem_already_in_cobranca($billingtype_in_4char_repr) {

    $cobrancatipo = $this->cobrancatipo_objs_array[$billingtype_in_4char_repr];
    // return true (ie, this billing type is already present) or false (it's not there yet)
    return $this->cobranca->billingitems()
      ->where('cobrancatipo_id', $cobrancatipo->id)->exists();
  } // verify_existence_of_billingitem_already_in_cobranca()

  private function create_if_not_exist_billingitem_for_aluguel() {

    // Check existing before instantiating a new object

    $billingtype_in_4char_repr = CobrancaTipo::K_4CHAR_ALUG;
    $does_biling_item_exist = $this->verify_existence_of_billingitem_already_in_cobranca($billingtype_in_4char_repr);
    if ($does_biling_item_exist == true) {
      return false;
    }

    // Okay: create new ALUG item
    $billingitem  = new BillingItem;
    // ->save() will be used below at method's end
    // $billingitem->cobranca_id       = $this->cobranca->id;
    $cobrancatipo = $this->cobrancatipo_objs_array[CobrancaTipo::K_4CHAR_ALUG];
    $billingitem->cobrancatipo_id   = $cobrancatipo->id;
    $billingitem->brief_description = $cobrancatipo->brief_description;
    $billingitem->charged_value     = $this->cobranca->contract->current_rent_value;
    $billingitem->ref_type          = BillingItem::K_REF_TYPE_IS_DATE;
    $billingitem->freq_used_ref     = BillingItem::K_FREQ_USED_IS_MONTHLY;
    $billingitem->monthyeardateref  = $this->cobranca->monthyeardateref;

    $this->cobranca->billingitems()->save($billingitem);
    $billingitem->save();
    return true;
  } // ends create_if_not_exist_billingitem_for_aluguel()

  private function create_if_not_exist_billingitem_for_iptu() {
    /*
      Return null from here if:
        [1] IPTU billing item is not applicable or
        [2] IPTU db-info is not available, so it probably will indicate not applicable or database is offline (try later on)
        [3] IPTU has been yearly paid already
    */

    if ($this->cobranca->contract->repassar_iptu==false) {
      return null; // [1] IPTU billing item is not applicable
    }
    // [recheck this] imovel is protected against null in Constructor (ie, $this->contract->imovel is not null at this point)
    $iptutabela = IPTUTabela
      ::where('imovel_id', $this->cobranca->contract->imovel->id)
      ->where('ano', $this->cobranca->monthyeardateref->year)
      ->first();
    if ($iptutabela == null) {
      return null; // [2] IPTU db-info is not available (see also docstring above)
    }
    // 1st case: entire IPTU has been fully paid
    if ($iptutabela->ano_quitado == true) {
      return null; // [3] IPTU has been yearly paid already
    }

    // 3rd case: non-incidence on ref.Jan and ref.Dez
    if ($this->cobranca->monthyeardateref->month == 1 || $this->cobranca->monthyeardateref->month == 12) {
      // this case is optado por 10x and the first one starts in March ref. February
      // Billing happens from March to December, ref. Feb to Nov
      return null;
    }

    $billingtype_in_4char_repr = CobrancaTipo::K_4CHAR_IPTU;
    $does_biling_item_exist = $this->verify_existence_of_billingitem_already_in_cobranca($billingtype_in_4char_repr);
    if ($does_biling_item_exist == true) {
      // billingitem exists, no need to recreate it, return
      return false;
    }

    $billingitem  = new BillingItem;
    // ->save() will be used below at method's end
    // $billingitem->cobranca_id       = $this->cobranca->id;
    $cobrancatipo = $this->cobrancatipo_objs_array[CobrancaTipo::K_4CHAR_IPTU];
    $billingitem->cobrancatipo_id   = $cobrancatipo->id;;
    $billingitem->brief_description = $cobrancatipo->brief_description;

    // 1st create case: cota-única anual foi escolhida a ser repassada em Fevereiro, ref. Janeiro
    if ($iptutabela->optado_por_cota_unica == true && $this->monthyeardateref->month == 1) {
      $billingitem->charged_value     = $iptutabela->valor_parcela_unica;
      $billingitem->ref_type          = BillingItem::K_REF_TYPE_IS_BOTH_DATE_N_PARCEL;
      $billingitem->freq_used_ref     = BillingItem::K_FREQ_USED_IS_YEARLY;
      $billingitem->n_cota_ref        = 1;
      $billingitem->total_cotas_ref   = 1; // no logical need for a const here,
      //  1 itself hardcoded is logically okay, but for N cotas,
      //  there'll be a static method in IPTUTabela to avoid hardcoding N (cotas) here
      $billingitem->monthyeardateref  = $this->cobranca->monthyeardateref;
    } else {
      // 2nd create case: escolhido o pagamento em 10 cotas (10 é const em IPTUTabela)
      // if even the cota-única was chosen (because it was chosen but not paid...  Review this)
      $billingitem->charged_value     = $iptutabela->valor_parcela_10x;
      $billingitem->ref_type          = BillingItem::K_REF_TYPE_IS_BOTH_DATE_N_PARCEL;
      $billingitem->freq_used_ref     = BillingItem::K_FREQ_USED_IS_MONTHLY;
      $billingitem->n_cota_ref        = $this->cobranca->monthyeardateref->month - 1;
      $billingitem->total_cotas_ref   = IPTUTabela::get_IPTU_N_COTAS_ANO();
      $billingitem->monthyeardateref  = $this->cobranca->monthyeardateref;
    }

    $this->cobranca->billingitems()->save($billingitem);
    $billingitem->save();
    return true;
  } // ends create_if_not_exist_billingitem_for_iptu()

  private function create_if_not_exist_billingitem_for_condominio() {
    /*
      Return null from here if:
        [1] condominio billing item is not applicable
    */
    if ($this->cobranca->contract->repassar_condominio == false) {
      return null; // [1] condominio billing item is not applicable
    }

    // REVISE this 'if' below!
    if ($this->cobranca->contract->imovel == null) {
      $error = '[In CobrancaGerador::create_billingitem_condominio()] Contract object does not have an imovel object attached to it.';
      throw new Exception($error);
    }

    $billingtype_in_4char_repr = CobrancaTipo::K_4CHAR_COND;
    $does_biling_item_exist = $this->verify_existence_of_billingitem_already_in_cobranca($billingtype_in_4char_repr);
    if ($does_biling_item_exist == true) {
      // billingitem exists, no need to recreate it, return
      return false;
    }

    $billingitem  = new BillingItem;
    // ->save() will be used below at method's end
    // $billingitem->cobranca_id       = $this->cobranca->id;
    $cobrancatipo = $this->cobrancatipo_objs_array[CobrancaTipo::K_4CHAR_COND];
    $billingitem->cobrancatipo_id   = $cobrancatipo->id;
    $billingitem->brief_description = $cobrancatipo->brief_description;

    // Find condominium tariff value
    $valor_e_ou_brief_info = CondominioTarifa::get_valor_tarifa_mesref_ou_alternativa_com_brief_info(
      $this->cobranca->contract->imovel->id,
      $this->cobranca->monthyeardateref
    );
    $condominio_tarifa_valor = $valor_e_ou_brief_info['condominio_tarifa_valor'];
    $brief_info = $valor_e_ou_brief_info['brief_info'];

    $billingitem->charged_value     = $condominio_tarifa_valor;
    $billingitem->ref_type          = BillingItem::K_REF_TYPE_IS_DATE;
    $billingitem->freq_used_ref     = BillingItem::K_FREQ_USED_IS_MONTHLY;
    $billingitem->monthyeardateref  = $this->cobranca->monthyeardateref;
    if ($brief_info != null) {
      $billingitem->obs = $brief_info;
    }
    $this->cobranca->billingitems()->save($billingitem);
    $billingitem->save();
    return true;
  } // ends create_if_not_exist_billingitem_for_condominio()


  public function true_false_null_to_str($true_false_null) {

    switch ($true_false_null) {
      case true:
        $true_false_null_str = 'true';
        break;
      case false:
        $true_false_null_str = 'false';
        break;
      case null:
        $true_false_null_str = 'null';
        break;
      default:
        $true_false_null_str = 'nenhum dos 3 (true false null)';
    }
    return $true_false_null_str;
  } // ends result_true_false_null_to_str()


  public function create_billingitem_for_mora($moradebito) {

    $billingitem  = new BillingItem;
    // ->save() will be used below at method's end
    // $billingitem->cobranca_id       = $this->cobranca->id;
    $cobrancatipo = $this->cobrancatipo_objs_array[CobrancaTipo::K_4CHAR_MORA];
    $billingitem->cobrancatipo_id   = $cobrancatipo->id;
    $billingitem->brief_description = $cobrancatipo->brief_description;
    $moradebito->run_time_correction_of_ini_debt_value();
    $billingitem->charged_value     = $moradebito->changed_debt_value;
    $billingitem->ref_type          = BillingItem::K_REF_TYPE_IS_DATE;
    $billingitem->freq_used_ref     = BillingItem::K_FREQ_USED_IS_MONTHLY;
    $billingitem->monthyeardateref  = $moradebito->monthyeardateref;
    $brief_info = $moradebito->get_lineinfo_n_time_correction_lineinfo();
    if ($brief_info != null) {
      $billingitem->obs = $brief_info;
    }
    $this->cobranca->billingitems()->save($billingitem);
    $billingitem->save();

  } // ends create_billingitem_for_mora()

  private function fetch_if_any_mora_items() {

    $contract_id = $this->cobranca->contract->id;
    $moradebitos = MoraDebito
      ::where('contract_id', $contract_id)
      ->where('is_open', true)
      ->get();

    foreach ($moradebitos as $moradebito) {
      $this->create_billingitem_for_mora($moradebito);
    }

  } // ends fetch_if_any_mora_items()

  private function gerar_itens_contratuais() {
    /*

    At this moment, gerar_itens_contratuais() will deal with
      the following billing items:
     => [1] Add Aluguel
     => [2] Add IPTU if applicable
     => [3] Add Condominio if applicable

    -----------------------------------------------------------
    TO-DO: implement the algorithm to use the billingitems_rules table
           so that the item generation may become more dynamic and automatic
           instead of pre-fixed here, each one in a corresponding method
    -----------------------------------------------------------

    */
    $call_recalculate_total_and_n_items = false;

    // [1] Before creating and adding "aluguel", verify whether it already exists
    $result_true_false_null = $this->create_if_not_exist_billingitem_for_aluguel();
    $result_true_false_null_str = $this->true_false_null_to_str($result_true_false_null);
    print ('[1] Aluguel result = [[' . $result_true_false_null_str . "]]\n");
    $call_recalculate_total_and_n_items = $call_recalculate_total_and_n_items || $result_true_false_null;

    // [2] Add IPTU if applicable
    $result_true_false_null = $this->create_if_not_exist_billingitem_for_iptu();
    $result_true_false_null_str = $this->true_false_null_to_str($result_true_false_null);
    print ('[2] IPTU result = [[' . $result_true_false_null_str . "]]\n");
    $call_recalculate_total_and_n_items = $call_recalculate_total_and_n_items || $result_true_false_null;

    // [3] Add Condominio if applicable
    $result_true_false_null = $this->create_if_not_exist_billingitem_for_condominio();
    $result_true_false_null_str = $this->true_false_null_to_str($result_true_false_null);
    print ('[3] Condomínio result = [[' . $result_true_false_null_str . "]]\n");

    $call_recalculate_total_and_n_items = $call_recalculate_total_and_n_items || $result_true_false_null;
    $call_recalculate_total_and_n_items_str = $this->true_false_null_to_str($call_recalculate_total_and_n_items);
    print ('[+] call_recalculate_total_and_n_items = [[' . $call_recalculate_total_and_n_items_str . "]]\n");

    $result_true_false_null = $this->fetch_if_any_mora_items();
    $result_true_false_null_str = $this->true_false_null_to_str($result_true_false_null);
    print ('[3] Condomínio result = [[' . $result_true_false_null_str . "]]\n");

    if ($call_recalculate_total_and_n_items==true) {
      $this->recalculate_total_and_n_items_and_resave();
    }

  } // ends gerar_itens_contratuais()

  public function recalculate_total_and_n_items_and_resave() {

    $this->cobranca->total = 0;
    $this->cobranca->n_items = 0;
    foreach ($this->cobranca->billingitems()->get() as $billingitem) {
      $this->cobranca->total += $billingitem->charged_value;
      $this->cobranca->n_items += 1;
    }
    // if ($this->cobranca->billingitems()->count()>0) {
    if ($this->cobranca->n_items > 0) {
      $this->cobranca->save();
    }

  } // ends recalculate_total_and_n_items_and_resave()

  public function __toString() {
    /*

      TODO: improve this __toString() later on when possible

    */
    $outstr = 'Gerador\n ';
    $outstr .= $this->cobranca->__toString();
    return $outstr;
  }

} // ends class CobrancaGerador
