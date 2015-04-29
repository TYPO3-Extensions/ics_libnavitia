<?php

/**
 * Represents the interface to query the NAViTiA API.
 *
 * @author Pierrick Caillon <pierrick@in-cite.net>
 * @package ics_libnavitia
 * @subpackage Services
 */
class tx_icslibnavitia_APIService {
	private static $convObj = NULL;
	const INTERFACE_VERSION = '1_16';
	const CACHE_DIR = 'typo3temp/libnavitia/cache/';
	private $serviceUrl;
	private $statId;
	private $lastParams = array();
	protected static $cacheable = array(
		'AddressList',
		'AddressTypeList',
		'ContributorList',
		'DirectStopAreaList',
		'LineRouteDescription',
		'LineStopAreaList',
		'MakeBinaryCriteria',
		'PTReferential',
		'RoutePointList',
		'SiteList',
		'SiteTypeList',
		'VPatternSetList',
		'VPTranslator'
	);
	private $lastCacheTime = FALSE;
	private $lastCacheHash = FALSE;
	private $lastCachePersist = FALSE;
	private static $cacheInstance = NULL;

	/**
	 *
	 * @param string $url URL of the gwnavitia.dll to use.
	 */
	public function __construct($url, $login) {
		if (self::$convObj == NULL)
			self::$convObj = t3lib_div::makeInstance('t3lib_cs');
		$this->serviceUrl = $url;
		$this->statId = $login;
	}
	
	public static function checkCacheFolder() {
		t3lib_div::mkdir_deep(PATH_site, tx_icslibnavitia_APIService::CACHE_DIR);
	}
	
	protected static function initializeCaching() {
		if (self::$cacheInstance) return;
		t3lib_cache::initializeCachingFramework();
		try {
			self::$cacheInstance = $GLOBALS['typo3CacheManager']->getCache('cache_hash');
		} catch (t3lib_cache_exception_NoSuchCache $e) {
			self::$cacheInstance = $GLOBALS['typo3CacheFactory']->create(
				'cache_hash',
				$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cache_hash']['frontend'],
				$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cache_hash']['backend'],
				$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cache_hash']['options']
			);
		}
	}
	
	protected static $wrapperObject = NULL;
	protected static function generateCallbackWrapper() {
		if (self::$wrapperObject == NULL) {
			$wrapperClassname = uniqid('tx_icslibnavitia_APIServiceW_');
			$childClassname = uniqid('tx_icslibnavitia_APIServiceC_');
			eval("class ${wrapperClassname} {
	protected \$obj;
	public function __construct() { \$this->obj = new ${childClassname}(); }
	public function XMLMoveToRootElement(XMLReader \$reader, \$name) { return \$this->obj->XMLMoveToRootElement_(\$reader, \$name); }
	public function SkipChildren(XMLReader \$reader) { \$this->obj->SkipChildren_(\$reader); }
}
class ${childClassname} extends tx_icslibnavitia_APIService {
	public function __construct() {}
	public function XMLMoveToRootElement_(XMLReader \$reader, \$name) { return \$this->XMLMoveToRootElement(\$reader, \$name); }
	public function SkipChildren_(XMLReader \$reader) { \$this->SkipChildren(\$reader); }
}");
			self::$wrapperObject = new $wrapperClassname();
		}
		return self::$wrapperObject;
	}
	
	protected function getDataCached($generateCallback) {
		$fromCache = NULL;
		$result = NULL;
		if ($this->lastCacheTime !== FALSE) {
			self::initializeCaching();
			$cacheId = 'navapi' . $this->lastCacheHash;
			if (self::$cacheInstance->has($cacheId)) {
				$fromCache = self::$cacheInstance->get($cacheId);
				if ($fromCache['time'] < $this->lastCacheTime) {
					$fromCache = NULL;
				}
			}
		}
		if ($fromCache == NULL) {
			$result = $generateCallback(self::generateCallbackWrapper());
		}
		if (($fromCache == NULL) && ($this->lastCacheTime !== FALSE) && ($result != NULL)) {
			self::$cacheInstance->set($cacheId, array('time' => $this->lastCacheTime, 'data' => $result), array(), $this->lastCachePersist ? 0 : 3600);
		}
		return $fromCache ? $fromCache['data'] : $result;
	}
	
	/**
	 * Does a RAW call to NAViTiA Const page.
	 */
	public function CallConst() {
		$url = $this->serviceUrl . '/Const';
		$report = array();
		$result = t3lib_div::getURL($url, 0, FALSE, $report);
		return $result;
	}
	
	/**
	 * Does a RAW call to a NAViTiA API fonction.
	 *
	 * @param string $action The API function to call.
	 * @param array $params The parameters to pass to the function.
	 * @param boolean $forceNoCache Explicitly disable caching for exceptional cases.
	 * @return mixed The response data or FALSE if failed.
	 */
	public function CallAPI($action, array $params, $forceNoCache = FALSE) {
		$cached = NULL;
		$this->lastCacheTime = $this->lastCacheHash = $this->lastCachePersist = FALSE;
		if (!defined('LIBNAVITIA_CACHING') && !$forceNoCache) {
			$cacheRow = array(
				$this->serviceUrl,
				$this->statId,
				'API',
				$action,
				$params
			);
			$hash = hash('sha256', serialize($cacheRow));
			$path = t3lib_div::getFileAbsFileName(tx_icslibnavitia_APIService::CACHE_DIR . $hash);
			$this->lastCacheHash = $hash;
			if (file_exists($path)) {
				$cached = t3lib_div::getURL($path, 0, FALSE, $report);
				$this->lastCacheTime = filemtime($path);
			}
			else {
				$this->lastCacheTime = FALSE;
			}
			$this->lastCachePersist = in_array($action, self::$cacheable);
		}
		$params['Interface'] = self::INTERFACE_VERSION;
		if ($this->statId)
			$params['login'] = $this->statId;
		$this->lastParams = $params;
		$ics = $GLOBALS['TSFE'] ? $GLOBALS['TSFE']->renderCharset : $GLOBALS['LANG']->charSet;
		$ocs = self::$convObj->parse_charset('ISO-8859-1');
		$convObj = self::$convObj;
		$params = array_map(function($v) use($convObj, $ics, $ocs) { return $convObj->conv($v, $ics, $ocs); }, $params);
		$this->lastParams['Function'] = 'API';
		$this->lastParams['Action'] = $action;
		$url = $this->serviceUrl . '/API?Action=' . urlencode($action) . t3lib_div::implodeArrayForUrl('', $params);
		if ($cached === NULL) {
			$report = array();
			$result = t3lib_div::getURL($url, 0, FALSE, $report);
			if (is_string($result) && !defined('LIBNAVITIA_CACHING')) {
				$result .= '<!-- ' .  t3lib_div::getIndpEnv('TYPO3_REQUEST_URL') . ' -->';
			}
		}
		else {
			$result = $cached;
			$report = array('fromCache' => 1);
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery(
				'tx_icslibnavitia_cachedrequests',
				'hash = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($hash, 'tx_icslibnavitia_cachedrequests'),
				array('usedLast' => time())
			);
		}
		tx_icslibnavitia_Debug::Log('Call to NAViTiA API', $url, $result ? 0 : 1, $report);
		if ($result) {
			tx_icslibnavitia_Debug::WriteResponse($action, $result);
			if (($cached === NULL) && !defined('LIBNAVITIA_CACHING') && !$forceNoCache) {
				self::checkCacheFolder();
				t3lib_div::writeFileToTypo3tempDir($path, $result);
				$this->lastCacheTime = filemtime($path);
				if (!$GLOBALS['TYPO3_DB']->exec_SELECTcountRows(
					'hash',
					'tx_icslibnavitia_cachedrequests',
					'hash = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($hash, 'tx_icslibnavitia_cachedrequests')
				)) {
					$req = array(
						'hash' => $hash,
						'updated' => time(),
					);
					if (in_array($action, self::$cacheable)) {
						$req = $req + array(
							'usedLast' => time(),
							'url' => $cacheRow[0],
							'login' => $cacheRow[1],
							'function' => $cacheRow[2],
							'action' => $cacheRow[3],
							'parameters' => serialize($cacheRow[4]),
						);
					}
					$GLOBALS['TYPO3_DB']->exec_INSERTquery(
						'tx_icslibnavitia_cachedrequests',
						$req
					);
				}
			}
		}
		return $result;
	}
	
