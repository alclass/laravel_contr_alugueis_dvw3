<?php
namespace App\Models\Billing;
// use App\Models\Billing\BillingItemGenStatic;

use App\Models\Billing\BillingItem;
use App\Models\Billing\BillingItemPO;
use App\Models\Billing\CobrancaTipo;
use App\Models\Tributos\IPTUTabela;
use App\Models\Tributos\FunesbomTabela;
use App\Models\Utils\StringFunctions;
use Carbon\Carbon;


class BillingItemGenStatic {
  /*
    Static Methods:
    ===============

    make_billingitem_from_po_n_cobranca()
    make_billingitempo()
    make_billingitempo_for_aluguel()
    make_billingitempo_for_condominio()
    make_billingitempo_for_iptu()
    make_billingitempo_for_iptu_with_iptutabela()

    make_billingitem_for_aluguel()
    create_alug_billing_item() (( same as above but doesn't use PO))

    make_billingitem_for_condominio()
    create_cond_billing_item() (( same as above but doesn't use PO))

    make_billingitem_for_iptu_with_iptutabela()
    make_billingitem_for_iptu()
    create_iptu_billing_item() (( same as above but doesn't use PO))

    make_billingitem_for_fune_with_funetabela()
    make_billingitem_for_fune()
    create_fune_billing_item() (( same as above but doesn't use PO))

    make_billingitem_for_carr()
    create_carr_billing_item() (( same as above but doesn't use PO))

    make_billingitem_for_cred()
    create_cred_billing_item() (( same as above but doesn't use PO))

    ---------------
      GENERIC creators, ie, with them any CobrancaTipo can be used
    ---------------

    create_billing_item_with_cobrancatipo4char()
    create_billing_item()

  */

  public static function make_billingitem_from_po_n_cobranca($billingitempo, $cobranca) {
    if ($billingitempo == null || $cobranca == null) {
      return null;
    }
    return $billingitempo->generate_billingitem_for_cobranca($cobranca);
  }

  public static function make_billingitempo(
      $cobrancatipo4char,
      $charged_value,
      $monthrefdate,
      $additionalinfo = '',
      $numberpart = 1,
      $totalparts = 1
    ) {
    if (empty($cobrancatipo4char)) {
      return null;
    }
    if (!CobrancaTipo::where('char4id', $cobrancatipo4char)->exists()) {
      return null;
    }
    return new BillingItemPO(
      $cobrancatipo4char,
      $charged_value,
      $monthrefdate,
      $additionalinfo,
      $numberpart,
      $totalparts
    );
  }

  public static function make_billingitempo_for_aluguel(
      $charged_value,
      $monthrefdate,
      $additionalinfo = '',
      $numberpart = 1,
      $totalparts = 1
    ) {

    // Okay: create new ALUG item
    return self::make_billingitempo(
      CobrancaTipo::K_4CHAR_ALUG,
      $charged_value,
      $monthrefdate,
      $additionalinfo
    );
  } // make_billingitempo_for_aluguel()

  public static function make_billingitempo_for_condominio(
      $charged_value,
      $monthrefdate,
      $additionalinfo = '',
      $numberpart = 1,
      $totalparts = 1
    ) {

    // Okay: create new ALUG item
    return self::make_billingitempo(
      CobrancaTipo::K_4CHAR_COND,
      $charged_value,
      $monthrefdate,
      $additionalinfo
    );
  } // make_billingitempo_for_condominio()

  public static function make_billingitempo_for_iptu(
      $charged_value,
      $monthrefdate,
      $additionalinfo='',
      $numberpart=1,
      $totalparts=null
    ) {
    if ($totalparts==null) {
      $totalparts = IPTUTabela::get_DEFAULT_IPTU_TOTAL_COTAS();
    }
    return self::make_billingitempo(
      CobrancaTipo::K_4CHAR_IPTU,
      $charged_value,
      $monthrefdate,
      $additionalinfo,
      $numberpart,
      $totalparts
    );
  } // make_billingitempo_for_iptu()

