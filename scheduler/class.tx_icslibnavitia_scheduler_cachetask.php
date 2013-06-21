<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2013 Plan.Net France <typo3@plan-net.fr>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 */
define('LIBNAVITIA_CACHING', true);

/**
 * Recurrent query cache task.
 *
 * The used queries are saved by the API service to a database table.
 * Queries in this table expires after self::LIFETIME seconds (one week).
 * Last used date is reset at each call in API except when updating cache.
 *
 * @author	Pierrick Caillon <pierrick@plan-net.fr>
 * @package	TYPO3
 * @subpackage	tx_icsnavitia
 */
class tx_icslibnavitia_scheduler_cachetask extends tx_scheduler_Task {
	const LIFETIME = 604800; // One week in seconds.

	public function execute() {
		$queries = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'tx_icslibnavitia_cachedrequests', '`usedLast` > (UNIX_TIMESTAMP() - ' . self::LIFETIME . ')', '', 'url, login');
		if (!empty($queries)) {
			$updated = $this->cacheQueries($queries);
			if (!empty($updated)) {
				$GLOBALS['TYPO3_DB']->exec_UPDATEquery(
					'tx_icslibnavitia_cachedrequests', 
					'hash IN (' . implode(',', $GLOBALS['TYPO3_DB']->fullQuoteArray($updated, 'tx_icslibnavitia_scheduler_cachetask')) . ')', 
					array('updated' => time())
				);
			}
		}
		$this->cleanQueries();
		return true;
	}
	
	protected function cacheQueries(array $queries) {
		tx_icslibnavitia_APIService::checkCacheFolder();
		$lastUrl = '';
		$lastLogin = '';
		$service = null;
		$hashes = array();
		foreach ($queries as $query) {
			if (($query['url'] != $lastUrl) || ($query['login'] != $lastLogin)) {
				$service = t3lib_div::makeInstance('tx_icslibnavitia_APIService', $query['url'], $query['login']);
				$lastUrl = $query['url'];
				$lastLogin = $query['login'];
			}
			$parameters = unserialize($query['parameters']);
			if ($parameters === false) continue;
			switch ($query['function']) {
				case 'API':
					$result = $service->CallAPI($query['action'], $parameters);
					if ($result) {
						t3lib_div::writeFileToTypo3tempDir(t3lib_div::getFileAbsFileName(tx_icslibnavitia_APIService::CACHE_DIR . $query['hash']), $result);
						$hashes[] = $query['hash'];
					}
					break;
			}
		}
		return $hashes;
	}
	
	protected function cleanQueries() {
		$queries = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('hash', 'tx_icslibnavitia_cachedrequests', '`usedLast` < (UNIX_TIMESTAMP() - ' . self::LIFETIME . ')');
		$hashes = array_map(function ($query) { return $query['hash']; }, $queries);
		$GLOBALS['TYPO3_DB']->exec_DELETEquery(
			'tx_icslibnavitia_cachedrequests', 
			'hash IN (' . implode(',', $GLOBALS['TYPO3_DB']->fullQuoteArray($hashes, 'tx_icslibnavitia_scheduler_cachetask')) . ')'
		);
		foreach ($hashes as $hash) {
			$path = t3lib_div::getFileAbsFileName(tx_icslibnavitia_APIService::CACHE_DIR . $hash);
			if (file_exists($path)) {
				unlink($path);
			}
		}
	}
}