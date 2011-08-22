<?php

class tx_icslibnavitia_StopPoint extends tx_icslibnavitia_Node {
	static $fields = array(
		'idx' => 'int',
		'id' => 'int',
		'name' => 'string',
		'externalCode' => 'string',
		// 'address' => 'object:', 
		// 'equipment' => 'object:', 
		'mode' => 'object:tx_icslibnavitia_Mode', 
		// 'city' => 'object:', 
		'stopArea' => 'object:tx_icslibnavitia_StopArea?', 
		'coord' => 'object:tx_icslibnavitia_Coord', 
		// 'comment' => 'object',
	);

	public function __construct() {
		parent::__construct(get_class($this) . '::$fields');
		// impactposlist
	}
	
	public function ReadXML(XMLReader $reader) {
		trigger_error('Not implemented', E_USER_NOTICE);
	}
	
	public function __toString() {
		return get_class($this);
	}
}
