<?php
namespace App\Models\Billing;

// this use App\Models\Billing\MoraDebitoCalculatorTester;

use Carbon\Carbon;
use App\Models\Immeubles\Contract;
use App\Models\Billing\MoraDebitoCalculator;
// use App\Models\Utils\DateFunctions;
// use App\Models\Utils\FinancialFunctions;

class MoraDebitoCalculatorTester {

  public $contract = null;
  public $mcalculator = null;

  public function __construct($contract_id) {
    $this->contract = Contract::find($contract_id);
    $this->mcalculator = new MoraDebitoCalculator($this->contract);
  }

  public function test() {
    $this->mcalculator->find_debitomoras();
    return $this->mcalculator->contractmoras;
  }

  public function g_cm() {
    $cmoras = $this->$mcalculator->contractmoras;
    return $cmoras;
  }

  public function g_cm0() {
    $cmoras = $this->$mcalculator->contractmoras;
    if (count($cmoras)>0) {
      return $cmoras[0];
    }
    return [];
  }


} // ends class MoraDebito extends Model
