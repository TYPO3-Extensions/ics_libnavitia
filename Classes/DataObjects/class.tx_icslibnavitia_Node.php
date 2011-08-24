<?php

/**
 * Represents a node in the Response from NAVitiA API.
 *
 * Each node provides a field definition array describing its internal structure.
 * The available field type are (with aliases in parenthesis):
 * - string
 * - bool
 * - integer (int)
 * - double (float)
 * - array
 * - object
 *
 * For the object type, the syntax is a little more complex. There is three cases:
 * 1. <b>object</b>: The field contains any object and can be null.
 * 2. <b>object:&lt;classname&gt;</b>: The field contains an object of type &lt;classname&gt;.
 * 3. <b>object:&lt;classname&gt;?</b>: Same as second. However, the field can be null.
 *
 * @author Pierrick Caillon <pierrick@in-cite.net>
 * @package ics_libnavitia
 * @subpackage DataObjects
 */
abstract class tx_icslibnavitia_Node {
	/**
	 * The reference to the fields definition array.
	 * @var string
	 * @access private
	 */
	private $fields;
	/**
	 * The values for the node fields.
	 * @var array
	 * @access protected
	 */
	protected $values;

	/**
	 * Retrieves a fields definition array.
	 * @param string $fieldsref The reference to the array in static context.
	 * @return array The field definition array. The array has field name as key and field type as value.
	 */
	private static function associateFieldsRef($fieldsref, tx_icslibnavitia_Node $node) {
		if (!preg_match('/^[A-Z0-9$:_]+$/i', trim($fieldsref))) {
			tx_icslibnavitia_Debug::error('Invalid variable reference "' . $fieldsref . '"', 1);
			return '';
		}
		eval('$node->fields = &' . $fieldsref . ';');
	}
	
	/**
	 * Define the default value for the specified field.
	 * @param string $fieldname The name of the field.
	 * @param string $type The type of the field.
	 */
	protected function setDefaultValue($fieldname, $type) {
		$type = t3lib_div::trimExplode(':', $type, true);
		switch ($type[0]) {
			case 'string':
				$this->values[$fieldname] = '';
				break;
			case 'bool':
				$this->values[$fieldname] = false;
				break;
			case 'integer':
			case 'int':
				$this->values[$fieldname] = 0;
				break;
			case 'float':
			case 'double':
				$this->values[$fieldname] = 0.0;
				break;
			case 'array':
				$this->values[$fieldname] = array();
				break;
			case 'object':
				$this->values[$fieldname] = null;
				if (!empty($type[1]) && ($type[1]{strlen($type[1]) - 1} != '?'))
					$this->values[$fieldname] = t3lib_div::makeInstance($type[1]);
				break;
			default:
				tx_icslibnavitia_Debug::warning('Unknow type "' . $type . '" for field "' . $fieldname . '" in ' . $fieldsref . '', 1);
		}
	}

	protected function __construct($fieldsref) {
		if (!preg_match('/^[A-Z0-9$:_]+$/i', trim($fieldsref))) {
			tx_icslibnavitia_Debug::error('Invalid variable reference "' . $fieldsref . '"');
			return;
		}
		self::associateFieldsRef($fieldsref, $this);
		$this->values = array();
		foreach ($this->fields as $fieldname => $type) {
			$this->setDefaultValue($fieldname, $type);
		}
	}

	public function __get($name) {
		if (array_key_exists($name, $this->values))
			return $this->values[$name];
		tx_icslibnavitia_Debug::notice('Undefined property via __get(): ' . $name);
		return null;
	}
	
	public function __set($name, $value) {
		if (array_key_exists($name, $this->values)) {
			$type = $this->fields[$name];
			if (empty($type)) {
				tx_icslibnavitia_Debug::warning('Property is readonly via __set(): ' . $name);
				return;
			}
			if (!self::checkValue($value, $type)) {
				tx_icslibnavitia_Debug::warning('Property type mismatch via __set(): ' . $name . 
					', expected: ' . $this->fields[$name]);
				return;
			}
			$this->values[$name] = $value;
			return;
		}
		tx_icslibnavitia_Debug::notice('Undefined property via __set(): ' . $name);
	}
	
	/**
	 * Checks a value agains a type definition.
	 * @param mixed $value The value to check type.
	 * @param string $type The type definition.
	 * @return boolean The success of the test.
	 */
	protected static function checkValue(& $value, $type) {
		$typeDef = t3lib_div::trimExplode(':', $type, true);
		if ($typeDef[0] == 'object') {
			if (strlen($typeDef[1]) > 1) {
				if ($typeDef[1]{strlen($typeDef[1]) - 1} == '?') {
					return class_exists(substr($typeDef[1], 0, -1)) ? (is_null($value) || (is_object($value) && is_a($value, substr($typeDef[1], 0, -1)))) : false;
				}
				else {
					return class_exists($typeDef[1]) ? (is_object($value) && is_a($value, $typeDef[1])) : false;
				}
			}
			else {
				if ($typeDef[1] == '?') {
					return is_null($value) || is_object($value);
				}
				else {
					return is_object($value);
				}
			}
		}
		else {
			$func = 'is_' . $typeDef[0];
			return function_exists($func) ? $func($value) : false;
		}
	}
	
