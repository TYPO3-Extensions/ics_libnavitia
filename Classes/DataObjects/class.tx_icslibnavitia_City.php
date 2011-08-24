<?php

class tx_icslibnavitia_City extends tx_icslibnavitia_Node {
	static $fields = array(
		'idx' => 'int',
		'id' => 'int',
		'name' => 'string',
		'externalCode' => 'string',
		'coord' => 'object:tx_icslibnavitia_Coord?',
	);

	public function __construct() {
		parent::__construct(get_class($this) . '::$fields');
	}
	
	public function ReadXML(XMLReader $reader) {
		$this->_ReadXML($reader, 'City');
	}
	
	protected function ReadAttribute(XMLReader $reader) {
		switch ($reader->name) {
			case 'CityExternalCode':
				$this->__set('externalCode', $reader->value);
				break;
			case 'CityName':
				$this->__set('name', $reader->value);
				break;
			case 'CityId':
				$this->__set('id', (int)$reader->value);
				break;
			case 'CityIdx':
				$this->__set('idx', (int)$reader->value);
				break;
		}
	}

	protected function ReadElement(XMLReader $reader) {
		switch ($reader->name) {
			case 'Coord':
				if (!$reader->isEmptyElement) {
					$obj = t3lib_div::makeInstance('tx_icslibnavitia_Coord');
					$obj->ReadXML($reader);
					$this->__set('coord', $obj);
				}
				break;
			default:
				$this->SkipChildren($reader);
		}
	}
	
	public function __toString() {
		return get_class($this);
	}
}
