<?php

class tx_icslibnavitia_Proximity extends tx_icslibnavitia_Node {
	static $fields = array(
		'distance' => 'integer',
		'stopArea' => 'object:tx_icslibnavitia_StopArea?',
		'stopPoint' => 'object:tx_icslibnavitia_StopPoint?',
		'site' => 'object:tx_icslibnavitia_Site?',
	);

	public function __construct() {
		parent::__construct(get_class($this) . '::$fields');
	}
	
	public function ReadXML(XMLReader $reader) {
		$this->_ReadXML($reader, 'Proximity');
	}
	
	protected function ReadInit() {
		parent::ReadInit();
	}
	
	protected function ReadAttribute(XMLReader $reader) {
		switch ($reader->name) {
			case 'distance':
				$this->__set('distance', (int)$reader->value);
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
			case 'StopPoint':
				$obj = t3lib_div::makeInstance('tx_icslibnavitia_StopPoint');
				$obj->ReadXML($reader);
				$this->__set('stopPoint', $obj);
				break;
			case 'Site':
				$obj = t3lib_div::makeInstance('tx_icslibnavitia_Site');
				$obj->ReadXML($reader);
				$this->__set('site', $obj);
				break;
			default:
				tx_icslibnavitia_Node::SkipChildren($reader);
		}
	}
	
	public function __toString() {
		return get_class($this);
	}
}
