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
  public static function fetch_cobranca_with_triple_contract_id_year_month(
      $contract_id,
      $year = null,
      $month = null
    )	{
    if ($contract_id == null) {
      return null;
    }
		$monthyeardateref = DateFunctions::make_n_get_monthyeardateref_with_year_n_month($year, $month);
		$cobranca = Cobranca
			::where('contract_id', $contract_id)
			->where('monthyeardateref', $monthyeardateref)
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
   'monthyeardateref',
   'duedate',
   //'created_at',
   //'updated_at',
 ];

	protected $fillable = [
		'monthyeardateref', 'n_seq_from_dateref', 'duedate',
    'discount',  'price_increase_if_any',
    'lineinfo_discount_or_increase', 'tot_adic_em_tribs',
    'n_parcelas', 'are_parcels_monthly', 'parcel_n_days_interval',
    'has_been_paid',
	];

  // contract_id is in DB Schema, but connected below with a belongsTo() method


  /*
    These (5) instance methods are 'brigde-methods' to static methods in CobrancaTipo:

      get_cobrancatipo_with_its_4charrepr()
      get_cobrancatipo_via_its_4charrepr_sqllikeword()
      get_exact_4charrepr_via_sqllikeword()
      get_4charrepr_via_cobrancatipo_id()
      get_collection_cobrancatipos()

  */

  public function get_cobrancatipo_with_its_4charrepr($char4id) {
    return CobrancaTipo::get_cobrancatipo_with_its_4charrepr($char4id);
  }

  public function get_cobrancatipo_via_its_4charrepr_sqllikeword($sqllikeword) {
    return CobrancaTipo::get_cobrancatipo_via_its_4charrepr_sqllikeword($sqllikeword);
  }

  public function get_exact_4charrepr_via_sqllikeword($sqllikeword) {
    return CobrancaTipo::get_exact_4charrepr_via_sqllikeword($sqllikeword);
  }

  public function get_4charrepr_via_cobrancatipo_id($cobrancatipo_id) {
    return CobrancaTipo::get_4charrepr_via_cobrancatipo_id($cobrancatipo_id);
  }

  public function get_collection_cobrancatipos() {
    return CobrancaTipo::all();
  }

  /*
    End of the (5) instance methods that are 'brigde-methods' to static methods in CobrancaTipo:
  */

  public function is_iptu_ano_quitado() {
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
      $total_value += $billingitem->charged_value;
    }
    return $total_value;
  }

  public function copy_without_billingitems() {
    /*
      This method copies an instance partially.
      Basically, the id will not be copied, for it belongs to the instance in place, used as mold
      The billing items, if any, will not be copied, for it's the purpose of the method here.
    */
    $copied_cobranca = new Cobranca;
    // ->id stays empty, for it's established by the Eloquent engine so to say
    if ($this->monthyeardateref != null) {
      $copied_cobranca->monthyeardateref = $this->monthyeardateref->copy();
    }
    $copied_cobranca->n_seq_from_dateref = $this->n_seq_from_dateref;
    if ($this->duedate != null) {
      $copied_cobranca->duedate = $this->duedate->copy();
    }
    $copied_cobranca->discount = $this->discount;

    $copied_cobranca->price_increase_if_any = $this->price_increase_if_any;
    $copied_cobranca->lineinfo_discount_or_increase = $this->lineinfo_discount_or_increase;
 	  $copied_cobranca->tot_adic_em_tribs = $this->tot_adic_em_tribs;

    $copied_cobranca->contract_id = $this->contract_id;
    $copied_cobranca->bankaccount_id = $this->bankaccount_id;
    $copied_cobranca->n_parcelas = $this->n_parcelas;
    $copied_cobranca->are_parcels_monthly = $this->are_parcels_monthly;
    $copied_cobranca->parcel_n_days_interval = $this->parcel_n_days_interval;
    $copied_cobranca->has_been_paid  = $this->has_been_paid;

    return $copied_cobranca;

  }

  public function get_total_items() {
    return count($this->billingitems);
  }
  public function billingitems() {
    return $this->hasMany('App\Models\Billing\BillingItem');
  }
  public function bankaccount() {
    return $this->belongsTo('App\Models\Finance\BankAccount');
  }
  public function contract() {
    return $this->belongsTo('App\Models\Immeubles\Contract');
  }

  public function get_imovel() {
    if ($this->contract == null) {
      return null;
    }
    $imovel = $this->contract->imovel;
    return $imovel;
  }

  public function get_users() {
    if ($this->contract != null) {
      // the returning users()->get() is a Collection
      return $this->contract->users()->get();
    }
    return null;
  }

  public function createOrFindNextMonthCobranca($n_seq_from_dateref=1) {
    $next_monthyeardateref = $this->monthyeardateref->copy()->addMonths(1);
    if ($this->contract == null) {
      if ($this->contract_id == null) {
        throw new Exception("contract is null when calling createOrFindNextMonthCobranca())", 1);
      }
      // Try to fetch contract from contract_id
      $this->contract = Contract::find($this->contract_id);
      if ($this->contract == null) {
        throw new Exception("contract_id was not db-found when calling createOrFindNextMonthCobranca())", 1);
      }
    }
    return CobrancaGerador
      ::createOrRetrieveCobrancaWithTripleContractRefSeq(
        $this->contract,
        $next_monthyeardateref,
        $n_seq_from_dateref
      );
  }

  public function extract_month_from_monthyeardateref() {
    if ($this->monthyeardateref == null) {
      return 's/n';
    }
    return $this->monthyeardateref->month;
  }

  public function extract_year_from_monthyeardateref() {
    if ($this->monthyeardateref == null) {
      return 's/n';
    }
    return $this->monthyeardateref->year;
  }

  public function createIfNeededBillingItemFor(
      $cobrancatipo,
      $value,
      $ref_type = null,
      $freq_used_type = null,
      $monthyeardateref=null,
      $n_cota_ref = null,
      $total_cotas_ref = null
    ) {

    // Defaults to ref_type, freq_used_type etc
    // Default to ref_type
    if ($ref_type == null) {
      $ref_type = CobrancaTipo::K_REF_TYPE_IS_DATE;
    }
    // Default to freq_used_type
    if ($freq_used_type == null) {
      $freq_used_type = CobrancaTipo::K_FREQ_USED_IS_MONTHLY;
    }

    if ($ref_type == CobrancaTipo::K_REF_TYPE_IS_PARCEL ||
        $ref_type == CobrancaTipo::K_REF_TYPE_IS_BOTH_DATE_N_PARCEL) {
      if ($n_cota_ref == null) {
        $n_cota_ref = 1;
      } // ends inner if
      if ($total_cotas_ref == null) {
        $total_cotas_ref = 1;
      } // ends inner if
    } // ends outer if

    // Finally, check default monthyeardateref if CobrancaTipo is D or B
    if ($ref_type == CobrancaTipo::K_REF_TYPE_IS_DATE ||
        $ref_type == CobrancaTipo::K_REF_TYPE_IS_BOTH_DATE_N_PARCEL) {
      if ($monthyeardateref == null) {
        $monthyeardateref = $this
          ->get_conventioned_monthyeardateref_if_it_is_null($monthyeardateref);
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
          ->where('charged_value',    $value)
          ->where('monthyeardateref', $monthyeardateref)
          ->where('ref_type',         $ref_type)
          ->where('freq_used_type',   $freq_used_type)
          ->first();
        break;
      } // ends case
      case CobrancaTipo::K_REF_TYPE_IS_PARCEL: {
        $billingitem = $this->billingitems
          ->where('cobrancatipo_id',  $cobrancatipo->id)
          ->where('charged_value',    $value)
          ->where('n_cota_ref',       $n_cota_ref)
          ->where('total_cotas_ref',  $n_cota_ref)
          ->where('ref_type',         $ref_type)
          ->where('freq_used_type',   $freq_used_type)
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
      $ref_type = null,
      $freq_used_type = null,
      $monthyeardateref = null,
      $n_cota_ref = null,
      $total_cotas_ref = null
    ) {
    // Fetch crédito's $cobrancatipo :: K_4CHAR_CRED
    $cobrancatipo = CobrancaTipo
      ::get_cobrancatipo_with_its_4char_repr(CobrancaTipo::K_4CHAR_CRED);
    return $this->createIfNeededBillingItemFor(
      $cobrancatipo,
      $value,
      $ref_type,
      $freq_used_type,
      $monthyeardateref,
      $n_cota_ref,
      $total_cotas_ref
    );
  }

  public function createIfNeededBillingItemForMora(
      $value,
      $ref_type = null,
      $freq_used_type = null,
      $monthyeardateref=null,
      $n_cota_ref = null,
      $total_cotas_ref = null
      ) {
    // Fetch mora's $cobrancatipo :: K_4CHAR_MORA
    $cobrancatipo = CobrancaTipo
      ::get_cobrancatipo_with_its_4char_repr(CobrancaTipo::K_4CHAR_MORA);
    return $this->createIfNeededBillingItemFor(
      $cobrancatipo,
      $value,
      $ref_type,
      $freq_used_type,
      $monthyeardateref,
      $n_cota_ref,
      $total_cotas_ref
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

} // ends class Cobranca extends Model
