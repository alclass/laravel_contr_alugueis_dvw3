<?php
namespace App\Models\Billing;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use App\Utils\InterestFunctions;

class MoraDebito extends Model {
    //
    protected $table = 'moradebitos';

  	/**
  	 * The attributes that are mass assignable.
  	 *
  	 * @var array
  	 */
  	protected $fillable = [
    'monthyeardateref',
    'countinterestfromdate',
    'originaldebtvalue',
    'lineinfo',
  ];


  public function update_intial_debt_to_date_with_rate($target_date, $interest_rate) {
    return InterestFunctions::update_debt_from_initial_date(
      $this->originaldebtvalue,
      $interest_rate,
      $this->monthyeardateref,
      $target_date
    );
  }

  public function contract() {
    return $this->belongsTo('App\Models\Immeubles\Contract');
  }

}