	public function __isset($name) {
		return array_key_exists($name, $this->values);
	}
	
	public function __unset($name) {
		if (array_key_exists($name, $this->values)) {
			$this->setDefaultValue($name, $this->fields[$name]);
		}
		tx_icslibnavitia_Debug::notice('Properties not unsettable, set default value if valid');
	}
	
	/**
	 * Parses the current XML node in the XMLReader.
	 * @abstract
	 * @param XMLReader $reader Reader to the parsed XML document.
	 */
	abstract public function ReadXML(XMLReader $reader);
	/**
	 * Parses the current XML attribute in the XMLReader.
	 *
	 * This method is called from {@link tx_icslibnavitia_Node::_ReadXML()} and
	 * should not be called explicitly by implementors.
	 * @abstract
	 * @param XMLReader $reader Reader to the parsed XML document.
	 */
	abstract protected function ReadAttribute(XMLReader $reader);
	/**
	 * Parses the current XML node in the XMLReader. For the child nodes.
	 *
	 * This method is called from {@link tx_icslibnavitia_Node::_ReadXML()} and
	 * should not be called explicitly by implementors.
	 * @abstract
	 * @param XMLReader $reader Reader to the parsed XML document.
	 */
	abstract protected function ReadElement(XMLReader $reader);
	
	/**
	 * Really parses the current XML node in the XMLReader.
	 *
	 * Should be called by implementors from {@link tx_icslibnavitia_Node::ReadXML()}.
	 * @access private
	 * @param XMLReader $reader Reader to the parsed XML document.
	 * @param string $elementName The name of the expected XML Element.
	 */
	protected function _ReadXML(XMLReader $reader, $elementName) {
		if (($reader->nodeType != XMLReader::ELEMENT) || ($reader->name != $elementName)) {
			tx_icslibnavitia_Debug::error('Unexpected XMLReader context, expected an ' . $elementName . ' element,' .
				' found node { type = ' . $reader->nodeType . '; name = ' . $reader->name . ' }', 1);
			return;
		}
		$this->ReadInit();
		while ($reader->moveToNextAttribute()) {
			$this->ReadAttribute($reader);
		}
		$reader->moveToElement();
		if (!$reader->isEmptyElement) {
			$reader->read();
			while ($reader->nodeType != XMLReader::END_ELEMENT) {
				if ($reader->nodeType == XMLReader::ELEMENT) {
					$this->ReadElement($reader);
				}
				$reader->read();
			}
		}
	}
	
	/**
	 * Initializes fields before XML parsing.
	 */
	protected function ReadInit() {
		foreach ($this->fields as $fieldname => $type) {
			$this->setDefaultValue($fieldname, $type);
		}
	}
	
	/**
	 * Populates a {@link tx_icslibnavitia_INodeList} with each valid child element
	 * of the current XML node in the XMLReader.
	 *
	 * @param XMLReader $reader Reader to the parsed XML document.
	 * @param tx_icslibnavitia_INodeList $list The list to populate.
	 * @param array $elementTypeMapping The mapping from the elements names to the
	 *        node object type.
	 */
	protected function ReadList(XMLReader $reader, tx_icslibnavitia_INodeList $list, array $elementTypeMapping) {
		if (!$reader->isEmptyElement) {
			$reader->read();
			while ($reader->nodeType != XMLReader::END_ELEMENT) {
				if (($reader->nodeType == XMLReader::ELEMENT) && array_key_exists($reader->name, $elementTypeMapping)) {
					$object = t3lib_div::makeInstance($elementTypeMapping[$reader->name]);
					if ($object instanceof tx_icslibnavitia_Node) {
						$object->ReadXML($reader);
						$list->Add($object);
					}
					else {
						$this->SkipChildren($reader);
					}
				}
				else {
					$this->SkipChildren($reader);
				}
				$reader->read();
			}
		}
	}
	
	/**
	 * Skips all child nodes of the current Element node.
	 *
	 * The method MUST be called only when the reader is on an Element node.
	 * After the call, the reader is on the corresponding EndElement node or not moved.
	 *
	 * @param XMLReader $reader The reader to manipulate.
	 */
	protected function SkipChildren(XMLReader $reader) {
		if (!$reader->isEmptyElement) {
			$reader->read();
			while (($reader->nodeType != XMLReader::END_ELEMENT) && ($reader->nodeType != XMLReader::NONE)) {
				if ($reader->nodeType == XMLReader::ELEMENT) {
					$this->SkipChildren($reader);
					// $reader->next(); // TODO: Check to behaviour.
				}
				// else 
					$reader->read();
			}
		}
	}
	
	abstract public function __toString();
}
