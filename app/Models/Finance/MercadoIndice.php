<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;

class MercadoIndice extends Model {

  const K_INDICE4CHAR_IGPM  = 'IGPM';
  const K_INDICE4CHAR_IPCA  = 'IPCA';
  const K_INDICE4CHAR_SELIC = 'SELI';

  public static function get_default_mercadoindice() {
    /*
      This method intends to return the $mercadoindice_obj default.

      [TODO] In the future, this default should be taken from a config file.
        Today it's merely a const in here.

      However, if the default const in here (see it above) is not in the database,
        an attempt will be made to retrieve the first object in database.

      If somehow database is empty, null will be returned.
    */
    $indice_obj = self
      ::where('indice4char', self::K_INDICE_4CHAR_IGPM)
      ->where('is_active', true)
      ->first();
    if ($indice_obj != null) {
      return $indice_obj;
    }
    $first_indice_obj = self::first(); // Notice that first() may return null
    return $first_indice_obj;
  } // ends [static] get_default_mercadoindice()

	public static function get_default_4char() {
    /*
      This method intends to return the $indice4char default.
      See above the docstring for retrieve the default object.
    */
    $default_mercadoindice = self::get_default_mercadoindice();
    return ($default_mercadoindice != null ? $default_mercadoindice->indice4char : null);
  } // ends [static] get_default_4char()

	public static function return_indice4char_if_exists_or_default_or_null($indice4char) {
    /*
      return uses a ternary operator, ie, if $indice4char is not in database,
        try to default it.  In the default method (self::get_default_4char()),
        an expection may be raised.
    */
    return
      self::where('indice4char', $indice4char)->exists() ?
        $indice4char,
        self::get_default_4char();

  } // ends [static] return_indice4char_if_exists_or_default_or_null()



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
