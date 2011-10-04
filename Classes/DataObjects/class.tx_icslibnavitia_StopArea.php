<?php

class tx_icslibnavitia_StopArea extends tx_icslibnavitia_Node {
	static $fields = array(
		'idx' => 'int',
		'id' => 'int',
		'name' => 'string',
		'externalCode' => 'string',
		'main' => 'bool',
		'multiModal' => 'bool',
		'carPark' => 'bool',
		'mainConnection' => 'bool',
		'data' => 'string',
		'city' => 'object:tx_icslibnavitia_City?',
		'coord' => 'object:tx_icslibnavitia_Coord?',
	);

	public function __construct() {
		parent::__construct(get_class($this) . '::$fields');
		$this->values['hangList'] = t3lib_div::makeInstance('tx_icslibnavitia_HangList');
		// impactposlist, modetypelist
	}
	
	public function ReadXML(XMLReader $reader) {
		$this->_ReadXML($reader, 'StopArea');
	}
	
	protected function ReadInit() {
		parent::ReadInit();
		$this->values['hangList']->Clear();
	}
	
	protected function ReadAttribute(XMLReader $reader) {
		switch ($reader->name) {
			case 'StopAreaName':
				$this->__set('name', $reader->value);
				break;
			case 'StopAreaId':
				$this->__set('id', (int)$reader->value);
				break;
			case 'StopAreaExternalCode':
				$this->__set('externalCode', $reader->value);
				break;
			case 'MainStopArea':
				$this->__set('main', (bool)$reader->value);
				break;
			case 'StopAreaIdx':
				$this->__set('idx', (int)$reader->value);
				break;
			case 'MultiModal':
				$this->__set('multiModal', (bool)$reader->value);
				break;
			case 'CarPark':
				$this->__set('carPark', (bool)$reader->value);
				break;
			case 'MainConnection':
				$this->__set('mainConnection', (bool)$reader->value);
				break;
			case 'AdditionalData':
				$this->__set('data', $reader->value);
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
			case 'Coord':
				if (!$reader->isEmptyElement && (strlen($reader->readString()) > 0)) {
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
