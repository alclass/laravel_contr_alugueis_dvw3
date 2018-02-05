<?php
namespace App\Models\Billing;

use Carbon\Carbon;
use App\Models\Immeubles\ContractMora;
use App\Models\Finance\CorrMonet;
use App\Models\Utils\DateFunctions;
use App\Models\Utils\FinancialFunctions;

class MoraDebitoCalculator {

  public $contract = null;
  public $contractmoras = null;
  public $total_mora_value = null;

  public function __construct($contract) {
    $this->contract = $contract;
  }

  public function find_debitomoras() {

    $debitomoras = MoraDebito::where('contract_id', $this->contract->id)
      ->where('is_open', true)
      ->orderBy('monthrefdate')
      ->get();

    $this->contractmoras = array();
    foreach ($debitomoras as $debitomora) {
      $contractmora = new ContractMora(
        $this->contract,
        $debitomora->monthrefdate, // $monthrefdate_ini
        null, // default for $monthrefdate_fim
        null, // default for $begin_interest_on_date
        null  // default for $finish_interest_on_date
      );
      $this->contractmoras[]=$contractmora;
    }

  } // ends find_debitomoras_for_contract()

  public function totalize_moras() {
    $this->total_mora_value = 0;
    foreach ($this->contractmoras as $contractmora) {
      $contractmora->calculate_mora_with_imontant();
    }
  } // ends totalize_moras()

} // ends class MoraDebitoCalculator
