<?php

class tx_icslibnavitia_Stop extends tx_icslibnavitia_Node {
	static $fields = array(
		'idx' => 'int',
		'pointIdx' => 'int',
		'vehicleJourneyIdx' => 'int',
		'vehicleJourneyExternalCode' => 'string',
		'hour' => 'int',
		'minute' => 'int',
		'destination' => 'int', // TODO: Add support for providing destination object.
		'validityPatternPos' => 'int',
		'order' => 'int',
		'vehicleIdx' => 'int',
		'stopTime' => 'object:tx_icslibnavitia_Time',
		'stopArrivalTime' => 'object:tx_icslibnavitia_Time',
		'stopPoint' => 'object:tx_icslibnavitia_StopPoint?',
		'stopArea' => 'object:tx_icslibnavitia_StopArea?',
		// 'VehicleJourney' => 'object:?',
		// 'route' => 'object:?',
		'vehicleJourneyNameAtStop' => 'string',
	);

	public function __construct() {
		parent::__construct(get_class($this) . '::$fields');
		// impactposlist
	}
	
	public function ReadXML(XMLReader $reader) {
		trigger_error('Not implemented', E_USER_NOTICE);
	}
	
	public function __toString() {
		return get_class($this);
	}
}
