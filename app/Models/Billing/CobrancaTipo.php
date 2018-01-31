<?php
namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Model;

class CobrancaTipo extends Model {

  const K_4CHAR_ALUG = 'ALUG';
  const K_4CHAR_COND = 'COND';
  const K_4CHAR_IPTU = 'IPTU';
  const K_4CHAR_MORA = 'MORA';
  const K_4CHAR_CRED = 'CRED';

  /*
    =================================
      Beginning of Static Methods
    =================================

    //---------------------------------------------
    [Static Method 1]
    get_cobrancatipo_with_its_4charrepr()
      eg.: get_cobrancatipo_with_its_4charrepr('ALUG') return the ALUG cobrancatipo obj.
    //---------------------------------------------
    [Static Method 2]
    get_cobrancatipo_via_its_4charrepr_sqllikeword()
      eg.: get_cobrancatipo_via_its_4charrepr_sqllikeword('aluguel') return the ALUG cobrancatipo obj.
    //---------------------------------------------
    [Static Method 3]
    get_exact_4charrepr_via_sqllikeword()
      eg.: get_exact_4charrepr_via_sqllikeword('aluguel') return 'ALUG'
    //---------------------------------------------
    [Static Method 4]
    get_4charrepr_via_cobrancatipo_id()
      eg.: get_4charrepr_via_cobrancatipo_id(1) return 'ALUG' (of course, it will depend on whether or not the db-table has id=1 for char4id='ALUG')

  */

  // Static Method 1
  public static function get_cobrancatipo_with_its_4charrepr($p_4char_repr, $raise_exception_if_null=false) {
    $cobrancatipo = CobrancaTipo::where('char4id', $p_4char_repr)
      ->first();
    if ($cobrancatipo == null && $raise_exception_if_null=true) {
      $error = 'cobrancatipo from CobrancaTipo::'.$p_4char_repr.' was not db-found, raise/throw exception.';
      throw new Exception($error);
    }
    return $cobrancatipo;
  } // ends [static] get_cobrancatipo_with_its_4charrepr()


  // Static Method 2
  public static function get_cobrancatipo_via_its_4charrepr_sqllikeword($p_oneword) {
    /*

      This method fetches the char4id of a CobrancaTipo
        allowing either a larger or shorter name to be used.
      Examples:

        get_4charid_via_oneword('aluguel')
        => returns 'ALUG'
        get_4charid_via_oneword('alu')
        => returns 'ALUG'
        get_4charid_via_oneword('ALUG')
        => returns 'ALUG'

      ie, 'aluguel', 'alu' and 'ALUG', all tokens were able to fetch the record

      CAUTION: ALUG, COND, IPTU etc thinking on all possible 4-chars,
        there may be some clash of shorter names, so it's to be avoided
        calling this method with shorter names.

    */

     if ($p_oneword == null) {
       return null;
     }
     // put $oneword in CAPITAL
     // $oneword = strtolower($p_oneword); // not needed, the SELECT finds it with lowercase
     // trim it to its first 4 chars or less if it has less
     $n_chars = strlen($oneword); // also: mb_strlen()
     $n_chars = ($n_chars > 4 ? 4 : $n_chars);
     $prospected_char4id = substr($oneword, 0, $n_chars);
     if (strlen($prospected_char4id) < 4) {
       $prospected_char4id .= '%'; // to be used in the 'where-like sql'
     }
     $cobrancatipo = CobrancaTipo::where('char4id', 'like', $prospected_char4id)->first();
     if ($cobrancatipo == null) {
       return null;
     }
     return $cobrancatipo;
  } // ends [static] get_cobrancatipo_via_its_4charrepr_sqllikeword()

  // Static Method 3
  public static function get_exact_4charrepr_via_sqllikeword($sqllikeword) {
    /*
      This ideia behind this method is to get the exact 4-char id
        using a word that works with the sql-like operator.

      Examples:
        ALUG is returned if incoming parameter is aluguel or even alu
      One caution is that if two 4char ids begin with 'alu' it would
        be better to not use 'alu', but 'alug' or 'aluguel' which is a better option

      This method is planned to be used in the HTML-forms, ie, for forming
        the radio button and/or drop-menus that may be used for the user
        to choosen a billing item that he or she intends to generate.

      Example:
      <select id="ref_type_select" name="ref_type_select" class="form-control">
        <option value="{{ bi->get_exact_4charrepr_via_sqllikeword('aluguel') }}">Aluguel</option>
        <option value="{{ bi->get_exact_4charrepr_via_sqllikeword('condo') }}">Condomínio</option>
        <option value="{{ bi->get_exact_4charrepr_via_sqllikeword('iptu') }}">IPTU</option>
      </select>

    */
    $cobrancatipo = self::get_cobrancatipo_via_4charrepr_sqllikeword($sqllikeword);
    if ($cobrancatipo == null) {
      return null;
    }
    return $cobrancatipo->char4id;
  } // ends get_exact_4charrepr_via_sqllikeword()

  // Static Method 4
  public static function get_4charrepr_via_cobrancatipo_id($cobrancatipo_id) {
    // $cobrancatipo_id
    if ($cobrancatipo_id == null) {
      return null;
    }
    // $oneword
    $cobrancatipo = CobrancaTipo::where('id', $cobrancatipo_id)->first();
    if ($cobrancatipo == null) {
      return null;
    }
    return $cobrancatipo->char4id;
  } // ends [static] get_exact_4charrepr_via_sqllikeword()

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
		'char4id', 'brief_description', 'is_repasse ',
    'aplicar_percentual ', 'percentual_a_aplicar ', 'percentual_a_aplicar_descricao',
    'long_description ',
  ];

} // class CobrancaTipo extends Model