  public static function make_billingitempo_for_iptu_with_iptutabela(
      $iptutabela,
      $monthrefdate,
      $additionalinfo = null
    ) {
    /*
          DB-FIELD month_for_cotaunica is	mesref_de_inicio_repasse
    */
    if ($monthrefdate == null) {
      return null;
    }

    /*
        It's enough to check just the name of the Class (not yet the full namespace)
        (This check is necessary for a caller might send in a wrongly typed object)
    */
    if (!StringFunctions::is_var_of_class($iptutabela, 'IPTUTabela')) {
      return null;
    }

    $charged_value = null;
    $numberpart = 1;
    $totalparts = null;
    // Check first when no iptu bill is to happen
    if (
      $iptutabela->optado_por_cota_unica &&
      $monthrefdate->month != $iptutabela->mesref_de_inicio_repasse
    ) {
      return null;
    }
    if ($monthrefdate->month < $iptutabela->mesref_de_inicio_repasse) {
      return null;
    }

    if (
      $iptutabela->optado_por_cota_unica == true &&
      $monthrefdate->month == $iptutabela->mesref_de_inicio_repasse
    ) {
      /*
        1st create-case: cota-única anual
          foi escolhida a ser repassada em Fevereiro, ref. Janeiro
      */
      $charged_value = $iptutabela->valor_parcela_unica;
      $numberpart = 1;
      $totalparts = 1;
    } else {
      /* 2nd create-case: escolhido o pagamento em 10 cotas (10 é const em IPTUTabela)
           if even the cota-única was chosen (because it was chosen but not paid...  Review this)
      */
      $charged_value = $iptutabela->valor_por_parcela;
      $numberpart = $monthrefdate->month - 1;
      $totalparts = $iptutabela->total_de_parcelas;
    }
    return self::make_billingitempo_for_iptu(
        $charged_value,
        $monthrefdate,
        $additionalinfo,
        $numberpart,
        $totalparts
    );
  } // ends

  public static function make_billingitempo_for_fune_with_funetabela(
      $funetabela,
      $monthrefdate,
      $additionalinfo = null
    ) {

    if ($funetabela == null) {
      return null;
    }
    if ($monthrefdate->month == $funetabela->mesref_de_repasse) {
      $charged_value = $funetabela->valor;
      $numberpart    = 1;
      $total_de_parcelas = 1;
    } else {
      return null;
    }

    return self::make_billingitempo_for_fune(
        $charged_value,
        $monthrefdate,
        $additionalinfo,
        $numberpart,
        $totalparts
    );
  } // ends

  public static function make_billingitempo_for_fune(
      $charged_value,
      $monthrefdate,
      $additionalinfo = '',
      $numberpart = 1,
      $totalparts = 1
    ) {

    // Okay: create new CARR item
    return self::make_billingitempo(
      CobrancaTipo::K_4CHAR_FUNE,
      $charged_value,
      $monthrefdate,
      $additionalinfo,
      $numberpart,
      $totalparts
    );
  } // make_billingitempo_for_fune()

  public static function make_billingitempo_for_carr(
      $charged_value,
      $monthrefdate,
      $additionalinfo = '',
      $numberpart = 1,
      $totalparts = 1
    ) {

    // Okay: create new CARR item
    return self::make_billingitempo(
      CobrancaTipo::K_4CHAR_CARR,
      $charged_value,
      $monthrefdate,
      $additionalinfo,
      $numberpart,
      $totalparts
    );
  } // make_billingitempo_for_carr()

  public static function make_billingitempo_for_cred(
      $charged_value,
      $monthrefdate,
      $additionalinfo = '',
      $numberpart = 1,
      $totalparts = 1
    ) {

    if ($charged_value > 0) {
      $charged_value = -$charged_value;
    }

    // Okay: create new CRED item
    return self::make_billingitempo(
      CobrancaTipo::K_4CHAR_CRED,
      $charged_value,
      $monthrefdate,
      $additionalinfo,
      $numberpart,
      $totalparts
    );
  } // make_billingitempo_for_cred()

  public static function make_billingitem_for_aluguel(
      $cobranca,
      $charged_value,
      $monthrefdate,
      $additionalinfo = null
    ) {

    $billingitempo = self::make_billingitempo_for_aluguel(
      $charged_value,
      $monthrefdate,
      $additionalinfo
    );
    return self::make_billingitem_from_po_n_cobranca(
      $billingitempo,
      $cobranca
    );
  } //ends make_billingitem_for_aluguel()

  public static function make_billingitem_for_condominio(
      $cobranca,
      $charged_value,
      $monthrefdate,
      $additionalinfo = null
    ) {

    $billingitempo = self::make_billingitempo_for_condominio(
      $charged_value,
      $monthrefdate,
      $additionalinfo
    );
    return self::make_billingitem_from_po_n_cobranca(
      $billingitempo,
      $cobranca
    );
  } //ends make_billingitem_for_condominio()

