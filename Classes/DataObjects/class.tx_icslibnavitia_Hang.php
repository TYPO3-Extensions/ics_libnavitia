<?php

class tx_icslibnavitia_Hang extends tx_icslibnavitia_Node {
	static $fields = array(
		'stopPointIdx' => 'int',
		'duration' => 'int',
		'kind' => 'int',
		'stopPointExternalCode' => 'string',
	);

	public function __construct() {
		parent::__construct(get_class($this) . '::$fields');
	}
	
	public function ReadXML(XMLReader $reader) {
		$this->_ReadXML($reader, 'Hang');
	}
	
	protected function ReadAttribute(XMLReader $reader) {
		switch ($reader->name) {
			case 'StopPointIdx':
				$this->__set('stopPointIdx', (int)$reader->value);
				break;
			case 'Duration':
				$this->__set('duration', (int)$reader->value);
				break;
			case 'ConnectionKind':
				$this->__set('kind', (int)$reader->value);
				break;
			case 'StopPointExternalCode':
				$this->__set('stopPointExternalCode', $reader->value);
				break;
		}
	}

	protected function ReadElement(XMLReader $reader) {
		tx_icslibnavitia_Node::SkipChildren($reader);
	}
	
	public function __toString() {
		return get_class($this);
	}
}

if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/ics_libnavitia/class.tx_icslibnavitia_Hang.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/ics_libnavitia/class.tx_icslibnavitia_Hang.php']);
}