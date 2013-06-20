<?php

class tx_icslibnavitia_StopPoint extends tx_icslibnavitia_Node {
	static $fields = array(
		'idx' => 'int',
		'id' => 'int',
		'name' => 'string',
		'externalCode' => 'string',
		// 'address' => 'object:', 
		'equipment' => 'object:tx_icslibnavitia_Equipment', 
		'mode' => 'object:tx_icslibnavitia_Mode', 
		'city' => 'object:tx_icslibnavitia_City', 
		'stopArea' => 'object:tx_icslibnavitia_StopArea?', 
		'coord' => 'object:tx_icslibnavitia_Coord?', 
		'comment' => 'object:tx_icslibnavitia_Comment?',
		'impactPosList' => 'array',
	);

	public function __construct() {
		parent::__construct(get_class($this) . '::$fields');
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
			case 'Equipment':
				$obj = t3lib_div::makeInstance('tx_icslibnavitia_Equipment');
				$obj->ReadXML($reader);
				$this->__set('equipment', $obj);
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
			case 'Comment':
				$obj = t3lib_div::makeInstance('tx_icslibnavitia_Comment');
				$obj->ReadXML($reader);
				$this->__set('comment', $obj);
				break;
			case 'ImpactPosList':
				$impacts = array();
				if (!$reader->isEmptyElement) {
					$reader->read();
					while ($reader->nodeType != XMLReader::END_ELEMENT) {
						if ($reader->nodeType == XMLReader::ELEMENT) {
							if ($reader->name == 'ImpactPos') {
								$impacts[] = (int)$reader->readString();
							}
							tx_icslibnavitia_Node::SkipChildren($reader);
						}
						$reader->read();
					}
				}
				$this->__set('impactPosList', $impacts);
				break;
			default:
				tx_icslibnavitia_Node::SkipChildren($reader);
		}
	}
	
	public function __toString() {
		return get_class($this);
	}
}
