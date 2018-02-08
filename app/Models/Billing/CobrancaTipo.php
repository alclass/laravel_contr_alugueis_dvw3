<?php
namespace App\Models\Billing;
// use App\Models\Billing\CobrancaTipo;

use Illuminate\Database\Eloquent\Model;

class CobrancaTipo extends Model {

  const K_4CHAR_ALUG = 'ALUG';
  const K_4CHAR_COND = 'COND';
  const K_4CHAR_IPTU = 'IPTU';
  const K_4CHAR_FUNE = 'FUNE'; // Funesbom Taxa Anual de Incêndio
  const K_4CHAR_CARR = 'CARR'; // débito carregado à próxima cobrança
  const K_4CHAR_CRED = 'CRED';

  const K_REFTYPE_D_DATE   = 'D';
  const K_REFTYPE_P_PARCEL = 'P';
  const K_REFTYPE_B_BOTH_DATE_N_PARCEL = 'B';

  const K_FREQTYPE_M_MONTHLY = 'M';
  const K_FREQTYPE_Y_YEARLY  = 'Y';
  const K_FREQTYPE_N_MONTHS_YEARLY = 'N'; // means N monthly parcels in year

  /*
    reftype accepts 3 letters, ie: D = Date, P = Parcel, B = Both Date and Parcel
      reftype defaults to D
      Eg. ALUG is D and IPTU is B
    freqtype accepts 3 letters, ie: M = Monthly, Y = Yearly, W = Weekly
      freqtype defaults to M
      Eg. ALUG is M and FUNE is Y
  */
  const REFTYPESMAP = [
    'D'=> 'Date',
    'P'=> 'Parcel',
    'B'=> 'Date & Parcel',
  ];
  const FREQTYPESMAP = [
    'M'=> 'Monthly',
    'N'=> 'N Months Yearly',
    'Y'=> 'Yearly',
  ];

  /*
    =================================
      Beginning of Static Methods
    =================================

    //---------------------------------------------
    [Static Method 2]
    fetch_or_create_cobrancatipo_by_char4id()
      eg.: fetch_or_create_cobrancatipo_by_char4id('ALUG') return the ALUG cobrancatipo obj.
    //---------------------------------------------
    [Static Method 3]
    get_cobrancatipo_via_its_4charrepr_sqllikeword()
      eg.: get_cobrancatipo_via_its_4charrepr_sqllikeword('aluguel') return the ALUG cobrancatipo obj.
    //---------------------------------------------
    [Static Method 4]
    get_exact_4charrepr_via_sqllikeword()
      eg.: get_exact_4charrepr_via_sqllikeword('aluguel') return 'ALUG'
    //---------------------------------------------
    [Static Method 5]
    get_char4id_by_id()
      eg.: get_char4id_by_id(1) return 'ALUG' (of course, it will depend on whether or not the db-table has id=1 for char4id='ALUG')

  */

  // Static Method 2
  public static function fetch_or_create_cobrancatipo_by_char4id(
      $p_4char_repr,
      $raise_exception_if_null=false
    ) {
    $cobrancatipo = CobrancaTipo::where('char4id', $p_4char_repr)
      ->first();
    if ($cobrancatipo == null) {
      switch ($p_4char_repr) {
        /*
          Cases below are the main billing item types, ie:
            ALUG, COND, IPTU, FUNE, CARR & CRED
        */
        case self::K_4CHAR_ALUG:
          return self::fetch_or_create_alug();
          break;
        case self::K_4CHAR_COND:
          return self::fetch_or_create_cond();
          break;
        case self::K_4CHAR_ITPU:
          return self::fetch_or_create_iptu();
          break;
        case self::K_4CHAR_FUNE:
          return self::fetch_or_create_fune();
          break;
        case self::K_4CHAR_CARR:
          return self::fetch_or_create_carr();
          break;
        case self::K_4CHAR_CRED:
          return self::fetch_or_create_cred();
          break;
        default:
          break;
      } // switch ($p_4char_repr) for cases ALUG, COND, IPTU, FUNE, CARR & CRED

    } // if ($cobrancatipo == null)

    if ($cobrancatipo == null && $raise_exception_if_null=true) {
      $error = 'cobrancatipo from CobrancaTipo::'.$p_4char_repr.' was not db-found, raise/throw exception.';
      throw new Exception($error);
    }
    // At this point, it'll return as null
    return $cobrancatipo;
  } // ends [static] fetch_or_create_cobrancatipo_by_char4id()


  public static function fetch_by_char4id($p_4char_repr) {
    return self::fetch_or_create_cobrancatipo_by_char4id($p_4char_repr, $raise_exception_if_null=false);
  }

  public static function get_char4id_by_id($cobrancatipo_id) {
    $cobrancatipo = CobrancaTipo::where('id', $cobrancatipo_id)->first();
    if ($cobrancatipo == null) {
      return null;
    }
    return $cobrancatipo->char4id;
  } // ends [static] get_exact_4charrepr_via_sqllikeword()


