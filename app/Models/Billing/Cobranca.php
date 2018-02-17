<?php
namespace App\Models\Billing;
// To import class Cobranca elsewhere in the Laravel App
// use App\Models\Billing\Cobranca;

use App\Models\Finance\BankAccount;
use App\Models\Billing\BillingItem;
use App\Models\Billing\CobrancaTipo;
use App\Models\Billing\BillingItemPO;
use App\Models\Billing\BillingItemGenStatic;
use App\Models\Immeubles\Contract;
use App\Models\Immeubles\Imovel;
use App\Models\Tributos\FunesbomTabela;
use App\Models\Tributos\IPTUTabela;
use App\Models\Utils\DateFunctions;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
*   Class Cobranca
*
*   @package App\Models\Billing\Cobranca
*   @author  Luiz Lewis <livrosetc@yahoo.com.br>
*/
class Cobranca extends Model {

  /*
    =================================
      Beginning of Static Methods
    =================================
  */

  public static function create_cobranca_for_edit_with_imovelapelido_year_month_n_seq(
    $imovelapelido,
    $year,
    $month,
    $monthseqnumber=1
  )	{
    return CobrancaGerador::create();
  }

  public static function fetch_cobranca_with_imovelapelido_year_month_n_seq(
      $imovelapelido,
      $year,
      $month,
      $monthseqnumber=1
    )	{

    $imovel = Imovel
      ::where('apelido', $imovelapelido)
      ->first();
    if ($imovel == null) {
      return null;
    }
    return self::fetch_cobranca_with_imovel_year_month_n_seq(
      $imovel,
      $year,
      $month,
      $monthseqnumber
    );
  }

  public static function fetch_cobranca_with_imovel_year_month_n_seq(
      $imovel,
      $year,
      $month,
      $monthseqnumber=1
    )	{
    $contract = $imovel->get_active_contract();
    if ($contract == null) {
      return null;
    }
    return self::fetch_cobranca_with_contract_year_month_n_seq(
      $contract,
      $year,
      $month,
      $monthseqnumber
    );
  }


  public static function fetch_cobranca_with_contract_year_month_n_seq(
      $contract,
      $year,
      $month,
      $monthseqnumber=1
    )	{
    return self::fetch_cobranca_with_contractid_year_month_n_seq(
      $contract->id,
      $year,
      $month,
      $monthseqnumber
    );
  }

  public static function fetch_cobranca_with_contractid_year_month_n_seq(
      $contract_id,
      $year,
      $month,
      $monthseqnumber=1
    ) {

    $today = Carbon::today();
    if ($year == null) {
      $year = $today->year;
    }
    if ($month == null) {
      $month = $today->month;
    }
		$monthrefdate = new Carbon("$year-$month-01");

    // Notice $cobranca may be null from here
    return Cobranca::fetch_cobranca_with_contractid_monthref_n_seq(
      $contract_id,
      $monthrefdate,
      $monthseqnumber
    );
 	} // ends [static] ()


  public static function fetch_cobranca_with_contractid_monthref_n_seq(
      $contract_id,
      $monthrefdate,
      $monthseqnumber=1
    ) {

    // Notice $cobranca may be null from here
    return Cobranca
      ::where('contract_id', $contract_id)
      ->where('monthrefdate', $monthrefdate)
      ->where('monthseqnumber', $monthseqnumber)
      ->first();
  } // ends [static] retrieve_cobranca_with_contractid_monthref_n_seq()


  /*
    =================================
      End of Static Methods
    =================================
  */

  protected $table = 'cobrancas';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */

 protected $dates = [
   'monthrefdate',
   'duedate',
   //'created_at',
   //'updated_at',
 ];
 //protected $properties = ['totalitems','totalvalue'];

/*
  monthrefdate
  monthseqnumber
  duedate
  contract_id
  total_amount_paid
  bankaccount_id
  amount_paid_ontime
  saldo_cobr_fechada
  lastprocessingdate
  billingitemsjson
  paymentsjson
  amountincreasetrailsjson
  obsinfo
  closed
*/

