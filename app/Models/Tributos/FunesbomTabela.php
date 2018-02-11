<?php
namespace App\Models\Tributos;
// use App\Models\Tributos\FunesbomTabela;

use App\Models\Immeubles\Imovel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class FunesbomTabela extends Model {


  const K_DEFAULT_MESREF_DE_REPASSE = 6;

  public static function fetch_by_imovel_n_ano($imovel, $ano=null) {
    /*
    */
    if ($imovel == null) {
      return null;
    }
    if (empty($ano)) {
      // default it to current year
      $today = Carbon::today();
      $ano = $today->year;
    }
    $funesbom_ano_imovel = self
      ::where('imovel_id', $imovel->id)
      ->where('ano', $ano)
      ->first();
    // notice null may be returned
    return $funesbom_ano_imovel;
  } // ends static fetch_by_imovel_n_ano()

  public static function fetch_by_imovelapelido_n_ano($imovelapelido, $ano) {
    $imovel = Imovel
      ::where('apelido', $imovelapelido)
      ->first();
    if ($imovel == null) {
      return null;
    }
    return self::fetch_by_imovel_n_ano($imovel, $ano);
  } // ends static fetch_by_imovelapelido_n_ano()


  protected $table = 'funesbomtabelas';

  /*
    Attributes from db-table:
    ====================
      imovel_id & imovel
      mesref_de_repasse
      ano
      ano_quitado
      valor

    Dynamic Attributes:
    ====================
      <none yet>

    protected $fillable = [   	];

  */

  // protected $attributes = ['mesref_de_fim_repasse', 'totalparts'];


  public function is_refmonth_billable($monthrefdate) {
    if ($monthrefdate == null) {
      return false;
    }
    // check if $monthrefdate implements ->month
    if (property_exists($monthrefdate, 'month')) {
      $mesref_de_repasse = self::K_DEFAULT_MESREF_DE_REPASSE;
      if ($this->mesref_de_repasse != null) {
        $mesref_de_repasse = $this->mesref_de_repasse;
      }
      if ($monthrefdate->month == $this->mesref_de_repasse) {
        return true;
      }
    }
    return false;
  }

  public function __get($propertyName) {
    // $methodname = 'get' . ucfirst($propertyName) . 'Attribute';
    $methodname = 'get_' . $propertyName . '_attribute';
    if (method_exists($this, $methodname)) {
      return $this->{$methodname}();
    }
  }

  public function imovel() {
    return $this->belongsTo('App\Models\Immeubles\Imovel');
  }

} // ends class FunesbomTaxa