	protected function XMLMoveToRootElement(XMLReader $reader, $name) {
		$reader->read();
		while (($reader->nodeType != XMLReader::ELEMENT) && ($reader->nodeType != XMLReader::NONE)) {
			$reader->read();
		}
		return $reader->name == $name;
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
			while ($reader->nodeType != XMLReader::END_ELEMENT) {
				if ($reader->nodeType == XMLReader::ELEMENT) {
					$this->SkipChildren($reader);
				}
				$reader->read();
			}
		}
	}
	
	/**
	 * Retrieves the last executed parameters used to query NAViTiA.
	 *
	 * @return array The parameter array with special key <code>Function</code> indicating which method was used to query NAViTiA.
	 */
	public function getLastParams() {
		return $this->lastParams;
	}
	
	/** EntryPoint quality: Return only the results with the best quality. May still be multiple results. */
	const ENTRYPOINTQUALITY_BESTONLY = 0;
	/** EntryPoint quality: Return only the results having quality over the mean plus standard deviation. */
	const ENTRYPOINTQUALITY_HIGH = 1;
	/** EntryPoint quality: Return only the results having quality over the mean minus standard deviation. */
	const ENTRYPOINTQUALITY_MEDIUM = 2;
	/** EntryPoint quality: Return only the results having quality over the mean minus two times the standard deviation. */
	const ENTRYPOINTQUALITY_LOW = 3;
	/** EntryPoint quality: Return all the results. */
	const ENTRYPOINTQUALITY_ALL = 4;
	
	/**
	 * Query the EntryPoint API function.
	 *
	 * @param string $name The element description to search for.
	 *        Can be a stop point, an address, a place, a city.
	 * @param string $city The city where to search. Optional.
	 * @param integer $quality The results quality. One of the ENTRYPOINTQUALITY_* constants. Optional. Default to ENTRYPOINTQUALITY_HIGH.
	 * @param integer $max The maximum number of results. Zero (0) for unlimited. Optional. Default to zero (0).
	 * @return tx_icslibnavitia_NodeList The list of results. Each element is a {@link tx_icslibnavitia_EntryPoint}.
	 */
	public function getEntryPointListByNameAndCity($name, $city = '', $quality = tx_icslibnavitia_APIService::ENTRYPOINTQUALITY_HIGH, $max = 0) {
		return $this->getEntryPointListByNameAndCityAndType($name, $city, $quality, $max, 'All');
	}

	/**
	 * Query the EntryPoint API function.
	 *
	 * @param string $name The element description to search for.
	 *        Can be a stop point, an address, a place, a city.
	 * @param string $city The city where to search. Optional.
	 * @param integer $quality The results quality. One of the ENTRYPOINTQUALITY_* constants. Optional. Default to ENTRYPOINTQUALITY_HIGH.
	 * @param integer $max The maximum number of results. Zero (0) for unlimited. Optional. Default to zero (0).
	 * @param string $type The type of expected results. Optional. Default to All. Valid values are: All, StopArea, Address, Site, City.
	 * @return tx_icslibnavitia_NodeList The list of results. Each element is a {@link tx_icslibnavitia_EntryPoint}.
	 */
	public function getEntryPointListByNameAndCityAndType($name, $city = '', $quality = tx_icslibnavitia_APIService::ENTRYPOINTQUALITY_HIGH, $max = 0, $type = 'All') {
		if ($name == '') {
			if ($city == '') {
				tx_icslibnavitia_Debug::notice('Empty parameters for EntryPoint query');
				return FALSE;
			}
			else {
				$name = $city;
				$city = '';
			}
		}
		if ($type != 'All') {
			foreach (explode(';', $type) as $t) {
				if (!in_array($t, array('StopArea', 'Address', 'Site', 'City'))) {
					tx_icslibnavitia_Debug::notice('Invalid type in type list.');
					return FALSE;
				}
			}
		}
		$params = array();
		$params['Filter'] = $type;
		$params['Name'] = $name;
		if ($city != '')
			$params['CityName'] = $city;
		$params['RawData'] = $quality;
		if ($max > 0)
			$params['NbMax'] = $max;
		$xml = $this->CallAPI('EntryPoint', $params);
		if (!$xml) {
			tx_icslibnavitia_Debug::warning('Failed to call EntryPoint API; See devlog for additional information');
			return NULL;
		}
		return $this->getDataCached(function($obj) use($xml) {
			$reader = new XMLReader();
			$reader->XML($xml);
			if (!$obj->XMLMoveToRootElement($reader, 'ActionEntryPointList')) {
				tx_icslibnavitia_Debug::warning('Invalid response from EntryPoint API; See saved response for additional information');
				return NULL;
			}
			$reader->read();
			$list = t3lib_div::makeInstance('tx_icslibnavitia_NodeList', 'tx_icslibnavitia_EntryPoint');
			while ($reader->nodeType != XMLReader::END_ELEMENT) {
				if ($reader->nodeType == XMLReader::ELEMENT) {
					switch ($reader->name) {
						case 'Params':
							$obj->SkipChildren($reader);
							break;
						case 'EntryPointList':
							tx_icslibnavitia_Node::ReadList($reader, $list, array('EntryPoint' => 'tx_icslibnavitia_EntryPoint'));
							break;
						case 'PagerInfo':
							$obj->SkipChildren($reader);
							break;
						default:
							$obj->SkipChildren($reader);
					}
				}
				$reader->read();
			}
			return $list;
		});
	}
	
	/** PlanJourney kind: Compute the route to arrive as soon as possible. */
	const PLANJOURNEYKIND_ASAP = 1;
	/** PlanJourney kind: Compute the route with the least connections. */
	const PLANJOURNEYKIND_LEAST_CHANGES = 2;
	/** PlanJourney kind: Compute the route with the shortest walk distance. */
	const PLANJOURNEYKIND_LEAST_WALK = 3;
	
	/**
	 * Query the PlanJourney API function.
	 *
	 * @param tx_icslibnavitia_EntryPointDefinition $from The departure point.
	 * @param tx_icslibnavitia_EntryPointDefinition $to The arrival point.
	 * @param boolean $isStartTime If true, the departure time will be after {@link $when}, otherwise the arrival time will be before {@link $when}. Optional. Default to <code>true</code>.
	 * @param DateTime $when The time to arrive before or start after depending on {@link $isStartTime}. Optional. Default to today at midnight.
	 * @param integer $kind The kind of search to run. Optional. One of the PLANJOURNEYKIND_* constants. Optional. Default to PLANJOURNEYKIND_ASAP.
	 * @param integer $before Number of results before the best match. Optional. Default to zero (0).
	 * @param integer $after Number of results after the best match. Optional. Default to zero (0).
	 * @param array $binaryCriteria Result of a call to {@link getBinaryCriteria()}. Optional. Default to not defined.
	 * @param array $manageDisrupt //TODO: Comment. Optional. Default to FALSE.
	 * @return array The list of results and comments. 
	 *        Results are in the {@link tx_icslibnavitia_NodeList} in <code>JourneyResultList</code> key. Each element is a {@link tx_icslibnavitia_JourneyResult};
	 *        Comments are in the {@link tx_icslibnavitia_NodeList} in <code>CommentList</code> key. Not yet defined.
	 */
	public function getPlanJourney(tx_icslibnavitia_EntryPointDefinition $from, tx_icslibnavitia_EntryPointDefinition $to,
		$isStartTime = TRUE, DateTime $when = NULL, $kind = tx_icslibnavitia_APIService::PLANJOURNEYKIND_ASAP, $before = 0, $after = 0, $binaryCriteria = NULL, $manageDisrupt = FALSE) {
		$params = array();
		$params['Departure'] = (string)$from;
		$params['Arrival'] = (string)$to;
		$params['Sens'] = $isStartTime ? 1 : -1;
		if ($when == NULL) {
			$when = new DateTime();
			$when->setTime(0, 0, 0);
		}
		$params['Time'] = $when->format('H|i');
		$params['Date'] = $when->format('Y|m|d');
		$params['Criteria'] = $kind;
		$params['NbBefore'] = $before;
		$params['NbAfter'] = $after;
		if (is_array($binaryCriteria)) {
			$params['ModeType'] = $binaryCriteria['ModeType'];
			$params['Equipment'] = $binaryCriteria['Equipment'];
			$params['Vehicle'] = $binaryCriteria['Vehicle'];
		}
		if ($manageDisrupt) {
			$params['ManageDisrupt'] = 1;
		}
		$xml = $this->CallAPI('PlanJourney', $params);
		if (!$xml) {
			tx_icslibnavitia_Debug::warning('Failed to call PlanJourney API; See devlog for additional information');
			return NULL;
		}
		return $this->getDataCached(function($obj) use($xml) {
			$reader = new XMLReader();
			$reader->XML($xml);
			if (!$obj->XMLMoveToRootElement($reader, 'ActionJourneyResultList')) {
				tx_icslibnavitia_Debug::warning('Invalid response from PlanJourney API; See saved response for additional information');
				return NULL;
			}
			$reader->read();
			$jrs = t3lib_div::makeInstance('tx_icslibnavitia_NodeList', 'tx_icslibnavitia_JourneyResult');
			$comments = t3lib_div::makeInstance('tx_icslibnavitia_NodeList', 'tx_icslibnavitia_Comment');
			$impacts = t3lib_div::makeInstance('tx_icslibnavitia_NodeList', 'tx_icslibnavitia_Impact');
			$result = array(
				'JourneyResultList' => $jrs,
				'CommentList' => $comments,
				'ImpactList' => $impacts,
			);
			while ($reader->nodeType != XMLReader::END_ELEMENT) {
				if ($reader->nodeType == XMLReader::ELEMENT) {
					switch ($reader->name) {
						case 'Params':
							$obj->SkipChildren($reader);
							break;
						case 'JourneyResultList':
							tx_icslibnavitia_Node::ReadList($reader, $jrs, array('JourneyResult' => 'tx_icslibnavitia_JourneyResult'));
							break;
						case 'CommentList':
							tx_icslibnavitia_Node::ReadList($reader, $comments, array('Comment' => 'tx_icslibnavitia_Comment'));
							break;
						case 'ImpactList':
							tx_icslibnavitia_Node::ReadList($reader, $impacts, array('Impact' => 'tx_icslibnavitia_Impact'));
							break;
						case 'PagerInfo':
							$obj->SkipChildren($reader);
							break;
						default:
							$obj->SkipChildren($reader);
					}
				}
				$reader->read();
			}
			return $result;
		});
	}
	
	/**
	 * Query the StreetNetwork API function.
	 *
	 * @param tx_icslibnavitia_Coord $start The start point.
	 * @param tx_icslibnavitia_Coord $end The end point.
	 * @param integer $walkSpeed The walk speed to use, in meters by minute. Optional. Default to 50.
	 * @param integer $hangDistanceStart The maximum distance used to hang to a road from the start point, in meters. Optional. Default to 1k.
	 * @param integer $hangDistanceEnd The maximum distance used to hang to a road from the end point, in meters. Optional. Default to {@link $hangDistanceStart}.
	 * @return tx_icslibnavitia_NodeList The list of segments composing the walk path. In no specific order. Each element is a {@link tx_icslibnavitia_Segment}.
	 */
	public function getStreetNetwork(tx_icslibnavitia_Coord $start, tx_icslibnavitia_Coord $end, $walkSpeed = 50, $hangDistanceStart = NULL, $hangDistanceEnd = NULL) {
		$params = array();
		$params['StartCoordX'] = str_replace('.', ',', $start->x);
		$params['StartCoordY'] = str_replace('.', ',', $start->y);
		$params['EndCoordX'] = str_replace('.', ',', $end->x);
		$params['EndCoordY'] = str_replace('.', ',', $end->y);
		$params['WalkSpeed'] = $walkSpeed;
		if ($hangDistanceStart == NULL)
			$hangDistanceStart = 1000;
		if ($hangDistanceEnd == NULL)
			$hangDistanceEnd = $hangDistanceStart;
		$params['HangDistanceDep'] = $hangDistanceStart;
		$params['HangDistanceArr'] = $hangDistanceEnd;
		$xml = $this->CallAPI('StreetNetwork', $params);
		if (!$xml) {
			tx_icslibnavitia_Debug::warning('Failed to call StreetNetwork API; See devlog for additional information');
			return NULL;
		}
		return $this->getDataCached(function($obj) use($xml) {
			$reader = new XMLReader();
			$reader->XML($xml);
			if (!$obj->XMLMoveToRootElement($reader, 'ActionStreetNetwork')) {
				tx_icslibnavitia_Debug::warning('Invalid response from StreetNetwork API; See saved response for additional information');
				return NULL;
			}
			$reader->read();
			$list = t3lib_div::makeInstance('tx_icslibnavitia_NodeList', 'tx_icslibnavitia_Segment');
			while ($reader->nodeType != XMLReader::END_ELEMENT) {
				if ($reader->nodeType == XMLReader::ELEMENT) {
					switch ($reader->name) {
						case 'Params':
							$obj->SkipChildren($reader);
							break;
						case 'SegmentList':
							tx_icslibnavitia_Node::ReadList($reader, $list, array('Segment' => 'tx_icslibnavitia_Segment'));
							break;
						case 'PagerInfo':
							$obj->SkipChildren($reader);
							break;
						default:
							$obj->SkipChildren($reader);
					}
				}
				$reader->read();
			}
			return $list;
		});
	}
	
	/**
	 * Query the PTReferential API function for NetworkList.
	 *
	 * @return tx_icslibnavitia_INodeList The list of available networks. Each element is a {@link tx_icslibnavitia_Network}.
	 */
	public function getNetworkList() {
		return $this->_getNetworkList();
	}
	
	/**
	 * Query the PTReferential API function for a Network.
	 *
	 * @param string $networkExternalCode The unique identifier of the network.
	 * @return tx_icslibnavitia_Network The requested network or null if not found.
	 */
	public function getNetworkByCode($networkExternalCode) {
		$list = $this->_getNetworkList($networkExternalCode);
		return ($list->Count() == 0) ? NULL : $list->Get(0);
	}
	
	/**
	 * Query the PTReferential API function for a set of Networks.
	 *
	 * @param array $networkExternalCodes The list of network's unique identifier.
	 * @return tx_icslibnavitia_INodeList The list of requested networks. Each element is a {@link tx_icslibnavitia_Network}.
	 */
	public function getNetworksByCodes(array $networkExternalCodes) {
		if (empty($networkExternalCodes)) return t3lib_div::makeInstance('tx_icslibnavitia_NodeList', 'tx_icslibnavitia_Network');
		return $this->_getNetworkList(implode(';', $networkExternalCodes));
	}
	
	/**
	 * Query the PTReferential API function for NetworkList.
	 *
	 * @param string $networkExternalCode The unique identifier of the network. Optional.
	 * @return tx_icslibnavitia_INodeList The list of available networks. Each element is a {@link tx_icslibnavitia_Network}.
	 */
	private function _getNetworkList($networkExternalCode = NULL) {
		$params = array();
		$params['RequestedType'] = 'NetworkList';
		if ($networkExternalCode !== NULL)
			$params['NetworkExternalCode'] = $networkExternalCode;
		$xml = $this->CallAPI('PTReferential', $params);
		if (!$xml) {
			tx_icslibnavitia_Debug::warning('Failed to call PTReferential API; See devlog for additional information');
			return NULL;
		}
		return $this->getDataCached(function($obj) use($xml) {
			$reader = new XMLReader();
			$reader->XML($xml);
			if (!$obj->XMLMoveToRootElement($reader, 'ActionNetworkList')) {
				tx_icslibnavitia_Debug::warning('Invalid response from PTReferential API; See saved response for additional information');
				return NULL;
			}
			$reader->read();
			$list = t3lib_div::makeInstance('tx_icslibnavitia_NodeList', 'tx_icslibnavitia_Network');
			while ($reader->nodeType != XMLReader::END_ELEMENT) {
				if ($reader->nodeType == XMLReader::ELEMENT) {
					switch ($reader->name) {
						case 'Params':
							$obj->SkipChildren($reader);
							break;
						case 'NetworkList':
							tx_icslibnavitia_Node::ReadList($reader, $list, array('Network' => 'tx_icslibnavitia_Network'));
							break;
						case 'PagerInfo':
							$obj->SkipChildren($reader);
							break;
						default:
							$obj->SkipChildren($reader);
					}
				}
				$reader->read();
			}
			return $list;
		});
	}
	
	/**
	 * Query the PTReferential API function for LineList.
	 *
	 * @param tx_icslibnavitia_INodeList $networks Networks used to filter the query. List element are instance of {@tx_icslibnavitia_Network}. Optional.
	 * @return tx_icslibnavitia_INodeList The list of available lines. Each element is a {@link tx_icslibnavitia_Line}.
	 */
	public function getLineList(tx_icslibnavitia_INodeList $networks = NULL) {
		return $this->_getLineList($networks);
	}
	
	/**
	 * Query the PTReferential API function for a Line.
	 *
	 * @param string $lineExternalCode The unique identifier of the line.
	 * @return tx_icslibnavitia_Line The requested line or null if not found.
	 */
	public function getLineByCode($lineExternalCode) {
		$list = $this->_getLineList(NULL, $lineExternalCode);
		return ($list->Count() == 0) ? NULL : $list->Get(0);
	}
	
	/**
	 * Query the PTReferential API function for LineList filtered by stop area.
	 *
	 * @param string $stopAreaExternalCode The unique identifier of the filtering stop area.
	 * @param tx_icslibnavitia_INodeList $networks Networks used to filter the query. List element are instance of {@tx_icslibnavitia_Network}. Optional.
	 * @return tx_icslibnavitia_INodeList The list of matching lines. Each element is a {@link tx_icslibnavitia_Line}.
	 */
	public function getLineListByStopAreaCode($stopAreaExternalCode, tx_icslibnavitia_INodeList $networks = NULL) {
		return $this->_getLineList($networks, NULL, array('StopArea' => array($stopAreaExternalCode)));
	}
	
	/**
	 * Query the PTReferential API function for LineList.
	 *
	 * @param tx_icslibnavitia_INodeList $networks Networks used to filter the query. List element are instance of {@tx_icslibnavitia_Network}. Optional.
	 * @param string $lineExternalCode The unique identifier of the line. Optional.
	 * @param array $filters Other elements external codes. Available filters are: ModeType, Mode, Company, VehicleJourney, Connection, StopPoint, StopArea, RoutePoint, Route, District, Department, City, ODT, Destination.
	 *        Each filter is an array of externalCodes.
	 * @return tx_icslibnavitia_INodeList The list of available lines. Each element is a {@link tx_icslibnavitia_Line}.
	 */
	private function _getLineList(tx_icslibnavitia_INodeList $networks = NULL, $lineExternalCode = NULL, array $filters = array()) {
		$params = array();
		$params['RequestedType'] = 'LineList';
		if ($networks != NULL) {
			$networkCodes = array();
			foreach ($networks->ToArray() as $network) {
				if ($network instanceof tx_icslibnavitia_Network) {
					if (!empty($network->externalCode)) {
						$networkCodes[] = $network->externalCode;
					}
				}
			}
			if (!empty($networkCodes)) {
				$params['NetworkExternalCode'] = implode(';', $networkCodes);
			}
		}
		if ($lineExternalCode !== NULL) {
			$params['LineExternalCode'] = $lineExternalCode;
		}
		else if (!empty($filters)) {
			foreach ($filters as $type => $values) {
				$params[$type . 'ExternalCode'] = implode(';', $values);
			}
		}
		$xml = $this->CallAPI('PTReferential', $params);
		if (!$xml) {
			tx_icslibnavitia_Debug::warning('Failed to call PTReferential API; See devlog for additional information');
			return NULL;
		}
		return $this->getDataCached(function($obj) use($xml) {
			$reader = new XMLReader();
			$reader->XML($xml);
			if (!$obj->XMLMoveToRootElement($reader, 'ActionLineList')) {
				tx_icslibnavitia_Debug::warning('Invalid response from PTReferential API; See saved response for additional information');
				return NULL;
			}
			$reader->read();
			$list = t3lib_div::makeInstance('tx_icslibnavitia_NodeList', 'tx_icslibnavitia_Line');
			while ($reader->nodeType != XMLReader::END_ELEMENT) {
				if ($reader->nodeType == XMLReader::ELEMENT) {
					switch ($reader->name) {
						case 'Params':
							$obj->SkipChildren($reader);
							break;
						case 'LineList':
							tx_icslibnavitia_Node::ReadList($reader, $list, array('Line' => 'tx_icslibnavitia_Line'));
							break;
						case 'PagerInfo':
							$obj->SkipChildren($reader);
							break;
						default:
							$obj->SkipChildren($reader);
					}
				}
				$reader->read();
			}
			return $list;
		});
	}
	
	/**
	 * Query the RoutePointList API function.
	 *
	 * @param string $lineExternalCode The unique identifier of the line.
	 * @param boolean $forward Indicates if the direction is forward. Otherwise backward. Optional. Default to forward (true).
	 * @return tx_icslibnavitia_INodeList The list of available route points. Each element is a {@link tx_icslibnavitia_RoutePoint}.
	 */
	public function getRoutePointList($lineExternalCode, $forward = TRUE) {
		return $this->_getRoutePointList(NULL, $lineExternalCode, $forward);
	}
	
	/**
	 * Query the RoutePointList API function.
	 *
	 * @param string $routePointExternalCode The unique identifier of the route point.
	 * @return tx_icslibnavitia_RoutePoint The requested route point or null if not found.
	 */
	public function getRoutePointByCode($routePointExternalCode) {
		$list = $this->_getRoutePointList($routePointExternalCode);
		return ($list->Count() == 0) ? NULL : $list->Get(0);
	}
	
	/**
	 * Query the RoutePointList API function.
	 *
	 * @param string $routePointExternalCode The unique identifier of the route point. Optional.
	 * @param string $lineExternalCode The unique identifier of the line. Optional if {@link $routePointExternalCode} is specified.
	 * @param boolean $forward Indicates if the direction is forward. Otherwise backward. Optional. Default to forward (true).
	 * @return tx_icslibnavitia_INodeList The list of available route points. Each element is a {@link tx_icslibnavitia_RoutePoint}.
	 */
	private function _getRoutePointList($routePointExternalCode = NULL, $lineExternalCode = NULL, $forward = TRUE) {
		$params = array();
		if ($routePointExternalCode !== NULL) {
			$params['RoutePointExternalCode'] = $routePointExternalCode;
		}
		else {
			$params['LineExternalCode'] = $lineExternalCode;
			$params['Sens'] = $forward ? 1 : -1;
		}
		$xml = $this->CallAPI('RoutePointList', $params);
		if (!$xml) {
			tx_icslibnavitia_Debug::warning('Failed to call RoutePointList API; See devlog for additional information');
			return NULL;
		}
		return $this->getDataCached(function($obj) use($xml) {
			$reader = new XMLReader();
			$reader->XML($xml);
			if (!$obj->XMLMoveToRootElement($reader, 'ActionRoutePointList')) {
				tx_icslibnavitia_Debug::warning('Invalid response from RoutePointList API; See saved response for additional information');
				return NULL;
			}
			$reader->read();
			$list = t3lib_div::makeInstance('tx_icslibnavitia_NodeList', 'tx_icslibnavitia_RoutePoint');
			while ($reader->nodeType != XMLReader::END_ELEMENT) {
				if ($reader->nodeType == XMLReader::ELEMENT) {
					switch ($reader->name) {
						case 'Params':
							$obj->SkipChildren($reader);
							break;
						case 'RoutePointList':
							tx_icslibnavitia_Node::ReadList($reader, $list, array('RoutePoint' => 'tx_icslibnavitia_RoutePoint'));
							break;
						case 'PagerInfo':
							$obj->SkipChildren($reader);
							break;
						default:
							$obj->SkipChildren($reader);
					}
				}
				$reader->read();
			}
			return $list;
		});
	}

	/**
	 * Query the PTReferential API function for StopAreaList for a single line.
	 *
	 * @param string $lineExternalCode The unique identifier of the line.
	 * @return tx_icslibnavitia_INodeList The list of line attached stop areas. Each element is a {@link tx_icslibnavitia_StopArea}.
	 */
	public function getStopAreasByLineCode($lineExternalCode) {
		$params = array();
		$params['RequestedType'] = 'StopAreaList';
		if ($lineExternalCode !== NULL) {
			$params['LineExternalCode'] = $lineExternalCode;
		}
		$xml = $this->CallAPI('PTReferential', $params);
		if (!$xml) {
			tx_icslibnavitia_Debug::warning('Failed to call PTReferential API; See devlog for additional information');
			return NULL;
		}
		return $this->getDataCached(function($obj) use($xml) {
			$reader = new XMLReader();
			$reader->XML($xml);
			if (!$obj->XMLMoveToRootElement($reader, 'ActionStopAreaList')) {
				tx_icslibnavitia_Debug::warning('Invalid response from PTReferential API; See saved response for additional information');
				return NULL;
			}
			$reader->read();
			$list = t3lib_div::makeInstance('tx_icslibnavitia_NodeList', 'tx_icslibnavitia_StopArea');
			while ($reader->nodeType != XMLReader::END_ELEMENT) {
				if ($reader->nodeType == XMLReader::ELEMENT) {
					switch ($reader->name) {
						case 'Params':
							$obj->SkipChildren($reader);
							break;
						case 'StopAreaList':
							tx_icslibnavitia_Node::ReadList($reader, $list, array('StopArea' => 'tx_icslibnavitia_StopArea'));
							break;
						case 'PagerInfo':
							$obj->SkipChildren($reader);
							break;
						default:
							$obj->SkipChildren($reader);
					}
				}
				$reader->read();
			}
			return $list;
		});
	}

	/**
	 * Query the PTReferential API function for StopPointList for a single line.
	 *
	 * @param string $lineExternalCode The unique identifier of the line.
	 * @return tx_icslibnavitia_INodeList The list of line attached stop points. Each element is a {@link tx_icslibnavitia_StopPoint}.
	 */
	public function getStopPointsByLineCode($lineExternalCode) {
		$params = array();
		$params['RequestedType'] = 'StopPointList';
		if ($lineExternalCode !== NULL) {
			$params['LineExternalCode'] = $lineExternalCode;
		}
		$xml = $this->CallAPI('PTReferential', $params);
		if (!$xml) {
			tx_icslibnavitia_Debug::warning('Failed to call PTReferential API; See devlog for additional information');
			return NULL;
		}
		return $this->getDataCached(function($obj) use($xml) {
			$reader = new XMLReader();
			$reader->XML($xml);
			if (!$obj->XMLMoveToRootElement($reader, 'ActionStopPointList')) {
				tx_icslibnavitia_Debug::warning('Invalid response from PTReferential API; See saved response for additional information');
				return NULL;
			}
			$reader->read();
			$list = t3lib_div::makeInstance('tx_icslibnavitia_NodeList', 'tx_icslibnavitia_StopPoint');
			while ($reader->nodeType != XMLReader::END_ELEMENT) {
				if ($reader->nodeType == XMLReader::ELEMENT) {
					switch ($reader->name) {
						case 'Params':
							$obj->SkipChildren($reader);
							break;
						case 'StopPointList':
							tx_icslibnavitia_Node::ReadList($reader, $list, array('StopPoint' => 'tx_icslibnavitia_StopPoint'));
							break;
						case 'PagerInfo':
							$obj->SkipChildren($reader);
							break;
						default:
							$obj->SkipChildren($reader);
					}
				}
				$reader->read();
			}
			return $list;
		});
	}
	
	/**
	 * Query the DepartureBoard API function. Using stop point.
	 *
	 * @param string $stopPointExternalCode The unique identifier of the stop point.
	 * @param string $lineExternalCode The unique identifier of the line.
	 * @param DateTime $when For which date to query the departure board.
	 * @param boolean $forward Indicates if the direction is forward. Otherwise backward. Optional. Default to forward (true).
	 * @param integer $startDayAt Number of minutes after midnight to set at the day start time. It is used to offset the time range like for TV shows.
	 *        For example, if set to 300 (5h00), the search will start at 5 o'clock for the current search day and end before 5 o'clock the next day.
	 * @return array The list of results and additional information. 
	 *        Results are in the {@link tx_icslibnavitia_NodeList} in <code>StopList</code> key. Each element is a {@link tx_icslibnavitia_Stop};
	 *        Stop points are in the {@link tx_icslibnavitia_NodeList} in <code>StopPointList</code> key. Each element is a {@link tx_icslibnavitia_StopPoint};
	 *        Lines are in the {@link tx_icslibnavitia_NodeList} in <code>LineList</code> key. Each element is a {@link tx_icslibnavitia_Line};
	 *        Routes are in the {@link tx_icslibnavitia_NodeList} in <code>RouteList</code> key. Each element is a {@link tx_icslibnavitia_Route};
	 *        Vehicle journeys are in the {@link tx_icslibnavitia_NodeList} in <code>VehicleJourneyList</code> key. Each element is a {@link tx_icslibnavitia_VehicleJourney};
	 *        Destinations are in the {@link tx_icslibnavitia_NodeList} in <code>DestinationList</code> key. Each element is a {@link tx_icslibnavitia_StopArea}.
	 */
	public function getDepartureBoardByStopPointForLine($stopPointExternalCode, $lineExternalCode, DateTime $when = NULL, $forward = TRUE, $startDayAt = 0) {
		return $this->_getDepartureBoardByStopPointOrAreaForLine($stopPointExternalCode, FALSE, $lineExternalCode, $when, $forward, $startDayAt);
	}
	
	/**
	 * Query the DepartureBoard API function. Using stop area.
	 *
	 * @param string $stopAreaExternalCode The unique identifier of the stop area.
	 * @param string $lineExternalCode The unique identifier of the line.
	 * @param DateTime $when For which date to query the departure board.
	 * @param boolean $forward Indicates if the direction is forward. Otherwise backward. Optional. Default to forward (true).
	 * @param integer $startDayAt Number of minutes after midnight to set at the day start time. It is used to offset the time range like for TV shows.
	 *        For example, if set to 300 (5h00), the search will start at 5 o'clock for the current search day and end before 5 o'clock the next day.
	 * @return array The list of results and additional information. 
	 *        Results are in the {@link tx_icslibnavitia_NodeList} in <code>StopList</code> key. Each element is a {@link tx_icslibnavitia_Stop};
	 *        Stop points are in the {@link tx_icslibnavitia_NodeList} in <code>StopPointList</code> key. Each element is a {@link tx_icslibnavitia_StopPoint};
	 *        Lines are in the {@link tx_icslibnavitia_NodeList} in <code>LineList</code> key. Each element is a {@link tx_icslibnavitia_Line};
	 *        Routes are in the {@link tx_icslibnavitia_NodeList} in <code>RouteList</code> key. Each element is a {@link tx_icslibnavitia_Route};
	 *        Vehicle journeys are in the {@link tx_icslibnavitia_NodeList} in <code>VehicleJourneyList</code> key. Each element is a {@link tx_icslibnavitia_VehicleJourney};
	 *        Destinations are in the {@link tx_icslibnavitia_NodeList} in <code>DestinationList</code> key. Each element is a {@link tx_icslibnavitia_StopArea}.
	 */
	public function getDepartureBoardByStopAreaForLine($stopAreaExternalCode, $lineExternalCode, DateTime $when = NULL, $forward = TRUE, $startDayAt = 0) {
		return $this->_getDepartureBoardByStopPointOrAreaForLine(FALSE, $stopAreaExternalCode, $lineExternalCode, $when, $forward, $startDayAt);
	}
	
	/**
	 * Query the DepartureBoard API function.
	 *
	 * @param string $stopPointExternalCode The unique identifier of the stop point.
	 * @param string $stopAreaExternalCode The unique identifier of the stop area.
	 * @param string $lineExternalCode The unique identifier of the line.
	 * @param DateTime $when For which date to query the departure board.
	 * @param boolean $forward Indicates if the direction is forward. Otherwise backward. Optional. Default to forward (true).
	 * @param integer $startDayAt Number of minutes after midnight to set at the day start time. It is used to offset the time range like for TV shows.
	 *        For example, if set to 300 (5h00), the search will start at 5 o'clock for the current search day and end before 5 o'clock the next day.
	 * @return array The list of results and additional information. 
	 *        Results are in the {@link tx_icslibnavitia_NodeList} in <code>StopList</code> key. Each element is a {@link tx_icslibnavitia_Stop};
	 *        Stop points are in the {@link tx_icslibnavitia_NodeList} in <code>StopPointList</code> key. Each element is a {@link tx_icslibnavitia_StopPoint};
	 *        Lines are in the {@link tx_icslibnavitia_NodeList} in <code>LineList</code> key. Each element is a {@link tx_icslibnavitia_Line};
	 *        Routes are in the {@link tx_icslibnavitia_NodeList} in <code>RouteList</code> key. Each element is a {@link tx_icslibnavitia_Route};
	 *        Vehicle journeys are in the {@link tx_icslibnavitia_NodeList} in <code>VehicleJourneyList</code> key. Each element is a {@link tx_icslibnavitia_VehicleJourney};
	 *        Destinations are in the {@link tx_icslibnavitia_NodeList} in <code>DestinationList</code> key. Each element is a {@link tx_icslibnavitia_StopArea}.
	 */
	private function _getDepartureBoardByStopPointOrAreaForLine($stopPointExternalCode, $stopAreaExternalCode, $lineExternalCode, DateTime $when = NULL, $forward = TRUE, $startDayAt = 0) {
		$params = array();
		if ($stopPointExternalCode) $params['StopPointExternalCode'] = $stopPointExternalCode;
		if ($stopAreaExternalCode)$params['StopAreaExternalCode'] = $stopAreaExternalCode;
		$params['LineExternalCode'] = $lineExternalCode;
		$params['Sens'] = $forward ? 1 : -1;
		if ($when == NULL)
			$when = new DateTime();
		$params['Date'] = $when->format('Y|m|d');
		$startDayAt %= 1440; // 1440 minutes = 24 hours.
		if ($startDayAt > 0)
			$params['DateChangeTime'] = sprintf('%d|%d', $startDayAt / 60, $startDayAt % 60);
		$xml = $this->CallAPI('DepartureBoard', $params);
		if (!$xml) {
			tx_icslibnavitia_Debug::warning('Failed to call DepartureBoard API; See devlog for additional information');
			return NULL;
		}
		return $this->getDataCached(function($obj) use($xml) {
			$reader = new XMLReader();
			$reader->XML($xml);
			if (!$obj->XMLMoveToRootElement($reader, 'DepartureBoardList')) {
				tx_icslibnavitia_Debug::warning('Invalid response from DepartureBoard API; See saved response for additional information');
				return NULL;
			}
			$reader->read();
			$stops = t3lib_div::makeInstance('tx_icslibnavitia_NodeList', 'tx_icslibnavitia_Stop');
			$stopPoints = t3lib_div::makeInstance('tx_icslibnavitia_NodeList', 'tx_icslibnavitia_StopPoint');
			$lines = t3lib_div::makeInstance('tx_icslibnavitia_NodeList', 'tx_icslibnavitia_Line');
			$routes = t3lib_div::makeInstance('tx_icslibnavitia_NodeList', 'tx_icslibnavitia_Route');
			$journeys = t3lib_div::makeInstance('tx_icslibnavitia_NodeList', 'tx_icslibnavitia_VehicleJourney');
			$destinations = t3lib_div::makeInstance('tx_icslibnavitia_NodeList', 'tx_icslibnavitia_StopArea');
			$comments = t3lib_div::makeInstance('tx_icslibnavitia_NodeList', 'tx_icslibnavitia_Comment');
			$impacts = t3lib_div::makeInstance('tx_icslibnavitia_NodeList', 'tx_icslibnavitia_Impact');
			$result = array(
				'StopList' => $stops,
				'StopPointList' => $stopPoints,
				'LineList' => $lines,
				'RouteList' => $routes,
				'VehicleJourneyList' => $journeys,
				'DestinationList' => $destinations,
				'CommentList' => $comments,
				'ImpactList' => $impacts,
			);
			while ($reader->nodeType != XMLReader::END_ELEMENT) {
				if ($reader->nodeType == XMLReader::ELEMENT) {
					switch ($reader->name) {
						case 'Params':
							$obj->SkipChildren($reader);
							break;
						case 'StopList':
							tx_icslibnavitia_Node::ReadList($reader, $stops, array('Stop' => 'tx_icslibnavitia_Stop'));
							break;
						case 'StopPointList':
							tx_icslibnavitia_Node::ReadList($reader, $stopPoints, array('StopPoint' => 'tx_icslibnavitia_StopPoint'));
							break;
						case 'LineList':
							tx_icslibnavitia_Node::ReadList($reader, $lines, array('Line' => 'tx_icslibnavitia_Line'));
							break;
						case 'RouteList':
							tx_icslibnavitia_Node::ReadList($reader, $routes, array('Route' => 'tx_icslibnavitia_Route'));
							break;
						case 'VehicleJourneyList':
							tx_icslibnavitia_Node::ReadList($reader, $journeys, array('VehicleJourney' => 'tx_icslibnavitia_VehicleJourney'));
							break;
						case 'DestinationList':
							tx_icslibnavitia_Node::ReadList($reader, $destinations, array('StopArea' => 'tx_icslibnavitia_StopArea'));
							break;
						case 'CommentList':
							tx_icslibnavitia_Node::ReadList($reader, $comments, array('Comment' => 'tx_icslibnavitia_Comment'));
							break;
						case 'ImpactList':
							tx_icslibnavitia_Node::ReadList($reader, $impacts, array('Impact' => 'tx_icslibnavitia_Impact'));
							break;
						case 'PagerInfo':
							$obj->SkipChildren($reader);
							break;
						default:
							$obj->SkipChildren($reader);
					}
				}
				$reader->read();
			}
			return $result;
		});
	}
	
	/**
	 * Query the PTReferential API function for ModeTypeList.
	 *
	 * @return tx_icslibnavitia_INodeList The list of available mode types. Each element is a {@link tx_icslibnavitia_ModeType}.
	 */
	public function getModeTypeList() {
		return $this->_getModeTypeList();
	}
	
	/**
	 * Query the PTReferential API function for a set of ModeTypes.
	 *
	 * @param array $modeTypeExternalCodes The list of modeType's unique identifier.
	 * @return tx_icslibnavitia_INodeList The list of requested modeTypes. Each element is a {@link tx_icslibnavitia_ModeType}.
	 */
	public function getModeTypesByCodes(array $modeTypeExternalCodes) {
		if (empty($modeTypeExternalCodes)) return t3lib_div::makeInstance('tx_icslibnavitia_NodeList', 'tx_icslibnavitia_ModeType');
		return $this->_getModeTypeList(implode(';', $modeTypeExternalCodes));
	}
	
	/**
	 * Query the PTReferential API function for ModeTypeList.
	 *
	 * @param string $modeTypeExternalCode The identifier of the modeType. Optional.
	 * @return tx_icslibnavitia_INodeList The list of available mode types. Each element is a {@link tx_icslibnavitia_ModeType}.
	 */
	private function _getModeTypeList($modeTypeExternalCode = NULL) {
		$params = array();
		$params['RequestedType'] = 'ModeTypeList';
		if ($modeTypeExternalCode !== NULL)
			$params['ModeTypeExternalCode'] = $modeTypeExternalCode;
		$xml = $this->CallAPI('PTReferential', $params);
		if (!$xml) {
			tx_icslibnavitia_Debug::warning('Failed to call PTReferential API; See devlog for additional information');
			return NULL;
		}
		return $this->getDataCached(function($obj) use($xml) {
			$reader = new XMLReader();
			$reader->XML($xml);
			if (!$obj->XMLMoveToRootElement($reader, 'ActionModeTypeList')) {
				tx_icslibnavitia_Debug::warning('Invalid response from PTReferential API; See saved response for additional information');
				return NULL;
			}
			$reader->read();
			$list = t3lib_div::makeInstance('tx_icslibnavitia_NodeList', 'tx_icslibnavitia_ModeType');
			while ($reader->nodeType != XMLReader::END_ELEMENT) {
				if ($reader->nodeType == XMLReader::ELEMENT) {
					switch ($reader->name) {
						case 'Params':
							$obj->SkipChildren($reader);
							break;
						case 'ModeTypeList':
							tx_icslibnavitia_Node::ReadList($reader, $list, array('ModeType' => 'tx_icslibnavitia_ModeType'));
							break;
						default:
							$obj->SkipChildren($reader);
					}
				}
				$reader->read();
			}
			return $list;
		});
	}

	/** Binary Criteria, Vehicle: Requires a vehicle with MIP access. */
	const CRITERIA_VEHICLE_MIP = 'VKMIPAccess';
	/** Binary Criteria, Vehicle: Requires a vehicle accepting bikes. */
	const CRITERIA_VEHICLE_BIKE = 'VKBikeAccepted';
	/** Binary Criteria, Vehicle: Requires a vehicle with air conditioning. */
	const CRITERIA_VEHICLE_AIR_CONDITIONING = 'VKAirConditioned';
	/** Binary Criteria, Vehicle: Requires a vehicle with visual display. */
	const CRITERIA_VEHICLE_VISUAL_NOTICE = 'VKVisualAnnoucement';
	/** Binary Criteria, Vehicle: Requires a vehicle with audible notice. */
	const CRITERIA_VEHICLE_AUDIBLE_NOTICE = 'VKAudibleAnnoucement';
	/** Binary Criteria, Stop point: Requires a stop point with shelter. */
	const CRITERIA_STOP_SHELTERED = 'SPSheltered';
	/** Binary Criteria, Stop point: Requires a stop point designed for MIP access. */
	const CRITERIA_STOP_MIP = 'SPMIPAccess';
	/** Binary Criteria, Stop point: Requires a stop point with an elevator. */
	const CRITERIA_STOP_ELEVATOR = 'SPElevator';
	/** Binary Criteria, Stop point: Requires a stop point with an escalator. */
	const CRITERIA_STOP_ESCALATOR = 'SPEscalator';
	/** Binary Criteria, Stop point: Requires a stop point with bike access. */
	const CRITERIA_STOP_BIKE = 'SPBikeAccepted';
	/** Binary Criteria, Stop point: Requires a stop point with a bike park. */
	const CRITERIA_STOP_BIKE_PARK = 'SPBikeDepot';
	/** Binary Criteria, Stop point: Requires a stop point with visual display. */
	const CRITERIA_STOP_VISUAL_NOTICE = 'SPVisualAnnoucement';
	/** Binary Criteria, Stop point: Requires a stop point with audible notice. */
	const CRITERIA_STOP_AUDIBLE_NOTICE = 'SPAudibleAnnoucement';

	/**
	 * Query the MakeBinaryCriteria API function for criteria value.
	 * 
	 * @param array $modeTypeExternalCodes The list of mode type external code to restrict the criteria to. Use the element <code>All</code> alone to select all.
	 * @param array $flags The boolean flags to add to the criteria. Optional. Default to none.
	 * @return array The array with criteria values: Vehicle, StopPointEquipment, ModeType.
	 */
	public function getBinaryCriteria(array $modeTypeExternalCodes, array $flags = array()) {
		static $availableFlags = NULL;
		if ($availableFlags == NULL) {
			$reflection = new ReflectionClass(get_class($this));
			$availableFlags = $reflection->getConstants();
			foreach (array_keys($availableFlags) as $key) {
				if (!preg_match('/^CRITERIA/', $key)) unset($availableFlags[$key]);
			}
		}
		$params = array();
		foreach ($flags as $flag) {
			if (in_array($flag, $availableFlags)) $params[$flag] = 1;
		}
		if (!empty($modeTypeExternalCodes)) {
			$params['ModeTypeExternalCode'] = implode(';', $modeTypeExternalCodes);
		}
		$xml = $this->CallAPI('MakeBinaryCriteria', $params);
		if (!$xml) {
			tx_icslibnavitia_Debug::warning('Failed to call MakeBinaryCriteria API; See devlog for additional information');
			return NULL;
		}
		return $this->getDataCached(function($obj) use($xml) {
			$reader = new XMLReader();
			$reader->XML($xml);
			if (!$obj->XMLMoveToRootElement($reader, 'BinaryCriteria')) {
				tx_icslibnavitia_Debug::warning('Invalid response from MakeBinaryCriteria API; See saved response for additional information');
				return NULL;
			}
			$reader->read();
			$values = array();
			while ($reader->nodeType != XMLReader::END_ELEMENT) {
				if ($reader->nodeType == XMLReader::ELEMENT) {
					switch ($reader->name) {
						case 'Params':
						case 'Mode':
							$obj->SkipChildren($reader);
							break;
						default:
							$values[$reader->name] = $reader->readString();
							$obj->SkipChildren($reader);
					}
				}
				$reader->read();
			}
			return $values;
		});
	}

	/**
	 * Query the NextDeparture API function.
	 *
	 * @param string $stopAreaExternalCode The unique identifier of the stop area.
	 * @param string $lineExternalCode The unique identifier of the line.
	 * @param boolean $forward Indicates if the direction is forward. Otherwise backward. Optional. Default to forward (true).
	 * @param integer $count The maximum number of stop times to obtain. Optional. Default to five (5).
	 * @param integer $startDayAt Number of minutes after midnight to set at the day start time. It is used to offset the time range like for TV shows.
	 *        For example, if set to 300 (5h00), the search will start at 5 o'clock for the current search day and end before 5 o'clock the next day.
	 * @param boolean $noNextDay Indicates if the results don't span over the next service day.
	 * @return tx_icslibnavitia_INodeList The list of next stops by chronological order. Each element is a {@link tx_icslibnavitia_Stop}.
	 */
	public function getNextDepartureByStopAreaForLine($stopAreaExternalCode, $lineExternalCode, $forward = TRUE, $count = 5, $startDayAt = 0, $noNextDay = TRUE) {
		$params = array();
		$params['StopAreaExternalCode'] = $stopAreaExternalCode;
		$params['LineExternalCode'] = $lineExternalCode;
		$params['Sens'] = $forward ? 1 : -1;
		$params['NbStop'] = $count;
		$startDayAt %= 1440; // 1440 minutes = 24 hours.
		if ($startDayAt > 0)
			$params['DateChangeTime'] = sprintf('%d|%d', $startDayAt / 60, $startDayAt % 60);
		$params['UseTransday'] = $noNextDay ? 0 : 1;
		$xml = $this->CallAPI('NextDeparture', $params);
		if (!$xml) {
			tx_icslibnavitia_Debug::warning('Failed to call NextDeparture API; See devlog for additional information');
			return NULL;
		}
		return $this->getDataCached(function($obj) use($xml) {
			$reader = new XMLReader();
			$reader->XML($xml);
			if (!$obj->XMLMoveToRootElement($reader, 'ActionNextDepartureList')) {
				tx_icslibnavitia_Debug::warning('Invalid response from NextDeparture API; See saved response for additional information');
				return NULL;
			}
			$reader->read();
			$stops = t3lib_div::makeInstance('tx_icslibnavitia_NodeList', 'tx_icslibnavitia_Stop');
			while ($reader->nodeType != XMLReader::END_ELEMENT) {
				if ($reader->nodeType == XMLReader::ELEMENT) {
					switch ($reader->name) {
						case 'Params':
							$obj->SkipChildren($reader);
							break;
						case 'NextDepartureList':
							tx_icslibnavitia_Node::ReadList($reader, $stops, array('Stop' => 'tx_icslibnavitia_Stop'));
							break;
						case 'PagerInfo':
							$obj->SkipChildren($reader);
							break;
						default:
							$obj->SkipChildren($reader);
					}
				}
				$reader->read();
			}
			return $stops;
		});
	}
	
	/**
	 * Query the PTReferential API function for StopAreaList.
	 *
	 * @param string stopAreaExternalCode The external code to look for.
	 * @return tx_icslibnavitia_StopArea The requested stop area.
	 */
	public function getStopAreaByCode($stopAreaExternalCode) {
		$list = $this->_getStopAreaList(array($stopAreaExternalCode));
		if ($list->Count() > 0) return $list->Get(0);
		return NULL;
	}
	
	/**
	 * Query the PTReferential API function for StopAreaList.
	 *
	 * @return tx_icslibnavitia_INodeList The list of available mode types. Each element is a {@link tx_icslibnavitia_StopArea}.
	 */
	private function _getStopAreaList(array $stopAreaExternalCodes) {
		$params = array();
		$params['RequestedType'] = 'StopAreaList';
		if (empty($stopAreaExternalCodes))
			return t3lib_div::makeInstance('tx_icslibnavitia_NodeList', 'tx_icslibnavitia_StopArea');
		$params['StopAreaExternalCode'] = implode(';', $stopAreaExternalCodes);
		$xml = $this->CallAPI('PTReferential', $params);
		if (!$xml) {
			tx_icslibnavitia_Debug::warning('Failed to call PTReferential API; See devlog for additional information');
			return NULL;
		}
		return $this->getDataCached(function($obj) use($xml) {
			$reader = new XMLReader();
			$reader->XML($xml);
			if (!$obj->XMLMoveToRootElement($reader, 'ActionStopAreaList')) {
				tx_icslibnavitia_Debug::warning('Invalid response from PTReferential API; See saved response for additional information');
				return NULL;
			}
			$reader->read();
			$list = t3lib_div::makeInstance('tx_icslibnavitia_NodeList', 'tx_icslibnavitia_StopArea');
			while ($reader->nodeType != XMLReader::END_ELEMENT) {
				if ($reader->nodeType == XMLReader::ELEMENT) {
					switch ($reader->name) {
						case 'Params':
							$obj->SkipChildren($reader);
							break;
						case 'StopAreaList':
							tx_icslibnavitia_Node::ReadList($reader, $list, array('StopArea' => 'tx_icslibnavitia_StopArea'));
							break;
						default:
							$obj->SkipChildren($reader);
					}
				}
				$reader->read();
			}
			return $list;
		});
	}
	
	/**
	 * Query the ProximityList API function for nearest StopArea.
	 *
	 * @param tx_icslibnavitia_Coord $coordinates The coordinates of the start point.
	 * @param integer $distance Maximum search distance, in meters. Optional. Default to 1000. Circle radius or half square side lenght.
	 * @param integer $min Minimum number of results. Optional. Default to 0.
	 * @param integer $max Maximum number of results. Optional. Default to 100.
	 * @param boolean $circle Force to maximum distance to be checked using a circle instead of a square.
	 * @return tx_icslibnavitia_INodeList The list of nearest stop area. Each element is a {@link tx_icslibnavitia_Proximity}.
	 *         stopArea and distance in each elements are defined.
	 */
	public function getStopAreaProximityList(tx_icslibnavitia_Coord $coordinates, $distance = 1000, $min = 0, $max = 100, $circle = TRUE) {
		return $this->_getProximityList('StopArea', $coordinates, $distance, $min, $max, $circle);
	}
	
	/**
	 * Query the ProximityList API function.
	 *
	 * @param string $type The type of searched elements. Can be Site, StopPoint, StopArea or MainStopArea. Invalid value replaced by StopPoint.
	 * @param tx_icslibnavitia_Coord $coordinates The coordinates of the start point.
	 * @param integer $distance Maximum search distance, in meters. Optional. Default to 1000. Circle radius or half square side lenght.
	 * @param integer $min Minimum number of results. Optional. Default to 0.
	 * @param integer $max Maximum number of results. Optional. Default to 100.
	 * @param boolean $circle Force to maximum distance to be checked using a circle instead of a square.
	 * @return tx_icslibnavitia_INodeList The list of nearest stop area, site, or stop point. Each element is a {@link tx_icslibnavitia_Proximity}.
	 */
	private function _getProximityList($type, tx_icslibnavitia_Coord $coordinates, $distance = 1000, $min = 0, $max = 100, $circle = TRUE) {
		$params = array();
		if (!in_array($type, array('Site', 'StopPoint', 'StopArea', 'MainStopArea')))
			$type = 'StopPoint';
		$params['Type'] = $type;
		$params['X'] = $coordinates->x;
		$params['Y'] = $coordinates->y;
		$params['Distance'] = $distance;
		$params['MinCount'] = $min;
		$params['NbMax'] = $max;
		$params['CircleFilter'] = $circle ? 1 : 0;
		$xml = $this->CallAPI('ProximityList', $params);
		if (!$xml) {
			tx_icslibnavitia_Debug::warning('Failed to call ProximityList API; See devlog for additional information');
			return NULL;
		}
		return $this->getDataCached(function($obj) use($xml) {
			$reader = new XMLReader();
			$reader->XML($xml);
			if (!$obj->XMLMoveToRootElement($reader, 'ActionProximityList')) {
				tx_icslibnavitia_Debug::warning('Invalid response from ProximityList API; See saved response for additional information');
				return NULL;
			}
			$reader->read();
			$list = t3lib_div::makeInstance('tx_icslibnavitia_NodeList', 'tx_icslibnavitia_Proximity');
			while ($reader->nodeType != XMLReader::END_ELEMENT) {
				if ($reader->nodeType == XMLReader::ELEMENT) {
					switch ($reader->name) {
						case 'Params':
							$obj->SkipChildren($reader);
							break;
						case 'ProximityList':
							tx_icslibnavitia_Node::ReadList($reader, $list, array('Proximity' => 'tx_icslibnavitia_Proximity'));
							break;
						default:
							$obj->SkipChildren($reader);
					}
				}
				$reader->read();
			}
			return $list;
		});
	}

	/**
	 * Query the EnOfCourse API function.
	 *
	 * @param int $stopIdx The internal identifier of the reference stop.
	 * @return array The list of stop points, beside the vehicule journey.
	 */
	public function getEndOfCourseByStopIdx($stopIdx) {
		$params = array(
			'StopIdx' => $stopIdx,
		);
		$xml = $this->CallAPI('EndOfCourse', $params);
		if (!$xml) {
			tx_icslibnavitia_Debug::warning('Failed to call EndOfCourse API; See devlog for additional information');
			return NULL;
		}
		return $this->getDataCached(function($obj) use($xml) {
			$reader = new XMLReader();
			$reader->XML($xml);
			if (!$obj->XMLMoveToRootElement($reader, 'EndOfCourseList')) {
				tx_icslibnavitia_Debug::warning('Invalid response from EndOfCourse API; See saved response for additional information');
				return NULL;
			}
			$reader->read();
			$list = t3lib_div::makeInstance('tx_icslibnavitia_NodeList', 'tx_icslibnavitia_Stop');
			$vj = NULL;
			while ($reader->nodeType != XMLReader::END_ELEMENT) {
				if ($reader->nodeType == XMLReader::ELEMENT) {
					switch ($reader->name) {
						case 'Params':
							$obj->SkipChildren($reader);
							break;
						case 'StopList':
							tx_icslibnavitia_Node::ReadList($reader, $list, array('Stop' => 'tx_icslibnavitia_Stop'));
							break;
						case 'VehiculeJourney':
							$vj = t3lib_div::makeInstance('tx_icslibnavitia_VehicleJourney');
							$vj->ReadXML($reader);
							break;
						case 'PagerInfo':
							$obj->SkipChildren($reader);
							break;
						default:
							$obj->SkipChildren($reader);
					}
				}
				$reader->read();
			}
			return array('stops' => $list, 'vehiculeJourney' => $vj);
		});
	}
	
	/**
	 * Query the PTReferential API function for CityList.
	 *
	 * @param tx_icslibnavitia_INodeList $networks Networks used to filter the query. List element are instance of {@tx_icslibnavitia_Network}. Optional.
	 * @return tx_icslibnavitia_INodeList The list of available cities. Each element is a {@link tx_icslibnavitia_City}.
	 */
	public function getCityList(tx_icslibnavitia_INodeList $networks = NULL) {
		return $this->_getCityList($networks);
	}
	
	/**
	 * Query the PTReferential API function for CityList.
	 *
	 * @param tx_icslibnavitia_INodeList $networks Networks used to filter the query. List element are instance of {@tx_icslibnavitia_Network}. Optional.
	 * @param array $filters Other elements external codes. Available filters are: ModeType, Mode, Company, VehicleJourney, Connection, StopPoint, StopArea, RoutePoint, Route, District, Department, City, ODT, Destination.
	 *        Each filter is an array of externalCodes.
	 * @return tx_icslibnavitia_INodeList The list of available cities. Each element is a {@link tx_icslibnavitia_City}.
	 */
	private function _getCityList(tx_icslibnavitia_INodeList $networks = NULL, array $filters = array()) {
		$params = array();
		$params['RequestedType'] = 'CityList';
		if ($networks != NULL) {
			$networkCodes = array();
			foreach ($networks->ToArray() as $network) {
				if ($network instanceof tx_icslibnavitia_Network) {
					if (!empty($network->externalCode)) {
						$networkCodes[] = $network->externalCode;
					}
				}
			}
			if (!empty($networkCodes)) {
				$params['NetworkExternalCode'] = implode(';', $networkCodes);
			}
		}
		if ($lineExternalCode !== NULL) {
			$params['LineExternalCode'] = $lineExternalCode;
		}
		else if (!empty($filters)) {
			foreach ($filters as $type => $values) {
				$params[$type . 'ExternalCode'] = implode(';', $values);
			}
		}
		$xml = $this->CallAPI('PTReferential', $params);
		if (!$xml) {
			tx_icslibnavitia_Debug::warning('Failed to call PTReferential API; See devlog for additional information');
			return NULL;
		}
		return $this->getDataCached(function($obj) use($xml) {
			$reader = new XMLReader();
			$reader->XML($xml);
			if (!$obj->XMLMoveToRootElement($reader, 'ActionCityList')) {
				tx_icslibnavitia_Debug::warning('Invalid response from PTReferential API; See saved response for additional information');
				return NULL;
			}
			$reader->read();
			$list = t3lib_div::makeInstance('tx_icslibnavitia_NodeList', 'tx_icslibnavitia_City');
			while ($reader->nodeType != XMLReader::END_ELEMENT) {
				if ($reader->nodeType == XMLReader::ELEMENT) {
					switch ($reader->name) {
						case 'Params':
							$obj->SkipChildren($reader);
							break;
						case 'CityList':
							tx_icslibnavitia_Node::ReadList($reader, $list, array('City' => 'tx_icslibnavitia_City'));
							break;
						case 'PagerInfo':
							$obj->SkipChildren($reader);
							break;
						default:
							$obj->SkipChildren($reader);
					}
				}
				$reader->read();
			}
			return $list;
		});
	}
	
	/**
	 * Query the LineSchedule API function.
	 *
	 * @param string $departureStopAreaExternalCode The unique identifier of the departure stop area.
	 * @param string $arrivalStopAreaExternalCode The unique identifier of the arrival stop area.
	 * @param DateTime $when For which date to query the line schedule.
	 * @param integer $startDayAt Number of minutes after midnight to set at the day start time. It is used to offset the time range like for TV shows.
	 *        For example, if set to 300 (5h00), the search will start at 5 o'clock for the current search day and end before 5 o'clock the next day.
	 * @return array The list of results and additional information. 
	 *        Results are in the {@link tx_icslibnavitia_NodeList} in <code>StopList</code> key. Each element is a {@link tx_icslibnavitia_Stop};
	 *        Stop points are in the {@link tx_icslibnavitia_NodeList} in <code>StopPointList</code> key. Each element is a {@link tx_icslibnavitia_StopPoint};
	 *        Routes are in the {@link tx_icslibnavitia_NodeList} in <code>RouteList</code> key. Each element is a {@link tx_icslibnavitia_Route};
	 *        Vehicle journeys are in the {@link tx_icslibnavitia_NodeList} in <code>VehicleJourneyList</code> key. Each element is a {@link tx_icslibnavitia_VehicleJourney};
	 */
	public function getLineScheduleByStopArea($departureStopAreaExternalCode, $arrivalStopAreaExternalCode, DateTime $when = NULL, $startDayAt = 0) {
		$params = array();
		$params['DepartureExternalCode'] = $departureStopAreaExternalCode;
		$params['ArrivalExternalCode'] = $arrivalStopAreaExternalCode;
		if ($when == NULL)
			$when = new DateTime();
		$params['Date'] = $when->format('Y|m|d');
		$startDayAt %= 1440; // 1440 minutes = 24 hours.
		if ($startDayAt > 0)
			$params['DateChangeTime'] = sprintf('%d|%d', $startDayAt / 60, $startDayAt % 60);
		$xml = $this->CallAPI('LineSchedule', $params);
		if (!$xml) {
			tx_icslibnavitia_Debug::warning('Failed to call LineSchedule API; See devlog for additional information');
			return NULL;
		}
		return $this->getDataCached(function($obj) use($xml) {
			$reader = new XMLReader();
			$reader->XML($xml);
			if (!$obj->XMLMoveToRootElement($reader, 'LineScheduleList')) {
				tx_icslibnavitia_Debug::warning('Invalid response from LineSchedule API; See saved response for additional information');
				return NULL;
			}
			$reader->read();
			$stops = t3lib_div::makeInstance('tx_icslibnavitia_NodeList', 'tx_icslibnavitia_Stop');
			$stopPoints = t3lib_div::makeInstance('tx_icslibnavitia_NodeList', 'tx_icslibnavitia_StopPoint');
			$routes = t3lib_div::makeInstance('tx_icslibnavitia_NodeList', 'tx_icslibnavitia_Route');
			$journeys = t3lib_div::makeInstance('tx_icslibnavitia_NodeList', 'tx_icslibnavitia_VehicleJourney');
			$comments = t3lib_div::makeInstance('tx_icslibnavitia_NodeList', 'tx_icslibnavitia_Comment');
			$impacts = t3lib_div::makeInstance('tx_icslibnavitia_NodeList', 'tx_icslibnavitia_Impact');
			$result = array(
				'StopList' => $stops,
				'StopPointList' => $stopPoints,
				'RouteList' => $routes,
				'VehicleJourneyList' => $journeys,
				'CommentList' => $comments,
				'ImpactList' => $impacts,
			);
			while ($reader->nodeType != XMLReader::END_ELEMENT) {
				if ($reader->nodeType == XMLReader::ELEMENT) {
					switch ($reader->name) {
						case 'Params':
							$obj->SkipChildren($reader);
							break;
						case 'StopList':
							tx_icslibnavitia_Node::ReadList($reader, $stops, array('Stop' => 'tx_icslibnavitia_Stop'));
							break;
						case 'StopPointList':
							tx_icslibnavitia_Node::ReadList($reader, $stopPoints, array('StopPoint' => 'tx_icslibnavitia_StopPoint'));
							break;
						case 'RouteList':
							tx_icslibnavitia_Node::ReadList($reader, $routes, array('Route' => 'tx_icslibnavitia_Route'));
							break;
						case 'VehicleJourneyList':
							tx_icslibnavitia_Node::ReadList($reader, $journeys, array('VehicleJourney' => 'tx_icslibnavitia_VehicleJourney'));
							break;
						case 'CommentList':
							tx_icslibnavitia_Node::ReadList($reader, $comments, array('Comment' => 'tx_icslibnavitia_Comment'));
							break;
						case 'ImpactList':
							tx_icslibnavitia_Node::ReadList($reader, $impacts, array('Impact' => 'tx_icslibnavitia_Impact'));
							break;
						case 'PagerInfo':
							$obj->SkipChildren($reader);
							break;
						default:
							$obj->SkipChildren($reader);
					}
				}
				$reader->read();
			}
			return $result;
		});
	}
	
	/**
	 * Retrieves Const values as object.
	 * @return object Object representing the XML structured data.
	 */
	public function getConst() {
		$xml = $this->CallConst();
		if (!$xml) {
			tx_icslibnavitia_Debug::warning('Failed to call Const');
			return NULL;
		}
		$reader = new XMLReader();
		$reader->XML($xml);
		if (!$this->XMLMoveToRootElement($reader, 'const')) {
			tx_icslibnavitia_Debug::warning('Invalid response from Const');
			return NULL;
		}
		$navigate = NULL;
		$navigate = function(XMLReader $reader) use(&$navigate) {
			$object = new stdClass();
			$attributes = $reader->hasAttributes;
			$children = FALSE;
			$value = NULL;
			if ($attributes) {
				$reader->moveToFirstAttribute();
				do {
					$key = $reader->localName;
					$val = $reader->value;
					$object->$key = $val;
				}
				while ($reader->moveToNextAttribute());
				$reader->moveToElement();
			}
			if (!$reader->isEmptyElement) {
				$reader->read();
				while (($reader->nodeType != XMLReader::END_ELEMENT) && ($reader->nodeType != XMLReader::NONE)) {
					switch ($reader->nodeType) {
						case XMLReader::TEXT:
							if (!$children) {
								if ($value == NULL) {
									$value = '';
								}
								$value .= $reader->value;
							}
							break;
						case XMLReader::ELEMENT:
							$children = TRUE;
							$key = $reader->localName;
							$object->$key = $navigate($reader);
							break;
					}
					$reader->read();
				}
			}
			if (!$children) {
				if (!$attributes) {
					$object = $value;
				}
				else {
					$object->value = $value;
				}
			}
			return $object;
		};
		$root = $navigate($reader);
		return $root;
	}
}
