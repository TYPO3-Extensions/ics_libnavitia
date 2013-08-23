<?php

/**
 * Represents a modifiable node list.
 *
 * This object contains a list of {@link tx_icslibnavitia_Node} elements.
 * A filter on the usable element type can be used. The final element type still
 * have to be a derived type of tx_icslibnavitia_Node.
 * @author Pierrick Caillon <pierrick@in-cite.net>
 * @package ics_libnavitia
 * @subpackage DataObjects
 */
class tx_icslibnavitia_NodeList implements tx_icslibnavitia_INodeList {
	private $values;
	private $type;
	
	/**
	 * Initializes the list.
	 * @param string $elementType The type filter for manipulated elements. Class or interface name.
	 */
	public function __construct($elementType = 'tx_icslibnavitia_Node') {
		$this->values = array();
		$this->type = $elementType;
	}
	
	public function Add(tx_icslibnavitia_Node $item) {
		if (!is_a($item, $this->type)) {
			tx_icslibnavitia_Debug::notice('Invalid item type for Add(), expecting ' . $this->type);
			return $this;
		}
		$this->values[] = $item;
		return $this;
	}
	
	public function Insert(tx_icslibnavitia_Node $item, $index) {
		if (!is_a($item, $this->type)) {
			tx_icslibnavitia_Debug::notice('Invalid item type for Insert(), expecting ' . $this->type);
			return $this;
		}
		if ((!is_int($index)) || ($index < 0) || ($index > count($this->values))) {
			tx_icslibnavitia_Debug::error('Index out of bound for Insert() or not an integer, index = ' . $index);			
		}
		array_splice($this->values, $index, 0, array($item));
		return $this;
	}

	public function IndexOf(tx_icslibnavitia_Node $item) {
		for ($i = 0; $i < count($this->values); $i++)
			if ($this->values[$i] == $item) {
				return $i;
			}
		return -1;
	}
	
	public function Contains(tx_icslibnavitia_Node $item) {
		return $this->IndexOf($item) >= 0;
	}

	public function Remove(tx_icslibnavitia_Node $item) {
		for ($i = 0; $i < count($this->values); $i++)
			if ($this->values[$i] == $item) {
				$this->RemoveAt($i);
				return $this;
			}
		return $this;
	}
	
	public function RemoveAt($index) {
		if ((!is_int($index)) || ($index < 0) || ($index >= count($this->values))) {
			tx_icslibnavitia_Debug::error('Index out of bound for RemoveAt() or not an integer, index = ' . $index);
			return $this;
		}
		array_splice($this->values, $index, 1);
		return $this;
	}
	
	public function Get($index) {
		if ((!is_int($index)) || ($index < 0) || ($index >= count($this->values))) {
			tx_icslibnavitia_Debug::error('Index out of bound for Get() or not an integer, index = ' . $index);			
		}
		return $this->values[$index];
	}
	
	public function Set($index, tx_icslibnavitia_Node $value) {
		if (!is_a($item, $this->type)) {
			tx_icslibnavitia_Debug::notice('Invalid item type for Set(), expecting ' . $this->type);
			return;
		}
		if ((!is_int($index)) || ($index < 0) || ($index > count($this->values))) {
			tx_icslibnavitia_Debug::error('Index out of bound for Set() or not an integer, index = ' . $index);			
		}
		$this->values[$index] = $item;
		return $this;
	}
	
	public function Count() {
		return count($this->values);
	}
	
	public function Clear() {
		$this->values = array();
		return $this;
	}

	public function AsReadOnly() {
		return new tx_icslibnavitia_ReadOnlyNodeList($this);
	}
	
	public function ToArray() {
		return $this->values;
	}
	
	public function Sort($callback) {
		return usort($this->values, $callback);
	}
}
