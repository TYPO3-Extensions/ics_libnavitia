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
		$this->_ReadXML($reader, 'Call');
	}
	
	protected function ReadAttribute(XMLReader $reader) {
	}

	protected function ReadElement(XMLReader $reader) {
		switch ($reader->name) {
			case 'Before':
				$obj = t3lib_div::makeInstance('tx_icslibnavitia_CallValue');
				$obj->ReadXML($reader);
				$this->__set('before', $obj);
				break;
			case 'This':
				$obj = t3lib_div::makeInstance('tx_icslibnavitia_CallValue');
				$obj->ReadXML($reader);
				$this->__set('this', $obj);
				break;
			case 'After':
				$obj = t3lib_div::makeInstance('tx_icslibnavitia_CallValue');
				$obj->ReadXML($reader);
				$this->__set('after', $obj);
				break;
			default:
				tx_icslibnavitia_Node::SkipChildren($reader);
		}
	}
	
	public function __toString() {
		return get_class($this);
	}
}
