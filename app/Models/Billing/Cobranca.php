<?php
namespace App\Models\Billing;

// To import class Cobranca elsewhere in the Laravel App
// use App\Models\Billing\Cobranca;

use App\Models\Finance\BankAccount;
use App\Models\Billing\BillingItem;
use App\Models\Billing\CobrancaTipo;
use App\Models\Immeubles\Contract;
use App\Models\Tributos\IPTUTabela;
use App\Models\Utils\DateFunctions;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Cobranca extends Model {

  /*
    =================================
      Beginning of Static Methods
    =================================
  */
  public static function fetch_cobranca_with_year_month_contractid_n_seq(
      $year,
      $month,
      $contract_id,
      $monthseqnumber=1
    )	{
    if ($contract_id == null) {
      return null;
    }
    $today = Carbon::today();
    if ($year == null) {
      $year = $today->year;
    }
    if ($month == null) {
      $month = $today->month;
    }

		$monthrefdate = DateFunctions::make_n_get_monthyeardateref_with_year_n_month($year, $month);
		$cobranca = Cobranca
			::where('contract_id', $contract_id)
			->where('monthrefdate', $monthrefdate)
      ->where('monthseqnumber', $monthseqnumber)
			->first();
		// Notice $cobranca may be null from here
		return $cobranca;
 	} // ends [static] fetch_cobranca_with_triple_contract_id_year_month()

  /*
    =================================
      End of Static Methods
    =================================
  */

  protected $table     = 'cobrancas';
  public $billingitems = null;

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

	protected $fillable = [
    'previous_bill_id',
		'monthrefdate', 'monthseqnumber', 'duedate',
    'contract_id',  'bankaccount_id',
    'value', 'numberpart', 'totalparts',
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
      ::where('imovel_id'  , $this->contract->imovel->id)
      ->where('ano'        , $this->monthyeardateref->year)
      ->first();

    if ($iptutabela != null && $iptutabela->ano_quitado == true) {
      return true;
    }

    return false;
  } // ends is_iptu_ano_quitado()

  public function get_total_value() {
    $total_value = 0;
    foreach ($this->billingitems()->get() as $billingitem) {
      $total_value += $billingitem->value;
    }
    return $total_value;
  }

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
    $copied_cobranca->obsinfo = $this->obsinfo;

    $copied_cobranca->contract_id = $this->contract_id;
    $copied_cobranca->bankaccount_id = $this->bankaccount_id;
    $copied_cobranca->totalparts = $this->totalparts;
    // $copied_cobranca->closed = $this->closed;

    return $copied_cobranca;
  }

  public function getImovelAttribute() {
    if ($this->contract == null) {
      return null;
    }
    // null may be returned
    return $this->contract->imovel;
  }

  public function get_users() {
    if ($this->contract != null) {
      // the returning users()->get() is a Collection
      return $this->contract->users()->get();
    }
    return [];
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

  public function search_monthly_focused_previous_bill(
      $targetmonthrefdate,
      $firstevermonthrefdate=null
    ) {
    /*

      This method is recursive.
      In a nutshell, it searches 'backward', as soon as
        a previous bill is found, that one is returned.
    */

    // Protect against infinite recursion
    if (self::count()==0) {
      return null;
    }

    if ($firstevermonthrefdate==null) {
      // At this point, at least ONE RECORD exists, for, above, count() is >0
      $firsteverbill = self
        ::where('contract_id', $this->contract_id)
        ->order_by('monthrefdate', 'asc')
        ->order_by('monthseqnumber', 'asc')
        ->first();
      $firstevermonthrefdate = $firsteverbill->monthrefdate;
    }
    $previousmonthsloopingbill = self
      ::where('contract_id', $this->contract_id)
      ->where('monthrefdate', $targetmonthrefdate)
      ->order_by('monthseqnumber', 'desc')
      ->first();
    if ($previousmonthsloopingbill != null) {
      // Here ends recursion
      return $previousmonthsloopingbill;
    }
    if ($targetmonthrefdate > $firstevermonthrefdate) {
      $targetmonthrefdate = $targetmonthrefdate->addMonths(-1);
      // Nothing found, recurse one month less
      return $this->search_monthly_focused_previous_bill(
        $targetmonthrefdate,
        $firstevermonthrefdate
      );
    }
    // Anyways... (this point may never be logically reached...)
    return null;
  }

  public function get_previous_bill() {
    /*
      If there is no previous bill, this method returns null

      Search is done by TWO steps:
        1) monthseqnumber is sounded, then
        2) monthrefdate is sounded
    */
    $firsteverbill = self
      ::where('contract_id', $this->contract_id)
      ->order_by('monthrefdate', 'asc')
      ->order_by('monthseqnumber', 'asc')
      ->first();
    if ($firsteverbill == $this) {
      return null;
    }
    $previousbill = null;
    if ($this->monthseqnumber > 1) {
      $previousbill = self
        ::where('contract_id', $this->contract_id)
        ->where('monthrefdate', $this->monthrefdate)
        ->where('monthseqnumber', '<',  $this->monthseqnumber)
        ::order_by('monthrefdate', 'desc')
        ->first();
    }
    if ($previousbill != null) {
      return $previousbill;
    }
    $targetmonthrefdate = $this->monthrefdate->copy()->addMonths(-1);
    $firstevermonthrefdate = $firsteverbill->monthrefdate;
    return $this->search_monthly_focused_previous_bill(
      $targetmonthrefdate,
      $firsteverbill
    );
  }

  public function get_routeparams_toformerbill_asarray() {
    /*
      Params are:
        year, month, imovel_char4id & monthseqnumber
    */
    $previous_bill = $this->get_previous_bill();
    if ($previous_bill == null) {
      return null;
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

  public function generate_billingitem_from_contract(
      $cobrancatipo,
      //$value,
      $numberpart = null,
      $totalparts = null
    ) {

    if ($this->contract == null) {
      return null;
    } // ->get_value_in_cobrancatipo($cobrancatipo)

    $cobrancatipos = $this->contract->get_auto_billing_types();
    $noninserted_cobrancatipos = array();
    $has_been_inserted = false;
    foreach ($cobrancatipos as $cobrancatipo) {
      $has_been_inserted = false;
      $char4id = $cobrancatipo->char4id;
      $cobrancatipo_id = CobrancaTipo::get_cobrancatipo_by_char4id($char4id);
      switch ($char4id) {
        case CobrancaTipo::K_4CHAR_ALUG:
          $value = $this->contract->get_value_of_cobrancatipo(CobrancaTipo::K_4CHAR_ALUG);
          $numberpart = 1;
          $totalparts = 1;
          $carried_cobranca_id = null; // use only for CARR (ie carried debts)
          $billingitem = fetch_or_create_billingitem_with(
                          $this->id, // cobranca_id
                          $cobrancatipo_id,
                          $value,
                          $this->monthrefdate,
                          $numberpart,
                          $totalparts,
                          $carried_cobranca_id
                        );
          $this->billingitems->add($billingitem);
          $has_been_inserted = true;
          break;

        case CobrancaTipo::K_4CHAR_COND:
          $value = $this->contract->imovel->get_value_of_condominium($this->monthrefdate);
          $numberpart = 1;
          $totalparts = 1;
          $carried_cobranca_id = null; // use only for CARR (ie carried debts)
          $billingitem = fetch_or_create_billingitem_with(
                          $this->id, // cobranca_id
                          $cobrancatipo_id,
                          $value,
                          $this->monthrefdate,
                          $numberpart,
                          $totalparts,
                          $carried_cobranca_id
                        );
          $this->billingitems->add($billingitem);
          $has_been_inserted = true;
          break;

        case CobrancaTipo::K_4CHAR_IPTU:
          $iptu_array = $this->contract->imovel->get_value_of_iptu_array($this->monthrefdate);
          if ($iptu_array->no_parcels_at_this_moment) {
            break;
          }
          $value = $iptu_array->value;
          $numberpart = $iptu_array->numberpart;
          $totalparts = $iptu_array->totalparts;
          $carried_cobranca_id = null; // use only for CARR (ie carried debts)
          $billingitem = fetch_or_create_billingitem_with(
                          $this->id, // cobranca_id
                          $cobrancatipo_id,
                          $value,
                          $this->monthrefdate,
                          $numberpart,
                          $totalparts,
                          $carried_cobranca_id
                        );
          $this->billingitems->add($billingitem);
          $has_been_inserted = true;
          break;

        case CobrancaTipo::K_4CHAR_CRED:
          $value = -$this->cred_account;
          $numberpart = 1;
          $totalparts = 1;
          $carried_cobranca_id = null; // use only for CARR (ie carried debts)
          $billingitem = fetch_or_create_billingitem_with(
                          $this->id, // cobranca_id
                          $cobrancatipo_id,
                          $value,
                          $this->monthrefdate,
                          $numberpart,
                          $totalparts,
                          $carried_cobranca_id
                        );
          $this->billingitems->add($billingitem);
          $has_been_inserted = true;
          break;

        case CobrancaTipo::K_4CHAR_CARR:
          $value = $this->debts_from_previous_bills;
          $numberpart = 1;
          $totalparts = 1;
          $carried_cobranca_id = $this->carried_cobranca_id; // use only for CARR (ie carried debts)
          $billingitem = fetch_or_create_billingitem_with(
                          $this->id, // cobranca_id
                          $cobrancatipo_id,
                          $value,
                          $this->monthrefdate,
                          $numberpart,
                          $totalparts,
                          $carried_cobranca_id
                        );
          $this->billingitems->add($billingitem);
          $has_been_inserted = true;
          break;

        case CobrancaTipo::K_4CHAR_FUNE:
          // Funesbom is yearly / annually
          $value = $this->imovel->get_funesbom_ifitisitsmonth();
          if ($value == null) {
            break;
          }
          $numberpart = 1;
          $totalparts = 1;
          $carried_cobranca_id = null; // use only for CARR (ie carried debts)
          $billingitem = fetch_or_create_billingitem_with(
                          $this->id, // cobranca_id
                          $cobrancatipo_id,
                          $value,
                          $this->monthrefdate,
                          $numberpart,
                          $totalparts,
                          $carried_cobranca_id
                        );
          $this->billingitems->add($billingitem);
          $has_been_inserted = true;
          break;


        default:
          # code...
          break;
      } // ends switch
      if (!$has_been_inserted) {
        $noninserted_cobrancatipos[] = $cobrancatipo;
      }
    } // ends foreach


    // Finally, check default monthyeardateref if CobrancaTipo is D or B
    if ($ref_type == CobrancaTipo::K_REF_TYPE_IS_DATE ||
        $ref_type == CobrancaTipo::K_REF_TYPE_IS_BOTH_DATE_N_PARCEL) {
      if ($monthyeardateref == null) {
        $monthyeardateref = $this
          ->get_conventioned_monthrefdate_if_it_is_null($monthyeardateref);
      } // ends inner if
    } // ends outer if

    if ($ref_type == CobrancaTipo::K_REF_TYPE_IS_DATE) {
      // Nullify these two if ref_type is D
      $n_cota_ref      = null;
      $total_cotas_ref = null;
    }

    if ($ref_type == CobrancaTipo::K_REF_TYPE_IS_PARCEL) {
      // Nullify this one two if ref_type is D
      $monthyeardateref = null;
    } else {
      // Zero time fields to guarantee date-equility will work
      $monthyeardateref->setTime(0,0,0);
    }

    // Query for Billing Item existence
    $billingitem = null;
    switch ($ref_type) {
      case CobrancaTipo::K_REF_TYPE_IS_BOTH_DATE_N_PARCEL: {
        // break;  // let it fall to the next
      } // ends case
      case CobrancaTipo::K_REF_TYPE_IS_DATE: {
        $billingitem = $this->billingitems
          ->where('cobrancatipo_id',  $cobrancatipo->id)
          ->where('value',    $value)
          ->where('monthrefdate', $monthyeardateref)
          ->where('numberpart',   $freq_used_type)
          ->first();
        break;
      } // ends case
      case CobrancaTipo::K_REF_TYPE_IS_PARCEL: {
        $billingitem = $this->billingitems
          ->where('cobrancatipo_id', $cobrancatipo->id)
          ->where('value', $value)
          ->where('monthrefdate', $monthyeardateref)
          ->where('numberpart', $numberpart)
          ->where('totalparts', $totalparts)
          ->where('reftype', $ref_type)
          ->first();
        break;
      } // ends case
    } // ends switch ($ref_type)

    if ($billingitem != null) {
      return $billingitem;
    }

    // create a new one
    $billingitem                   = new BillingItem;
    $billingitem->cobrancatipo_id  = $cobrancatipo->id;
    $billingitem->charged_value    = $value;
    $billingitem->ref_type         = $ref_type;
    $billingitem->freq_used_type   = $freq_used_type;
    $billingitem->monthyeardateref = $monthyeardateref;
    $billingitem->n_cota_ref       = $n_cota_ref;
    $billingitem->total_cotas_ref  = $total_cotas_ref;
    $this->billingitems()->save($billingitem);
    $billingitem->save();

    return $billingitem;
  } // ends createIfNeededBillingItemFor()


  public function createIfNeededBillingItemForCredito(
      $value,
      $reftype = null,
      $monthrefdate = null,
      $numberpart = null,
      $totalparts = null
    ) {
    // Fetch crédito's $cobrancatipo :: K_4CHAR_CRED
    $cobrancatipo = CobrancaTipo
      ::get_cobrancatipo_with_its_4char_repr(CobrancaTipo::K_4CHAR_CRED);
    return $this->createIfNeededBillingItemFor(
      $cobrancatipo,
      $value,
      $reftype,
      $monthrefdate,
      $numberpart,
      $totalparts
    );
  }

  public function createIfNeededBillingItemForMora(
      $cobranca,
      $cobrancatipo,
      $monthrefdate,
      $value,
      $numberpart = null,
      $totalparts = null
    ) {
    // Fetch mora's $cobrancatipo :: K_4CHAR_MORA
    $cobrancatipo = CobrancaTipo
      ::get_cobrancatipo_with_its_4char_repr(CobrancaTipo::K_4CHAR_MORA);
    return $this->createIfNeededBillingItemFor(
      $cobranca,
      $cobrancatipo,
      $monthrefdate,
      $value,
      $numberpart,
      $totalparts
    );
  }

  public function createIfNeededBillingItemForMoraOrCreditoMonthlyRef(
      $valor_negativo_mora_positivo_credito,
      $monthyeardateref=null
    ) {
    // First method's parameter cannot be null. Raise exception if it is
    if ($valor_negativo_mora_positivo_credito==null) {
      throw new Exception("valor_negativo_mora_positivo_credito==null in createIfNeededBillingItemForMoraOrCreditoMonthlyRef()", 1);
    }
    $ref_type        = BillingItem::K_REF_TYPE_IS_DATE;
    $freq_used_type  = BillingItem::K_FREQ_USED_IS_MONTHLY;
    $n_cota_ref      = null;
    $total_cotas_ref = null;
    // $monthyeardateref = $this->get_conventioned_monthyeardateref_if_it_is_null($monthyeardateref);
    if ($valor_negativo_mora_positivo_credito == 0) {
      return null;
    }
    if ($valor_negativo_mora_positivo_credito < 0) {
      // take |modulus|, ie, a positive value will be the 'mora'
      $value = $valor_negativo_mora_positivo_credito * (-1);
      return $this->createIfNeededBillingItemForMora(
        $value,
        $ref_type,
        $freq_used_type,
        $monthyeardateref,
        $n_cota_ref,
        $total_cotas_ref
      );
    }
    // Now, here, $value > 0
    // 'changing' variable names for better expressing
    $value = $valor_negativo_mora_positivo_credito;
    return $this->createIfNeededBillingItemForCredito(
      $value,
      $ref_type,
      $freq_used_type,
      $monthyeardateref,
      $n_cota_ref,
      $total_cotas_ref
    );
  }

  public function find_n_days_until_duedate() {
    $today = Carbon::today();
    $n_days_until_duedate = $this->duedate->diffInDays($today);
    return $n_days_until_duedate;
  }

  public function monthyeardateref_or_its_convention_if_it_is_null(
      $monthyeardateref
    ) {
    if ($monthyeardateref == null) {
      return DateFunctions::find_conventional_monthyeardateref_with_date_n_dueday(
        null, // $p_monthyeardateref
        $this->contract->pay_day_when_monthly
      );
    }
    return $monthyeardateref;
  } // ends get_conventioned_monthyeardateref_if_it_is_null()

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
  } // public function __toString()


  public function bankaccount() {
    return $this->belongsTo('App\Models\Finance\BankAccount');
  }
  public function contract() {
    return $this->belongsTo('App\Models\Immeubles\Contract');
  }

  public function get_total_items() {
    return count($this->billingitems);
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

} // ends class Cobranca extends Model
