<?php
namespace App\Models\Tributos;
// use App\Models\Tributos\FunesbomTaxa;

// use App\Models\Immeubles\Imovel;
// use App\Models\Tributos\IPTUTabela;
// use Carbon\Carbon;

class FunesbomTaxa {

  // dynamic attribute: funesbomvalue

  const APELIDOS_WITH_FUNESBOM = [
    'cdutra' => '75',
    'hlobo'  => '65',
    'jacum'  => '60',
  ];

  public static function get_instance_by_imovel_apelido($apelido) {
    $apelido = strtolower($apelido);
    if (array_key_exists($apelido, self::APELIDOS_WITH_FUNESBOM)) {
      return new self($apelido);
    }
    return null;
  } // ends static get_instance_by_imovel_apelido()

  public function __construct($apelido) {
    $this->apelido = $apelido;
  }

  public function is_refmonth_billable($monthrefdate) {
    if ($monthrefdate == null) {
      return false;
    }
    // check if $monthrefdate implements ->month
    if (property_exists($monthrefdate, 'month')) {
      if ($monthrefdate->month == 6) {
        return true;
      }
    }
    return false;
  }

  // dynamic attribute: funesbomvalue
  public function get_funesbomvalue_attribute() {
    // constructor has protected against non-key cases below
    return self::APELIDOS_WITH_FUNESBOM[$this->apelido];
  }

  public function __get($propertyName) {
    // $methodname = 'get' . ucfirst($propertyName) . 'Attribute';
    $methodname = 'get_' . $propertyName . '_attribute';
    if (method_exists($this, $methodname)) {
      return $this->{$methodname}();
    }
  }

} // ends class FunesbomTaxa
