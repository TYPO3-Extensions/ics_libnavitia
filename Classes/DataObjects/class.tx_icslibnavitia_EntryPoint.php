<?php

class tx_icslibnavitia_EntryPoint extends tx_icslibnavitia_Node {
	static $fields = array(
		'name' => 'string',
		'cityName' => 'string',
		'type' => 'string',
		'quality' => 'int',
		'number' => 'string',
		'typeName' => 'string',
		'stopArea' => 'object:tx_icslibnavitia_StopArea?',
		'site' => 'object:tx_icslibnavitia_Site?',
		'address' => 'object:tx_icslibnavitia_Address?',
		'city' => 'object:tx_icslibnavitia_City?',
		'coord' => 'object:tx_icslibnavitia_Coord?',
	);

	public function __construct() {
		parent::__construct(get_class($this) . '::$fields');
		$this->values['hangList'] = t3lib_div::makeInstance('tx_icslibnavitia_NodeList', 'tx_icslibnavitia_Hang');
	}
	
	public function ReadXML(XMLReader $reader) {
		trigger_error('Not implemented', E_USER_NOTICE);
	}
	
	public function __toString() {
		return get_class($this);
	}
}
