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

  const K_PERC_MULTA_INCID_MORA       = 10;
  const K_PERC_JUROS_FIXOS_AM         = 1;
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
		'initial_rent_value', 'current_rent_value',
    'reajuste_indice4char', 'mora_indice4char',
    'pay_day_when_monthly',
    'apply_multa_incid_mora', 'perc_multa_incid_mora',
    'apply_juros_fixos_am',   'perc_juros_fixos_am',   'apply_corrmonet_am',
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
  } // ends get_end_date()

  public function get_multa_incid_mora_in_fraction() {
    if ($this->perc_multa_incid_mora == null) {
      if ($this->apply_multa_incid_mora == true) {
        return env('PERC_MULTA_INCID_MORA', self::K_PERC_MULTA_INCID_MORA);
      } else {
        return null;
      } // ends inner if
    } // ends outer if
    return $this->perc_multa_incid_mora / 100;
  } // ends get_multa_incid_mora_in_fraction()

  public function get_juros_fixos_am_in_fraction() {
    if ($this->perc_juros_fixos_am == null) {
      if ($this->apply_juros_fixos_am == true) {
        $juros_fixos_am_in_perc = (int) env('PERC_JUROS_FIXOS_AM', self::K_PERC_JUROS_FIXOS_AM);
        return $juros_fixos_am_in_perc/100;
      } else {
        return null;
      } // ends inner if
    } // ends outer if
    return $this->perc_juros_fixos_am / 100;
  } // ends get_juros_fixos_am_in_fraction()

  public function find_rent_value_next_reajust_date($from_date = null) {
    /*
      $from_date = null is a convention (in the class) for it to be today's date
    */
    $from_date = ($from_date != null ? $from_date : Carbon::today());
    return DateFunctions
      ::find_next_anniversary_date_with_triple_start_end_n_from(
        $this->start_date,
        $this->get_end_date(),
        $from_date //$inbetween_date
    );
  }


  public function get_monthly_value() {
    return $this->current_rent_value;
  }

  public function todays_diff_to_rent_value_next_reajust_date($from_date = null) {
    /*
      $from_date = null means a convention (in the class) for it to be today's date

      This method is intended to be called from blade.php templates
      It returns "<m> meses e <d> dias",
        where m and d are quantities of months and days respectively
      It still lacks i18n (internationalization), for mês/meses//dia/dias is Portuguese

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

  public function get_ultimas_n_cobrancas_relative_to_ref(
      $monthyeardateref=null,
      $n_lasts=null
    ) {

    // return $this->cobrancas()->orderBy('monthyeardateref', 'desc')->take($n_lasts)->get();

    if ($monthyeardateref == null) {
      $monthyeardateref = DateFunctions
        ::find_conventional_monthyeardateref_with_date_n_dueday(
          null, // $p_monthyeardateref
          $this->pay_day_when_monthly
        );
    }
    if ($n_lasts == null) {
      $n_lasts = self::K_DEFAULT_N_ULTIMAS_COBRANCAS;
    }
    $cobrancas_passadas = Cobranca
      ::where('contract_id', $this->id)
      ->where('monthyeardateref', '<',  $monthyeardateref)
      ->orderBy('monthyeardateref', 'desc')
      ->take($n_lasts)->get();

    return $cobrancas_passadas;
  } // ends get_ultimas_n_cobrancas()

  public function get_cobranca_by_monthyeardateref($monthyeardateref=null) {

    if ($monthyeardateref == null) {
      $monthyeardateref = DateFunctions
        ::find_conventional_monthyeardateref_with_date_n_dueday(
          null, // $p_monthyeardateref
          $this->pay_day_when_monthly
        );
    }
    $cobranca = Cobranca
      ::where('contract_id', $this->id)
      ->where('monthyeardateref', $monthyeardateref)
      ->first();

    return $cobranca;
  } // ends get_cobranca_by_monthyeardateref()

  public function calc_fmontant_from_imontant_monthdaterange_under_contract_mora() {
    /*
        Conveyor method to same named method in class ContractMora
    */
    $contract_mora = new ContractMora($this);
    return $contract_mora
      ->calc_fmontant_from_imontant_monthdaterange_under_contract_mora(
        $initial_montant,
        $monthyeardateref_ini,
        $monthyeardateref_fim,
        $first_month_took_n_days = null,
        $last_month_took_n_days  = null
      );
  }
  public function strlist_all_contractors() {
    $strlist_contractors ='';
    foreach ($this->users as $user) {
      $strlist_contractors .= $user->get_first_n_last_names();
      $strlist_contractors .= ' | ';
    }
    return $strlist_contractors;
  }

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
