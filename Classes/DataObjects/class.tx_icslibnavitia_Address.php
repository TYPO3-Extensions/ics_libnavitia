<?php

class tx_icslibnavitia_Address extends tx_icslibnavitia_Node {
	static $fields = array(
		'idx' => 'int',
		'id' => 'int',
		'name' => 'string',
		'externalCode' => 'string',
		'type' => 'object:tx_icslibnavitia_AddressType?',
		'coord' => 'object:tx_icslibnavitia_Coord?',
		'city' => 'object:tx_icslibnavitia_City',
	);

	public function __construct() {
		parent::__construct(get_class($this) . '::$fields');
	}
	
	public function ReadXML(XMLReader $reader) {
		$this->_ReadXML($reader, 'Address');
	}
	
	protected function ReadAttribute(XMLReader $reader) {
		switch ($reader->name) {
			case 'AddressId':
				$this->__set('id', (int)$reader->value);
				break;
			case 'AddressName':
				$this->__set('name', $reader->value);
				break;
			case 'AddressIdx':
				$this->__set('idx', (int)$reader->value);
				break;
			case 'AddressExternalCode':
				$this->__set('externalCode', $reader->value);
				break;
		}
	}

	protected function ReadElement(XMLReader $reader) {
		switch ($reader->name) {
			case 'City':
				$obj = t3lib_div::makeInstance('tx_icslibnavitia_City');
				$obj->ReadXML($reader);
				$this->__set('city', $obj);
				break;
			case 'AddressType':
				$obj = t3lib_div::makeInstance('tx_icslibnavitia_AddressType');
				$obj->ReadXML($reader);
				$this->__set('type', $obj);
				break;
			case 'Coord':
				if (strlen($reader->readString()) > 0) {
					$obj = t3lib_div::makeInstance('tx_icslibnavitia_Coord');
					$obj->ReadXML($reader);
					$this->__set('coord', $obj);
				}
				else
					tx_icslibnavitia_Node::SkipChildren($reader);
				break;
			default:
				tx_icslibnavitia_Node::SkipChildren($reader);
		}
	}
	
	public function __toString() {
		return get_class($this);
	}
}

if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/ics_libnavitia/class.tx_icslibnavitia_Address.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/ics_libnavitia/class.tx_icslibnavitia_Address.php']);
}