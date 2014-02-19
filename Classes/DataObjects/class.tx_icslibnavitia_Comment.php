<?php

class tx_icslibnavitia_Comment extends tx_icslibnavitia_Node {
	static $fields = array(
		'id' => 'int',
		'idx' => 'int',
		'name' => 'string',
		'externalCode' => 'string',
		'position' => 'integer',
		'startDate' => 'object:DateTime?',
		'endDate' => 'object:DateTime?',
	);
	
	private $startDate;
	private $endDate;

	public function __construct() {
		parent::__construct(get_class($this) . '::$fields');
	}
	
	public function ReadXML(XMLReader $reader) {
		$this->endDate = array('date' => array('y' => 0, 'm' => 0, 'd' => 0), 'set' => false);
		$this->startDate = array('date' => array('y' => 0, 'm' => 0, 'd' => 0), 'set' => false);
		$this->_ReadXML($reader, 'Comment');
		$v = $this->startDate;
		unset($this->startDate);
		$this->__set('startDate', $v['set'] ? new DateTime(date('c', mktime(0, 0, 0, $v['date']['m'], $v['date']['d'], $v['date']['y']))) : null);
		$v = $this->endDate;
		unset($this->endDate);
		$this->__set('endDate', $v['set'] ? new DateTime(date('c', mktime(0, 0, 0, $v['date']['m'], $v['date']['d'], $v['date']['y']))) : null);
	}
	
	protected function ReadInit() {
		parent::ReadInit();
	}
	
	protected function ReadAttribute(XMLReader $reader) {
		switch ($reader->name) {
			case 'CommentExternalCode':
				$this->__set('externalCode', $reader->value);
				break;
			case 'CommentName':
				$this->__set('name', $reader->value);
				break;
			case 'CommentId':
				$this->__set('id', (int)$reader->value);
				break;
			case 'CommentIdx':
				$this->__set('idx', (int)$reader->value);
				break;
			case 'Position':
				$this->__set('position', (int)$reader->value);
				break;
		}
	}

	protected function ReadElement(XMLReader $reader) {
		switch ($reader->name) {
			case 'StartDate':
				$this->ReadDate($reader, $this->startDate['date']);
				$this->startDate['set'] = true;
				break;
			case 'EndDate':
				$this->ReadDate($reader, $this->endDate['date']);
				$this->endDate['set'] = true;
				break;
			default:
				tx_icslibnavitia_Node::SkipChildren($reader);
		}
	}
	
	private function ReadDate(XMLReader $reader, &$dateDef) {
		if (!$reader->isEmptyElement) {
			$reader->read();
			while ($reader->nodeType != XMLReader::END_ELEMENT) {
				if ($reader->nodeType == XMLReader::ELEMENT) {
					switch ($reader->name) {
						case 'Year':
							$dateDef['y'] = (int)$reader->readString();
							break;
						case 'Month':
							$dateDef['m'] = (int)$reader->readString();
							break;
						case 'Day':
							$dateDef['d'] = (int)$reader->readString();
							break;
					}
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

if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/ics_libnavitia/class.tx_icslibnavitia_Comment.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/ics_libnavitia/class.tx_icslibnavitia_Comment.php']);
}