<?php

class tx_icslibnavitia_Debug {
	private static $settings = null;
	private static $urls = array();
	
	private static function LoadSettings() {
		self::$settings = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['ics_libnavitia']);
		if (!self::$settings) {
			self::$settings = array(
				'debug' => '0',
				'devlog' => '0',
				'writeXML' => '0',
				'debug_require' => '1',
			);
		}
		if (self::$settings['debug']) {
			self::$settings['debug'] = t3lib_div::_GP(self::$settings['debug_param']);
		}
		if (!self::$settings['debug'] && self::$settings['debug_require']) {
			self::$settings['devlog'] = '0';
			self::$settings['writeXML'] = '0';
		}
		if (self::$settings['debug']) {
			if (self::$settings['debug'] == 2) {
				self::$settings['debug_echo'] = true;
			}
			$GLOBALS['TSFE']->TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-output'][] = 'tx_icslibnavitia_Debug->appendDebugCode';
		}
		unset(self::$settings['debug_param']);
	}
	
	public static function Init() {
		if (self::$settings == null)
			self::LoadSettings();
	}
	
	public static function Log($message, $url = '', $level = 0, array $data = null) {
		if (self::$settings['debug'] && $url) {
			if (self::$settings['debug_echo']) {
				echo '<pre>' . htmlspecialchars($url) . '</pre>' . PHP_EOL;
			}
			self::$urls[] = $url;
		}
		if (self::$settings['devlog']) {
			if ($url) {
				$data = array(
					'url' => $url,
					'data' => $data,
				);
			}
			t3lib_div::devLog($message, 'tx_icslibnavitia', $level, $data);
		}
	}
	
	private static $responseNum = 0;
	
	public static function WriteResponse($action, $xml) {
		if (self::$settings['writeXML']) {
			t3lib_div::writeFileToTypo3tempDir(t3lib_div::getFileAbsFileName('typo3temp/libnavitia/' . sprintf('%s_%d_%s.xml', $_SERVER['REQUEST_TIME'], self::$responseNum++, $action)), $xml);
		}
	}
	
	public static function error($message, $backlevel = 0) {
		$trace = debug_backtrace();
		trigger_error(
			$message .
			' in ' . $trace[1 + $backlevel]['file'] .
			' on line ' . $trace[1 + $backlevel]['line'],
			E_USER_ERROR);
	}
	
	public static function warning($message, $backlevel = 0) {
		$trace = debug_backtrace();
		trigger_error(
			$message .
			' in ' . $trace[1 + $backlevel]['file'] .
			' on line ' . $trace[1 + $backlevel]['line'],
			E_USER_WARNING);
	}
	
	public static function notice($message, $backlevel = 0) {
		$trace = debug_backtrace();
		trigger_error(
			$message .
			' in ' . $trace[1 + $backlevel]['file'] .
			' on line ' . $trace[1 + $backlevel]['line'],
			E_USER_NOTICE);
	}
	
	public static function IsDebugEnabled() {
		return self::$settings['debug'];
	}
	
	public function appendDebugCode($params, $caller) {
		if (!empty(self::$urls)) {
			$params['pObj']->content = str_replace('</body>', '<pre><![CDATA[' . PHP_EOL . implode(PHP_EOL, self::$urls) . PHP_EOL . ']]></pre></body>', $params['pObj']->content);
		}
	}
}

tx_icslibnavitia_Debug::Init();