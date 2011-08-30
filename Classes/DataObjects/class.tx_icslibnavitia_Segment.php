<?php

class tx_icslibnavitia_Segment extends tx_icslibnavitia_Node {
	static $fields = array(
		'length' => 'int',
		'fromNbPar' => 'int',
		'toNbPar' => 'int',
		'fromNbOdd' => 'int',
		'toNbOdd' => 'int',
		'duration' => 'int',
		'address' => 'object:tx_icslibnavitia_Address',
		'startNode' => 'object:tx_icslibnavitia_Coord',
		'endNode' => 'object:tx_icslibnavitia_Coord',
	);

	public function __construct() {
		parent::__construct(get_class($this) . '::$fields');
	}
	
	public function ReadXML(XMLReader $reader) {
		$this->_ReadXML($reader, 'Segment');
	}
	
	protected function ReadAttribute(XMLReader $reader) {
		switch ($reader->name) {
			case 'Length':
				$this->__set('length', (int)$reader->value);
				break;
			case 'FromNbPar':
				$this->__set('fromNbPar', (int)$reader->value);
				break;
			case 'ToNbPar':
				$this->__set('toNbPar', (int)$reader->value);
				break;
			case 'FromNbOdd':
				$this->__set('fromNbOdd', (int)$reader->value);
				break;
			case 'ToNbOdd':
				$this->__set('toNbOdd', (int)$reader->value);
				break;
			case 'Duration':
				$this->__set('duration', (int)$reader->value);
				break;
		}
	}

	protected function ReadElement(XMLReader $reader) {
		switch ($reader->name) {
			case 'Address':
				$obj = t3lib_div::makeInstance('tx_icslibnavitia_Address');
				$obj->ReadXML($reader);
				$this->__set('address', $obj);
				break;
			case 'StartNode':
				$this->ReadNode($reader, 'startNode');
				break;
			case 'EndNode':
				$this->ReadNode($reader, 'endNode');
				break;
			default:
				tx_icslibnavitia_Node::SkipChildren($reader);
		}
	}
	
	private function ReadNode(XMLReader $reader, $fieldname) {
		if (!$reader->isEmptyElement) {
			$reader->read();
			while ($reader->nodeType != XMLReader::END_ELEMENT) {
				if ($reader->nodeType == XMLReader::ELEMENT) {
					if ($reader->name == 'Coord') {
						$obj = t3lib_div::makeInstance('tx_icslibnavitia_Coord');
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
