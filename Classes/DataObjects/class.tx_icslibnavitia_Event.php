<?php

class tx_icslibnavitia_Event extends tx_icslibnavitia_Node {
	static $fields = array(
		'id' => 'int',
		'providerId' => 'int',
		'levelTitle' => 'string',
		'title' => 'string',
		'externalCode' => 'string',
		'publicationStartDate' => 'string',
		'publicationEndDate' => 'string',
	);

	public function __construct() {
		parent::__construct(get_class($this) . '::$fields');
		$this->values['impacts'] = t3lib_div::makeInstance('tx_icslibnavitia_NodeList', 'tx_icslibnavitia_Impact');
	}
	
	public function ReadXML(XMLReader $reader) {
		$this->_ReadXML($reader, 'Event');
	}
	
	protected function ReadInit() {
		parent::ReadInit();
		$this->values['impacts']->Clear();
	}
	
	protected function ReadAttribute(XMLReader $reader) {
		switch ($reader->name) {
			case 'EventID':
				$this->__set('id', (int)$reader->value);
				break;
			case 'ProviderID':
				$this->__set('providerId', (int)$reader->value);
				break;
		}
	}

	protected function ReadElement(XMLReader $reader) {
		switch ($reader->name) {
			case 'EventLevelTitle':
				$this->__set('levelTitle', $reader->readString());
				tx_icslibnavitia_Node::SkipChildren($reader);
				break;
			case 'EventTitle':
				$this->__set('title', $reader->readString());
				tx_icslibnavitia_Node::SkipChildren($reader);
				break;
			case 'EventExternalCode':
				$this->__set('externalCode', $reader->readString());
				tx_icslibnavitia_Node::SkipChildren($reader);
				break;
			case 'EventPublicationStartDate':
				$this->__set('publicationStartDate', $reader->readString());
				tx_icslibnavitia_Node::SkipChildren($reader);
				break;
			case 'EventPublicationEndDate':
				$this->__set('publicationEndDate', $reader->readString());
				tx_icslibnavitia_Node::SkipChildren($reader);
				break;
			case 'ImpactList':
				tx_icslibnavitia_Node::ReadList($reader, $this->values['impacts'], array('Impact' => 'tx_icslibnavitia_Impact'));
				break;
			default:
				tx_icslibnavitia_Node::SkipChildren($reader);
		}
	}
	
	public function __toString() {
		return get_class($this);
	}
}

if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/ics_libnavitia/class.tx_icslibnavitia_Event.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/ics_libnavitia/class.tx_icslibnavitia_Event.php']);
}