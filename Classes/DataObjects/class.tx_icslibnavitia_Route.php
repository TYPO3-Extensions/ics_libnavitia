<?php

class tx_icslibnavitia_Route extends tx_icslibnavitia_Node {
	static $fields = array(
		'id' => 'int',
		'idx' => 'int',
		'name' => 'string',
		'externalCode' => 'string',
		'forward' => 'bool',
		'lineIdx' => 'int',
		'frequence' => 'bool',
		'adapted' => 'bool',
		// 'comment' => 'object:tx_icslibnavitia_?',
		'line' => 'object:tx_icslibnavitia_Line?',
	);

	public function __construct() {
		parent::__construct(get_class($this) . '::$fields');
		$this->values['routePointList'] = t3lib_div::makeInstance('tx_icslibnavitia_NodeList', 'tx_icslibnavitia_RoutePoint');
		// impactposlist
	}
	
	public function ReadXML(XMLReader $reader) {
		$this->_ReadXML($reader, 'Route');
	}
	
	protected function ReadInit() {
		parent::ReadInit();
		$this->values['routePointList']->Clear();
	}
	
	protected function ReadAttribute(XMLReader $reader) {
		switch ($reader->name) {
			case 'RouteId':
				$this->__set('id', (int)$reader->value);
				break;
			case 'RouteIdx':
				$this->__set('idx', (int)$reader->value);
				break;
			case 'RouteName':
				$this->__set('name', $reader->value);
				break;
			case 'RouteExternalCode':
				$this->__set('externalCode', $reader->value);
				break;
			case 'IsForward':
				$this->__set('forward', (bool)$reader->value);
				break;
			case 'RouteLineIdx':
				$this->__set('lineIdx', (int)$reader->value);
				break;
			case 'IsFrequence':
				$this->__set('frequence', (bool)$reader->value);
				break;
			case 'IsAdapted':
				$this->__set('adapted', (bool)$reader->value);
				break;
		}
	}

	protected function ReadElement(XMLReader $reader) {
		switch ($reader->name) {
			case 'RoutePointList':
				$this->ReadList($reader, $this->values['routePointList'], array('RoutePoint' => 'tx_icslibnavitia_RoutePoint'));
				break;
			case 'Line':
				$obj = t3lib_div::makeInstance('tx_icslibnavitia_Line');
				$obj->ReadXML($reader);
				$this->__set('line', $obj);
				break;
			default:
				$this->SkipChildren($reader);
		}
	}
	
	public function __toString() {
		return get_class($this);
	}
}
