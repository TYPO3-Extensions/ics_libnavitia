<?php

class tx_icslibnavitia_Forward extends tx_icslibnavitia_Node {
	static $fields = array(
		'name' => 'string',
		'direction' => 'object:tx_icslibnavitia_StopArea?',
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