	protected $fillable = [
    'id',
		'monthrefdate', 'monthseqnumber', 'duedate',
    'contract_id',  'bankaccount_id',

    'closed', 'obsinfo',
	];

  /*
    Dynamic Attributes (those from get<[N]ame>Attributes())
      ->imovel [from getImovelAttribute()]
      ->urlrouteparamsasarray [from getUrlrouteparamsasarrayAttribute()]
  */

  /*
    End of the (5) instance methods that are 'brigde-methods' to static methods in CobrancaTipo:
  */

  public function is_iptu_ano_quitado() {

    if ($this->contract == null) {
      return false;
    }

    if ($this->contract->imovel == null) {
      return false;
    }

    $iptutabela = IPTUTabela
      ::where('imovel_id', $this->contract->imovel->id)
      ->where('ano'      , $this->monthrefdate->year)
      ->first();

    if ($iptutabela != null && $iptutabela->ano_quitado == true) {
      return true;
    }

    return false;
  } // ends is_iptu_ano_quitado()


  public function fetch_previous_cobranca() {
    $previous_monthrefdate = $this->monthrefdate->copy()->addMonths(-1);
    $previous_cobranca = Cobranca
      ::where('contract_id', $this->contract_id)
      ->where('monthrefdate', $previous_monthrefdate)
      ->where('monthseqnumber', 1) // carries are conventioned to bill seq 1
      ->first();
    if ($previous_cobranca == null) {
      return null;
    }
    if (!$previous_cobranca->closed) {
      // $previous_cobranca->closeit();
      $previous_cobranca->close = true;
    }
    return $previous_cobranca;
  }


  public function add_autoincludeable_billing_items() {

    $this->add_rent_billingitem();
    $this->add_condominiotarifa_if_apply();
    $this->add_iptu_if_apply();
    $this->add_funesbom_if_apply();
    $this->carryup_debt_or_cred_from_the_previous_monthref_if_any();

  } // ends add_configured_billing_items()

  public function update_billing_item_having_seqnumber(
			$billingitem_n,
      $cobrancatipo4char,
      $charged_value,
      $billingitem_monthrefdate,
      $additionalinfo,
      $numberpart,
      $totalparts
    ) {
		if ($billingitem_n >= count($this->billingitems)) {
      return false;
    }
    $billingitem = $this->billingitems[$billingitem_n];
		if ($billingitem == null) {
      return false;
    }
    
    $was_updated = $billingitem->update_with(
      $this,
      $cobrancatipo4char,
      $charged_value,
      $billingitem_monthrefdate,
      $additionalinfo,
      $numberpart,
      $totalparts
    );

    return $was_updated;
    
  } // ends update_billing_item_having_seqnumber()

  public function add_rent_billingitem() {

    $charged_value = $this->contract->get_monthly_value();
    $billing_item = BillingItemGenStatic::make_billingitem_for_aluguel(
      $this,
      $charged_value,
      $this->monthrefdate
    );
    if ($billing_item != null) {
      $this->billingitems->push($billing_item);
    }

  } // ends add_rent_billingitem()

  public function add_condominiotarifa_if_apply() {

    if ($this->imovel==null){
      return;
    }

    if (!$this->imovel->is_condominio_billable()) {
      return;
    }
    $charged_value = $this->imovel->get_condominiotarifa_in_refmonth($this->monthrefdate);
    $billing_item = BillingItemGenStatic::make_billingitem_for_condominio(
      $this,
      $charged_value,
      $this->monthrefdate
    );
    if ($billing_item != null) {
      // $this->billingitems[] = $billing_item;
      $this->billingitems->push($billing_item);
    }

  } // ends add_condominiotarifa()

