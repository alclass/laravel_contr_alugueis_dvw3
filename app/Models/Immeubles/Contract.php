<?php
namespace App\Models\Immeubles;

// To import class Contract elsewhere in the Laravel App
// use App\Models\Immeubles\Contract

use App\Models\Billing\Cobranca;
use App\Models\Utils\DateFunctions;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Contract extends Model {

  const K_DEFAULT_N_ULTIMAS_COBRANCAS = 3;

  protected $table = 'contracts';

  protected $dates = [
    'signing_date',
    'start_date',
  ];

  private $today            = null;
  private $monthyeardateref = null;
  private $monthly_duedate  = null;
  // private $cobranca_to_save = null;

  protected $fillable = [
		'initial_rent_value', 'current_rent_value', 'indice_reajuste_4char',
    'pay_day_when_monthly',
    'percentual_multa', 'percentual_juros', 'aplicar_corr_monet',
    'signing_date', 'start_date', 'duration_in_months', 'n_days_aditional',
    'repassar_condominio', 'repassar_iptu',
    'is_active',
	];

  public function get_end_date() {
    $end_date = $this->start_date->copy()->addMonths($this->duration_in_months);
    if ($this->n_days_aditional > 0) {
      $end_date->addDays($this->n_days_aditional);
    }
    // Now take off 1 day to ajust the new contract to the same yearly anniversary date
    $end_date->addDays(-1);
    return $end_date;
  }

  public function find_rent_value_next_reajust_date($from_date = null) {
    /*
      $from_date = null is a convention (in the class) for it to be today's date
    */
    $today = Carbon::today();
    $from_date = ($from_date == null ? $today : $from_date);
    return DateFunctions::find_next_anniversary_date_with_triple_start_inbetween_end(
      $this->start_date,
      $this->get_end_date(),
      $from_date //$inbetween_date
    );
  }

  public function calculate_final_montant_with_initial_montant_within_date_range(
      $monthly_interest_rate,
      $initial_montant,
      $monthyeardateref_ini,
      $monthyeardateref_fim,
      $first_month_n_days = null,
      $last_month_n_days = null
    ) {
      /*
        This instance method just wraps up $this->indice_reajuste_4char
          into the parameters and then issues static method:
          => CorrMonet::calculate_final_montant_with_initial_montant_within_date_range()
      */

    return CorrMonet::calculate_final_montant_with_initial_montant_within_date_range(
      $this->indice_reajuste_4char, // $indice_reajuste_4char,
      $monthly_interest_rate,
      $initial_montant,
      $monthyeardateref_ini,
      $monthyeardateref_fim,
      $first_month_n_days = null,
      $last_month_n_days = null
    );
  }

  public function todays_diff_to_rent_value_next_reajust_date($from_date = null) {
    /*
      $from_date = null is a convention (in the class) for it to be today's date
    */
    $today = Carbon::today();
    $from_date = ($from_date == null ? $today : $from_date);
    $next_reajust_date = $this->find_rent_value_next_reajust_date($from_date);
    $diff_date_in_months = $from_date->diffInMonths($next_reajust_date);
    $str_months_n_days_till_reajust = "";
    if ($diff_date_in_months > 0) {
      $str_months_till_reajust = "$diff_date_in_months mês";
      if ($diff_date_in_months > 1) {
        $str_months_till_reajust = "$diff_date_in_months meses";
      } // ends inner if
    } // ends outer if
    $from_date_moved_ahead = $from_date->copy()->addMonths($diff_date_in_months);
    $diff_date_in_days     = $from_date_moved_ahead->diffInDays($next_reajust_date);
    if ($diff_date_in_days > 0) {
      $str_days_till_reajust = "$diff_date_in_days dias";
      if ($diff_date_in_days > 1) {
        $str_days_till_reajust = "$diff_date_in_days dias";
      } // ends inner if
    } // ends outer if
    $str_months_n_days_till_reajust = $str_months_till_reajust . ' e ' . $str_days_till_reajust;
    return $str_months_n_days_till_reajust;
  }

  public function get_ultimas_n_cobrancas_pagas($n_lasts=null) {

    if ($n_lasts == null) {
      $n_lasts = self::K_DEFAULT_N_ULTIMAS_COBRANCAS;
    }
    $ultimas_n_cobrancas_pagas = Cobranca
      ::where('contract_id', $contract->id)
      ->where('has_been_paid', true)
      ->orderBy('monthyeardateref', 'desc')
      ->take($n_lasts)->get();

    return $ultimas_n_cobrancas_pagas;
  }

  public function get_ultimas_n_cobrancas_relative_to_ref($p_monthyeardateref=null, $n_lasts=null) {

    // return $this->cobrancas()->orderBy('monthyeardateref', 'desc')->take($n_lasts)->get();

    if ($p_monthyeardateref == null) {
      $p_monthyeardateref = DateFunctions::find_rent_monthyeardateref_under_convention();
    }
    if ($n_lasts == null) {
      $n_lasts = self::K_DEFAULT_N_ULTIMAS_COBRANCAS;
    }
    $cobrancas_passadas = Cobranca
      ::where('contract_id', $this->id)
      ->where('monthyeardateref', '<',  $p_monthyeardateref)
      ->orderBy('monthyeardateref', 'desc')
      ->take($n_lasts)->get();

    return $cobrancas_passadas;
  } // ends get_ultimas_n_cobrancas()

  public function get_cobranca_by_monthyeardateref($p_monthyeardateref=null) {

    if ($p_monthyeardateref == null) {
      $p_monthyeardateref = DateFunctions::find_rent_monthyeardateref_under_convention();
    }
    $cobranca = Cobranca
      ::where('contract_id', $this->id)
      ->where('monthyeardateref', '=',  $p_monthyeardateref)
      ->first();

    return $cobranca;
  } // ends get_ultimas_n_cobrancas()

  public function get_cobranca_atual() {
    return $this->get_cobranca_by_monthyeardateref();
  }

  public function imovel() {
    return $this->belongsTo('App\Models\Immeubles\Imovel');
  }

  public function bankaccount() {
    return $this->belongsTo('App\Models\Finance\BankAccount');
  }

  public function cobrancas() {
    return $this->hasMany('App\Models\Billing\Cobranca');
  }

  public function users() {
    return $this->belongsToMany('App\User');
  }

}
