<?php

class tx_icslibnavitia_Coord extends tx_icslibnavitia_Node {
	static $fields = array(
		'x' => 'float',
		'y' => 'float',
		'lat' => 'float',
		'lng' => 'float',
	);

	public function __construct() {
		parent::__construct(get_class($this) . '::$fields');
	}
	
	public function __set($name, $value) {
		if (array_key_exists($name, $this->values)) {
			$oldValue = $this->__get($name);
		}
		parent::__set($name, $value);
		if (isset($oldValue) && ($oldValue != $this->__get($name))) {
		}
	}
	
	public function ReadXML(XMLReader $reader) {
		trigger_error('Not implemented', E_USER_NOTICE);
	}
	
	public function __toString() {
		return get_class($this);
	}
}
