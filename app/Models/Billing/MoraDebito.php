<?php
namespace App\Models\Billing;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use App\Models\Immeubles\ContractMora;
use App\Models\Finance\CorrMonet;
use App\Models\Utils\DateFunctions;
use App\Models\Utils\FinancialFunctions;

class MoraDebito extends Model {
    //
  protected $table = 'moradebitos';

  protected $dates = [
    'monthyeardateref', 'ini_debt_date', 'changed_debt_date',
  ];

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
    'monthyeardateref',  'is_open',
    'ini_debt_date',     'ini_debt_value',
    'changed_debt_date', 'changed_debt_value',
    'mora_rules_id', // null if rules are those of contract's
    'lineinfo', 'history',
  ];

  public function run_time_correction_of_ini_debt_value(
      $end_date = null,
      $corrmonet4charid = null
    ) {
    if ($this->ini_debt_date == null) {
      return null;
    }
    $end_date = ( $end_date != null ? $end_date : Carbon::today() );
    // Update right here both ->changed_debt_value & ->changed_debt_date
    $this->changed_debt_value = $this->ini_debt_value;
    $this->changed_debt_date  = $end_date;
    $corrmonet_array = CorrMonet
      //::calc_latervalue_from_inivalue_w_ini_end_dates_n_corrmonet4charid(
      //::calc_latervalue_from_inivalue_w_ini_end_dates_n_corrmonet4charid(
        $this->ini_debt_date,
        $end_date,
        $corrmonet4charid
      );
    if ($this->contratct->apply_juros_fixos_am) {
      foreach ($corrmonet_array as $i=>$value) {
        $added_fraction = $corrmonet_array[$i] + $this->contratct->get_juros_fixos_am_in_fraction();
        $corrmonet_array[$i] = $added_fraction;
      }
    }
    if ($this->contratct->apply_multa_incid_mora) {
      $added_fraction = $corrmonet_array[0] + $this->contratct->get_apply_multa_incid_mora_in_fraction();
      $corrmonet_array[0] = $added_fraction;
    }
    $corrected_debt_value = FinancialFunctions
      ::calc_fmontant_from_imontantnnnnnnnnnnnnnnnnn();
    //         $this->ini_debt_value,

    if ($corrected_debt_value > $this->ini_debt_value) {
      $this->changed_debt_value = $corrected_debt_value;
    } // if not, the assigning above would have equalled the two
  } // ends run_time_correction_of_ini_debt_value()

  public function get_time_correction_lineinfo() {
    $formatstr_ini_debt_date = 'n/a';
    if ($this->ini_debt_date != null) {
      $formatstr_ini_debt_date = $this->ini_debt_date->format('d/m/Y');
    }
    $formatstrvalue = number_format($this->ini_debt_value,2);
    $time_correction_lineinfo = "[inic.val.=$formatstrvalue; inic.dt.=$formatstr_ini_debt_date]";
    return $time_correction_lineinfo;
  }
  public function get_lineinfo_n_time_correction_lineinfo() {
    return $this->lineinfo . '::' . $this->get_time_correction_lineinfo();
  }

  public function contract() {
    return $this->belongsTo('App\Models\Immeubles\Contract');
  }

} // ends class MoraDebito extends Model
