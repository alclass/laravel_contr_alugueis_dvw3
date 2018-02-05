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

  public $month_n_fractionindex_tuplelist;
  public $fractionindex_list;

  protected $table = 'moradebitos';

  protected $dates = [
    'monthrefdate', 'ini_debt_date', 'changed_debt_date',
  ];

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
    'monthrefdate',  'is_open',
    'ini_debt_date',     'ini_debt_value',
    'changed_debt_date', 'changed_debt_value',
    'mora_rules_id', // null if rules are those of contract's
    'lineinfo', 'history',
  ];

  public function set_month_n_fractionindex_tuplelist($month_n_fractionindex_tuplelist) {
    $this->month_n_fractionindex_tuplelist = $month_n_fractionindex_tuplelist;
  }

  public function run_time_correction_of_ini_debt_value(
      $end_date = null,
      $corrmonet4charid = null
    ) {
    if ($this->ini_debt_date == null) {
      return null;
    }
    $end_date = ( $end_date != null ? $end_date : Carbon::today() );
    //-----------------------------------------------
    // Update right here both ->changed_debt_value & ->changed_debt_date
    // If context is right, these will change ahead to an updated value and date
    $this->changed_debt_value = $this->ini_debt_value;
    $this->changed_debt_date  = $end_date;
    //-----------------------------------------------
    $month_n_fractionindex_tuplelist = CorrMonet
      ::get_month_n_fractionindex_tuplelist_w_char4indic_n_daterange(
        $corrmonet4charid,
        $this->ini_debt_date,
        $end_date
      );

    $this->set_month_n_fractionindex_tuplelist($month_n_fractionindex_tuplelist);

    $fractionindex_list = array();
    // Extract the fractions from the tuples
    foreach ($month_n_fractionindex_tuplelist as $month_n_fractionindex_tuple) {
      $fractionindex_list[] = $month_n_fractionindex_tuple[1];
    }
    if ($this->contract->apply_juros_fixos_am) {
      foreach ($fractionindex_list as $i=>$value) {
        $added_fraction = $fractionindex_list[$i] + $this->contract->get_juros_fixos_am_in_fraction();
        $fractionindex_list[$i] = $added_fraction;
      }
    }
    if ($this->contract->apply_multa_incid_mora) {
      $added_fraction = $fractionindex_list[0] + $this->contract->get_multa_incid_mora_in_fraction();
      $fractionindex_list[0] = $added_fraction;
    }
    $this->fractionindex_list = $fractionindex_list;
    $corrected_debt_value = FinancialFunctions
      ::calc_fmontant_from_imontant_n_interest_array(
        $this->ini_debt_value,
        $fractionindex_list
      );
    if ($corrected_debt_value > $this->ini_debt_value) {
      $this->changed_debt_value = $corrected_debt_value;
      $this->changed_debt_date  = $end_date;
    } // if not, the assigning above would have equalled the two to the ini value & date
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

  public function get_explanation_lines() {
    $lines = [];
    $line = '';
    $value = $this->ini_debt_value;
    //$line = "$formatstrdate : $value inc. $factor res. $newvalue";
    $lines[] = $line;
    foreach ($this->month_n_fractionindex_tuplelist as $i=>$month_n_fractionindex_tuple) {
      $factor = $this->fractionindex_list[$i];
      $formatstrdate = $month_n_fractionindex_tuple[0]->format('M/Y');
      $newvalue = $value * (1 + $factor);
      $line = "Ref. $formatstrdate: $value ajuste-índice $factor resultando em $newvalue";
      $lines[] = $line;
      $value = $newvalue;
    }
    return $lines;
  }

  public function contract() {
    return $this->belongsTo('App\Models\Immeubles\Contract');
  }

} // ends class MoraDebito extends Model
