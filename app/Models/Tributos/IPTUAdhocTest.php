<?php
namespace App\Models\Tributos;
// use App\Models\Tributos\IPTUAdhocTest;

use App\Models\Immeubles\Imovel;
use App\Models\Tributos\IPTUTabela;
use Carbon\Carbon;

class IPTUAdhocTest {

  public function t1() {
    $imovel = Imovel::fetch_by_apelido('cdutra');
    echo "Imóvel id = $imovel->apelido";
    $monthrefdate = new Carbon('2018-2-1');
    echo "monthrefdate = $monthrefdate";
    $ano = $monthrefdate->year;
    $iptu = IPTUTabela::make_instance_with_imovel_n_ano_or_get_default($imovel, $ano);
    $bool = $iptu->is_refmonth_billable($monthrefdate);
    echo "is_refmonth_billable = $bool";
    $months_repass_value = $iptu->get_months_repass_value($monthrefdate);
    echo "months_repass_value = $months_repass_value";
    return $iptu;
  } // ends t1()

  public function t2() {
    $imovel = Imovel::fetch_by_apelido('cdutra');
    print_r($imovel->apelido);
    $monthrefdate = new Carbon('2018-4-1');
    print_r($monthrefdate);
    $iptu = $imovel->get_iptuanoimovel_with_refmonth_or_default($monthrefdate);
    $months_repass_value = $iptu->get_months_repass_value($monthrefdate);
    print("months_repass_value\n");
    print_r($months_repass_value);
    print("is_refmonth_billable\n");
    $is_refmonth_billable = $iptu->is_refmonth_billable($monthrefdate);
    print_r($is_refmonth_billable);
    return $iptu;
  } // ends t2()


} // ends class IPTUAdhocTest
