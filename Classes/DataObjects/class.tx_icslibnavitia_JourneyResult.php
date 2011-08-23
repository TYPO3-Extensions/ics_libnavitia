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
		trigger_error('Not implemented', E_USER_NOTICE);
	}
	
	public function __toString() {
		return get_class($this);
	}
}
