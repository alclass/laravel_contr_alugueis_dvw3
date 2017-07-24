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

  public function contract() {
    return $this->belongsTo('App\Models\Immeubles\Contract');
  }

} // ends class MoraDebito extends Model