  public static function make_billingitem_for_iptu(
      $cobranca,
      $charged_value,
      $monthrefdate,
      $additionalinfo = '',
      $numberpart=1,
      $totalparts=null
    ) {

    $billingitempo = self::make_billingitempo_for_iptu(
      $charged_value,
      $monthrefdate,
      $additionalinfo,
      $numberpart,
      $totalparts
    );
    return self::make_billingitem_from_po_n_cobranca(
      $billingitempo,
      $cobranca
    );
  } //ends make_billingitem_for_condominio()

  public static function make_billingitem_for_iptu_with_iptutabela(
      $cobranca,
      $iptutabela,
      $monthrefdate,
      $additionalinfo = null
    ) {
    $billingitempo = self::make_billingitempo_for_iptu_with_iptutabela(
      $iptutabela,
      $monthrefdate,
      $additionalinfo
    );
    return self::make_billingitem_from_po_n_cobranca(
      $billingitempo,
      $cobranca
    );
  } //ends make_billingitem_for_()

  public static function make_billingitem_for_fune_with_funetabela(
      $cobranca,
      $funetabela,
      $monthrefdate,
      $additionalinfo = null
    ) {
    $billingitempo = self::make_billingitempo_for_fune_with_funetabela(
      $funetabela,
      $monthrefdate,
      $additionalinfo
    );
    return self::make_billingitem_from_po_n_cobranca(
      $billingitempo,
      $cobranca
    );
  } //ends make_billingitem_for_()

  public static function make_billingitem_for_carr(
      $cobranca,
      $charged_value,
      $monthrefdate,
      $additionalinfo = null
    ) {
    $billingitempo = self::make_billingitempo_for_carr(
      $charged_value,
      $monthrefdate,
      $additionalinfo
    );
    return self::make_billingitem_from_po_n_cobranca(
      $billingitempo,
      $cobranca
    );
  } //ends make_billingitem_for_carr()

  public static function make_billingitem_for_cred(
      $cobranca,
      $charged_value,
      $monthrefdate,
      $additionalinfo = null
    ) {
    $billingitempo = self::make_billingitempo_for_cred(
      $charged_value,
      $monthrefdate,
      $additionalinfo
    );
    return self::make_billingitem_from_po_n_cobranca(
      $billingitempo,
      $cobranca
    );
  } //ends make_billingitem_for_cred()

  public static function create_billing_item(
      $cobrancatipo,
      $value,
      $monthrefdate,
      $numberpart = 1,
      $totalparts = 1
    ) {
    $billingitem = new BillingItem();
    $billingitem->cobrancatipo_id = $cobrancatipo->id;
    $billingitem->value = $value;
    $billingitem->monthrefdate = $monthrefdate;
    $billingitem->numberpart = $numberpart;
    $billingitem->totalparts = $totalparts;
    return $billingitem;
  }

  public static function create_billing_item_with_cobrancatipo4char(
      $cobrancatipo_char4id,
      $value,
      $monthrefdate,
      $numberpart = 1,
      $totalparts = 1
    ) {
    $cobrancatipo = CobrancaTipo::fetch_by_char4id($cobrancatipo_char4id);
    // wrap 'cobrancatipo' with the incoming parameters to the next method:
    return self::create_billing_item(
      $cobrancatipo,
      $value,
      $monthrefdate,
      $numberpart,
      $totalparts
    );
  }

  public static function create_alug_billing_item(
    $value,
    $monthrefdate,
    $numberpart = 1,
    $totalparts = 1
  ) {
    return self::create_billing_item_with_cobrancatipo4char(
      CobrancaTipo::K_4CHAR_ALUG,
      $value,
      $monthrefdate,
      $numberpart,
      $totalparts
    );
  }

  public static function create_cond_billing_item(
    $value,
    $monthrefdate,
    $numberpart = 1,
    $totalparts = 1
  ) {
    return self::create_billing_item_with_cobrancatipo4char(
      CobrancaTipo::K_4CHAR_COND,
      $value,
      $monthrefdate,
      $numberpart,
      $totalparts
    );
  }

  public static function create_iptu_billing_item(
    $value,
    $monthrefdate,
    $numberpart,
    $totalparts
  ) {
    return self::create_billing_item_with_cobrancatipo4char(
      CobrancaTipo::K_4CHAR_IPTU,
      $value,
      $monthrefdate,
      $numberpart,
      $totalparts
    );
  }

  public static function create_carr_billing_item(
    $value,
    $monthrefdate,
    $numberpart = 1,
    $totalparts = 1
  ) {
    return self::create_billing_item_with_cobrancatipo4char(
      CobrancaTipo::K_4CHAR_CARR,
      $value,
      $monthrefdate,
      $numberpart,
      $totalparts
    );
  }

