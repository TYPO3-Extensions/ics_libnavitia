<?php

class tx_icslibnavitia_CallValue extends tx_icslibnavitia_Node {
	static $fields = array(
		'sens' => 'int',
		'dateTime' => 'object:DateTime',
		'criteria' => 'int',
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
	}

	protected function ReadElement(XMLReader $reader) {
		switch ($reader->name) {
			case 'Sens':
				$this->__set('sens', (int)$reader->readString());
				break;
			case 'Year':
				$this->dateTime['date']['y'] = (int)$reader->readString();
				break;
			case 'Month':
				$this->dateTime['date']['m'] = (int)$reader->readString();
				break;
			case 'Day':
				$this->dateTime['date']['d'] = (int)$reader->readString();
				break;
			case 'Hour':
				$this->dateTime['time']['h'] = (int)$reader->readString();
				break;
			case 'Minute':
				$this->dateTime['time']['m'] = (int)$reader->readString();
				break;
			case 'Second':
				$this->dateTime['time']['s'] = (int)$reader->readString();
				break;
			case 'Criteria':
				$this->__set('criteria', (int)$reader->readString());
				break;
		}
		tx_icslibnavitia_Node::SkipChildren($reader);
	}
	
	public function __toString() {
		return get_class($this);
	}
}

if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/ics_libnavitia/class.tx_icslibnavitia_CallValue.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/ics_libnavitia/class.tx_icslibnavitia_CallValue.php']);
}