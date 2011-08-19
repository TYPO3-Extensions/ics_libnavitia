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
		switch ($type) {
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
			$testFunc = 'is_' . self::getFieldsRefField($this->fieldsref, $name);
			if (!$testFunc($value) && (($testFunc != 'is_object') || (!is_null($value)))) {
				$trace = debug_backtrace();
				trigger_error(
					'Property type mismatch via __set(): ' . $name . 
					', expected: ' . self::getFieldsRefField($this->fieldsref, $name)
					' in ' . $trace[0]['file'] .
					' on line ' . $trace[0]['line'],
					E_USER_WARNING);
			}
			$this->values[$name] = $value;
		}
		$trace = debug_backtrace();
		trigger_error(
			'Undefined property via __set(): ' . $name .
			' in ' . $trace[0]['file'] .
			' on line ' . $trace[0]['line'],
			E_USER_NOTICE);
		return null;
	}
	
	public function __isset($name) {
		return array_key_exists($name, $this->values);
	}
	
	public function __unset($name) {
		if (array_key_exists($name, $this->values)) {
			$this->setDefaultValue($name, self::getFieldsRefField($this->fieldsref, $name));
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
