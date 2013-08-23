<?php

/**
 * Represents an unmodifiable node list.
 *
 * @author Pierrick Caillon <pierrick@in-cite.net>
 * @package ics_libnavitia
 * @subpackage DataObjects
 */
class tx_icslibnavitia_ReadOnlyNodeList implements tx_icslibnavitia_INodeList {
	private $list;

	/**
	 * Initializes the node list.
	 * @param tx_icslibnavitia_INodeList $list The source node list.
	 */
	public function __construct(tx_icslibnavitia_INodeList $list) {
		$this->list = $list;
	}
	
	public function Add(tx_icslibnavitia_Node $item) {
		throw new Exception('Unsupported operation.');
	}
	
	public function Insert(tx_icslibnavitia_Node $item, $index) {
		throw new Exception('Unsupported operation.');
	}

	public function IndexOf(tx_icslibnavitia_Node $item) {
		return $this->list->IndexOf($item);
	}
	
	public function Contains(tx_icslibnavitia_Node $item) {
		return $this->list->Contains($item);
	}

	public function Remove(tx_icslibnavitia_Node $item) {
		throw new Exception('Unsupported operation.');
	}
	
	public function RemoveAt($index) {
		throw new Exception('Unsupported operation.');
	}
	
	public function Get($index) {
		return $this->list->Get($index);
	}
	
	public function Set($index, tx_icslibnavitia_Node $value) {
		throw new Exception('Unsupported operation.');
	}
	
	public function Count() {
		return $this->list->Count();
	}
	
	public function Clear() {
		throw new Exception('Unsupported operation.');
	}
	
	public function AsReadOnly() {
		return $this;
	}
	
	public function ToArray() {
		return $list->ToArray();
	}

	public function Sort($callback) {
		throw new Exception('Unsupported operation.');
	}
}
