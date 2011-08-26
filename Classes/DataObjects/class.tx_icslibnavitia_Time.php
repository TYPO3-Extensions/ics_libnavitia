<?php

class tx_icslibnavitia_Time extends tx_icslibnavitia_Node {
	static $fields = array(
		'day' => 'int',
		'hour' => 'int',
		'minute' => 'int',
	);

	public function __construct() {
		parent::__construct(get_class($this) . '::$fields');
	}
	
	public function ReadXML(XMLReader $reader) {
		$this->_ReadXML($reader, false);
	}
	
	protected function ReadAttribute(XMLReader $reader) {
	}

	protected function ReadElement(XMLReader $reader) {
		switch ($reader->name) {
			case 'Day':
				$this->__set('day', (int)$reader->readString());
				break;
			case 'Hour':
				$this->__set('hour', (int)$reader->readString());
				break;
			case 'Minute':
				$this->__set('minute', (int)$reader->readString());
				break;
		}
		$this->SkipChildren($reader);
	}
	
	public function __toString() {
		return get_class($this);
	}
	
	public function __get($name) {
		if ($name == 'totalSeconds') {
			return (($this->values['day'] * 24 + $this->values['hour']) * 60 + $this->values['minute']) * 60;
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
			$this->values['minute'] = $value % 60;
			$value /= 60;
			$this->values['hour'] = $value % 24;
			$value /= 24;
			$this->values['day'] = $value;
			return;
		}
		parent::__set($name, $value);
	}
}
