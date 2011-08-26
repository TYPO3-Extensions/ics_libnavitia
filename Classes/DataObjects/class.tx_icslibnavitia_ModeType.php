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
		$this->_ReadXML($reader, 'ModeType');
	}
	
	protected function ReadAttribute(XMLReader $reader) {
		switch ($reader->name) {
			case 'ModeTypeIdx':
				$this->__set('idx', (int)$reader->value);
				break;
			case 'ModeTypeName':
				$this->__set('name', $reader->value);
				break;
			case 'ModeTypeExternalCode':
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