  public static function fetch_or_create_alug() {
    $cobrancatipo = self::fetch_or_create_cobrancatipo_by_char4id(self::K_4CHAR_ALUG);
    if ($cobrancatipo == null) {
      $cobrancatipo = new CobrancaTipo();
      $cobrancatipo->char4id = self::K_4CHAR_ALUG;
      $cobrancatipo->reftype = K_REFTYPE_D_DATE;
      $cobrancatipo->brief_description = 'Aluguel';
      $cobrancatipo->freqtype = K_FREQTYPE_M_MONTHLY;
      $cobrancatipo->brief_description = 'Aluguel referente a contrato de locação';
      $cobrancatipo->save();
    }
    return $cobrancatipo;
  } // ends [static] create_alug()

  public static function fetch_or_create_cond() {
    $cobrancatipo = self::fetch_or_create_cobrancatipo_by_char4id(self::K_4CHAR_COND);
    if ($cobrancatipo == null) {
      $cobrancatipo = new CobrancaTipo();
      $cobrancatipo->char4id = self::K_4CHAR_COND;
      $cobrancatipo->reftype = K_REFTYPE_D_DATE;
      $cobrancatipo->brief_description = 'Condomínio';
      $cobrancatipo->freqtype = K_FREQTYPE_M_MONTHLY;
      $cobrancatipo->brief_description = 'Tarifa do Condomínio Edilício';
    }
    return $cobrancatipo;
  } // ends [static] create_cond()

  public static function fetch_or_create_iptu() {
    $cobrancatipo = self::fetch_or_create_cobrancatipo_by_char4id(self::K_4K_4CHAR_IPTU);
    if ($cobrancatipo == null) {
      $cobrancatipo = new CobrancaTipo();
      $cobrancatipo->char4id = self::K_4CHAR_IPTU;
      $cobrancatipo->reftype = K_REFTYPE_B_BOTH_DATE_N_PARCEL;
      $cobrancatipo->brief_description = 'IPTU';
      $cobrancatipo->freqtype = K_FREQTYPE_N_MONTHS_YEARLY;
      $cobrancatipo->brief_description = 'IPTU (Imposto sobre Propriedade Territorial Urbana)';
    }
    return $cobrancatipo;
  } // ends [static] create_iptu()

  public static function fetch_or_create_fune() {
    $cobrancatipo = self::fetch_or_create_cobrancatipo_by_char4id(self::K_4CHAR_FUNE);
    if ($cobrancatipo == null) {
      $cobrancatipo = new CobrancaTipo();
      $cobrancatipo->char4id = self::K_4CHAR_FUNE;
      $cobrancatipo->reftype = K_REFTYPE_D_DATE;
      $cobrancatipo->brief_description = 'Funesbom Tx.Anual';
      $cobrancatipo->freqtype = K_FREQTYPE_Y_YEARLY;
      $cobrancatipo->brief_description = 'Funesbom: Taxa Anual de Incêndio cobrada pelo Corpo de Bombeiros RJ';
    }
    return $cobrancatipo;
  } // ends [static] create_fune()

  public static function fetch_or_create_carr() {
    $cobrancatipo = self::fetch_or_create_cobrancatipo_by_char4id(self::K_4CHAR_CARR);
    if ($cobrancatipo == null) {
      $cobrancatipo = new CobrancaTipo();
      $cobrancatipo->char4id = self::K_4CHAR_CARR;
      $cobrancatipo->reftype = K_REFTYPE_D_DATE;
      $cobrancatipo->brief_description = 'Débito Anterior';
      $cobrancatipo->freqtype = K_FREQTYPE_M_MONTHLY;
      $cobrancatipo->brief_description = 'Débito Anterior levado e/ou atualizado à cobrança corrente';
    }
    return $cobrancatipo;
  } // ends [static] create_carr()

  public static function fetch_or_create_cred() {
    $cobrancatipo = self::fetch_or_create_cobrancatipo_by_char4id(self::K_4CHAR_CRED);
    if ($cobrancatipo == null) {
      $cobrancatipo = new CobrancaTipo();
      $cobrancatipo->char4id = self::K_4CHAR_CRED;
      $cobrancatipo->reftype = K_REFTYPE_D_DATE;
      $cobrancatipo->brief_description = 'Crédito a Compensar';
      $cobrancatipo->freqtype = K_FREQTYPE_M_MONTHLY;
      $cobrancatipo->brief_description = 'Crédito restante de pagamento anterior ou valor a compensar';
    }
    return $cobrancatipo;
  } // ends [static] create_cred()

  public static function str_all_cobrancatipos() {
    $outstr = '';
    $cobrancatipos = CobrancaTipo::all();
    foreach ($cobrancatipos as $cobrancatipo) {
      $line = $cobrancatipo->toString();
      $outstr .= $line . '\n';
    }
    return $outstr;
  } // ends [static] ()

 /*
   =================================
     End of Static Methods
   =================================
 */

  protected $table = 'cobrancatipos';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'char4id', 'brief_description',
    'reftype', 'freqtype',
    'long_description ',
  ];

  public function is_it_carried_debt() {
    if ($this->char4id == self::K_4CHAR_CARR) {
      return true;
    }
    return false;
  }

  public function toString() {
    $outline  = "[$this->char4id] => $this->brief_description ";
    $outline .= "(reftipo=$this->reftype, freqtipo=$this->freqtype)";
    return $outline;
  }

  public function toStringAllCobrancaTipos() {
    return self::str_all_cobrancatipos();
  }

} // class CobrancaTipo extends Model
