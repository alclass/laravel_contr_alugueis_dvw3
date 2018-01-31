<?php namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model {

  // These constants below are not intended to be exhaustive
  // The plan is to have only 3 of them, even if there are not banks in database

  const K_BANK_4CHAR_BBRA = 'BBRA'; // Banco do Brasil
  const K_BANK_4CHAR_CAIX = 'CAIX'; // CEF
  const K_BANK_4CHAR_ITAU = 'ITAU'; // Itaú

  public static function get_default() {
    /*
      This method is just a second simpler name for the one called below:
    */
    return self::get_defaultindb_or_firstindb_or_env_or_hardcoded();
  }

  public static function get_defaultindb_or_firstindb_or_env_or_hardcoded() {
    /*
      1st case => default comes from const or configfile and is in database
      --------------------------------------------------------------------
      The default setup may be reimplemented in the future, today
        it's established as a 4-char 'const' here (bank_4char = 'BBRA'), ie
          Default is Banco do Brasil.
        In the future, it will be picked up from a config file.
        In both cases, here or config file,
        the default must also be confirmed in database.

      2nd case => default is not in database, default to the first record there
      --------------------------------------------------------------------
      This default was not confirmed in database. In this case, the default
        will be the first bankaccount in database.

      3rd case => the corresponding database table is empty, so return null
      --------------------------------------------------------------------
      The system can work withou a bankaccount registered. If none is found,
        null is returned.

    */

    // 1st case => default comes from const or configfile and is in database
    //--------------------------------------------------------------------
    $bankaccount_obj = self
      ::where('bank4char', self::K_BANK_4CHAR_BBRA)
      ->where('is_active', true)
      ->first();
    if ($bankaccount_obj != null) {
      return $bankaccount_obj;
    }
    // 2nd case => default is not in database, default to the first record there
    //--------------------------------------------------------------------
    $bankaccount_obj = self::first(); // if it's null, that's the 3rd case in the docstring above
    if ($bankaccount_obj == null) {
      /*
        The first default was given by K_BANK_4CHAR_BBRA and searched in db
        The second default is either in .env or hardcoded here

      Because the last try is the hardcoded one, in fact,
        this method will not really return null as its name implies
      */
      $bankaccount_obj = new self();
      $bankaccount_obj->bankname = env(BANKNAME_DEFAULT, 'Itaú');
      $bankaccount_obj->agency   = env(BANKAGENCY_DEFAULT, '8112');
      $bankaccount_obj->account  = env(BANKACCOUNT_DEFAULT, '07977-0');
      $bankaccount_obj->customer = env(BANKSCUSTOMER, 'LET Ferreira');
      $bankaccount_obj->cpf = env(BANKHOLDERSCPF_DEFAULT, '465156746');
    }
    return $bankaccount_obj;
  } // ends [static] get_bankaccount_default_or_first_or_null()

  public static function return_samebankaccountid_or_adefaultid($bankaccount_id) {
    /*
        Return the same $bankaccount_id if it exists.
        If not, pick up the default.  See doctring for the default above.
          Notice that this method may return null.
          (Two hypotheses for getting null is empty data or database connection failure.)
    */

    if (self::where('id', $bankaccount_id)->exists()) {
      return $bankaccount_id;
    }
    $bankaccount_default = self::get_defaultindb_or_firstindb_or_env_or_hardcoded();

    return ($bankaccount_default != null ? $$bankaccount_default->id : null);

  } // ends [static] bankaccount_id_or_default_or_null()


  protected $table = 'bankaccounts';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */

	protected $fillable = [
		'banknumber', 'bank_4char',
    'bankname', 'agency', 'account', 'customer', 'cpf',
  ];

  public function user() {
    return $this->belongsTo('App\User');
  }

} // ends class BankAccount extends Model
