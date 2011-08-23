<?php

class tx_icslibnavitia_Section extends tx_icslibnavitia_Node {
	static $fields = array(
		'type' => 'string',
		'key' => 'string',
		'length' => 'int',
		'duration' => 'object:tx_icslibnavitia_Time?',
		'departure' => 'object:tx_icslibnavitia_EntryPointPlanJourney?',
		'arrival' => 'object:tx_icslibnavitia_EntryPointPlanJourney?',
		// 'vehicleJourney' => 'object:',
		'nota' => 'object:tx_icslibnavitia_Nota',
		// 'fareSectionList' => '',
		// 'fareZoneList' => '',
		// 'freqSetting' => '',
	);

	public function __construct() {
		parent::__construct(get_class($this) . '::$fields');
	}
	
	public function ReadXML(XMLReader $reader) {
		trigger_error('Not implemented', E_USER_NOTICE);
	}
	
	public function __toString() {
		return get_class($this);
	}
}
