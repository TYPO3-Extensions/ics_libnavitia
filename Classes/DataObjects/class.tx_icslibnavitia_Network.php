<?php

class tx_icslibnavitia_Network extends tx_icslibnavitia_Node {
	static $fields = array(
		'idx' => 'int',
		'id' => 'int',
		'name' => 'string',
		'externalCode' => 'string',
		'impactPosList' => 'array',
	);

	public function __construct() {
		parent::__construct(get_class($this) . '::$fields');
	}
	
	public function ReadXML(XMLReader $reader) {
		$this->_ReadXML($reader, 'Network');
	}
	
	protected function ReadAttribute(XMLReader $reader) {
		switch ($reader->name) {
			case 'NetworkId':
				$this->__set('id', (int)$reader->value);
				break;
			case 'NetworkIdx':
				$this->__set('idx', (int)$reader->value);
				break;
			case 'NetworkName':
				$this->__set('name', $reader->value);
				break;
			case 'NetworkExternalCode':
				$this->__set('externalCode', $reader->value);
				break;
		}
	}

	protected function ReadElement(XMLReader $reader) {
		switch ($reader->name) {
			case 'ImpactPosList':
				$impacts = array();
				if (!$reader->isEmptyElement) {
					$reader->read();
					while ($reader->nodeType != XMLReader::END_ELEMENT) {
						if ($reader->nodeType == XMLReader::ELEMENT) {
							if ($reader->name == 'ImpactPos') {
								$impacts[] = (int)$reader->readString();
							}
							tx_icslibnavitia_Node::SkipChildren($reader);
						}
						$reader->read();
					}
				}
				$this->__set('impactPosList', $impacts);
				break;
			default:
				tx_icslibnavitia_Node::SkipChildren($reader);
		}
	}
	
	public function __toString() {
		return get_class($this);
	}
}

if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/ics_libnavitia/class.tx_icslibnavitia_Network.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/ics_libnavitia/class.tx_icslibnavitia_Network.php']);
}