<?php

class tx_icslibnavitia_Summary extends tx_icslibnavitia_Node {
	static $fields = array(
		'interchange' => 'int',
		'key' => 'string',
		'departureEstimatedTime' => 'bool',
		'arrivalEstimatedTime' => 'bool',
		'departure' => 'object:DateTime',
		'arrival' => 'object:DateTime',
		'duration' => 'object:tx_icslibnavitia_Time',
		'linkTime' => 'object:tx_icslibnavitia_Time',
		'waitTime' => 'object:tx_icslibnavitia_Time',
		'call' => 'object:tx_icslibnavitia_Call',
	);

	public function __construct() {
		parent::__construct(get_class($this) . '::$fields');
	}
	
	private $departure;
	private $arrival;
	
	public function ReadXML(XMLReader $reader) {
		$this->departure = array('date' => array('y' => 0, 'm' => 0, 'd' => 0), 'time' => array('h' => 0, 'm' => 0, 's' => 0));
		$this->arrival = array('date' => array('y' => 0, 'm' => 0, 'd' => 0), 'time' => array('h' => 0, 'm' => 0, 's' => 0));
		$this->_ReadXML($reader, 'Summary');
		$v = $this->departure;
		unset($this->departure);
		$this->__set('departure', new DateTime(date('c', mktime($v['time']['h'], $v['time']['m'], $v['time']['s'], $v['date']['m'], $v['date']['d'], $v['date']['y']))));
		$v = $this->arrival;
		unset($this->arrival);
		$this->__set('arrival', new DateTime(date('c', mktime($v['time']['h'], $v['time']['m'], $v['time']['s'], $v['date']['m'], $v['date']['d'], $v['date']['y']))));
	}
	
	protected function ReadAttribute(XMLReader $reader) {
		switch ($reader->name) {
			case 'Interchange':
				$this->__set('interchange', (int)$reader->value);
				break;
			case 'JourneyKey':
				$this->__set('key', $reader->value);
				break;
			case 'DepartureEstimatedTime':
				$this->__set('departureEstimatedTime', (bool)$reader->value);
				break;
			case 'ArrivalEstimatedTime':
				$this->__set('arrivalEstimatedTime', (bool)$reader->value);
				break;
		}
	}

	protected function ReadElement(XMLReader $reader) {
		switch ($reader->name) {
			case 'DepartureDate':
				$this->ReadDate($reader, $this->departure['date']);
				break;
			case 'DepartureTime':
				$this->ReadTime($reader, $this->departure['time']);
				break;
			case 'ArrivalDate':
				$this->ReadDate($reader, $this->arrival['date']);
				break;
			case 'ArrivalTime':
				$this->ReadTime($reader, $this->arrival['time']);
				break;
			case 'Duration':
				$obj = t3lib_div::makeInstance('tx_icslibnavitia_Time');
				$obj->ReadXML($reader);
				$this->__set('duration', $obj);
				break;
			case 'TotalLinkTime':
				$obj = t3lib_div::makeInstance('tx_icslibnavitia_Time');
				$obj->ReadXML($reader);
				$this->__set('linkTime', $obj);
				break;
			case 'TotalWaitTime':
				$obj = t3lib_div::makeInstance('tx_icslibnavitia_Time');
				$obj->ReadXML($reader);
				$this->__set('waitTime', $obj);
				break;
			case 'Call':
				$obj = t3lib_div::makeInstance('tx_icslibnavitia_Call');
				$obj->ReadXML($reader);
				$this->__set('call', $obj);
				break;
			default:
				tx_icslibnavitia_Node::SkipChildren($reader);
		}
	}
	
	private function ReadDate(XMLReader $reader, &$dateDef) {
		if (!$reader->isEmptyElement) {
			$reader->read();
			while ($reader->nodeType != XMLReader::END_ELEMENT) {
				if ($reader->nodeType == XMLReader::ELEMENT) {
					switch ($reader->name) {
						case 'Year':
							$dateDef['y'] = (int)$reader->readString();
							break;
						case 'Month':
							$dateDef['m'] = (int)$reader->readString();
							break;
						case 'Day':
							$dateDef['d'] = (int)$reader->readString();
							break;
					}
					tx_icslibnavitia_Node::SkipChildren($reader);
				}
				$reader->read();
			}
		}
	}
	
	private function ReadTime(XMLReader $reader, &$timeDef) {
		if (!$reader->isEmptyElement) {
			$reader->read();
			while ($reader->nodeType != XMLReader::END_ELEMENT) {
				if ($reader->nodeType == XMLReader::ELEMENT) {
					switch ($reader->name) {
						case 'Hour':
							$timeDef['h'] = (int)$reader->readString();
							break;
						case 'Minute':
							$timeDef['m'] = (int)$reader->readString();
							break;
						case 'Second':
							$timeDef['s'] = (int)$reader->readString();
							break;
					}
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
