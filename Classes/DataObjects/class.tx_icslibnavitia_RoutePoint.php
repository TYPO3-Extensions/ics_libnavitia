<?php

class tx_icslibnavitia_RoutePoint extends tx_icslibnavitia_Node {
	static $fields = array(
		'idx' => 'int',
		'id' => 'int',
		'externalCode' => 'string',
		'routeIdx' => 'int',
		'main' => 'string',
		'stopPoint' => 'object:tx_icslibnavitia_StopPoint',
		'stopArea' => 'object:tx_icslibnavitia_StopArea?',
	);

	public function __construct() {
		parent::__construct(get_class($this) . '::$fields');
	}
	
	public function ReadXML(XMLReader $reader) {
		$this->_ReadXML($reader, 'RoutePoint');
	}
	
	protected function ReadInit() {
		parent::ReadInit();
	}
	
	protected function ReadAttribute(XMLReader $reader) {
		switch ($reader->name) {
			case 'RoutePointId':
				$this->__set('id', (int)$reader->value);
				break;
			case 'RoutePointIdx':
				$this->__set('idx', (int)$reader->value);
				break;
			case 'RoutePointExternalCode':
				$this->__set('externalCode', $reader->value);
				break;
			case 'RouteIdx':
				$this->__set('routeIdx', (int)$reader->value);
				break;
			case 'MainStopPoint':
				$this->__set('main', $reader->value);
				break;
		}
	}

	protected function ReadElement(XMLReader $reader) {
		switch ($reader->name) {
			case 'StopPoint':
				$obj = t3lib_div::makeInstance('tx_icslibnavitia_StopPoint');
				$obj->ReadXML($reader);
				$this->__set('stopPoint', $obj);
				break;
			case 'StopArea':
				$obj = t3lib_div::makeInstance('tx_icslibnavitia_StopArea');
				$obj->ReadXML($reader);
				$this->__set('stopArea', $obj);
				break;
			default:
				tx_icslibnavitia_Node::SkipChildren($reader);
		}
	}
	
	public function __toString() {
		return get_class($this);
	}
}

if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/ics_libnavitia/class.tx_icslibnavitia_RoutePoint.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/ics_libnavitia/class.tx_icslibnavitia_RoutePoint.php']);
}