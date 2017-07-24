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

  public $copy_monthly_mora_fraction_index_array = null;

  private $updated_debt_value = null;
  public function get_updated_debt_value(){
    return $this->updated_debt_value;
  }

  public function update_debt_value(
      $initial_montant      = null,
      $monthyeardateref_ini = null,
      $monthyeardateref_fim = null,
      $n_days_in_monthyeardateref_ini = null,
      $n_days_in_monthyeardateref_fim = null
    ) {
    if ($initial_montant == null) {
      $initial_montant = $this->ini_debt_value;
    }
    if ($monthyeardateref_ini == null) {
      $monthyeardateref_ini = $this->monthyeardateref;
    }
    if ($monthyeardateref_fim == null) {
      $monthyeardateref_fim = DateFunctions
        ::find_conventional_monthyeardateref_with_date_n_dueday();
    }

    $contract_mora = new ContractMora($this->contract);

    $mora_details_assoc_array = $contract_mora->generate_mora_details();

    $corrmonet_indice4char = $this->contract->mora_indice4char;


    return number_format($this->updated_debt_value, 2);

  } // calculate_mora_updated_value_within_daterange

  public function contract() {
    return $this->belongsTo('App\Models\Immeubles\Contract');
  }

} // ends class MoraDebito extends Model
