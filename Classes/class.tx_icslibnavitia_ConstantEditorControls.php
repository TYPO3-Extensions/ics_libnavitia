<?php

class tx_icslibnavitia_ConstantEditorControls {
	public function selectNetworkControl($constantInfo, $ceditor) {
		if (!preg_match('/^data\\[(.+)\\]$/', $constantInfo['fieldName'], $match))
			return $this->errorControl($constantInfo, 'Constant name could not be extracted.');
		$constantName = $match[1];
		$comment = $GLOBALS['tmpl']->flatSetup[$constantName . '..'];
		$fieldName = $constantInfo['fieldName'];
		$selected = $constantInfo['fieldValue'];
		$c_arr = explode(LF, $comment);
		$url = '';
		$login = '';
		foreach ($c_arr as $k => $v) {
			$line = trim(preg_replace('/^[#\/]*/', '', $v));
			if ($line) {
				$parts = explode(';', $line);
				foreach ($parts as $par) {
					if (strstr($par, '=')) {
						$keyValPair = explode('=', $par, 2);
						switch (trim(strtolower($keyValPair[0]))) {
							case 'url':
								$url = $GLOBALS['tmpl']->flatSetup[trim($keyValPair[1])];
								break;
							case 'login':
								$login = $GLOBALS['tmpl']->flatSetup[trim($keyValPair[1])];
								break;
						}
					}
				}
			}
		}
		if (empty($url))
			return $this->errorControl($constantInfo, 'No NAViTiA URL value defined.');
		
	}
	
	protected function errorControl($constantInfo, $message) {
		return '<input type="hidden" name="' . htmlspecialchars($constantInfo['fieldName']) . '" value="' . htmlspecialchars($constantInfo['fieldValue']) . '" />' . $message;
	}
}
