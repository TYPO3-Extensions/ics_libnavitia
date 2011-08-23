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
		trigger_error('Not implemented', E_USER_NOTICE);
	}
	
	public function __toString() {
		return get_class($this);
	}
}
