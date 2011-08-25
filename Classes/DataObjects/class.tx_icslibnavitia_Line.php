<?php

class tx_icslibnavitia_Line extends tx_icslibnavitia_Node {
	static $fields = array(
		'idx' => 'int',
		'id' => 'int',
		'name' => 'string',
		'code' => 'string',
		'externalCode' => 'string',
		'data' => 'string',
		'order' => 'int',
		'color' => 'string',
		'adaptedRoute' => 'bool',
		'modeType' => 'object:tx_icslibnavitia_ModeType',
		// 'comment' => 'object',
		'network' => 'object:tx_icslibnavitia_Network',
		'forward' => 'object:tx_icslibnavitia_Forward',
		'backward' => 'object:tx_icslibnavitia_Backward',
	);

	public function __construct() {
		parent::__construct(get_class($this) . '::$fields');
		// impactposlist
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
