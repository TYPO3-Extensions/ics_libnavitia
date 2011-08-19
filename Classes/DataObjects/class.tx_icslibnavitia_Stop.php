<?php

class tx_icslibnavitia_Stop extends tx_icslibnavitia_Node {
	static $fields = array(
		'idx' => 'int',
		'id' => 'int',
		'journeyIdx' => 'int',
		'hour' => 'int',
		'minute' => 'int',
		'destination' => 'int', // TODO: Add support for providing destination object.
		'order' => 'int',
		// 'stopTime' => 'object:',
		// 'toute' => 'object:',
		// '' => '',
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
