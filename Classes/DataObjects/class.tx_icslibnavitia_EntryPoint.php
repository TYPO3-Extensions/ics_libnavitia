<?php

class tx_icslibnavitia_EntryPoint extends tx_icslibnavitia_Node {
	static $fields = array(
		'name' => 'string',
		'cityName' => 'string',
		'type' => 'string',
		'quality' => 'int',
		'number' => 'string',
		'typeName' => 'string',
		'stopArea' => 'object:tx_icslibnavitia_StopArea?',
		'site' => 'object:tx_icslibnavitia_Site?',
		'address' => 'object:tx_icslibnavitia_Address?',
		'city' => 'object:tx_icslibnavitia_City?',
		'coord' => 'object:tx_icslibnavitia_Coord?',
	);

	public function __construct() {
		parent::__construct(get_class($this) . '::$fields');
		$this->values['hangList'] = t3lib_div::makeInstance('tx_icslibnavitia_NodeList', 'tx_icslibnavitia_Hang');
	}
	
	public function ReadXML(XMLReader $reader) {
		if (($reader->nodeType != XMLReader::ELEMENT) || ($reader->name != 'EntryPoint')) {
			$trace = debug_backtrace();
			trigger_error(
				'Unexpected XMLReader context, expected an EntryPoint element,' .
				' found node { type = ' . $reader->nodeType . '; name = ' . $reader->name . ' }' .
				' in ' . $trace[0]['file'] .
				' on line ' . $trace[0]['line'],
				E_USER_ERROR);
			return;
		}
		foreach (self::$fields as $fieldname => $type) {
			$this->setDefaultValue($fieldname, $type);
		}
		$this->values['hangList']->Clear();
		while ($reader->moveToNextAttribute()) {
			$this->ReadAttribute($reader);
		}
		$reader->moveToElement();
		if (!$reader->isEmptyElement) {
			do {
				$reader->read();
				if ($reader->nodeType == ELEMENT) {
					$this->ReadElement($reader);
					continue;
				}
			}
			while ($reader->nodeType != XMLReader::END_ELEMENT);
		}
	}
	
	protected function ReadAttribute(XMLReader $reader) {
		switch ($reader->name) {
			case 'EntryPointType':
				$this->__set('type', $reader->value);
				break;
			case 'EntryPointResponseQuality':
				$this->__set('quality', parseInt($reader->value));
				break;
			case 'CityName':
				$this->__set('cityName', $reader->value);
				break;
			case 'Number':
				$this->__set('number', $reader->value);
				break;
			case 'TypeName':
				$this->__set('typeName', $reader->value);
				break;
			case 'EntryPointName':
				$this->__set('name', $reader->value);
				break;
		}
	}

	protected function ReadElement(XMLReader $reader) {
		switch ($reader->name) {
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
			case 'Coord':
				if (!$reader->isEmptyElement) {
					$obj = t3lib_div::makeInstance('tx_icslibnavitia_Coord');
					$obj->ReadXML($reader);
					$this->__set('coord', $obj);
				}
				break;
			case 'HangList':
				$this->ReadHangList($reader);
				break;
		}
	}
	
	private function ReadHangList(XMLReader $reader) {
		$this->SkipChildren($reader); // TODO: Read the children really.
	}
	
	public function __toString() {
		return get_class($this);
	}
}
