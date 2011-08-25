<?php

class tx_icslibnavitia_RoutePoint extends tx_icslibnavitia_Node {
	static $fields = array(
		'idx' => 'int',
		'id' => 'int',
		'externalCode' => 'string',
		'routeIdx' => 'int',
		'main' => 'string',
		'stopPoint' => 'object:tx_icslibnavitia_StopPoint',
		'stopArea' => 'object:tx_icslibnavitia_StopArea?',
	);

	public function __construct() {
		parent::__construct(get_class($this) . '::$fields');
	}
	
	public function ReadXML(XMLReader $reader) {
		trigger_error('Not implemented', E_USER_NOTICE);
	}
	protected function ReadAttribute(XMLReader $reader) {
	}
	protected function ReadElement(XMLReader $reader) {
	}
	
	public function __toString() {
		return get_class($this);
	}
}