  public function add_iptu_if_apply() {

    if ($this->imovel==null){
      return;
    }

    $iptuanoimovel = $this->contract->imovel
      ->get_iptuanoimovel_with_refmonth_or_default($this->monthrefdate);

    if (!$iptuanoimovel->is_refmonth_billable($this->monthrefdate)) {
      return;
    }
    $charged_value = $iptuanoimovel->get_months_repass_value($this->monthrefdate);
    $numberpart    = $iptuanoimovel->get_numberpart_with_refmonth($this->monthrefdate);
    $totalparts    = $iptuanoimovel->totalparts; // do not use: total_de_parcelas for totalparts may embody either of two values
    $billing_item = BillingItemGenStatic::make_billingitem_for_iptu(
      $this,
      $charged_value,
      $this->monthrefdate,
      'additional info iptu',
      $numberpart,
      $totalparts
    );
    if ($billing_item != null) {
      //$this->billingitems[]=$billing_item;
      $this->billingitems->push($billing_item);
    }

  } // ends add_iptu()

  public function add_funesbom_if_apply() {
    if ($this->imovel == null) {
      return;
    }
    $funesbom = FunesbomTabela::fetch_by_imovelapelido_n_ano($this->imovel->apelido, $this->monthrefdate->year);
    if ($funesbom == null) {
      return;
    }
    if ($funesbom->is_refmonth_billable($this->monthrefdate)) {
      $charged_value = $funesbom->get_months_repass_value($this->monthrefdate);
      $numberpart = $funesbom->get_numberpart_with_refmonth($this->monthrefdate);
      $totalparts = $funesbom->totalparts; // do not use: total_de_parcelas for totalparts may embody either of two values
      $billing_item = BillingItemGenStatic::make_billingitem_for_fune(
        $this,
        $charged_value,
        $this->monthrefdate,
        'additional info iptu',
        $numberpart,
        $totalparts
      );
      if ($billing_item != null) {
        //$this->billingitems[]=$billing_item;
        $this->billingitems->push($billing_item);
      }
    }
  } // ends ()

  public function carryup_debt_or_cred_from_the_previous_monthref_if_any() {
    $previous_cobranca = $this->fetch_previous_cobranca();
    if ($previous_cobranca == null) {
      return;
    }
    $balance = $previous_cobranca->get_balance();
    if ($balance > 0) {
      $this->carryup_debt_from_the_previous_monthref_if_any($balance);
    }
    elseif ($balance < 0) {
      $this->carryup_cred_from_the_previous_monthref_if_any($balance);
    }
  } // ends ()

  private function carryup_debt_from_the_previous_monthref_if_any($debt_to_carry) {

    $billing_item = BillingItemGenStatic::make_billingitem_for_carr(
      $this,
      $debt_to_carry,
      $this->monthrefdate,
      '$additional info carr',
      $numberpart = 1,
      $totalparts = 1
    );
    if ($billing_item != null) {
      //$this->billingitems[] = $billing_item;
      $this->billingitems->push($billing_item);
    }
  } //

  private function carryup_cred_from_the_previous_monthref_if_any($cred_to_carry) {

    $billing_item = BillingItemGenStatic::make_billingitem_for_cred(
      $this,
      $cred_to_carry, // should be negative (though, if not, it's corrected inside)
      $this->monthrefdate,
      '$additional info cred',
      $numberpart = 1,
      $totalparts = 1
    );
    if ($billing_item != null) {
      // $this->billingitems[] = $billing_item;
      $this->billingitems->push($billing_item);
    }
  } // ends

