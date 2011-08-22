<?php

class tx_icslibnavitia_ extends tx_icslibnavitia_Node {
	static $fields = array(
		'day' => 'int',
		'hour' => 'int',
		'minutes' => 'int',
	);

	public function __construct() {
		parent::__construct(get_class($this) . '::$fields');
	}
	
	public function ReadXML(XMLReader $reader) {
		trigger_error('Not implemented', E_USER_NOTICE);
	}
	
	public function __toString() {
		return get_class($this);
	}
	
	public function __get($name) {
		if ($name == 'totalSeconds') {
			return (($this->fields['day'] * 24 + $this->fields['hour']) * 60 + $this->fields['minutes']) * 60;
		}
		return parent::__get($name);
	}
	
	public function __set($name, $value) {
		if ($name == 'totalSeconds') {
			if (!is_int($value)) {
				$trace = debug_backtrace();
				trigger_error(
					'Property type mismatch via __set(): ' . $name . 
					', expected: int' .
					' in ' . $trace[0]['file'] .
					' on line ' . $trace[0]['line'],
					E_USER_WARNING);
				return;
			}
			$value /= 60;
			$this->fields['minutes'] = $value % 60;
			$value /= 60;
			$this->fields['hour'] = $value % 24;
			$value /= 24;
			$this->fields['day'] = $value;
			return;
		}
		parent::__get($name);
	}
}
