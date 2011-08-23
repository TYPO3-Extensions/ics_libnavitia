<?php

class tx_icslibnavitia_Undefined extends tx_icslibnavitia_Node {
	static $fields = array(
		'name' => 'string',
		'cityName' => 'string',
		'coord' => 'object:tx_icslibnavitia_Coord?',
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
}