  public function copy_without_billingitems() {
    /*
      This method copies an instance partially.
      Basically, the id will not be copied, for it belongs to the instance
        in place, used as mold
      The billing items, if any, will not be copied,
        for it's the purpose of the method here.
    */
    $copied_cobranca = new Cobranca;
    // ->id stays empty, for it's established by the Eloquent engine so to say
    if ($this->monthrefdate != null) {
      $copied_cobranca->monthrefdate = $this->monthrefdate->copy();
    }
    $copied_cobranca->monthseqnumber = $this->monthseqnumber;
    if ($this->duedate != null) {
      $copied_cobranca->duedate = $this->duedate->copy();
    }

    $copied_cobranca->modifying_amount = $this->modifying_amount;
    $copied_cobranca->obsinfo          = $this->obsinfo;

    $copied_cobranca->contract_id    = $this->contract_id;
    $copied_cobranca->bankaccount_id = $this->bankaccount_id;
    $copied_cobranca->totalparts     = $this->totalparts;
    // $copied_cobranca->closed = $this->closed;

    return $copied_cobranca;
  }

  // dynamic attribute 'imovel'
  public function getImovelAttribute() {
    if ($this->contract == null) {
      return null;
    }
    $imovel = $this->contract->imovel;
    // null may be returned
    return $imovel;
  }

  public function getUrlrouteparamsasarrayAttribute() {
    /*
      This method maps to dynamic attribute:
        ->urlrouteparamsasarray

      This method is for the url-route option that receives:
        (year, month, imovel_char4id, monthseqnumber)

      The simpler url-route only needs the id
    */
    $imovelapelido = '';
    if ($this->imovel != null) {
      $imovelapelido = $this->imovel->apelido;
    }
    return [
      $this->monthrefdate->year,
      $this->monthrefdate->month,
      $imovelapelido,
      $this->monthseqnumber
    ];
  }

  public function fetch_next_bill() {
    /*
      Obs.:
        Because there's no guarantee the logical keys (contract_id, monthrefdate & monthseqnumber)
        might be repeated in db,
        this fetch must be taken in TWO steps.

        See also docstring for fetch_previous_bill()
    */
    $next_bill = self
      ::where('contract_id', $this->contract_id)
      ->where('monthrefdate', '=', $this->monthrefdate)
      ->where('monthseqnumber', '>', $this->monthseqnumber)
      ->orderBy('monthseqnumber', 'asc')
      ->first(); // skip(1) does not make it null if there's only "itself"

    if ($next_bill != null) {
      return $next_bill;
    }

    $next_bill = self
      ::where('contract_id', $this->contract_id)
      ->where('monthrefdate', '>', $this->monthrefdate)
      ->orderBy('monthrefdate', 'asc')
      ->orderBy('monthseqnumber', 'asc')
      ->first(); // skip(1) does not make it null if there's only "itself"

    return $next_bill;
}

// get_routeparams_tonextbill_asarray
  public function fetch_previous_bill() {
    /*

      This method searches 'backwards' in two steps.

      1) The first step is to see if there's a lower monthseqnumber
      in the same month as the original bill;

      2) The second step is to see if there's a lower monthrefdate
      also guaranteeing that monthseqnumber is descendingly ordered
    */

    // 1st step: check if there's a lower monthseqnumber
    // =================================================
    $previous_bill = self
      ::where('contract_id', $this->contract_id)
      ->where('monthrefdate', $this->monthrefdate)
      ->where('monthseqnumber', '<', $this->monthseqnumber)
      ->orderBy('monthrefdate', 'desc')
      ->first();

    if ($previous_bill != null) {
      // Found it !
      return $previous_bill;
    }

    // 2nd step: pick up later record before monthrefdate
    // =================================================

    $previous_bill = self
      ::where('contract_id', $this->contract_id)
      ->where('monthrefdate', '<', $this->monthrefdate)
      ->orderBy('monthrefdate', 'desc')
      ->orderBy('monthseqnumber', 'desc')
      ->first();

    // null may be returned from here which means that no previous bill has been found
    return $previous_bill;
  }

  public function get_routeparams_tonextbill_asarray() {
    $next_bill = $this->fetch_next_bill();
    if ($next_bill == null) {
      return [];
    }
    return $next_bill->urlrouteparamsasarray;
  }

