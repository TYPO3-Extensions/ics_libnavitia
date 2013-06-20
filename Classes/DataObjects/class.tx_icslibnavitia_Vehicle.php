<?php

class tx_icslibnavitia_VehicleJourney extends tx_icslibnavitia_Node {
	static $fields = array(
		'id' => 'int',
		'idx' => 'int',
		'name' => 'string',
		'externalCode' => 'string',
		'mipAccess' => 'string',// and others
	);

	public function __construct() {
		parent::__construct(get_class($this) . '::$fields');
	}
	
	public function ReadXML(XMLReader $reader) {
		$this->_ReadXML($reader, 'Vehicle');
	}
	
	protected function ReadInit() {
		parent::ReadInit();
		$this->values['stopList']->Clear();
	}
	
	protected function ReadAttribute(XMLReader $reader) {
		switch ($reader->name) {
			case 'VehicleId':
				$this->__set('id', (int)$reader->value);
				break;
			case 'VehicleIdx':
				$this->__set('idx', (int)$reader->value);
				break;
			case 'VehicleName':
				$this->__set('name', $reader->value);
				break;
			case 'VehicleExternalCode':
				$this->__set('externalCode', $reader->value);
				break;
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
