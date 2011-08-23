<?php

class tx_icslibnavitia_EntryPointPlanJourney extends tx_icslibnavitia_Node {
	static $fields = array(
		'type' => 'string',
		'stopIdx' => 'int',
		'stopOrder' => 'int',
		'commentPos' => 'int',
		'estimatedTime' => 'bool',
		'stopPoint' => 'object:tx_icslibnavitia_StopPoint?',
		'stopArea' => 'object:tx_icslibnavitia_StopArea?',
		'site' => 'object:tx_icslibnavitia_Site?',
		'address' => 'object:tx_icslibnavitia_Address?',
		'city' => 'object:tx_icslibnavitia_City?',
		'undefined' => 'object:tx_icslibnavitia_Undefined?',
		// 'comment' => 'object:',
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
