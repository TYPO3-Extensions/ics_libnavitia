<?php

/**
 * Represents the node list interface.
 *
 * This object contains a list of {@link tx_icslibnavitia_Node} elements.
 * @author Pierrick Caillon <pierrick@in-cite.net>
 * @package ics_libnavitia
 * @subpackage DataObjects
 */
interface tx_icslibnavitia_INodeList {
	/**
	 * Adds an element at the end of the list.
	 * @param tx_icslibnavitia_Node $item The item to add.
	 * @return tx_icslibnavitia_INodeList The list for multiple changes.
	 */
	public function Add(tx_icslibnavitia_Node $item);
	/**
	 * Inserts an element at the specified index in the list.
	 * @param tx_icslibnavitia_Node $item The item to add.
	 * @param int $item The item to add.
	 * @return tx_icslibnavitia_INodeList The list for multiple changes.
	 */
	public function Insert(tx_icslibnavitia_Node $item, $index);
	public function IndexOf(tx_icslibnavitia_Node $item);
	public function Contains(tx_icslibnavitia_Node $item);
	/**
	 * Removes an element from the list.
	 * @param tx_icslibnavitia_Node $item The item to remove.
	 * @return tx_icslibnavitia_INodeList The list for multiple changes.
	 */
	public function Remove(tx_icslibnavitia_Node $item);
	/**
	 * Removes an element from the list.
	 * @param int $index The index of the item to remove.
	 * @return tx_icslibnavitia_INodeList The list for multiple changes.
	 */
	public function RemoveAt($index);
	/**
	 * Retrieves the item at the specified index.
	 * @param int $index The index of the item to retrieve.
	 * @return tx_icslibnavitia_Node The requested item.
	 */
	public function Get($index);
	/**
	 * Defines the item at the specified index.
	 * @param int $index The index of the item to define.
	 * @param tx_icslibnavitia_Node $value The new item.
	 * @return tx_icslibnavitia_INodeList The list for multiple changes.
	 */
	public function Set($index, tx_icslibnavitia_Node $value);
	/**
	 * Retrieves the element count.
	 * @return int
	 */
	public function Count();
	/**
	 * Clears the list.
	 */
	public function Clear();
	/**
	 * Convert the list to a not modifiable list.
	 *
	 * Changes in this current list are reflected in the returned list.
	 */
	public function AsReadOnly();
	/**
	 * Converts the list to a standard array.
	 *
	 * The returned array is a copy of the internal element list in the same order.
	 */
	public function ToArray();
}
