<?php
namespace App\Models\Billing;

// use App\Models\Billing\Payment;
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

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */

 protected $dates = [
   'monthyeardateref',
   //'created_at',
   //'updated_at',
 ];

	protected $fillable = [
		'brief_description', 'charged_value', 'ref_type', 'freq_used_ref',
    'monthyeardateref', 'n_cota_ref', 'total_cotas_ref',
    'was_original_value_modified', 'brief_description_for_modifier_if_any',
    'original_value_if_needed', 'percent_in_modifying_if_any',
    'money_amount_in_modifying_if_any',
    'obs',
	];

  public function format_monthyeardateref_as_m_slash_y() {
    return DateFunctions::format_monthyeardateref_as_m_slash_y($this->monthyeardateref);
  }

  public function cobranca() {
    $this->belongsTo('App\Models\Billing\Cobranca');
  }

} // ends class BillingItem extends Model
