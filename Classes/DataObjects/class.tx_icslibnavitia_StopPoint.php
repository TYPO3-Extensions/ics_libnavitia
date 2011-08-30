<?php

class tx_icslibnavitia_StopPoint extends tx_icslibnavitia_Node {
	static $fields = array(
		'idx' => 'int',
		'id' => 'int',
		'name' => 'string',
		'externalCode' => 'string',
		// 'address' => 'object:', 
		// 'equipment' => 'object:', 
		'mode' => 'object:tx_icslibnavitia_Mode', 
		'city' => 'object:tx_icslibnavitia_City', 
		'stopArea' => 'object:tx_icslibnavitia_StopArea?', 
		'coord' => 'object:tx_icslibnavitia_Coord', 
		// 'comment' => 'object',
	);

	public function __construct() {
		parent::__construct(get_class($this) . '::$fields');
		// impactposlist
	}
	
	public function ReadXML(XMLReader $reader) {
		$this->_ReadXML($reader, 'StopPoint');
	}
	
	protected function ReadAttribute(XMLReader $reader) {
		switch ($reader->name) {
			case 'StopPointId':
				$this->__set('id', (int)$reader->value);
				break;
			case 'StopPointIdx':
				$this->__set('idx', (int)$reader->value);
				break;
			case 'StopPointName':
				$this->__set('name', $reader->value);
				break;
			case 'StopPointExternalCode':
				$this->__set('externalCode', $reader->value);
				break;
		}
	}

	protected function ReadElement(XMLReader $reader) {
		switch ($reader->name) {
			case 'Mode':
				$obj = t3lib_div::makeInstance('tx_icslibnavitia_Mode');
				$obj->ReadXML($reader);
				$this->__set('mode', $obj);
				break;
			case 'City':
				$obj = t3lib_div::makeInstance('tx_icslibnavitia_City');
				$obj->ReadXML($reader);
				$this->__set('city', $obj);
				break;
			case 'StopArea':
				$obj = t3lib_div::makeInstance('tx_icslibnavitia_StopArea');
				$obj->ReadXML($reader);
				$this->__set('stopArea', $obj);
				break;
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
