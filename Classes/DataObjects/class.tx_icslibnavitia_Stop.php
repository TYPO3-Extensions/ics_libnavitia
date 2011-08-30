<?php

class tx_icslibnavitia_Stop extends tx_icslibnavitia_Node {
	static $fields = array(
		'idx' => 'int',
		'pointIdx' => 'int',
		'vehicleJourneyIdx' => 'int',
		'vehicleJourneyExternalCode' => 'string',
		'hour' => 'int',
		'minute' => 'int',
		'destination' => 'int',
		'validityPatternPos' => 'int',
		'order' => 'int',
		'vehicleIdx' => 'int',
		'stopTime' => 'object:tx_icslibnavitia_Time',
		'stopArrivalTime' => 'object:tx_icslibnavitia_Time',
		'stopPoint' => 'object:tx_icslibnavitia_StopPoint?',
		'stopArea' => 'object:tx_icslibnavitia_StopArea?',
		'vehicleJourney' => 'object:tx_icslibnavitia_VehicleJourney?',
		'route' => 'object:tx_icslibnavitia_Route?',
		'vehicleJourneyNameAtStop' => 'string',
	);

	public function __construct() {
		parent::__construct(get_class($this) . '::$fields');
		// impactposlist
	}
	
	public function ReadXML(XMLReader $reader) {
		$this->_ReadXML($reader, 'Stop');
	}
	
	protected function ReadInit() {
		parent::ReadInit();
	}
	
	protected function ReadAttribute(XMLReader $reader) {
		switch ($reader->name) {
			case 'StopIdx':
				$this->__set('idx', (int)$reader->value);
				break;
			case 'StopPointIdx':
				$this->__set('pointIdx', (int)$reader->value);
				break;
			case 'VehicleJourneyIdx':
				$this->__set('vehicleJourneyIdx', (int)$reader->value);
				break;
			case 'VehicleJourneyExternalCode':
				$this->__set('vehicleJourneyExternalCode', $reader->value);
				break;
			case 'HourNumber':
				$this->__set('hour', (int)$reader->value);
				break;
			case 'MinuteNumber':
				$this->__set('minute', (int)$reader->value);
				break;
			case 'DestinationPos':
				$this->__set('destination', (int)$reader->value);
				break;
			case 'ValidityPatternSetCommentPos':
				$this->__set('validityPatternPos', (int)$reader->value);
				break;
			case 'StopOrder':
				$this->__set('order', (int)$reader->value);
				break;
			case 'VehicleIdx':
				$this->__set('vehicleIdx', (int)$reader->value);
				break;
		}
	}

	protected function ReadElement(XMLReader $reader) {
		switch ($reader->name) {
			case 'StopTime':
				$obj = t3lib_div::makeInstance('tx_icslibnavitia_Time');
				$obj->ReadXML($reader);
				$this->__set('stopTime', $obj);
				break;
			case 'StopArrivalTime':
				$obj = t3lib_div::makeInstance('tx_icslibnavitia_Time');
				$obj->ReadXML($reader);
				$this->__set('stopArrivalTime', $obj);
				break;
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
			case 'VehicleJourney':
				$obj = t3lib_div::makeInstance('tx_icslibnavitia_VehicleJourney');
				$obj->ReadXML($reader);
				$this->__set('vehicleJourney', $obj);
				break;
			case 'Route':
				$obj = t3lib_div::makeInstance('tx_icslibnavitia_Route');
				$obj->ReadXML($reader);
				$this->__set('route', $obj);
				break;
			case 'VehicleJourneyNameAtStop':
				$this->__set('vehicleJourneyNameAtStop', $reader->readString());
				tx_icslibnavitia_Node::SkipChildren($reader);
				break;
			default:
				tx_icslibnavitia_Node::SkipChildren($reader);
		}
	}
	
	public function __toString() {
		return get_class($this);
	}
}
