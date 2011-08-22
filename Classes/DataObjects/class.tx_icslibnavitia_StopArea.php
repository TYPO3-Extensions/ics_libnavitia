<?php

class tx_icslibnavitia_StopArea extends tx_icslibnavitia_Node {
	static $fields = array(
		'idx' => 'int',
		'id' => 'int',
		'name' => 'string',
		'externalCode' => 'string',
		'main' => 'bool',
		'multiModal' => 'bool',
		'carPark' => 'bool',
		'mainConnection' => 'bool',
		'data' => 'string',
		// 'city' => 'object:?',
		'coord' => 'object:tx_icslibnavitia_Coord?',
	);

	public function __construct() {
		parent::__construct(get_class($this) . '::$fields');
		// hanglist, impactposlist, modetypelist
	}
	
	public function ReadXML(XMLReader $reader) {
		trigger_error('Not implemented', E_USER_NOTICE);
	}
	
	public function __toString() {
		return get_class($this);
	}
}
