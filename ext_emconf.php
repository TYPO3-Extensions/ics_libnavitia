<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "ics_libnavitia".
 *
 * Auto generated 29-04-2014 11:28
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Library for NAViTiA',
	'description' => 'Library to access NAViTiA API. NAViTiA is an information system for transportation created by Canal TP.',
	'category' => 'misc',
	'author' => 'In Cite Solution',
	'author_email' => 'technique@in-cite.net',
	'shy' => '',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'beta',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author_company' => '',
	'version' => '0.0.0',
	'constraints' => array(
		'depends' => array(
			'typo3' => '4.5.0-0.0.0',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:56:{s:9:"ChangeLog";s:4:"ca7d";s:16:"ext_autoload.php";s:4:"62be";s:21:"ext_conf_template.txt";s:4:"2da7";s:12:"ext_icon.gif";s:4:"1bdc";s:17:"ext_localconf.php";s:4:"fffb";s:14:"ext_tables.php";s:4:"d53a";s:14:"ext_tables.sql";s:4:"ed0b";s:10:"README.txt";s:4:"ee2d";s:47:"Classes/class.tx_icslibnavitia_CacheManager.php";s:4:"5505";s:57:"Classes/class.tx_icslibnavitia_ConstantEditorControls.php";s:4:"a996";s:54:"Classes/class.tx_icslibnavitia_CoordinateConverter.php";s:4:"e830";s:40:"Classes/class.tx_icslibnavitia_Debug.php";s:4:"a1ec";s:54:"Classes/DataObjects/class.tx_icslibnavitia_Address.php";s:4:"1697";s:58:"Classes/DataObjects/class.tx_icslibnavitia_AddressType.php";s:4:"5d1b";s:55:"Classes/DataObjects/class.tx_icslibnavitia_Backward.php";s:4:"3685";s:51:"Classes/DataObjects/class.tx_icslibnavitia_Call.php";s:4:"9d85";s:56:"Classes/DataObjects/class.tx_icslibnavitia_CallValue.php";s:4:"22c8";s:51:"Classes/DataObjects/class.tx_icslibnavitia_City.php";s:4:"581d";s:54:"Classes/DataObjects/class.tx_icslibnavitia_Comment.php";s:4:"ed73";s:52:"Classes/DataObjects/class.tx_icslibnavitia_Coord.php";s:4:"5612";s:57:"Classes/DataObjects/class.tx_icslibnavitia_EntryPoint.php";s:4:"3024";s:68:"Classes/DataObjects/class.tx_icslibnavitia_EntryPointPlanJourney.php";s:4:"992e";s:56:"Classes/DataObjects/class.tx_icslibnavitia_Equipment.php";s:4:"53d4";s:52:"Classes/DataObjects/class.tx_icslibnavitia_Event.php";s:4:"95fa";s:54:"Classes/DataObjects/class.tx_icslibnavitia_Forward.php";s:4:"4f94";s:51:"Classes/DataObjects/class.tx_icslibnavitia_Hang.php";s:4:"6c26";s:55:"Classes/DataObjects/class.tx_icslibnavitia_HangList.php";s:4:"b82b";s:53:"Classes/DataObjects/class.tx_icslibnavitia_Impact.php";s:4:"72b3";s:60:"Classes/DataObjects/class.tx_icslibnavitia_JourneyResult.php";s:4:"f5dc";s:51:"Classes/DataObjects/class.tx_icslibnavitia_Line.php";s:4:"0860";s:51:"Classes/DataObjects/class.tx_icslibnavitia_Mode.php";s:4:"5ab3";s:55:"Classes/DataObjects/class.tx_icslibnavitia_ModeType.php";s:4:"61a4";s:54:"Classes/DataObjects/class.tx_icslibnavitia_Network.php";s:4:"8950";s:51:"Classes/DataObjects/class.tx_icslibnavitia_Node.php";s:4:"008d";s:55:"Classes/DataObjects/class.tx_icslibnavitia_NodeList.php";s:4:"a356";s:51:"Classes/DataObjects/class.tx_icslibnavitia_Nota.php";s:4:"3523";s:56:"Classes/DataObjects/class.tx_icslibnavitia_Proximity.php";s:4:"22a8";s:63:"Classes/DataObjects/class.tx_icslibnavitia_ReadOnlyNodeList.php";s:4:"9971";s:52:"Classes/DataObjects/class.tx_icslibnavitia_Route.php";s:4:"c65d";s:57:"Classes/DataObjects/class.tx_icslibnavitia_RoutePoint.php";s:4:"75e1";s:54:"Classes/DataObjects/class.tx_icslibnavitia_Section.php";s:4:"9916";s:54:"Classes/DataObjects/class.tx_icslibnavitia_Segment.php";s:4:"3dbd";s:51:"Classes/DataObjects/class.tx_icslibnavitia_Site.php";s:4:"5dc7";s:51:"Classes/DataObjects/class.tx_icslibnavitia_Stop.php";s:4:"4491";s:55:"Classes/DataObjects/class.tx_icslibnavitia_StopArea.php";s:4:"564b";s:56:"Classes/DataObjects/class.tx_icslibnavitia_StopPoint.php";s:4:"9649";s:54:"Classes/DataObjects/class.tx_icslibnavitia_Summary.php";s:4:"5490";s:51:"Classes/DataObjects/class.tx_icslibnavitia_Time.php";s:4:"7616";s:56:"Classes/DataObjects/class.tx_icslibnavitia_Undefined.php";s:4:"e636";s:54:"Classes/DataObjects/class.tx_icslibnavitia_Vehicle.php";s:4:"f5bd";s:61:"Classes/DataObjects/class.tx_icslibnavitia_VehicleJourney.php";s:4:"ffaf";s:60:"Classes/DataObjects/interface.tx_icslibnavitia_INodeList.php";s:4:"d312";s:54:"Classes/Services/class.tx_icslibnavitia_APIService.php";s:4:"a562";s:64:"Classes/Services/class.tx_icslibnavitia_EntryPointDefinition.php";s:4:"3acd";s:30:"res/lang/locallang_ceditor.xml";s:4:"2349";s:56:"scheduler/class.tx_icslibnavitia_scheduler_cachetask.php";s:4:"bbef";}',
	'suggests' => array(
	),
);

?>