  public function get_routeparams_toformerbill_asarray() {
    /*
      output route params are:
        [year, month, imovel_char4id, monthseqnumber]
      Eg.:
        /urlroute.../2018/1/cdutra/1/
    */
    $previous_bill = $this->fetch_previous_bill();
    if ($previous_bill == null) {
      return [];
    }
    return $previous_bill->urlrouteparamsasarray;
  } // ends get_routeparams_toformerbill_asarray()

  public function fetch_or_create_next_months_bill($monthseqnumber=1) {
    $next_monthrefdate = $this->monthrefdate->copy()->addMonths(1);
    if ($this->contract_id == null) {
        throw new Exception("contract_id was not db-found when calling createOrFindNextMonthCobranca())", 1);
    }
    return CobrancaGerador
      ::fetch_or_create_next_months_bill(
        $this->contract_id,
        $next_monthrefdate,
        $monthseqnumber
      );
    }

  public function set_duedate_from_monthrefdate() {
    /*
      WEIRD behaviou has been found here, maybe it's a bug in Carbon-Laravel.

      'duedate' below only gets the day value given,
        when model is not yet saved in db,
        if the day() method is chained with copy() and addMonths()

      If, on the other hand, method day() is used after in a following line,
        the day is not changed. (This has been seen in both Tinker and on browser).

      The expected value comes, as said above, if day() is chained and
        the whole instruction goes into one line of code (see below).

    */
    $this->duedate = $this->monthrefdate->copy()->addMonths(1)->day(10);
    // TO-DO take out the 10 hardcoded when possible !!!
    //$this->duedate->day(10);
    //$this->duedate->addMonths(1);
  }

  public function find_n_days_until_duedate_in_future() {
    $today = Carbon::today();
    if ($today > $this->duedate) {
      return null;
    }
    $n_days_until_duedate = $this->duedate->diffInDays($today);
    return $n_days_until_duedate;
  }

  public function generate_autoinsertable_billingitems() {
    $billingitems = [];
    $cobrancatipo = CobrancaTipo::fetch_by_char4id(CobrancaTipo::K_4CHAR_ALUG);
    $charged_value = $this->contract->current_rent_value;
    $monthrefdate = $this->monthrefdate->copy();
    $additionalinfo = 'additional info';
    $numberpart = 1;
    $totalparts = 1;
    $billingitem = BillingItemGenStatic::make_billingitem_for_aluguel(
      $this,
      $charged_value,
      $monthrefdate,
      $additionalinfo,
      $numberpart,
      $totalparts
    );
    if ($billingitem != null) {
      $billingitems[] = $billingitem;
    }

    $cobrancatipo = CobrancaTipo::fetch_by_char4id(CobrancaTipo::K_4CHAR_COND);
    $charged_value = $this->imovel->get_condominiotarifa_in_refmonth($monthrefdate);
    $billingitem = BillingItemGenStatic::make_billingitem_for_condominio(
      $this,
      $charged_value,
      $monthrefdate,
      $additionalinfo,
      $numberpart,
      $totalparts
    );
    if ($billingitem != null) {
      $billingitems[] = $billingitem;
    }

    $cobrancatipo = CobrancaTipo::fetch_by_char4id(CobrancaTipo::K_4CHAR_IPTU);
    $iptutabela = IPTUTabela::fetch_by_imovel_n_ano_or_return_null($this->imovel, $this->monthrefdate->year);
    if ($iptutabela != null) {
      $billingitem = BillingItemGenStatic::make_billingitem_for_iptu_with_iptutabela(
        $this,
        $iptutabela,
        $monthrefdate,
        $additionalinfo
      );
      if ($billingitem != null) {
        $billingitems[] = $billingitem;
      }
    }
    foreach ($billingitems as $billingitem) {
      $this->billingitems->push($billingitem);
    }
    return $billingitems;
  }

