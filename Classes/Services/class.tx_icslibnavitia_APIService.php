<?php

/**
 * Represents the interface to query the NAViTiA API.
 *
 * @author Pierrick Caillon <pierrick@in-cite.net>
 * @package ics_libnavitia
 * @subpackage Services
 */
class tx_icslibnavitia_APIService {
	const INTERFACE_VERSION = '1_16';
	private $serviceUrl;
	private $statId;
	private $lastParams = array();

	/**
	 *
	 * @param string $url URL of the gwnavitia.dll to use.
	 */
	public function __construct($url, $login) {
		$this->serviceUrl = $url;
		$this->statId = $login;
	}
	
	/**
	 * Does a RAW call to a NAViTiA API fonction.
	 *
	 * @param string $action The API function to call.
	 * @param array $params The parameters to pass to the function.
	 * @return mixed The response data or FALSE if failed.
	 */
	public function CallAPI($action, array $params) {
		$params['Interface'] = self::INTERFACE_VERSION;
		$params['RequestUrl'] = str_replace('&', '%26', t3lib_div::getIndpEnv('TYPO3_REQUEST_URL'));
		if ($this->statId)
			$params['login'] = $this->statId;
		$this->lastParams = $params;
		$this->lastParams['RequestUrl'] = t3lib_div::getIndpEnv('TYPO3_REQUEST_URL');
		$this->lastParams['Function'] = 'API';
		$this->lastParams['Action'] = $action;
		$url = $this->serviceUrl . '/API?Action=' . urlencode($action) . t3lib_div::implodeArrayForUrl('', $params);
		$report = array();
		$result = t3lib_div::getURL($url, 0, false, $report);
		tx_icslibnavitia_Debug::Log('Call to NAViTiA API', $url, $result ? 0 : 1, $report);
		if ($result) {
			tx_icslibnavitia_Debug::WriteResponse($action, $result);
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
		if ($name == '') {
			if ($city == '') {
				tx_icslibnavitia_Debug::notice('Empty parameters for EntryPoint query');
				return false;
			}
			else {
				$name = $city;
				$city = '';
			}
		}
		$params = array();
		$params['Filter'] = 'All';
		$params['Name'] = $name;
		if ($city != '')
			$params['CityName'] = $city;
		$params['RawData'] = $quality;
		if ($max > 0)
			$params['NbMax'] = $max;
		$xml = $this->CallAPI('EntryPoint', $params);
		if (!$xml) {
			tx_icslibnavitia_Debug::warning('Failed to call EntryPoint API; See devlog for additional information');
			return null;
		}
		$reader = new XMLReader();
		$reader->XML($xml);
		if (!$this->XMLMoveToRootElement($reader, 'ActionEntryPointList')) {
			tx_icslibnavitia_Debug::warning('Invalid response from EntryPoint API; See saved response for additional information');
			return null;
		}
		$reader->read();
		$list = t3lib_div::makeInstance('tx_icslibnavitia_NodeList', 'tx_icslibnavitia_EntryPoint');
		while ($reader->nodeType != XMLReader::END_ELEMENT) {
			if ($reader->nodeType == XMLReader::ELEMENT) {
				switch ($reader->name) {
					case 'Params':
						$this->SkipChildren($reader);
						break;
					case 'EntryPointList':
						tx_icslibnavitia_Node::ReadList($reader, $list, array('EntryPoint' => 'tx_icslibnavitia_EntryPoint'));
						break;
					case 'PagerInfo':
						$this->SkipChildren($reader);
						break;
					default:
						$this->SkipChildren($reader);
				}
			}
			$reader->read();
		}
		return $list;
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
	 * @return array The list of results and comments. 
	 *        Results are in the {@link tx_icslibnavitia_NodeList} in <code>JourneyResultList</code> key. Each element is a {@link tx_icslibnavitia_JourneyResult};
	 *        Comments are in the {@link tx_icslibnavitia_NodeList} in <code>CommentList</code> key. Not yet defined.
	 */
	public function getPlanJourney(tx_icslibnavitia_EntryPointDefinition $from, tx_icslibnavitia_EntryPointDefinition $to,
		$isStartTime = true, DateTime $when = null, $kind = tx_icslibnavitia_APIService::PLANJOURNEYKIND_ASAP, $before = 0, $after = 0) {
		$params = array();
		$params['Departure'] = (string)$from;
		$params['Arrival'] = (string)$to;
		$params['Sens'] = $isStartTime ? 1 : -1;
		if ($when == null) {
			$when = new DateTime();
			$when->setTime(0, 0, 0);
		}
		$params['Time'] = $when->format('H|i');
		$params['Date'] = $when->format('Y|m|d');
		$params['Criteria'] = $kind;
		$params['NbBefore'] = $before;
		$params['NbAfter'] = $after;
		$xml = $this->CallAPI('PlanJourney', $params);
		if (!$xml) {
			tx_icslibnavitia_Debug::warning('Failed to call PlanJourney API; See devlog for additional information');
			return null;
		}
		$reader = new XMLReader();
		$reader->XML($xml);
		if (!$this->XMLMoveToRootElement($reader, 'ActionJourneyResultList')) {
			tx_icslibnavitia_Debug::warning('Invalid response from PlanJourney API; See saved response for additional information');
			return null;
		}
		$reader->read();
		$jrList = t3lib_div::makeInstance('tx_icslibnavitia_NodeList', 'tx_icslibnavitia_JourneyResult');
		$commentList = t3lib_div::makeInstance('tx_icslibnavitia_NodeList'/*, 'tx_icslibnavitia_Comment'*/);
		$result = array('JourneyResultList' => $jrList, 'CommentList' => $commentList);
		while ($reader->nodeType != XMLReader::END_ELEMENT) {
			if ($reader->nodeType == XMLReader::ELEMENT) {
				switch ($reader->name) {
					case 'Params':
						$this->SkipChildren($reader);
						break;
					case 'JourneyResultList':
						tx_icslibnavitia_Node::ReadList($reader, $jrList, array('JourneyResult' => 'tx_icslibnavitia_JourneyResult'));
						break;
					case 'PagerInfo':
						$this->SkipChildren($reader);
						break;
					default:
						$this->SkipChildren($reader);
				}
			}
			$reader->read();
		}
		return $result;
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
	public function getStreetNetwork(tx_icslibnavitia_Coord $start, tx_icslibnavitia_Coord $end, $walkSpeed = 50, $hangDistanceStart = null, $hangDistanceEnd = null) {
		$params = array();
		$params['StartCoordX'] = str_replace('.', ',', $start->x);
		$params['StartCoordY'] = str_replace('.', ',', $start->y);
		$params['EndCoordX'] = str_replace('.', ',', $end->x);
		$params['EndCoordY'] = str_replace('.', ',', $end->y);
		$params['WalkSpeed'] = $walkSpeed;
		if ($hangDistanceStart == null)
			$hangDistanceStart = 1000;
		if ($hangDistanceEnd == null)
			$hangDistanceEnd = $hangDistanceStart;
		$params['HangDistanceDep'] = $hangDistanceStart;
		$params['HangDistanceArr'] = $hangDistanceEnd;
		$xml = $this->CallAPI('StreetNetwork', $params);
		if (!$xml) {
			tx_icslibnavitia_Debug::warning('Failed to call StreetNetwork API; See devlog for additional information');
			return null;
		}
		$reader = new XMLReader();
		$reader->XML($xml);
		if (!$this->XMLMoveToRootElement($reader, 'ActionStreetNetwork')) {
			tx_icslibnavitia_Debug::warning('Invalid response from StreetNetwork API; See saved response for additional information');
			return null;
		}
		$reader->read();
		$list = t3lib_div::makeInstance('tx_icslibnavitia_NodeList', 'tx_icslibnavitia_Segment');
		while ($reader->nodeType != XMLReader::END_ELEMENT) {
			if ($reader->nodeType == XMLReader::ELEMENT) {
				switch ($reader->name) {
					case 'Params':
						$this->SkipChildren($reader);
						break;
					case 'SegmentList':
						tx_icslibnavitia_Node::ReadList($reader, $list, array('Segment' => 'tx_icslibnavitia_Segment'));
						break;
					case 'PagerInfo':
						$this->SkipChildren($reader);
						break;
					default:
						$this->SkipChildren($reader);
				}
			}
			$reader->read();
		}
		return $list;
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
		return ($list->Count() == 0) ? null : $list->Get(0);
	}
	
	/**
	 * Query the PTReferential API function for NetworkList.
	 *
	 * @param string $networkExternalCode The unique identifier of the network. Optional.
	 * @return tx_icslibnavitia_INodeList The list of available networks. Each element is a {@link tx_icslibnavitia_Network}.
	 */
	private function _getNetworkList($networkExternalCode = null) {
		$params = array();
		$params['RequestedType'] = 'NetworkList';
		if ($networkExternalCode !== null)
			$params['NetworkExternalCode'] = $networkExternalCode;
		$xml = $this->CallAPI('PTReferential', $params);
		if (!$xml) {
			tx_icslibnavitia_Debug::warning('Failed to call PTReferential API; See devlog for additional information');
			return null;
		}
		$reader = new XMLReader();
		$reader->XML($xml);
		if (!$this->XMLMoveToRootElement($reader, 'ActionNetworkList')) {
			tx_icslibnavitia_Debug::warning('Invalid response from PTReferential API; See saved response for additional information');
			return null;
		}
		$reader->read();
		$list = t3lib_div::makeInstance('tx_icslibnavitia_NodeList', 'tx_icslibnavitia_Network');
		while ($reader->nodeType != XMLReader::END_ELEMENT) {
			if ($reader->nodeType == XMLReader::ELEMENT) {
				switch ($reader->name) {
					case 'Params':
						$this->SkipChildren($reader);
						break;
					case 'NetworkList':
						tx_icslibnavitia_Node::ReadList($reader, $list, array('Network' => 'tx_icslibnavitia_Network'));
						break;
					case 'PagerInfo':
						$this->SkipChildren($reader);
						break;
					default:
						$this->SkipChildren($reader);
				}
			}
			$reader->read();
		}
		return $list;
	}
	
	/**
	 * Query the PTReferential API function for LineList.
	 *
	 * @param tx_icslibnavitia_INodeList $networks Networks used to filter the query. List element are instance of {@tx_icslibnavitia_Network}. Optional.
	 * @return tx_icslibnavitia_INodeList The list of available lines. Each element is a {@link tx_icslibnavitia_Line}.
	 */
	public function getLineList(tx_icslibnavitia_INodeList $networks = null) {
		return $this->_getLineList($networks);
	}
	
	/**
	 * Query the PTReferential API function for a Line.
	 *
	 * @param string $lineExternalCode The unique identifier of the line.
	 * @return tx_icslibnavitia_Line The requested line or null if not found.
	 */
	public function getLineByCode($lineExternalCode) {
		$list = $this->_getLineList(null, $lineExternalCode);
		return ($list->Count() == 0) ? null : $list->Get(0);
	}
	
	/**
	 * Query the PTReferential API function for NetworkList.
	 *
	 * @param tx_icslibnavitia_INodeList $networks Networks used to filter the query. List element are instance of {@tx_icslibnavitia_Network}. Optional.
	 * @param string $lineExternalCode The unique identifier of the line. Optional.
	 * @return tx_icslibnavitia_INodeList The list of available lines. Each element is a {@link tx_icslibnavitia_Line}.
	 */
	private function _getLineList(tx_icslibnavitia_INodeList $networks = null, $lineExternalCode = null) {
		$params = array();
		$params['RequestedType'] = 'LineList';
		if ($networks != null) {
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
		if ($lineExternalCode !== null) {
			$params['LineExternalCode'] = $lineExternalCode;
		}
		$xml = $this->CallAPI('PTReferential', $params);
		if (!$xml) {
			tx_icslibnavitia_Debug::warning('Failed to call PTReferential API; See devlog for additional information');
			return null;
		}
		$reader = new XMLReader();
		$reader->XML($xml);
		if (!$this->XMLMoveToRootElement($reader, 'ActionLineList')) {
			tx_icslibnavitia_Debug::warning('Invalid response from PTReferential API; See saved response for additional information');
			return null;
		}
		$reader->read();
		$list = t3lib_div::makeInstance('tx_icslibnavitia_NodeList', 'tx_icslibnavitia_Line');
		while ($reader->nodeType != XMLReader::END_ELEMENT) {
			if ($reader->nodeType == XMLReader::ELEMENT) {
				switch ($reader->name) {
					case 'Params':
						$this->SkipChildren($reader);
						break;
					case 'LineList':
						tx_icslibnavitia_Node::ReadList($reader, $list, array('Line' => 'tx_icslibnavitia_Line'));
						break;
					case 'PagerInfo':
						$this->SkipChildren($reader);
						break;
					default:
						$this->SkipChildren($reader);
				}
			}
			$reader->read();
		}
		return $list;
	}
	
	/**
	 * Query the RoutePointList API function.
	 *
	 * @param string $lineExternalCode The unique identifier of the line.
	 * @param boolean $forward Indicates if the direction is forward. Otherwise backward. Optional. Default to forward (true).
	 * @return tx_icslibnavitia_INodeList The list of available route points. Each element is a {@link tx_icslibnavitia_RoutePoint}.
	 */
	public function getRoutePointList($lineExternalCode, $forward = true) {
		return $this->_getRoutePointList(null, $lineExternalCode, $forward);
	}
	
	/**
	 * Query the RoutePointList API function.
	 *
	 * @param string $routePointExternalCode The unique identifier of the route point.
	 * @return tx_icslibnavitia_RoutePoint The requested route point or null if not found.
	 */
	public function getRoutePointByCode($routePointExternalCode) {
		$list = $this->_getRoutePointList($routePointExternalCode);
		return ($list->Count() == 0) ? null : $list->Get(0);
	}
	
	/**
	 * Query the RoutePointList API function.
	 *
	 * @param string $routePointExternalCode The unique identifier of the route point. Optional.
	 * @param string $lineExternalCode The unique identifier of the line. Optional if {@link $routePointExternalCode} is specified.
	 * @param boolean $forward Indicates if the direction is forward. Otherwise backward. Optional. Default to forward (true).
	 * @return tx_icslibnavitia_INodeList The list of available route points. Each element is a {@link tx_icslibnavitia_RoutePoint}.
	 */
	private function _getRoutePointList($routePointExternalCode = null, $lineExternalCode = null, $forward = true) {
		$params = array();
		if ($routePointExternalCode !== null) {
			$params['RoutePointExternalCode'] = $routePointExternalCode;
		}
		else {
			$params['LineExternalCode'] = $lineExternalCode;
			$params['Sens'] = $forward ? 1 : -1;
		}
		$xml = $this->CallAPI('RoutePointList', $params);
		if (!$xml) {
			tx_icslibnavitia_Debug::warning('Failed to call RoutePointList API; See devlog for additional information');
			return null;
		}
		$reader = new XMLReader();
		$reader->XML($xml);
		if (!$this->XMLMoveToRootElement($reader, 'ActionRoutePointList')) {
			tx_icslibnavitia_Debug::warning('Invalid response from RoutePointList API; See saved response for additional information');
			return null;
		}
		$reader->read();
		$list = t3lib_div::makeInstance('tx_icslibnavitia_NodeList', 'tx_icslibnavitia_RoutePoint');
		while ($reader->nodeType != XMLReader::END_ELEMENT) {
			if ($reader->nodeType == XMLReader::ELEMENT) {
				switch ($reader->name) {
					case 'Params':
						$this->SkipChildren($reader);
						break;
					case 'RoutePointList':
						tx_icslibnavitia_Node::ReadList($reader, $list, array('RoutePoint' => 'tx_icslibnavitia_RoutePoint'));
						break;
					case 'PagerInfo':
						$this->SkipChildren($reader);
						break;
					default:
						$this->SkipChildren($reader);
				}
			}
			$reader->read();
		}
		return $list;
	}
	
	/**
	 * Query the DepartureBoard API function.
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
	public function getDepartureBoardByStopPointForLine($stopPointExternalCode, $lineExternalCode, DateTime $when = null, $forward = true, $startDayAt = 0) {
		$params = array();
		$params['StopPointExternalCode'] = $stopPointExternalCode;
		$params['LineExternalCode'] = $lineExternalCode;
		$params['Sens'] = $forward ? 1 : -1;
		if ($when == null)
			$when = new DateTime();
		$params['Date'] = $when->format('Y|m|d');
		$startDayAt %= 1440; // 1440 minutes = 24 hours.
		if ($startDayAt > 0)
			$params['DateChangeTime'] = sprintf('%d|%d', $startDayAt / 60, $startDayAt % 60);
		$xml = $this->CallAPI('DepartureBoard', $params);
		if (!$xml) {
			tx_icslibnavitia_Debug::warning('Failed to call DepartureBoard API; See devlog for additional information');
			return null;
		}
		$reader = new XMLReader();
		$reader->XML($xml);
		if (!$this->XMLMoveToRootElement($reader, 'DepartureBoardList')) {
			tx_icslibnavitia_Debug::warning('Invalid response from DepartureBoard API; See saved response for additional information');
			return null;
		}
		$reader->read();
		$stops = t3lib_div::makeInstance('tx_icslibnavitia_NodeList', 'tx_icslibnavitia_Stop');
		$stopPoints = t3lib_div::makeInstance('tx_icslibnavitia_NodeList', 'tx_icslibnavitia_StopPoint');
		$lines = t3lib_div::makeInstance('tx_icslibnavitia_NodeList', 'tx_icslibnavitia_Line');
		$routes = t3lib_div::makeInstance('tx_icslibnavitia_NodeList', 'tx_icslibnavitia_Route');
		$journeys = t3lib_div::makeInstance('tx_icslibnavitia_NodeList', 'tx_icslibnavitia_VehicleJourney');
		$destinations = t3lib_div::makeInstance('tx_icslibnavitia_NodeList', 'tx_icslibnavitia_StopArea');
		$result = array(
			'StopList' => $stops,
			'StopPointList' => $stopPoints,
			'LineList' => $lines,
			'RouteList' => $routes,
			'VehicleJourneyList' => $journeys,
			'DestinationList' => $destinations,
		);
		while ($reader->nodeType != XMLReader::END_ELEMENT) {
			if ($reader->nodeType == XMLReader::ELEMENT) {
				switch ($reader->name) {
					case 'Params':
						$this->SkipChildren($reader);
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
					case 'PagerInfo':
						$this->SkipChildren($reader);
						break;
					default:
						$this->SkipChildren($reader);
				}
			}
			$reader->read();
		}
		return $result;
	}
}
