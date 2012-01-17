<?php

// Convert coordinates with cs2cs (GDAL/OGR
class tx_icslibnavitia_CoordinateConverter {

	private static $wgs84Definition = '+proj=lonlat +a=6378137.0 +rf=298.257223563 +units=m +no_defs';
	private static $lambIIEDefinition = '+proj=lcc +towgs84=-168.0,-60.0,320.0 +a=6378249.2 +rf=293.466021 +pm=2.337229167 +lat_0=46.8 +lon_0=0.0 +k_0=0.99987742 +lat_1=45.8989188 +lat_2=47.6960144 +x_0=600000.0 +y_0=2200000.0 +units=m +no_defs';
	private static $helperProcName = 'cs2cs';
	
	public static function convertFromWGS84($lat, $lng) {
		$result = self::Call(self::$wgs84Definition, self::$lambIIEDefinition, '%.15f', '%.2f', array($lng, $lat));
		if (!$result)
			return null;
		return array('X' => $result[0], 'Y' => $result[1]);
	}
	
	public static function convertToWGS84($X, $Y) {
		$result = self::Call(self::$lambIIEDefinition, self::$wgs84Definition, '%.2f', '%.15f', array($X, $Y));
		if (!$result)
			return null;
		return array('lat' => $result[1], 'lng' => $result[0]);
	}
	
	private static function Call($from, $to, $fromFormat, $toFormat, array $values) {
		$descspec = array(
			0 => array('pipe', 'r'),
			1 => array('pipe', 'w'),
		);
		
		$command = self::$helperProcName . ' ' . $from . ' +to ' . $to . ' -f ' . escapeshellarg($toFormat);
		$input = array();
		foreach ($values as $value)
			$input[] = sprintf($fromFormat, $value);
		$input = implode(' ', $input);
		
		$pipes = array();
		$process = proc_open($command, $descspec, $pipes);

		$result = false;
		if (is_resource($process)) {
			fwrite($pipes[0], $input);
			fclose($pipes[0]);
			
			$read = array($pipes[1]);
			$write = $except = NULL;
			if (stream_select($read, $write, $except, 0, 3000) !== false) {
				$output = fread($pipes[1], 1024);
				$output = preg_split('/[ 	]+/', $output);
				if (is_numeric($output[0]) && is_numeric($output[1])) {
					$result = array(floatval($output[0]), floatval($output[1]));
				}
			}
			fclose($pipes[1]);
			proc_close($process);
		}
		return $result;
	}
}
