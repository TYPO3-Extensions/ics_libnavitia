<?php

class tx_icslibnavitia_JourneyResult extends tx_icslibnavitia_Node {
	static $fields = array(
		'criteria' => 'string',
		'last' => 'bool',
		'first' => 'bool',
		'found' => 'bool',
		'position' => 'int',
		'best' => 'bool',
		'disrupt' => 'bool',
		'adapted' => 'bool',
		'summary' => 'object:tx_icslibnavitia_Summary?',
		// 'validityPattern' => 'object:',
		// 'vpTranslation' => 'object:',
	);

	public function __construct() {
		parent::__construct(get_class($this) . '::$fields');
		$this->values['sections'] = t3lib_div::makeInstance('tx_icslibnavitia_NodeList', 'tx_icslibnavitia_Section');
	}
	
	public function ReadXML(XMLReader $reader) {
		$this->_ReadXML($reader, 'JourneyResult');
	}
	
	protected function ReadInit() {
		parent::ReadInit();
		$this->values['sections']->Clear();
	}
	
	protected function ReadAttribute(XMLReader $reader) {
		switch ($reader->name) {
			case 'Criteria':
				$this->__set('criteria', $reader->value);
				break;
			case 'IsLastSoluce':
				$this->__set('last', (bool)$reader->value);
				break;
			case 'IsFirstSoluce':
				$this->__set('first', (bool)$reader->value);
				break;
			case 'IsCriteriaFound':
				$this->__set('found', (bool)$reader->value);
				break;
			case 'JourneyResultPosition':
				$this->__set('position', (int)$reader->value);
				break;
			case 'IsBest':
				$this->__set('best', (bool)$reader->value);
				break;
			case 'IsDisrupt':
				$this->__set('disrupt', (bool)$reader->value);
				break;
			case 'IsAdapted':
				$this->__set('adapted', (bool)$reader->value);
				break;
		}
	}

	protected function ReadElement(XMLReader $reader) {
		switch ($reader->name) {
			case 'Summary':
				$obj = t3lib_div::makeInstance('tx_icslibnavitia_Summary');
				$obj->ReadXML($reader);
				$this->__set('summary', $obj);
				break;
			case 'Section':
				$obj = t3lib_div::makeInstance('tx_icslibnavitia_Section');
				$obj->ReadXML($reader);
				$this->values['sections']->Add($obj);
				break;
			default:
				tx_icslibnavitia_Node::SkipChildren($reader);
		}
	}
	
	public function __toString() {
		return get_class($this);
	}
}

if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/ics_libnavitia/class.tx_icslibnavitia_JourneyResult.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/ics_libnavitia/class.tx_icslibnavitia_JourneyResult.php']);
}