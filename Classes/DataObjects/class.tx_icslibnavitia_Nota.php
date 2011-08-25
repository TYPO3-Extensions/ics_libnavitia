<?php

class tx_icslibnavitia_Nota extends tx_icslibnavitia_Node {
	static $fields = array(
		'type' => 'string',
	);

	public function __construct() {
		parent::__construct(get_class($this) . '::$fields');
	}
	
	public function ReadXML(XMLReader $reader) {
		$this->_ReadXML($reader, 'Nota');
	}
	
	protected function ReadAttribute(XMLReader $reader) {
		switch ($reader->name) {
			case 'NotaType':
				$this->__set('type', $reader->value);
				break;
		}
	}

	protected function ReadElement(XMLReader $reader) {
		$this->SkipChildren($reader);
	}
	
	public function __toString() {
		return get_class($this);
	}
}
