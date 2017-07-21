<?php
namespace App\Models\Immeubles;

// use Carbon\Carbon;
use App\Models\Billing\Cobranca;
use App\Models\Utils\DateFunctions;
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
		'initial_rent_value', 'current_rent_value', 'indicador_reajuste',
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
    return $end_date;
  }

  public function find_rent_value_next_reajust_date($from_date = null) {
    return DateFunctions::find_next_anniversary_date_with_triple_start_inbetween_end(
      $this->start_date,
      $from_date, //$inbetween_date,
      $this->get_end_date()
    );
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
