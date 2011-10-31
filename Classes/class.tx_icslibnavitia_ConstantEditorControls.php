<?php

class tx_icslibnavitia_ConstantEditorControls {
	/**
	 * Create a select control with the Network list queried for NAViTiA.
	 * Additional parameters in constant definition:
	 * - url: The path to the constant containing the NAViTiA URL.
	 * - login: The path to the constant containing the NAViTiA stat identifier.
	 */
	public function selectNetworkControl($constantInfo, $ceditor) {
		if (!preg_match('/^data\\[(.+)\\]$/', $constantInfo['fieldName'], $match))
			return $this->errorControl($constantInfo, $this->getLL('error_constantname'));
		$constantName = $match[1];
		$raname = substr(md5($constantName), 0, 10);
		$aname = '\'' . $raname . '\'';
		$comment = $GLOBALS['tmpl']->flatSetup[$constantName . '..'];
		$fieldName = $constantInfo['fieldName'];
		$selected = $constantInfo['fieldValue'];
		$parameters = $this->readCustomParameters($comment);
		$url = '';
		$login = '';
		$size = 1;
		if (isset($parameters['url']))
			$url = $GLOBALS['tmpl']->flatSetup[$parameters['url']];
		if (isset($parameters['login']))
			$login = $GLOBALS['tmpl']->flatSetup[$parameters['login']];
		if (isset($parameters['size']))
			$size = intval($parameters['size']);
		if ($size == 0)
			$size = 1;
		if (empty($url))
			return $this->fallbackControl($fieldName, $selected, $aname) . $this->errorControl($constantInfo, $this->getLL('error_navitiaurl'));
		$dataProvider = t3lib_div::makeInstance('tx_icslibnavitia_APIService', $url, $login);
		$networks = $dataProvider->getNetworkList();
		if ($networks == null)
			return $this->fallbackControl($fieldName, $selected, $aname) . $this->errorControl($constantInfo, $this->getLL('error_unavailable'));
		$values = array(
			'' => '',
		);
		for ($i = 0; $i < $networks->Count(); $i++) {
			$values[$networks->Get($i)->externalCode] = $networks->Get($i)->name;
		}
		return $this->displayOptions($fieldName, $aname, $values, $selected, $size);
	}
	
	protected function readCustomParameters($constantComment) {
		$c_arr = explode(LF, $constantComment);
		$parameters = array();
		foreach ($c_arr as $k => $v) {
			$line = trim(preg_replace('/^[#\/]*/', '', $v));
			if ($line) {
				$parts = explode(';', $line);
				foreach ($parts as $par) {
					if (strstr($par, '=')) {
						$keyValPair = explode('=', $par, 2);
						switch (trim(strtolower($keyValPair[0]))) {
							case 'cat':
							case 'type':
							case 'label':
								break;
							default:
								$parameters[trim(strtolower($keyValPair[0]))] = trim($keyValPair[1]);
								break;
						}
					}
				}
			}
		}
		return $parameters;
	}
	
	protected function errorControl($constantInfo, $message) {
		return '<input type="hidden" name="' . htmlspecialchars($constantInfo['fieldName']) . '" value="' . htmlspecialchars($constantInfo['fieldValue']) . '" />' . $message;
	}
	
	protected function fallbackControl($name, $value, $aname) {
		return '<input id="' . $name . '" type="text" name="' . $name . '" value="' . $value . '"' . $GLOBALS['TBE_TEMPLATE']->formWidth(46) . ' onChange="uFormUrl(' . $aname . ')" />';
	}
	
	protected function getLL($label) {
		return $GLOBALS['LANG']->sL('LLL:EXT:ics_libnavitia/res/lang/locallang_ceditor.xml:' . $label);
	}
	
	protected function displayOptions($name, $aname, $options, $selected, $size = 1) {
		if ($size == 0)
			$size = 1;
		elseif ($size == -1)
			$size = count($options);
		if ($size == 1)
			return $this->displayOptions_single($name, $aname, $options, $selected);
		else
			return $this->displayOptions_multiple($name, $aname, $options, t3lib_div::trimExplode(',', $selected), $size);
	}
	
	protected function displayOptions_single($name, $aname, $options, $selected) {
		$content = '';
		foreach ($options as $value => $label) {
			$selectedAttribute = '';
			if ($selected == $value) {
				$selectedAttribute = ' selected="selected"';
			}
			$content .= '<option value="' . htmlspecialchars($value) . '"' . $selectedAttribute . '>' . htmlspecialchars($label) . '</option>';
		}
		return '<select id="' . htmlspecialchars($name) . '" name="' . htmlspecialchars($name) . '" onChange="uFormUrl(' . $aname . ')">' . $content . '</select>';
	}
	
	protected function displayOptions_multiple($name, $aname, $options, array $selected, $size = 1) {
		unset($options['']);
		if ($size > count($options))
			$size = count($options);
		$script = <<<EOJS
function uNavitiaOptionsMultiple(select, hiddenID)
{
	var hidden = document.getElementById(hiddenID); 
	var values = []; 
	for (var i = 0; i < select.options.length; i++)
	{
		var option = select.options.item(i);
		if (option.selected)
			values.push(option.value);
	}
	hidden.value = values.join(',');
}
EOJS;
		$hiddenID = uniqid('navitiaconst');
		$content = '';
		foreach ($options as $value => $label) {
			$selectedAttribute = '';
			if (in_array($value, $selected)) {
				$selectedAttribute = ' selected="selected"';
			}
			$content .= '<option value="' . htmlspecialchars($value) . '"' . $selectedAttribute . '>' . htmlspecialchars($label) . '</option>';
		}
		$content = '<select id="' . htmlspecialchars($name) . '" onChange="uFormUrl(' . $aname . '); uNavitiaOptionsMultiple(this, \'' . htmlspecialchars($hiddenID) . '\');" size="' . $size . '" multiple="1">' . $content . '</select>';
		$content .= '<input type="hidden" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars(implode(',', $selected)) . '" id="' . htmlspecialchars($hiddenID) . '" />';
		return '<script type="text/javascript" language="javascript">' . $script . '</script>' . $content;
	}
}
