<?php

class tx_icslibnavitia_ModeType extends tx_icslibnavitia_Node {
	static $fields = array(
		'idx' => 'int',
		'name' => 'string',
		'externalCode' => 'string',
	);

	public function __construct() {
		parent::__construct(get_class($this) . '::$fields');
		// modelist
	}
	
	public function ReadXML(XMLReader $reader) {
		trigger_error('Not implemented', E_USER_NOTICE);
	}
	protected function ReadAttribute(XMLReader $reader) {
	}
	protected function ReadElement(XMLReader $reader) {
		$this->SkipChildren($reader);
	}
	
	public function __toString() {
		return get_class($this);
	}
}
