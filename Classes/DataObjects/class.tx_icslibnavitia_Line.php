<?php

class tx_icslibnavitia_Line extends tx_icslibnavitia_Node {
	static $fields = array(
		'idx' => 'int',
		'id' => 'int',
		'name' => 'string',
		'code' => 'string',
		'externalCode' => 'string',
		'data' => 'string',
		'order' => 'int',
		'color' => 'string',
		'adaptedRoute' => 'bool',
		'modeType' => 'object:tx_icslibnavitia_ModeType',
		'comment' => 'object:tx_icslibnavitia_Comment?',
		'network' => 'object:tx_icslibnavitia_Network',
		'forward' => 'object:tx_icslibnavitia_Forward',
		'backward' => 'object:tx_icslibnavitia_Backward',
		'impactPosList' => 'array',
	);

	public function __construct() {
		parent::__construct(get_class($this) . '::$fields');
	}
	
	public function ReadXML(XMLReader $reader) {
		$this->_ReadXML($reader, 'Line');
	}
	
	protected function ReadAttribute(XMLReader $reader) {
		switch ($reader->name) {
			case 'LineId':
				$this->__set('id', (int)$reader->value);
				break;
			case 'LineIdx':
				$this->__set('idx', (int)$reader->value);
				break;
			case 'LineName':
				$this->__set('name', $reader->value);
				break;
			case 'LineExternalCode':
				$this->__set('externalCode', $reader->value);
				break;
			case 'LineCode':
				$this->__set('code', $reader->value);
				break;
			case 'LineAdditionalData':
				$this->__set('data', $reader->value);
				break;
			case 'SortOrder':
				$this->__set('order', (int)$reader->value);
				break;
			case 'LineColor':
				$this->__set('color', $reader->value);
				break;
			case 'HasAdaptedRoute':
				$this->__set('adaptedRoute', (bool)$reader->value);
				break;
		}
	}

	protected function ReadElement(XMLReader $reader) {
		switch ($reader->name) {
			case 'ModeType':
				$obj = t3lib_div::makeInstance('tx_icslibnavitia_ModeType');
				$obj->ReadXML($reader);
				$this->__set('modeType', $obj);
				break;
			case 'Network':
				$obj = t3lib_div::makeInstance('tx_icslibnavitia_Network');
				$obj->ReadXML($reader);
				$this->__set('network', $obj);
				break;
			case 'Comment':
				$obj = t3lib_div::makeInstance('tx_icslibnavitia_Comment');
				$obj->ReadXML($reader);
				$this->__set('comment', $obj);
				break;
			case 'Forward':
				$obj = t3lib_div::makeInstance('tx_icslibnavitia_Forward');
				$obj->ReadXML($reader);
				$this->__set('forward', $obj);
				break;
			case 'Backward':
				$obj = t3lib_div::makeInstance('tx_icslibnavitia_Backward');
				$obj->ReadXML($reader);
				$this->__set('backward', $obj);
				break;
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

if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/ics_libnavitia/class.tx_icslibnavitia_Line.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/ics_libnavitia/class.tx_icslibnavitia_Line.php']);
}