<?php

class tx_icslibnavitia_Backward extends tx_icslibnavitia_Node {
	static $fields = array(
		'name' => 'string',
	);

	public function __construct() {
		parent::__construct(get_class($this) . '::$fields');
		$this->values['direction'] = t3lib_div::makeInstance('tx_icslibnavitia_NodeList', 'tx_icslibnavitia_StopArea');
	}
	
	public function ReadXML(XMLReader $reader) {
		trigger_error('Not implemented', E_USER_NOTICE);
	}
	
	public function __toString() {
		return get_class($this);
	}
	
}
