<?php

interface tx_icslibnavitia_INodeList {
	public function Add(tx_icslibnavitia_Node $item);
	public function Insert(tx_icslibnavitia_Node $item, $index);
	public function Remove(tx_icslibnavitia_Node $item);
	public function RemoveAt($index);
	public function Get($index);
	public function Set($index, $value);
	public function Count();
	public function Clear();
	public function AsReadOnly();
	public function ToArray();
}