  public static function create_cred_billing_item(
    $value, // care should be taken by callee, ie, $value here should be < 0
    $monthrefdate,
    $numberpart = 1,
    $totalparts = 1
  ) {
    if ($value > 0) {
      $value = -$value;
    }
    return self::create_typed_billing_item(
      CobrancaTipo::K_4CHAR_CRED,
      $value,
      $monthrefdate,
      $numberpart,
      $totalparts
    );
  }

  public static function adhoctest1() {

    // test aluguel $billingitempo
    print ("1) test aluguel billingitempo \n");
    $charged_value = 1900;
    $monthrefdate = new Carbon('2018-2-1');
    $additionalinfo = 'additional info';
    $numberpart = null;
    $totalparts = null;
    $billingitempo = self::make_billingitempo_for_aluguel(
      $charged_value,
      $monthrefdate,
      $additionalinfo,
      $numberpart,
      $totalparts
    );
    print ("billingitempo => $billingitempo \n");

    // test condominio $billingitempo
    print ("2) test condominio billingitempo \n");
    $charged_value = 600;
    $monthrefdate = new Carbon('2018-2-1');
    $additionalinfo = 'additional info';
    $numberpart = null;
    $totalparts = null;
    $billingitempo = self::make_billingitempo_for_condominio(
      $charged_value,
      $monthrefdate,
      $additionalinfo,
      $numberpart,
      $totalparts
    );
    print ("billingitempo => $billingitempo \n");

    // test iptu $billingitempo
    print ("3a) test iptu billingitempo (jacum, 2018) \n");
    $monthrefdate = new Carbon('2018-5-1');
    $additionalinfo = 'additional info iptu';
    $iptutabela = IPTUTabela
      ::fetch_by_imovelapelido_n_ano_or_return_null('jacum', 2018);
    $numberpart = null;
    $totalparts = null;
    $billingitempo = self::make_billingitempo_for_iptu_with_iptutabela(
      $iptutabela,
      $monthrefdate,
      $additionalinfo,
      $numberpart,
      $totalparts
    );
    print ("billingitempo => $billingitempo \n");
    // var_dump($billingitempo);

    // test iptu $billingitempo
    print ("3b) test iptu billingitempo (hlobo, 2018, month=5 with cota ún.) \n");
    $monthrefdate = new Carbon('2018-5-1');
    $additionalinfo = 'additional info iptu';
    $iptutabela = IPTUTabela
      ::fetch_by_imovelapelido_n_ano_or_return_null('hlobo', 2018);
    $numberpart = null;
    $totalparts = null;
    $billingitempo = self::make_billingitempo_for_iptu_with_iptutabela(
      $iptutabela,
      $monthrefdate,
      $additionalinfo,
      $numberpart,
      $totalparts
    );
    print ("billingitempo => $billingitempo \n");
    // var_dump($billingitempo);

    // test funesbom $billingitempo
    print ("4) test funesbom billingitempo \n");
    $monthrefdate = new Carbon('2018-6-1');
    $additionalinfo = 'additional info funesbom';
    $funetabela = FunesbomTabela
      ::fetch_by_imovelapelido_n_ano('cdutra', 2018);
    $numberpart = 1;
    $totalparts = 1;
    $billingitempo = self::make_billingitempo_for_fune_with_funetabela(
      $funetabela,
      $monthrefdate,
      $additionalinfo,
      $numberpart,
      $totalparts
    );
    print ("billingitempo => $billingitempo \n");
    // var_dump($billingitempo);

    // test carr $billingitempo
    print ("5) test carr billingitempo \n");
    $charged_value = 600;
    $monthrefdate = new Carbon('2018-2-1');
    $additionalinfo = 'additional info';
    $numberpart = 1;
    $totalparts = 1;
    $billingitempo = self::make_billingitempo_for_carr(
      $charged_value,
      $monthrefdate,
      $additionalinfo,
      $numberpart,
      $totalparts
    );
    print ("billingitempo => $billingitempo \n");
    // var_dump($billingitempo);

    // test cred $billingitempo
    print ("6) test cred billingitempo \n");
    $charged_value = 600;
    $monthrefdate = new Carbon('2018-2-1');
    $additionalinfo = 'additional info';
    $numberpart = 1;
    $totalparts = 1;
    $billingitempo = self::make_billingitempo_for_cred(
      $charged_value,
      $monthrefdate,
      $additionalinfo,
      $numberpart,
      $totalparts
    );
    print ("billingitempo => $billingitempo \n");
    // var_dump($billingitempo);

  }

} // ends class class BillingItemGenStatic
