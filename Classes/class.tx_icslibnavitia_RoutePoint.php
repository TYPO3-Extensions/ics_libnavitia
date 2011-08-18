<?php

class tx_icslibnavitia_RoutePoint extends tx_icslibnavitia_Node {
	static $fields = array(
		'idx' => 'int',
		'id' => 'int',
		'externalCode' => 'string',
		'routeIdx' => 'int',
		'main' => 'bool',
		'fareSection' => 'int',
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
