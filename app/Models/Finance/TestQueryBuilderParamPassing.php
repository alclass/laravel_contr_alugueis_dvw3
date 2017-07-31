<?php
namespace App\Models\Finance;
// To import class TestQueryBuilderParamPassing elsewhere in the Laravel App
// use App\Models\Finance\TestQueryBuilderParamPassing;


use App\Models\Billing\AmortizationParcelTimeEvolver;
use App\Models\Persons\Borrower;
use App\Models\Immeubles\Contract;
// use App\Models\Utils\DateFunctions;
// use App\Models\Utils\FinancialFunctions;
use Carbon\Carbon;

class TestQueryBuilderParamPassing {

  public $contract = null;

  public function __construct() {
    // $contract_id
    $contract_qb = Contract::where('is_active', true);
    print ("Query builder contract_qb BEFORE calling method");
    $this->method_target($contract_qb);
  }

  public function method_target($contract_qb) {
    print ("Query builder contract_qb AFTER method called");
    $contract = $contract_qb->find(3);
    print ("contract id 3 $contract!");
  }
} // ends class TestQueryBuilderParamPassing
