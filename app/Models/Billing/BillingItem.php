<?php
namespace App\Models\Billing;

// To import class BillingItem elsewhere in the Laravel App
// use App\Models\Billing\BillingItem;

use App\Models\Billing\CobrancaTipo;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class BillingItem extends Model {
  /**
   * docstring
  */


  protected $table = 'billingitems';

  protected $dates = [
   'monthrefdate',
   //'created_at',
   //'updated_at',
 ];

  /**
  * The attributes that are mass assignable.
  *
  * @var array
  */
	protected $fillable = [
		'brief_description', 'charged_value', 'monthrefdate',
    'use_partnumber', 'numberpart', 'totalparts',
    'was_original_value_modified', 'brief_description_for_modifier',
    'original_charged_value', 'modifying_percent', 'modifying_amount',
    'obsinfo',
	];

  protected $attributes = ['reftype', 'freqtype', 'imovel'];

  public function generate_ref_repr_for_cota_column() {
    $cobrancatipo = $this->get_cobrancatipo();
    if ($cobrancatipo != null) {
      if ($cobrancatipo->reftype == CobrancaTipo::K_REFTYPE_D_DATE) {
        return '';
      }
    }
    $numberpart = '' . $this->numberpart;
    $totalparts = '' . $this->totalparts;
    $outstr = "$numberpart/$totalparts";
    return $outstr;
  }

  public function copy() {
    /**
    'brief_description', 'value', 'monthrefdate',
    'use_partnumber', 'numberpart', 'totalparts',
    'was_original_value_modified', 'brief_description_for_modifier',
    'original_value', 'modifying_percent', 'modifying_amount',
    'obsinfo',
    */
    $bi_copy = new BillingItem;
    $bi_copy->brief_description = $this->brief_description;
    $bi_copy->value = $this->value;
    if ($this->monthrefdate != null) {
      $bi_copy->monthrefdate = $this->monthrefdate->copy();
    }
    $bi_copy->use_partnumber = $this->use_partnumber;
    $bi_copy->numberpart     = $this->numberpart;
    $bi_copy->totalparts     = $this->totalparts;
    $bi_copy->was_original_value_modified = $this->was_original_value_modified;
    $bi_copy->brief_description_for_modifier = $this->brief_description_for_modifier;
    $bi_copy->original_value = $this->original_value;
    $bi_copy->modifying_percent = $this->modifying_percent;
    $bi_copy->modifying_amount = $this->modifying_amount;
    return $bi_copy;

  } // ends copy()

  public function toString() {
    /*
        toString() for BillingItem
    */

    $outstr  = '[BillingItem object]' . "\n";
    $outstr .= '====================' . "\n";
    $outstr .= 'id       = ' . $this->id . "\n";
    $outstr .= 'breve des= ' . $this->brief_description . "\n";
    $outstr .= 'reftipo  = ' . $this->reftype     . "\n";
    $outstr .= 'freqtipo = ' . $this->freqtype    . "\n";
    $outstr .= 'valor    = ' . $this->value       . "\n";
    $outstr .= 'mês ref. = ' . $this->monthrefdate . "\n";
    $outstr .= 'parte n. = ' . $this->numberpart . "\n";
    $outstr .= 'partes   = ' . $this->totalparts . "\n";
    $outstr .= 'cobr. id = ' . $this->cobranca_id . "\n";
    $imovel_apelido = 'n/a';
    if ($this->imovel != null) {
      $imovel_apelido = $this->imovel->apelido;
    }
    $outstr .= 'sigla imv= ' . $imovel_apelido . "\n";
    $outstr .= '====================' . "\n";

    return $outstr;

  } // ends toString()

  public function getReftypeAttribute() {
    if ($this->reftype != null) {
      return $this->reftype;
    }
    if ($this->cobrancatipo != null) {
      return $this->cobrancatipo->reftype;
    }
    return 'n/a';
  }

  public function getFreqtypeAttribute() {
    if ($this->freqtype != null) {
      return $this->freqtype;
    }
    if ($this->cobrancatipo != null) {
      return $this->cobrancatipo->freqtype;
    }
    return 'n/a';
  }

  public function getImovelAttribute() {
    $imovel = null;
    if ($this->contract != null) {
      if ($this->contract->imovel != null) {
        $imovel = $this->contract->imovel;
      }
    }
    return $imovel;
  }

  public function cobranca() {
    $this->belongsTo('App\Models\Billing\Cobranca');
  }

  //=========================================================
  // TO-DO: make cobrancatipo work with Eloquent-ORM
  //=========================================================
  public function get_cobrancatipo() {
    return CobrancaTipo::find($this->cobrancatipo_id);
  }
  public function cobrancatipo() {
    $this->belongsTo('App\Models\Billing\CobrancaTipo');
  }
  //=========================================================

} // ends class BillingItem extends Model
