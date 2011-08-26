<?php

class tx_icslibnavitia_Network extends tx_icslibnavitia_Node {
	static $fields = array(
		'idx' => 'int',
		'id' => 'int',
		'name' => 'string',
		'externalCode' => 'string',
	);

	public function __construct() {
		parent::__construct(get_class($this) . '::$fields');
		// impactposlist
	}
	
	public function ReadXML(XMLReader $reader) {
		$this->_ReadXML($reader, 'Network');
	}
	
	protected function ReadAttribute(XMLReader $reader) {
		switch ($reader->name) {
			case 'NetworkId':
				$this->__set('id', (int)$reader->value);
				break;
			case 'NetworkIdx':
				$this->__set('idx', (int)$reader->value);
				break;
			case 'NetworkName':
				$this->__set('name', $reader->value);
				break;
			case 'NetworkExternalCode':
				$this->__set('externalCode', $reader->value);
				break;
		}
	}

	protected function ReadElement(XMLReader $reader) {
		switch ($reader->name) {
			default:
				$this->SkipChildren($reader);
		}
	}
	
	public function __toString() {
		return get_class($this);
	}
}
