<?php

class tx_icslibnavitia_Equipment extends tx_icslibnavitia_Node {
	static $fields = array(
		'mipAccess' => 'string',// and others
	);

	public function __construct() {
		parent::__construct(get_class($this) . '::$fields');
	}
	
	public function ReadXML(XMLReader $reader) {
		$this->_ReadXML($reader, 'Equipment');
	}
	
	protected function ReadInit() {
		parent::ReadInit();
		$this->values['stopList']->Clear();
	}
	
	protected function ReadAttribute(XMLReader $reader) {
		switch ($reader->name) {
			case 'MIPAccess':
				$this->__set('mipAccess', (int)$reader->value);
				break;
		}
	}

	protected function ReadElement(XMLReader $reader) {
	}
	
	public function __toString() {
		return get_class($this);
	}
}
