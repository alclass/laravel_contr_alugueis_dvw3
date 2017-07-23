<?php
namespace App\Models\Billing;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use App\Models\Utils\FinancialFunctions;

class MoraDebito extends Model {
    //
  protected $table = 'moradebitos';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
    'monthyeardateref',  'countinterestfromdate',
    'originaldebtvalue', 'lineinfo',
  ];


  public function calculate_mora_updated_value_within_daterange(
      $initial_montant,
      $monthyeardateref_ini,
      $monthyeardateref_fim,
      $n_days_in_monthyeardateref_fim
    ) {


    // TO-DO this method!

    return FinancialFunctions
      ::calc_fmontant_from_imontant_plus_interest_array_plus_border_proportions(
        $initial_montant, // ie, not in percent
        $interest_array, // eg. [[0]=>0.04, [1]=>0.015, ...]
        $first_interest_proportion, // eg. 14 days / 31 days = 0.45161290322581
        $last_interest_proportion // eg. 15 days / 30 days = 0.5
      );

  } // calculate_mora_updated_value_within_daterange

  public function contract() {
    return $this->belongsTo('App\Models\Immeubles\Contract');
  }

} // ends class MoraDebito extends Model
