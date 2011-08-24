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
	
	/**
	 * Query the EntryPoint API function.
	 *
	 * @param string $name The element description to search for.
	 *        Can be a stop point, an address, a place, a city.
	 * @param string $city The city where to search. Optional.
	 * @param integer $quality The results quality. See API documentation for parameter RawData. Optional.
	 * @param integer $max The maximum number of results. Optional. 0 for unlimited.
	 */
	public function getEntryPointListByNameAndCity($name, $city = '', $quality = 1, $max = 0) {
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
}