  public function __toString() {
    /*
          TODO: improve this __toString()
    */
    $outstr = "Cobrança\n ";
    $apelido = '';
    if ($this->contract != null) {
      if ($this->contract->imovel != null) {
        $apelido = $this->contract->imovel->apelido;
      }
    }
    $outstr .= 'Contract Imóvel ' . $apelido . '\n';
    // $outstr .= $this->billingitemsinjson;
    return $outstr;
  } // ends __toString()

  public function get_balance() {
    /*
    TO-DO:
      This method, for the time being, just calls 'totalvalue',
      however, the idea that we probably tried to express
      is one for closing the cobrança at the end of month,
      having another one to be opened and carry debts or credits
      if there are any, or, if no new month is open, keep this open.
    */

    return $this->totalvalue;
  }

  public function get_users() {
    if ($this->contract != null) {
      // the returning users()->get() is a Collection
      return $this->contract->users()->get();
    }
    return [];
  }

  public function get_contract_queried_or_by_id() {
    /*
      This method is useful at the object's creation time, before saving,
      when Eloquent has not yet resolved its belongsTo relationship

      (Notwithstanding, we don't know it yet,
       there may be some other way to solve this chicken-and-egg problem...)
    */

    if (!empty($this->contract)) {
      return $this->contract;
    }

    return Contract::find($this->contract_id);

  } // ends get_contract_queried_or_by_id()

  public function set_bankaccountid_from_contract_or_default() {

    if (!empty($this->bankaccount_id)) {
      return;
    }

    $contract = $this->get_contract_queried_or_by_id();
    if (!empty($contract)) {
      if (!empty($contract->bankaccount)) {
        // SET only bankaccount_id, NOT bankaccount
        $this->bankaccount_id = $contract->bankaccount->id;
        return;
      } elseif (!empty($contract->bankaccount_id)) {
        $this->bankaccount_id = $contract->bankaccount_id;
        return;
      }
    }
    // If method has not yet returned, fall back bankaccount_id to default_id
    $bankaccount = BankAccount::get_default();
    $this->bankaccount_id = $bankaccount->id;
    return;
  } // ends set_bankaccountid_from_contract_or_default()

  public function get_bankaccount_queried_or_by_id() {

    /*
      This method retrieves bankaccount either by the Eloquent automatc fetch
        or, at the object's creation before save() and id assignment, by bankaccount_id
    */
    if (!empty($this->bankaccount)) {
      return $this->bankaccount;
    }
    if (empty($this->bankaccount_id)) {
      $this->set_bankaccountid_from_contract_or_default();
    }
    return BankAccount::find($this->bankaccount_id);
  } // ends get_contract_queried_or_by_id()

  public function bankaccount() {
    return $this->belongsTo('App\Models\Finance\BankAccount');
  }
  public function contract() {
    return $this->belongsTo('App\Models\Immeubles\Contract');
  }

  // dynamic attribute: 'totalitems'
  public function getTotalitemsAttribute() {
    return count($this->billingitems);
  }
  // dynamic attribute: 'totalvalue'
  public function getTotalvalueAttribute() {
    /*
      Billing Items, summed up, tell the bill's total.
      Notice this ($this->totalvalue) is a dynamic attribute
    */
    $total_charged = 0;
    foreach ($this->billingitems as $billingitem) {
      $total_charged += $billingitem->charged_value;
    }
    return $total_charged;
  }
  public function billingitems() {
    return $this->hasMany('App\Models\Billing\BillingItem');
  }

  public function payments() {
    return $this->hasMany('App\Models\Billing\Payment');
  }

  public function amountincreasetrails() {
    return $this->hasMany('App\Models\Billing\AmountIncreaseTrail');
  }

  public function save_propagating_billingitems() {
    $this->save();
    foreach ($this->billingitems as $billingitem) {
      $billingitem->cobranca_id = $this->id;
      $billingitem->save();
    }
  }


} // ends class Cobranca extends Model
