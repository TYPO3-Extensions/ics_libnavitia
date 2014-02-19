<?php

class tx_icslibnavitia_AddressType extends tx_icslibnavitia_Node {
	static $fields = array(
		'idx' => 'int',
		'id' => 'int',
		'name' => 'string',
		'externalCode' => 'string',
	);

	public function __construct() {
		parent::__construct(get_class($this) . '::$fields');
	}
	
	public function ReadXML(XMLReader $reader) {
		$this->_ReadXML($reader, 'AddressType');
	}
	
	protected function ReadAttribute(XMLReader $reader) {
		switch ($reader->name) {
			case 'AddressTypeId':
				$this->__set('id', (int)$reader->value);
				break;
			case 'AddressTypeName':
				$this->__set('name', $reader->value);
				break;
			case 'AddressTypeIdx':
				$this->__set('idx', (int)$reader->value);
				break;
			case 'AddressTypeExternalCode':
				$this->__set('externalCode', $reader->value);
				break;
		}
	}

	protected function ReadElement(XMLReader $reader) {
		switch ($reader->name) {
			default:
				tx_icslibnavitia_Node::SkipChildren($reader);
		}
	}
	
	public function __toString() {
		return get_class($this);
	}
}

if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/ics_libnavitia/class.tx_icslibnavitia_AddressType.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/ics_libnavitia/class.tx_icslibnavitia_AddressType.php']);
}