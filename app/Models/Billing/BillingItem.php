<?php
namespace App\Models\Billing;

// use App\Models\Billing\Payment;
// use App\Models\Utils\DateFunctions;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class BillingItem extends Model {

  const K_REF_TYPE_IS_DATE   = 'D';
  const K_REF_TYPE_IS_PARCEL = 'P';
  const K_REF_TYPE_IS_BOTH_DATE_N_PARCEL = 'B';

  const K_FREQ_USED_IS_WEEKLY  = 'W';
  const K_FREQ_USED_IS_MONTHLY = 'D';
  const K_FREQ_USED_IS_YEARLY  = 'Y';

  protected $table = 'billingitems';

 protected $dates = [
   'monthyeardateref',
   //'created_at',
   //'updated_at',
 ];

 /**
  * The attributes that are mass assignable.
  *
  * @var array
  */
	protected $fillable = [
		'brief_description', 'charged_value', 'ref_type', 'freq_used_ref',
    'monthyeardateref', 'n_cota_ref', 'total_cotas_ref',
    'was_original_value_modified', 'brief_description_for_modifier_if_any',
    'original_value_if_needed', 'percent_in_modifying_if_any',
    'money_amount_in_modifying_if_any',
    'obs',
	];


  public function generate_ref_repr_for_cota_column() {
    if ($this->ref_type == self::K_REF_TYPE_IS_DATE) {
      return "1";
    }
    $outstr = "$this->n_cota_ref/$this->total_cotas_ref";
    return $outstr;
  }

  public function copy() {
    /*
    'brief_description', 'charged_value', 'ref_type', 'freq_used_ref',
    'monthyeardateref', 'n_cota_ref', 'total_cotas_ref',
    'was_original_value_modified', 'brief_description_for_modifier_if_any',
    'original_value_if_needed', 'percent_in_modifying_if_any',
    'money_amount_in_modifying_if_any',
    'obs',
    */
    $bi = new BillingItem;
    $bi->brief_description = $this->brief_description;
    $bi->charged_value = $this->charged_value;
    $bi->ref_type = $this->ref_type;
    $bi->freq_used_ref = $this->freq_used_ref;
    if ($this->monthyeardateref != null) {
      $bi->monthyeardateref = $this->monthyeardateref->copy();
    }
    $bi->n_cota_ref = $this->n_cota_ref;
    $bi->total_cotas_ref = $this->total_cotas_ref;
    $bi->was_original_value_modified = $this->was_original_value_modified;
    $bi->brief_description_for_modifier_if_any = $this->brief_description_for_modifier_if_any;
    $bi->original_value_if_needed = $this->original_value_if_needed;
    $bi->percent_in_modifying_if_any = $this->percent_in_modifying_if_any;
    $bi->money_amount_in_modifying_if_any = $this->money_amount_in_modifying_if_any;

  } // ends copy()

  public function toString() {
    /*
        toString() for BillingItem
    */

    $outstr  = '[BillingItem object]' . "\n";
    $outstr .= '====================' . "\n";
    $outstr .= 'id                = ' . $this->id                . "\n";
    $outstr .= 'brief_description = ' . $this->brief_description . "\n";
    $outstr .= 'date ref          = ' . $this->monthyeardateref  . "\n";
    $outstr .= 'charged_value     = ' . $this->charged_value     . "\n";
    $outstr .= 'ref type          = ' . $this->ref_type          . "\n";
    $outstr .= 'freq_used_type    = ' . $this->freq_used_type    . "\n";
    $outstr .= '====================' . "\n";

    return $outstr;

  } // ends toString()

  public function cobranca() {
    $this->belongsTo('App\Models\Billing\Cobranca');
  }


} // ends class BillingItem extends Model
