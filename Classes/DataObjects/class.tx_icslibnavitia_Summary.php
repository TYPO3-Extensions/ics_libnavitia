<?php

class tx_icslibnavitia_Summary extends tx_icslibnavitia_Node {
	static $fields = array(
		'interchange' => 'int',
		'key' => 'string',
		'departureEstimatedTime' => 'bool',
		'arrivalEstimatedTime' => 'bool',
		'departure' => 'object:DateTime',
		'arrival' => 'object:DateTime',
		'duration' => 'object:tx_icslibnavitia_Time',
		'linkTime' => 'object:tx_icslibnavitia_Time',
		'waitTime' => 'object:tx_icslibnavitia_Time',
		'call' => 'object:tx_icslibnavitia_Call',
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
