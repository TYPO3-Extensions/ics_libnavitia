<?php

abstract class tx_icslibnavitia_Node {
	private $fields;
	protected $values;
	
	private static function getFieldsRef($fieldsref) {
		return eval('return ' . $fieldsref . ';');
	}
	
	private static function getFieldsRefField($fieldsref, $field) {
		if (!preg_match('/^[A-Z0-9$:_]+$/i', trim($fieldsref))) {
			$trace = debug_backtrace();
			trigger_error('Invalid variable reference "' . $fieldsref . '"' .
				' in ' . $trace[1]['file'] .
				' on line ' . $trace[1]['line'],
				E_USER_ERROR);
		}
		return eval('return ' . $fieldsref . '[\'' . $field . '\'];');
	}
	
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
				trigger_error('Unknow type "' . $type . '" for field "' . $fieldname . '" in ' . $fieldsref . '.', E_USER_WARNING);
		}
	}

	protected function __construct($fieldsref) {
		if (!preg_match('/^[A-Z0-9$:_]+$/i', trim($fieldsref))) {
			$trace = debug_backtrace();
			trigger_error('Invalid variable reference "' . $fieldsref . '"' .
				' in ' . $trace[0]['file'] .
				' on line ' . $trace[0]['line'],
				E_USER_ERROR);
		}
		$this->fields = $fieldsref;
		$this->values = array();
		foreach (self::getFieldsRef($fieldsref) as $fieldname => $type) {
			$this->setDefaultValue($fieldname, $type);
		}
	}

	public function __get($name) {
		if (array_key_exists($name, $this->values))
			return $this->values[$name];
		$trace = debug_backtrace();
		trigger_error(
			'Undefined property via __get(): ' . $name .
			' in ' . $trace[0]['file'] .
			' on line ' . $trace[0]['line'],
			E_USER_NOTICE);
		return null;
	}
	
	public function __set($name, $value) {
		if (array_key_exists($name, $this->values)) {
			$type = self::getFieldsRefField($this->fields, $name);
			if (empty($type)) {
				$trace = debug_backtrace();
				trigger_error(
					'Property is readonly via __set(): ' . $name . 
					' in ' . $trace[0]['file'] .
					' on line ' . $trace[0]['line'],
					E_USER_WARNING);
				return;
			}
			if (!self::checkValue($value, $type)) {
				$trace = debug_backtrace();
				trigger_error(
					'Property type mismatch via __set(): ' . $name . 
					', expected: ' . self::getFieldsRefField($this->fields, $name) .
					' in ' . $trace[0]['file'] .
					' on line ' . $trace[0]['line'],
					E_USER_WARNING);
				return;
			}
			$this->values[$name] = $value;
			return;
		}
		$trace = debug_backtrace();
		trigger_error(
			'Undefined property via __set(): ' . $name .
			' in ' . $trace[0]['file'] .
			' on line ' . $trace[0]['line'],
			E_USER_NOTICE);
	}
	
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
			$this->setDefaultValue($name, self::getFieldsRefField($this->fields, $name));
		}
		trigger_error(
			'Properties not unsettable, set default value if valid' .
			' in ' . $trace[0]['file'] .
			' on line ' . $trace[0]['line'],
			E_USER_NOTICE);
	}
	
	abstract public function ReadXML(XMLReader $reader);
	
	abstract public function __toString();
}
