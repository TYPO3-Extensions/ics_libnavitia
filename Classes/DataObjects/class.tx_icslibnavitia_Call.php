<?php

class tx_icslibnavitia_Call extends tx_icslibnavitia_Node {
	static $fields = array(
		'before' => 'object:tx_icslibnavitia_CallValue',
		'this' => 'object:tx_icslibnavitia_CallValue',
		'after' => 'object:tx_icslibnavitia_CallValue',
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
