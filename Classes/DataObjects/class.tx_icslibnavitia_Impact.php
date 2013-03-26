<?php

class tx_icslibnavitia_Impact extends tx_icslibnavitia_Node {
	static $fields = array(
		'id' => 'int',
		'mon' => 'bool',
		'tue' => 'bool',
		'wed' => 'bool',
		'thu' => 'bool',
		'fri' => 'bool',
		'sat' => 'bool',
		'sun' => 'bool',
		'stopPoint' => 'object:tx_icslibnavitia_StopPoint?',
		'stopArea' => 'object:tx_icslibnavitia_StopArea?',
		'vehiculeJourney' => 'object:tx_icslibnavitia_VehiculeJourney?',
		'line' => 'object:tx_icslibnavitia_Line?',
		'network' => 'object:tx_icslibnavitia_Network?',
		'route' => 'object:tx_icslibnavitia_Route?',
		'routePoint' => 'object:tx_icslibnavitia_RoutePoint?',
		'state' => 'string',
		'startDate' => 'string',
		'endDate' => 'string',
		'dailyStartTime' => 'string',
		'dailyEndTime' => 'string',
		'closeDate' => 'string',
		'duration' => 'int',
		'message' => 'string',
		'event' => 'object:tx_icslibnavitia_Event?',
	);

	public function __construct() {
		parent::__construct(get_class($this) . '::$fields');
	}
	
	public function ReadXML(XMLReader $reader) {
		$this->_ReadXML($reader, 'Impact');
	}
	
	protected function ReadAttribute(XMLReader $reader) {
		switch ($reader->name) {
			case 'ImpactID':
				$this->__set('id', (int)$reader->value);
				break;
			case 'Mon':
				$this->__set('mon', (bool)$reader->value);
				break;
			case 'Tue':
				$this->__set('tue', (bool)$reader->value);
				break;
			case 'Wed':
				$this->__set('wed', (bool)$reader->value);
				break;
			case 'Thu':
				$this->__set('thu', (bool)$reader->value);
				break;
			case 'Fri':
				$this->__set('fri', (bool)$reader->value);
				break;
			case 'Sat':
				$this->__set('sat', (bool)$reader->value);
				break;
			case 'Sun':
				$this->__set('sun', (bool)$reader->value);
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
			case 'VehiculeJourney':
				$obj = t3lib_div::makeInstance('tx_icslibnavitia_VehiculeJourney');
				$obj->ReadXML($reader);
				$this->__set('vehiculeJourney', $obj);
				break;
			case 'Line':
				$obj = t3lib_div::makeInstance('tx_icslibnavitia_Line');
				$obj->ReadXML($reader);
				$this->__set('line', $obj);
				break;
			case 'Network':
				$obj = t3lib_div::makeInstance('tx_icslibnavitia_Network');
				$obj->ReadXML($reader);
				$this->__set('network', $obj);
				break;
			case 'Route':
				$obj = t3lib_div::makeInstance('tx_icslibnavitia_Route');
				$obj->ReadXML($reader);
				$this->__set('route', $obj);
				break;
			case 'RoutePoint':
				$obj = t3lib_div::makeInstance('tx_icslibnavitia_RoutePoint');
				$obj->ReadXML($reader);
				$this->__set('routePoint', $obj);
				break;
			case 'ImpactState':
				$this->__set('state', $reader->readString());
				tx_icslibnavitia_Node::SkipChildren($reader);
				break;
			case 'ImpactStartDate':
				$this->__set('startDate', $reader->readString());
				tx_icslibnavitia_Node::SkipChildren($reader);
				break;
			case 'ImpactEndDate':
				$this->__set('endDate', $reader->readString());
				tx_icslibnavitia_Node::SkipChildren($reader);
				break;
			case 'ImpactDailyStartTime':
				$this->__set('dailyStartTime', $reader->readString());
				tx_icslibnavitia_Node::SkipChildren($reader);
				break;
			case 'ImpactDailyEndTime':
				$this->__set('dailyEndTime', $reader->readString());
				tx_icslibnavitia_Node::SkipChildren($reader);
				break;
			case 'ImpactCloseDate':
				$this->__set('closeDate', $reader->readString());
				tx_icslibnavitia_Node::SkipChildren($reader);
				break;
			case 'ImpactDuration':
				$this->__set('duration', (int)$reader->readString());
				tx_icslibnavitia_Node::SkipChildren($reader);
				break;
			case 'ImpactMessage':
				$this->__set('message', $reader->readString());
				tx_icslibnavitia_Node::SkipChildren($reader);
				break;
			case 'Event':
				$obj = t3lib_div::makeInstance('tx_icslibnavitia_Event');
				$obj->ReadXML($reader);
				$this->__set('event', $obj);
				break;
			default:
				tx_icslibnavitia_Node::SkipChildren($reader);
		}
	}
	
	public function __toString() {
		return get_class($this);
	}
}
