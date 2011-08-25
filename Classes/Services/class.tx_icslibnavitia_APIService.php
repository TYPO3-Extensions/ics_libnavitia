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
					// $this->SkipChildren($reader);
					$reader->next(); // TODO: Check to behaviour.
				}
				else 
					$reader->read();
			}
		}
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
	 * @return tx_icslibnavitia_NodeList The list of results.
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
						if (!$reader->isEmptyElement) {
							$reader->read();
							while ($reader->nodeType != XMLReader::END_ELEMENT) {
								if ($reader->nodeType == XMLReader::ELEMENT) {
									if ($reader->name == 'EntryPoint') {
										$entryPoint = t3lib_div::makeInstance('tx_icslibnavitia_EntryPoint');
										$entryPoint->ReadXML($reader);
										$list->Add($entryPoint);
									}
									else {
										$this->SkipChildren($reader);
									}
								}
								$reader->read();
							}
						}
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
	 *        Results are in the {@link tx_icslibnavitia_NodeList} in <code>JourneyResultList</code> key;
	 *        Comments are in the {@link tx_icslibnavitia_NodeList} in <code>CommentList</code> key.
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
		}var_dump($when->format('c U'));
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
						if (!$reader->isEmptyElement) {
							$reader->read();
							while ($reader->nodeType != XMLReader::END_ELEMENT) {
								if ($reader->nodeType == XMLReader::ELEMENT) {
									if ($reader->name == 'JourneyResult') {
										$journeyResult = t3lib_div::makeInstance('tx_icslibnavitia_JourneyResult');
										$journeyResult->ReadXML($reader);
										$jrList->Add($journeyResult);
									}
									else {
										$this->SkipChildren($reader);
									}
								}
								$reader->read();
							}
						}
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
