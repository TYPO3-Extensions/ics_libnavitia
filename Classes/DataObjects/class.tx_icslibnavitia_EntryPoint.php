<?php

class tx_icslibnavitia_EntryPoint extends tx_icslibnavitia_Node {
	static $fields = array(
		'name' => 'string',
		'cityName' => 'string',
		'type' => 'string',
		'quality' => 'int',
		'number' => 'string',
		'typeName' => 'string',
		'stopArea' => 'object:tx_icslibnavitia_StopArea?',
		'site' => 'object:tx_icslibnavitia_Site?',
		'address' => 'object:tx_icslibnavitia_Address?',
		'city' => 'object:tx_icslibnavitia_City?',
		'coord' => 'object:tx_icslibnavitia_Coord?',
	);

	public function __construct() {
		parent::__construct(get_class($this) . '::$fields');
		$this->values['hangList'] = t3lib_div::makeInstance('tx_icslibnavitia_HangList');
	}
	
	public function ReadXML(XMLReader $reader) {
		$this->_ReadXML($reader, 'EntryPoint');
	}
	
	protected function ReadInit() {
		parent::ReadInit();
		$this->values['hangList']->Clear();
	}
	
	protected function ReadAttribute(XMLReader $reader) {
		switch ($reader->name) {
			case 'EntryPointType':
				$this->__set('type', $reader->value);
				break;
			case 'EntryPointResponseQuality':
				$this->__set('quality', (int)$reader->value);
				break;
			case 'CityName':
				$this->__set('cityName', $reader->value);
				break;
			case 'Number':
				$this->__set('number', $reader->value);
				break;
			case 'TypeName':
				$this->__set('typeName', $reader->value);
				break;
			case 'EntryPointName':
				$this->__set('name', $reader->value);
				break;
		}
	}

	protected function ReadElement(XMLReader $reader) {
		switch ($reader->name) {
			case 'StopArea':
				$obj = t3lib_div::makeInstance('tx_icslibnavitia_StopArea');
				$obj->ReadXML($reader);
				$this->__set('stopArea', $obj);
				break;
			case 'Site':
				$obj = t3lib_div::makeInstance('tx_icslibnavitia_Site');
				$obj->ReadXML($reader);
				$this->__set('site', $obj);
				break;
			case 'Address':
				$obj = t3lib_div::makeInstance('tx_icslibnavitia_Address');
				$obj->ReadXML($reader);
				$this->__set('address', $obj);
				break;
			case 'City':
				$obj = t3lib_div::makeInstance('tx_icslibnavitia_City');
				$obj->ReadXML($reader);
				$this->__set('city', $obj);
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
			case 'HangList':
				$this->values['hangList']->ReadXML($reader);
				break;
			default:
				tx_icslibnavitia_Node::SkipChildren($reader);
		}
	}
	
	public function __toString() {
		return get_class($this);
	}
}

if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/ics_libnavitia/class.tx_icslibnavitia_EntryPoint.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/ics_libnavitia/class.tx_icslibnavitia_EntryPoint.php']);
}