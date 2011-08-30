<?php

class tx_icslibnavitia_VehicleJourney extends tx_icslibnavitia_Node {
	static $fields = array(
		'id' => 'int',
		'idx' => 'int',
		'name' => 'string',
		'externalCode' => 'string',
		'routeIdx' => 'int',
		'adapted' => 'bool',
		'extrapolated' => 'bool',
		'route' => 'object:tx_icslibnavitia_Route?',
		'destination' => 'object:tx_icslibnavitia_StopArea?',
		'origin' => 'object:tx_icslibnavitia_StopArea?',
		'mode' => 'object:tx_icslibnavitia_Mode',
		// 'company' => 'object:tx_icslibnavitia_',
		// 'vehicle' => 'object:tx_icslibnavitia_',
		// 'validityPattern' => 'object:tx_icslibnavitia_',
	);

	public function __construct() {
		parent::__construct(get_class($this) . '::$fields');
		$this->values['stopList'] = t3lib_div::makeInstance('tx_icslibnavitia_NodeList', 'tx_icslibnavitia_Stop');
		// impactposlist
	}
	
	public function ReadXML(XMLReader $reader) {
		$this->_ReadXML($reader, 'VehicleJourney');
	}
	
	protected function ReadInit() {
		parent::ReadInit();
		$this->values['stopList']->Clear();
	}
	
	protected function ReadAttribute(XMLReader $reader) {
		switch ($reader->name) {
			case 'VehicleJourneyId':
				$this->__set('id', (int)$reader->value);
				break;
			case 'VehicleJourneyIdx':
				$this->__set('idx', (int)$reader->value);
				break;
			case 'VehicleJourneyName':
				$this->__set('name', $reader->value);
				break;
			case 'VehicleJourneyExternalCode':
				$this->__set('externalCode', $reader->value);
				break;
			case 'VehicleJourneyRouteIdx':
				$this->__set('routeIdx', (int)$reader->value);
				break;
			case 'IsAdapted':
				$this->__set('adapted', (bool)$reader->value);
				break;
			case 'IsExtrapolated':
				$this->__set('extrapolated', (bool)$reader->value);
				break;
		}
	}

	protected function ReadElement(XMLReader $reader) {
		switch ($reader->name) {
			case 'Route':
				$obj = t3lib_div::makeInstance('tx_icslibnavitia_Route');
				$obj->ReadXML($reader);
				$this->__set('route', $obj);
				break;
			case 'Destination':
				$this->ReadOrigDest($reader, 'destination');
				break;
			case 'Origin':
				$this->ReadOrigDest($reader, 'origin');
				break;
			case 'Mode':
				$obj = t3lib_div::makeInstance('tx_icslibnavitia_Mode');
				$obj->ReadXML($reader);
				$this->__set('mode', $obj);
				break;
			case 'StopList':
				tx_icslibnavitia_Node::ReadList($reader, $this->values['stopList'], array('Stop', 'tx_icslibnavitia_Stop'));
				break;
			default:
				tx_icslibnavitia_Node::SkipChildren($reader);
		}
	}
	
	private function ReadOrigDest(XMLReader $reader, $fieldname) {
		if (!$reader->isEmptyElement) {
			$reader->read();
			while ($reader->nodeType != XMLReader::END_ELEMENT) {
				if ($reader->nodeType == XMLReader::ELEMENT) {
					if ($reader->name == 'StopArea') {
						$obj = t3lib_div::makeInstance('tx_icslibnavitia_StopArea');
						$obj->ReadXML($reader);
						$this->__set($fieldname, $obj);
					}
					else
						tx_icslibnavitia_Node::SkipChildren($reader);
				}
				$reader->read();
			}
		}
	}
	
	public function __toString() {
		return get_class($this);
	}
}
