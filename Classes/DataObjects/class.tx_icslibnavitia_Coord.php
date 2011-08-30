<?php

class tx_icslibnavitia_Coord extends tx_icslibnavitia_Node {
	static $fields = array(
		'x' => 'float',
		'y' => 'float',
		'lat' => 'float',
		'lng' => 'float',
	);

	public function __construct() {
		parent::__construct(get_class($this) . '::$fields');
	}
	
	public function __set($name, $value) {
		if (array_key_exists($name, $this->values)) {
			$oldValue = $this->__get($name);
		}
		parent::__set($name, $value);
		if (isset($oldValue) && ($oldValue != $this->__get($name))) {
		}
	}
	
	public function ReadXML(XMLReader $reader) {
		$this->_ReadXML($reader, 'Coord');
	}
	
	protected function ReadAttribute(XMLReader $reader) {
		switch ($reader->name) {
			case '':
				$this->__set('', $reader->value);
				break;
		}
	}

	protected function ReadElement(XMLReader $reader) {
		switch ($reader->name) {
			case 'CoordX':
				$this->__set('x', (double)str_replace(',', '.', $reader->readString()));
				break;
			case 'CoordY':
				$this->__set('y', (double)str_replace(',', '.', $reader->readString()));
				break;
		}
		tx_icslibnavitia_Node::SkipChildren($reader);
	}
	
	public function __toString() {
		return get_class($this);
	}
}
