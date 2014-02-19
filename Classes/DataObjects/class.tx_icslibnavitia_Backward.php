<?php

class tx_icslibnavitia_Backward extends tx_icslibnavitia_Node {
	static $fields = array(
		'name' => 'string',
		'direction' => 'object:tx_icslibnavitia_StopArea?',
	);

	public function __construct() {
		parent::__construct(get_class($this) . '::$fields');
	}
	
	public function ReadXML(XMLReader $reader) {
		$this->_ReadXML($reader, 'Backward');
	}
	
	protected function ReadAttribute(XMLReader $reader) {
		switch ($reader->name) {
			case 'BackwardName':
				$this->__set('name', $reader->value);
				break;
		}
	}

	protected function ReadElement(XMLReader $reader) {
		switch ($reader->name) {
			case 'Direction':
				$this->ReadOrigDest($reader, 'direction');
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

if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/ics_libnavitia/class.tx_icslibnavitia_Backward.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/ics_libnavitia/class.tx_icslibnavitia_Backward.php']);
}