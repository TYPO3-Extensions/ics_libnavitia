<?php

class tx_icslibnavitia_NodeList {
	private $values;
	private $type;
	
	public function __construct($elementType = 'tx_icslibnavitia_Node') {
		$this->values = array();
		$this->type = $elementType;
	}
	
	public function Add(tx_icslibnavitia_Node $item) {
		if (!is_a($item, $this->type)) {
			$trace = debug_backtrace();
			trigger_error(
				'Invalid item type for Add(), expecting ' . $this->type .
				' in ' . $trace[0]['file'] .
				' on line ' . $trace[0]['line'],
				E_USER_NOTICE);
			return;
		}
		$this->values[] = $item;
	}
	
	public function Insert(tx_icslibnavitia_Node $item, $index) {
		if (!is_a($item, $this->type)) {
			$trace = debug_backtrace();
			trigger_error(
				'Invalid item type for Insert(), expecting ' . $this->type .
				' in ' . $trace[0]['file'] .
				' on line ' . $trace[0]['line'],
				E_USER_NOTICE);
			return;
		}
		if ((!is_int($index)) || ($index < 0) || ($index > count($this->values))) {
			$trace = debug_backtrace();
			trigger_error(
				'Index out of bound for Insert() or not an integer, index = ' . $index .
				' in ' . $trace[0]['file'] .
				' on line ' . $trace[0]['line'],
				E_USER_ERROR);			
		}
		array_splice($this->values, $index, 0, array($item));
	}

	public function Remove(tx_icslibnavitia_Node $item) {
		for ($i = 0; $i < count($this->values); $i++)
			if ($this->values[$i] == $item) {
				$this->RemoveAt($i);
				return;
			}
	}
	
	public function RemoveAt($index) {
		if ((!is_int($index)) || ($index < 0) || ($index >= count($this->values))) {
			$trace = debug_backtrace();
			trigger_error(
				'Index out of bound for RemoveAt() or not an integer, index = ' . $index .
				' in ' . $trace[0]['file'] .
				' on line ' . $trace[0]['line'],
				E_USER_ERROR);			
		}
		array_splice($this->values, $index, 1);
	}
	
	public function Get($index) {
		if ((!is_int($index)) || ($index < 0) || ($index >= count($this->values))) {
			$trace = debug_backtrace();
			trigger_error(
				'Index out of bound for Get() or not an integer, index = ' . $index .
				' in ' . $trace[0]['file'] .
				' on line ' . $trace[0]['line'],
				E_USER_ERROR);			
		}
		return $this->values[$index];
	}
	
	public function Set($index, $value) {
		if (!is_a($item, $this->type)) {
			$trace = debug_backtrace();
			trigger_error(
				'Invalid item type for Set(), expecting ' . $this->type .
				' in ' . $trace[0]['file'] .
				' on line ' . $trace[0]['line'],
				E_USER_NOTICE);
			return;
		}
		if ((!is_int($index)) || ($index < 0) || ($index > count($this->values))) {
			$trace = debug_backtrace();
			trigger_error(
				'Index out of bound for Set() or not an integer, index = ' . $index .
				' in ' . $trace[0]['file'] .
				' on line ' . $trace[0]['line'],
				E_USER_ERROR);			
		}
		$this->values[$index] = $item;
	}
	
	public function Count() {
		return count($this->values);
	}
	
	public function Clear() {
		$this->values = array();
	}
}
