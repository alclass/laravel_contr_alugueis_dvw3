<?php
namespace App\Models\Billing;

use App\Models\Billing\BillingItem;
use App\Models\Billing\CobrancaTipo;

class BillingItemGenerator {


  public static function create_n_return_billing_item(
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

  public static function create_n_return_typed_billing_item(
      $cobrancatipo_char4id,
      $value,
      $monthrefdate,
      $numberpart = 1,
      $totalparts = 1
    ) {
    $cobrancatipo = CobrancaTipo::fetch_by_char4id($cobrancatipo_char4id);
    // wrap 'cobrancatipo' with the incoming parameters to the next method:
    return self::create_n_return_billing_item(
      $cobrancatipo,
      $value,
      $monthrefdate,
      $numberpart,
      $totalparts
    );
  }

  public static function create_n_return_alug_billing_item(
    $value,
    $monthrefdate,
    $numberpart = 1,
    $totalparts = 1
  ) {
    return self::create_n_return_typed_billing_item(
      CobrancaTipo::K_4CHAR_ALUG,
      $value,
      $monthrefdate,
      $numberpart,
      $totalparts
    );
  }

  public static function create_n_return_cond_billing_item(
    $value,
    $monthrefdate,
    $numberpart = 1,
    $totalparts = 1
  ) {
    return self::create_n_return_typed_billing_item(
      CobrancaTipo::K_4CHAR_COND,
      $value,
      $monthrefdate,
      $numberpart,
      $totalparts
    );
  }

  public static function create_n_return_iptu_billing_item(
    $value,
    $monthrefdate,
    $numberpart,
    $totalparts
  ) {
    return self::create_n_return_typed_billing_item(
      CobrancaTipo::K_4CHAR_IPTU,
      $value,
      $monthrefdate,
      $numberpart,
      $totalparts
    );
  }

  public static function create_n_return_carr_billing_item(
    $value,
    $monthrefdate,
    $numberpart = 1,
    $totalparts = 1
  ) {
    return self::create_n_return_typed_billing_item(
      CobrancaTipo::K_4CHAR_CARR,
      $value,
      $monthrefdate,
      $numberpart,
      $totalparts
    );
  }

  public static function create_n_return_cred_billing_item(
    $value, // care should be taken by callee, ie, $value here should be < 0
    $monthrefdate,
    $numberpart = 1,
    $totalparts = 1
  ) {
    return self::create_n_return_typed_billing_item(
      CobrancaTipo::K_4CHAR_CRED,
      $value,
      $monthrefdate,
      $numberpart,
      $totalparts
    );
  }

  public function __construct($cobranca) {
    $this->cobranca = $cobranca;
  }

  public function createIfNeededBillingItemFor(
      $cobranca,
      $cobrancatipo,
      $monthrefdate,
      $value,
      $numberpart=null,
      $totalparts=null
    ) {

    // Defaults to ref_type, freq_used_ref etc
    // Default to ref_type
    if ($ref_type == null) {
      $ref_type = BillingItem::K_REF_TYPE_IS_DATE;
    }
    // Default to freq_used_ref
    if ($freq_used_ref == null) {
      $freq_used_ref = BillingItem::K_FREQ_USED_IS_MONTHLY;
    }

    if ($ref_type == BillingItem::K_REF_TYPE_IS_PARCEL ||
        $ref_type == BillingItem::K_REF_TYPE_IS_BOTH_DATE_N_PARCEL) {
      if ($n_cota_ref == null) {
        $n_cota_ref = 1;
      } // ends inner if
      if ($total_cotas_ref == null) {
        $total_cotas_ref = 1;
      } // ends inner if
    } // ends outer if

    // Finally, check default monthyeardateref if CobrancaTipo is D or B
    if ($ref_type == BillingItem::K_REF_TYPE_IS_DATE ||
        $ref_type == BillingItem::K_REF_TYPE_IS_BOTH_DATE_N_PARCEL) {
      if ($monthyeardateref == null) {
        $monthyeardateref = $this->cobranca
          ->return_monthyeardateref_or_if_null_its_convention($monthyeardateref);
      } // ends inner if
    } // ends outer if

    if ($ref_type == BillingItem::K_REF_TYPE_IS_DATE) {
      // Nullify these two if ref_type is D
      $n_cota_ref      = null;
      $total_cotas_ref = null;
    }

    if ($ref_type == BillingItem::K_REF_TYPE_IS_PARCEL) {
      // Nullify this one two if ref_type is D
      $monthyeardateref = null;
    } else {
      // Zero time fields to guarantee date-equility will work
      $monthyeardateref->setTime(0,0,0);
    }

    // Query for Billing Item existence
    $billingitem = null;
    switch ($ref_type) {
      case BillingItem::K_REF_TYPE_IS_BOTH_DATE_N_PARCEL: {
        // break;  // let it fall to the next
      } // ends case
      case BillingItem::K_REF_TYPE_IS_DATE: {
        $billingitem = $this->cobranca->billingitems()
          ->where('cobrancatipo_id',  $cobrancatipo->id)
          ->where('charged_value',    $value)
          ->where('monthyeardateref', $monthyeardateref)
          ->where('ref_type',         $ref_type)
          ->where('freq_used_ref',   $freq_used_ref)
          ->first();
        break;
      } // ends case
      case BillingItem::K_REF_TYPE_IS_PARCEL: {
        $billingitem = $this->cobranca->billingitems()
          ->where('cobrancatipo_id',  $cobrancatipo->id)
          ->where('charged_value',    $value)
          ->where('n_cota_ref',       $n_cota_ref)
          ->where('total_cotas_ref',  $n_cota_ref)
          ->where('ref_type',         $ref_type)
          ->where('freq_used_ref',   $freq_used_ref)
          ->first();
        break;
      } // ends case
    } // ends switch ($ref_type)

    if ($billingitem != null) {
      return $billingitem;
    }

    // create a new one
    $billingitem                   = new BillingItem;
    $billingitem->cobrancatipo_id  = $cobrancatipo->id;
    $billingitem->brief_description = $cobrancatipo->brief_description;
    $billingitem->charged_value    = $value;
    $billingitem->ref_type         = $ref_type;
    $billingitem->freq_used_ref   = $freq_used_ref;
    $billingitem->monthyeardateref = $monthyeardateref;
    $billingitem->n_cota_ref       = $n_cota_ref;
    $billingitem->total_cotas_ref  = $total_cotas_ref;
    $this->cobranca->billingitems()->save($billingitem);
    $billingitem->save();

    return $billingitem;
  } // ends createIfNeededBillingItemFor()

  public function createIfNeededBillingItemForCredito(
    /*
      This method wraps $cobrancatipo CRED to the data it receives and
        chains onwards to createIfNeededBillingItemFor()
    */
      $value,
      $ref_type = null,
      $freq_used_ref = null,
      $monthyeardateref = null,
      $n_cota_ref = null,
      $total_cotas_ref = null
    ) {
    // Fetch crédito's $cobrancatipo :: K_4CHAR_CRED
    $cobrancatipo = CobrancaTipo
      ::get_cobrancatipo_with_its_4char_repr(CobrancaTipo::K_4CHAR_CRED);
    return $this->createIfNeededBillingItemFor(
      $cobranca,
      $cobrancatipo,
      $monthrefdate,
      $value,
      $numberpart,
      $totalparts
		);
  } // ends createIfNeededBillingItemForCredito()

  public function createIfNeededBillingItemForMora(
    /*
      This method wraps $cobrancatipo MORA to the data it receives and
        chains onwards to createIfNeededBillingItemFor()
    */
      $value,
      $ref_type = null,
      $freq_used_ref = null,
      $monthyeardateref=null,
      $n_cota_ref = null,
      $total_cotas_ref = null
      ) {
    // Fetch mora's $cobrancatipo :: K_4CHAR_MORA
    $cobrancatipo = CobrancaTipo
      ::get_cobrancatipo_with_its_4char_repr(CobrancaTipo::K_4CHAR_MORA);
    return $this->createIfNeededBillingItemFor(
      $cobrancatipo,
      $value,
      $ref_type,
      $freq_used_ref,
      $monthyeardateref,
      $n_cota_ref,
      $total_cotas_ref
    );
  } // ends createIfNeededBillingItemForMora()

  public function createIfNeededBillingItemForMoraOrCreditoMonthlyRef(
    /*
      This method wraps:
        $ref_type as K_REF_TYPE_IS_DATE;
        $freq_used_ref as K_FREQ_USED_IS_MONTHLY;

       to the data it receives and
        chains onwards to either CRED or MORA on-course methods

      It also treats the negative value of mora,
        to become a positive one plus its CobrancaTipo (4-char MORA)
    */
      $valor_negativo_mora_positivo_credito,
      $monthyeardateref=null
    ) {
    // First method's parameter cannot be null. Raise exception if it is
    if ($valor_negativo_mora_positivo_credito==null) {
      throw new Exception("valor_negativo_mora_positivo_credito==null in createIfNeededBillingItemForMoraOrCreditoMonthlyRef()", 1);
    }
    $ref_type        = BillingItem::K_REF_TYPE_IS_DATE;
    $freq_used_ref  = BillingItem::K_FREQ_USED_IS_MONTHLY;
    $n_cota_ref      = null;
    $total_cotas_ref = null;
    // $monthyeardateref = $this->cobranca->return_monthyeardateref_or_if_null_its_convention($monthyeardateref);
    if ($valor_negativo_mora_positivo_credito == 0) {
      return null;
    }
    if ($valor_negativo_mora_positivo_credito < 0) {
      // take |modulus|, ie, a positive value will be the 'mora'
      $value = $valor_negativo_mora_positivo_credito * (-1);
      return $this->createIfNeededBillingItemForMora(
        $value,
        $ref_type,
        $freq_used_ref,
        $monthyeardateref,
        $n_cota_ref,
        $total_cotas_ref
      );
    }
    // Now, here, $value > 0
    // 'changing' variable names for better expressing
    $value = $valor_negativo_mora_positivo_credito;
    return $this->createIfNeededBillingItemForCredito(
      $value,
      $ref_type,
      $freq_used_ref,
      $monthyeardateref,
      $n_cota_ref,
      $total_cotas_ref
    );
  } // ends createIfNeededBillingItemForMoraOrCreditoMonthlyRef()

} // ends class class BillingItemGenerator
