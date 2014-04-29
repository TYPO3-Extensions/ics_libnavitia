<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}
	// register Clear Cache Menu hook
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['additionalBackendItems']['cacheActions']['clearNavitiaCache'] = 'tx_icslibnavitia_CacheManager';

	// register Clear Cache Menu ajax call
$TYPO3_CONF_VARS['BE']['AJAX']['libnavitia::clearNavitiaCache'] = 'tx_icslibnavitia_CacheManager->cleanup';
