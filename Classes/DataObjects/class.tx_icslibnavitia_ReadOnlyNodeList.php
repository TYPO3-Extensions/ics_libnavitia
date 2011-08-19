<?php

class tx_icslibnavitia_ReadOnlyNodeList implements tx_icslibnavitia_INodeList {
	private $list;
	
	public function __construct(tx_icslibnavitia_INodeList $list) {
		$this->list = $list;
	}
	
	public function Add(tx_icslibnavitia_Node $item) {
		throw new Exception('Unsupported operation.');
	}
	
	public function Insert(tx_icslibnavitia_Node $item, $index) {
		throw new Exception('Unsupported operation.');
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
	
	public function Set($index, $value) {
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
}
