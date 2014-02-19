<?php

class tx_icslibnavitia_Undefined extends tx_icslibnavitia_Node {
	static $fields = array(
		'name' => 'string',
		'cityName' => 'string',
		'coord' => 'object:tx_icslibnavitia_Coord?',
	);

	public function __construct() {
		parent::__construct(get_class($this) . '::$fields');
	}
	
	public function ReadXML(XMLReader $reader) {
		$this->_ReadXML($reader, 'Undefined');
	}
	
	protected function ReadAttribute(XMLReader $reader) {
		switch ($reader->name) {
			case 'UndefinedName':
				$this->__set('name', $reader->value);
				break;
			case 'CityName':
				$this->__set('cityName', $reader->value);
				break;
		}
	}

	protected function ReadElement(XMLReader $reader) {
		switch ($reader->name) {
			case 'Coord':
				if (strlen($reader->readString()) > 0) {
					$obj = t3lib_div::makeInstance('tx_icslibnavitia_Coord');
					$obj->ReadXML($reader);
					$this->__set('coord', $obj);
				}
				break;
			default:
				tx_icslibnavitia_Node::SkipChildren($reader);
		}
	}
	
	public function __toString() {
		return get_class($this);
	}
}

if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/ics_libnavitia/class.tx_icslibnavitia_Undefined.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/ics_libnavitia/class.tx_icslibnavitia_Undefined.php']);
}