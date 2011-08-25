<?php

class tx_icslibnavitia_EntryPointPlanJourney extends tx_icslibnavitia_Node {
	static $fields = array(
		'type' => 'string',
		'stopIdx' => 'int',
		'stopOrder' => 'int',
		'commentPos' => 'int',
		'estimatedTime' => 'bool',
		'dateTime' => 'object:DateTime',
		'coord' => 'object:tx_icslibnavitia_Coord?',
		'stopPoint' => 'object:tx_icslibnavitia_StopPoint?',
		'stopArea' => 'object:tx_icslibnavitia_StopArea?',
		'site' => 'object:tx_icslibnavitia_Site?',
		'address' => 'object:tx_icslibnavitia_Address?',
		'city' => 'object:tx_icslibnavitia_City?',
		'undefined' => 'object:tx_icslibnavitia_Undefined?',
		// 'comment' => 'object:',
	);

	public function __construct() {
		parent::__construct(get_class($this) . '::$fields');
	}
	
	private $dateTime;
	
	public function ReadXML(XMLReader $reader) {
		$this->dateTime = array('date' => array('y' => 0, 'm' => 0, 'd' => 0), 'time' => array('h' => 0, 'm' => 0, 's' => 0));
		$this->_ReadXML($reader, false);
		$v = $this->dateTime;
		unset($this->dateTime);
		$this->__set('dateTime', new DateTime(date('c', mktime($v['time']['h'], $v['time']['m'], $v['time']['s'], $v['date']['m'], $v['date']['d'], $v['date']['y']))));
	}
	
	protected function ReadAttribute(XMLReader $reader) {
		switch ($reader->name) {
			case 'Type':
				$this->__set('type', $reader->value);
				break;
			case 'StopIdx':
				$this->__set('stopIdx', (int)$reader->value);
				break;
			case 'StopOrder':
				$this->__set('stopOrder', (int)$reader->value);
				break;
			case 'CommentPos':
				$this->__set('commentPos', (int)$reader->value);
				break;
			case 'EstimatedTime':
				$this->__set('estimatedTime', (bool)$reader->value);
				break;
		}
	}

	protected function ReadElement(XMLReader $reader) {
		switch ($reader->name) {
			case 'EntryPointDate':
				$this->ReadDate($reader, $this->dateTime['date']);
				break;
			case 'EntryPointTime':
				$this->ReadTime($reader, $this->dateTime['time']);
				break;
			case 'Coord':
				if (strlen($reader->readString()) > 0) {
					$obj = t3lib_div::makeInstance('tx_icslibnavitia_Coord');
					$obj->ReadXML($reader);
					$this->__set('coord', $obj);
				}
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
			case 'Site':
				$obj = t3lib_div::makeInstance('tx_icslibnavitia_Site');
				$obj->ReadXML($reader);
				$this->__set('site', $obj);
				break;
			case 'Address':
				$obj = t3lib_div::makeInstance('tx_icslibnavitia_Address');
				$obj->ReadXML($reader);
				$this->__set('address', $obj);
				break;
			case 'City':
				$obj = t3lib_div::makeInstance('tx_icslibnavitia_City');
				$obj->ReadXML($reader);
				$this->__set('city', $obj);
				break;
			case 'Undefined':
				$obj = t3lib_div::makeInstance('tx_icslibnavitia_Undefined');
				$obj->ReadXML($reader);
				$this->__set('undefined', $obj);
				break;
			default:
				$this->SkipChildren($reader);
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
					$this->SkipChildren($reader);
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
					$this->SkipChildren($reader);
				}
				$reader->read();
			}
		}
	}
	
	public function __toString() {
		return get_class($this);
	}
}
