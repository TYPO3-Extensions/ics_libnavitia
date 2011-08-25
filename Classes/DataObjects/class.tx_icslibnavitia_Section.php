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
		$this->_ReadXML($reader, 'Section');
	}
	
	protected function ReadAttribute(XMLReader $reader) {
		switch ($reader->name) {
			case 'Type':
				$this->__set('type', $reader->value);
				break;
			case 'SectionKey':
				$this->__set('key', $reader->value);
				break;
			case 'SectionLength':
				$this->__set('length', (int)$reader->value);
				break;
		}
	}

	protected function ReadElement(XMLReader $reader) {
		switch ($reader->name) {
			case 'Duration':
				$obj = t3lib_div::makeInstance('tx_icslibnavitia_Time');
				$obj->ReadXML($reader);
				$this->__set('duration', $obj);
				break;
			case 'Departure':
				$obj = t3lib_div::makeInstance('tx_icslibnavitia_EntryPointPlanJourney');
				$obj->ReadXML($reader);
				$this->__set('departure', $obj);
				break;
			case 'Arrival':
				$obj = t3lib_div::makeInstance('tx_icslibnavitia_EntryPointPlanJourney');
				$obj->ReadXML($reader);
				$this->__set('arrival', $obj);
				break;
			case 'Nota':
				$obj = t3lib_div::makeInstance('tx_icslibnavitia_Nota');
				$obj->ReadXML($reader);
				$this->__set('nota', $obj);
				break;
			default:
				$this->SkipChildren($reader);
		}
	}
	
	public function __toString() {
		return get_class($this);
	}
}
