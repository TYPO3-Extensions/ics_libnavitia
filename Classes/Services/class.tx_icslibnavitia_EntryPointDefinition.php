<?php

/**
 * Represent an EntryPoint parameter.
 *
 * This kind of parameter is mainly used for the PlanJourney API function to
 * specify the departure and arrival locations.
 *
 * @author Pierrick Caillon <pierrick@in-cite.net>
 * @package ics_libnavitia
 * @subpackage Services
 */
class tx_icslibnavitia_EntryPointDefinition {
	private static $convObj = null;
	private $definition;
	private $hanglist;

	private function __construct() {
		$definition = array(
			'TypePoint' => '',
			'IdxPoint' => null,
			'Name' => '',
			'CityName' => '',
			'Number' => '',
			'TypeName' => '',
			'X' => null,
			'Y' => null,
		);
		$hanglist = array();
	}
	
	public static function fromEntryPoint(tx_icslibnavitia_EntryPoint $entryPoint) {
		$epd = new tx_icslibnavitia_EntryPointDefinition();
		$epd->definition['TypePoint'] = $entryPoint->type;
		$element = lcfirst($entryPoint->type);
		$epd->definition['IdxPoint'] = $entryPoint->$element->idx;
		$epd->definition['Name'] = $entryPoint->name;
		$epd->definition['CityName'] = $entryPoint->cityName;
		$epd->definition['Number'] = $entryPoint->number;
		$epd->definition['TypeName'] = $entryPoint->typeName;
		$coord = ($entryPoint->$element->coord != null) ? ($entryPoint->$element->coord) : ($entryPoint->coord);
		if ($coord != null) {
			$epd->definition['X'] = $coord->x;
			$epd->definition['Y'] = $coord->y;
		}
		$epd->parseHangList($entryPoint->hangList);
		return $epd;
	}
	
	public static function fromCoord(tx_icslibnavitia_Coord $coords) {
		$epd = new tx_icslibnavitia_EntryPointDefinition();
		$epd->definition['TypePoint'] = 'Undefined';
		$epd->definition['X'] = $coords->x;
		$epd->definition['Y'] = $coords->y;
		return $epd;
	}
	
	public static function fromHangList(tx_icslibnavitia_INodeList $hangList) {
		$epd = new tx_icslibnavitia_EntryPointDefinition();
		$epd->definition['TypePoint'] = 'Undefined';
		$epd->parseHangList($hangList);
		return $epd;
	}
	
	public static function fromDefinition($definition) {
		$epd = new tx_icslibnavitia_EntryPointDefinition();
		$definition = explode('|', $definition);
		$epd->definition = array(
			'TypePoint' => $definition[0],
			'IdxPoint' => $definition[1],
			'Name' => $definition[2],
			'CityName' => $definition[3],
			'Number' => $definition[4],
			'TypeName' => $definition[5],
			'X' => $definition[6],
			'Y' => $definition[7],
		);
		if (!empty($definition[8]))
			$epd->hangList = t3lib_div::trimExplode(';', $definition[8], true);
		return $epd;
	}
	
	private function parseHangList(tx_icslibnavitia_INodeList $hangList) {
		foreach ($hangList->ToArray() as $hang) {
			if (!($hang instanceof tx_icslibnavitia_Hang))
				continue;
			$this->hanglist[] = $hang->stopPointIdx . '!' . $hang->duration . '!' . $hang->kind;
		}
	}
	
	public function __toString() {
		if (self::$convObj == null)
			self::$convObj = t3lib_div::makeInstance('t3lib_cs');
		$definition = implode('|', $this->definition);
		if (!empty($this->hanglist)) {
			$definition .= '|' . implode(';', $this->hanglist);
		}
		return self::$convObj->conv($definition, $GLOBALS['TSFE'] ? $GLOBALS['TSFE']->renderCharset : $GLOBALS['LANG']->charSet, self::$convObj->parse_charset('ISO-8859-1'));
	}
}

if (!function_exists('lcfirst')) {
	function lcfirst($str) {
		if (strlen($str) > 0) {
			$str = strtolower($str{0}) . substr($str, 1);
		}
		return $str;
	}
}
