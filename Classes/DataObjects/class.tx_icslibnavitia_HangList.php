<?php

class tx_icslibnavitia_HangList extends tx_icslibnavitia_Node implements tx_icslibnavitia_INodeList {
	static $fields = array(
		'odd' => 'bool',
		'start' => 'integer',
		'end' => 'integer',
	);
	
	private $list;

	public function __construct() {
		parent::__construct(get_class($this) . '::$fields');
		$this->list = t3lib_div::makeInstance('tx_icslibnavitia_NodeList', 'tx_icslibnavitia_Hang');
	}
	
	public function ReadXML(XMLReader $reader) {
		$this->_ReadXML($reader, 'HangList');
	}
	protected function _ReadXML(XMLReader $reader, $elementName) {
		if (($reader->nodeType != XMLReader::ELEMENT) || ($elementName && ($reader->name != $elementName))) {
			tx_icslibnavitia_Debug::error('Unexpected XMLReader context, expected an ' . $elementName . ' element,' .
				' found node { type = ' . $reader->nodeType . '; name = ' . $reader->name . ' }', 0);
			return;
		}
		$this->ReadInit();
		while ($reader->moveToNextAttribute()) {
			$this->ReadAttribute($reader);
		}
		tx_icslibnavitia_Node::ReadList($reader, $this, array('Hang' => 'tx_icslibnavitia_Hang'));
	}
	
	protected function ReadInit() {
		parent::ReadInit();
		$this->list->Clear();
	}
	
	protected function ReadAttribute(XMLReader $reader) {
		switch ($reader->name) {
			case 'Odd':
				$this->__set('odd', ($reader->value == 'Yes'));
				break;
			case 'StartNb':
				$this->__set('start', (int)$reader->value);
				break;
			case 'EndNb':
				$this->__set('end', (int)$reader->value);
				break;
		}
	}
	
	protected function ReadElement(XMLReader $reader)
	{
	}
	
	public function __toString() {
		return get_class($this);
	}

	public function Add(tx_icslibnavitia_Node $item) { return $this->list->Add($item); }
	public function Insert(tx_icslibnavitia_Node $item, $index) { return $this->list->Insert($item, $index); }
	public function IndexOf(tx_icslibnavitia_Node $item) { return $this->list->IndexOf($item); }
	public function Contains(tx_icslibnavitia_Node $item) { return $this->list->Contains($item); }
	public function Remove(tx_icslibnavitia_Node $item) { return $this->list->Remove($item); }
	public function RemoveAt($index) { return $this->list->RemoveAt($index); }
	public function Get($index) { return $this->list->Get($index); }
	public function Set($index, tx_icslibnavitia_Node $value) { return $this->list->Set($index, $value); }
	public function Count() { return $this->list->Count(); }
	public function Clear() { $this->list->Clear(); }
	public function AsReadOnly() { return $this->list->AsReadOnly(); }
	public function ToArray() { return $this->list->ToArray(); }
	public function Sort($callback) { return usort($this->values, $callback); }
}
