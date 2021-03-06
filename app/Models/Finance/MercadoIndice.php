<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;

class MercadoIndice extends Model {

  //-----------------------------------------------------------
  //--- Siglas 4-char dos principais indicadores monetários ---
  //-----------------------------------------------------------
  const K_INDICE4CHAR_IGPM  = 'IGPM';
  const K_INDICE4CHAR_IPCA  = 'IPCA';
  const K_INDICE4CHAR_SELIC = 'SELI';
  //-----------------------------------------------------------
  const K_JUROS_FIXOS_AM_CONVENCIONADOS_IN_PERC = 0.01;

  public static function get_default_juros_fixos_am_in_perc() {
    /*
      This method searches the default in the following manner:
        1) first, look up the .env config file
        2) second, if it's not there in .env, pick up the one up here
            ie, self::K_JUROS_FIXOS_AM_CONVENCIONADOS_IN_PERC
    */
    $default_juros_fixos_am_in_perc = env('K_JUROS_FIXOS_AM_CONVENCIONADOS_IN_PERC', self::K_JUROS_FIXOS_AM_CONVENCIONADOS_IN_PERC);
    return $default_juros_fixos_am_in_perc;

  } // ends [static] get_default_juros_fixos_am_in_perc()

  public static function get_default_juros_fixos_am_in_fraction() {

    return self::get_default_juros_fixos_am_in_perc() / 100;


  } // ends [static] get_default_juros_fixos_am_in_perc()


  public static function get_default_char4indicator_for_reajuste_imob() {
    /*
      This method searches the default in the following manner:
        1) first, look up the .env config file
        2) second, if it's not there in .env, pick up the one in here
            ie, self::K_INDICE4CHAR_IGPM
    */
    $char4indicator = env('K_INDICE4CHAR_IGPM', self::K_INDICE4CHAR_IGPM);
    return $first_indice_obj;

  } // ends [static] get_default_char4indicator_for_reajuste_imob()

  public static function get_default_financ_indicator_for_reajuste_imob() {
    /*
      The default first is the char4indicator, eg, IGPM IPCA SELI etc.
      The object however must be fetched in the database.
      If somehow database is empty, null will be returned.
    */
    $char4indicator = self::get_default_char4indicator_for_reajuste_imob();
    return self
     ::where('indice4char', $char4indicator)
     ->where('is_active', true)
     ->first();
  } // ends [static] get_default_financ_indicator_for_reajuste_imob()

	public static function get_default_char4indicator_for_corrmonet() {
    /*
    /*
      This method searches the default in the following manner:
        1) first, look up the .env config file
        2) second, if it's not there in .env, pick up the one in here
            ie, self::K_INDICE4CHAR_SELIC
    */
    $char4indicator = env('K_INDICE4CHAR_SELIC', self::K_INDICE4CHAR_SELIC);
    return $char4indicator;

  } // ends [static] get_default_char4indicator_for_corrmonet()

  public static function get_default_financ_indicator_for_corrmonet() {
    /*
      The default first is the char4indicator, eg, IGPM IPCA SELI etc.
      The object however must be fetched in the database.
      If somehow database is empty, null will be returned.
    */
    $char4indicator = self::get_default_char4indicator_for_corrmonet();
    return self
     ::where('indice4char', $char4indicator)
     ->where('is_active', true)
     ->first();
   } // ends [static] get_default_financ_indicator_for_corrmonet()


  protected $table = ['mercadoindices'];

  protected $dates = [
    'since',
  ];

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
   protected $fillable = [
     'indice4char', 'sigla', 'description', 'since',
     'url_datasource', 'is_active',
 ];

} // ends class MercadoIndice extends Model
