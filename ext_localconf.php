<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['tx_icslibnavitia_scheduler_cachetask'] = array(
    'extension'        => $_EXTKEY,
    'title'            => 'NAViTiA cache',
    'description'      => 'Update cache of static information from NAViTiA.',
);
