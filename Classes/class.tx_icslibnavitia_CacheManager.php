<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2014 Plan.Net France <typo3@plan-net.fr>
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
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/* $Id:$ */

class tx_icslibnavitia_CacheManager implements backend_cacheActionsHook {
	/**
	 * modifies CacheMenuItems array
	 *
	 * @param	array	array of CacheMenuItems
	 * @param	array	array of AccessConfigurations-identifiers (typically  used by userTS with options.clearCache.identifier)
	 * @return
	 */
	public function manipulateCacheActions(&$cacheActions, &$optionValues) {
		if ($GLOBALS['BE_USER']->isAdmin()) {
			$title = $GLOBALS['LANG']->sL('Clear NAViTiA caches');

			$cacheActions[] = array(
				'id'    => 'clearNavitiaCache',
				'title' => $title,
				'href'  => $GLOBALS['BACK_PATH'] . 'ajax.php?ajaxID=libnavitia::clearNavitiaCache',
				'icon'  => t3lib_iconWorks::getSpriteIcon('actions-system-cache-clear-impact-medium')
			);
			$optionValues[] = 'clearNavitiaCache';
		}
	}
	
	public function cleanup() {
		$folder = t3lib_div::getFileAbsFileName(tx_icslibnavitia_APIService::CACHE_DIR);
		if (is_dir($folder)) {
			while ($this->batchCleanup($folder));
		}
	}
	
	protected function batchCleanup($folder) {
		$files = array();
		$dir = opendir($folder);
		if (!is_resource($dir)) {
			return FALSE;
		}
		while ((count($files) < 1000) && (($file = readdir($dir)) !== FALSE)) {
			if (($file != '.') && ($file != '..') && is_file($folder . $file)) {
				$files[] = $file;
			}
		}
		closedir($dir);
		foreach ($files as $file) unlink($folder . $file);
		return $file !== FALSE;
	}
}