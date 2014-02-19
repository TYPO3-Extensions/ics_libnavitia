<?php

class tx_icslibnavitia_Equipment extends tx_icslibnavitia_Node {
	static $fields = array(
		'mipAccess' => 'bool',// and others
	);

	public function __construct() {
		parent::__construct(get_class($this) . '::$fields');
	}
	
	public function ReadXML(XMLReader $reader) {
		$this->_ReadXML($reader, 'Equipment');
	}
	
	protected function ReadAttribute(XMLReader $reader) {
		switch ($reader->name) {
			case 'MIPAccess':
				$this->__set('mipAccess', $reader->value == 'True');
				break;
		}
	}

	protected function ReadElement(XMLReader $reader) {
	}
	
	public function __toString() {
		return get_class($this);
	}
}

if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/ics_libnavitia/class.tx_icslibnavitia_Equipment.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/ics_libnavitia/class.tx_icslibnavitia_Equipment.php']);
}