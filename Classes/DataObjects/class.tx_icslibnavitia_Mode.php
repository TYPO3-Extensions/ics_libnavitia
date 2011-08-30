<?php

class tx_icslibnavitia_Mode extends tx_icslibnavitia_Node {
	static $fields = array(
		'idx' => 'int',
		'id' => 'int',
		'name' => 'string',
		'externalCode' => 'string',
		'typeExternalCode' => 'string',
	);

	public function __construct() {
		parent::__construct(get_class($this) . '::$fields');
	}
	
	public function ReadXML(XMLReader $reader) {
		$this->_ReadXML($reader, 'Mode');
	}
	
	protected function ReadInit() {
		parent::ReadInit();
	}
	
	protected function ReadAttribute(XMLReader $reader) {
		switch ($reader->name) {
			case 'ModeIdx':
				$this->__set('idx', (int)$reader->value);
				break;
			case 'ModeId':
				$this->__set('id', (int)$reader->value);
				break;
			case 'ModeName':
				$this->__set('name', $reader->value);
				break;
			case 'ModeExternalCode':
				$this->__set('externalCode', $reader->value);
				break;
			case 'ModeTypeExternalCode':
				$this->__set('typeExternalCode', $reader->value);
				break;
		}
	}

	protected function ReadElement(XMLReader $reader) {
		tx_icslibnavitia_Node::SkipChildren($reader);
	}
	
	public function __toString() {
		return get_class($this);
	}
}
