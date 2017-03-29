<?php

class C {

	// var $first_name;
	var $middle_name;
	var $last_name;

	public function __construct( ) {
		$this->first_name = "Jo";
		$this->middle_name = "Jay"; 
		$this->last_name = "Jackson";
	}

	public function fullname( ) {
		$name = "";
		$name = $this->first_name;
		if ( strlen($this->middle_name) > 0 ) {
			$name = $name . ' ' . $this->middle_name;
		}
		$name = $name . ' ' . $this->last_name;
		return $name;
	}

		
}
	
$c = new C;	
echo $c->fullname()  . "\n";  // '\n' with single quotes it doesn't do the newline;
