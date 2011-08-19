<?php

class tx_icslibnavitia_DummyScheduleService {
	public function __construct($url, $login) {
	}

	public function getLineList(tx_icslibnavitia_INodeList $networks = null) {
		if (($networks != null) && ($networks->Count() > 0) && ($networks->Get(0)->name != 'STA1'))
			return t3lib_div::makeInstance('tx_icslibnavitia_NodeList');
		$list = t3lib_div::makeInstance('tx_icslibnavitia_NodeList', 'tx_icslibnavitia_Line');
		$list->Add($this->getLineByCode('STANav1632'));
		$list->Add($this->getLineByCode('STA1188'));
		return $list;
	}
	
	public function getLineByCode($lineExternalCode) {
		if (!in_array($lineExternalCode, array('STANav1632', 'STA1188')))
			return null;
		switch ($lineExternalCode) {
			case 'STANav1632':
			{
				$line = t3lib_div::makeInstance('tx_icslibnavitia_Line');
				$line->idx = 64;
				$line->id = 42;
				$line->name = 'Chartres de Bretagne vers Bruz';
				$line->code = '230';
				$line->externalCode = 'STANav1632';
				$line->data = '04|035|hp|';
				$line->order = 43;
				$line->color = '';
				$line->adaptedRoute = false;
				$line->modeType->idx = 1;
				$line->modeType->name = 'Bus';
				$line->modeType->externalCode = 'Bus';
				$line->network->idx = 1;
				$line->network->id = 0;
				$line->network->name = 'Bus';
				$line->network->externalCode = 'STA1';
				{
					$stop = t3lib_div::makeInstance('tx_icslibnavitia_StopArea');
					$stop->idx = 130;
					$stop->id = 129;
					$stop->name = 'Lycée Anita Conti\'';
					$stop->externalCode = 'STA2377';
					$stop->main = true;
					$line->forward->name = 'vers Bruz';
					$line->forward->direction->Add($stop);
				}
				{
					$stop = t3lib_div::makeInstance('tx_icslibnavitia_StopArea');
					$stop->idx = 233;
					$stop->id = 232;
					$stop->name = 'Vieux Bourg';
					$stop->externalCode = 'STA113';
					$stop->main = true;
					$stop->coord = t3lib_div::makeInstance('tx_icslibnavitia_Coord');
					$stop->coord->x = 299324.00;
					$stop->coord->y = 2345325.00;
					$line->backward->name = 'vers Chartres de Bretagne';
					$line->backward->direction->Add($stop);
				}
				break;
			}
			case 'STA1188':
			{
				$line = t3lib_div::makeInstance('tx_icslibnavitia_Line');
				$line->idx = 75;
				$line->id = 53;
				$line->name = 'Beaulieu Atalante - Beauregard';
				$line->code = '4';
				$line->externalCode = 'STA1188';
				$line->data = '01|005|hp|';
				$line->order = 54;
				$line->color = '';
				$line->adaptedRoute = false;
				$line->modeType->idx = 1;
				$line->modeType->name = 'Bus';
				$line->modeType->externalCode = 'Bus';
				$line->network->idx = 1;
				$line->network->id = 0;
				$line->network->name = 'Bus';
				$line->network->externalCode = 'STA1';
				{
					$stop = t3lib_div::makeInstance('tx_icslibnavitia_StopArea');
					$stop->idx = 699;
					$stop->id = 698;
					$stop->name = 'Beauregard';
					$stop->externalCode = 'STA1353';
					$stop->main = true;
					$stop->coord = t3lib_div::makeInstance('tx_icslibnavitia_Coord');
					$stop->coord->x = 300184.80;
					$stop->coord->y = 2355759.15;
					$line->forward->name = 'vers Beauregard';
					$line->forward->direction->Add($stop);
				}
				{
					$stop = t3lib_div::makeInstance('tx_icslibnavitia_StopArea');
					$stop->idx = 738;
					$stop->id = 737;
					$stop->name = 'Clos Courtel';
					$stop->externalCode = 'STA303';
					$stop->main = true;
					$stop->coord = t3lib_div::makeInstance('tx_icslibnavitia_Coord');
					$stop->coord->x = 304950.50;
					$stop->coord->y = 2355019.00;
					$line->backward->name = 'vers Beaulieu Atalante';
					$line->backward->direction->Add($stop);
				}
				break;
			}
		}
		return $line;
	}
	
	public function getRoutePointList($lineExternalCode, $forward = true) {
		if (!in_array($lineExternalCode, array('STANav1632', 'STA1188')))
			return null;
		$list = t3lib_div::makeInstance('tx_icslibnavitia_NodeList', 'tx_icslibnavitia_RoutePoint');
		switch ($lineExternalCode) {
			case 'STANav1632':
			{
				if ($forward) {
					$list->
						Add(self::makeRoutePoint(1923, 0, 119, 'STANav2467:STANav3785', true, 581, 578, 'STANav3785', 'Vieux Bourg', 233, 232, 'STA113', 'Vieux Bourg', true, 299324.00, 2345325.00, 299324.00, 2345325.00))->
						Add(self::makeRoutePoint(1925, 0, 119, 'STANav2467:STANav2910', true, 546, 547, 'STANav2910', 'Argoat', 220, 219, 'STA111', 'Argoat', true, 298633.82, 2345102.88, 298634.00, 2345103.00))->
						Add(self::makeRoutePoint(1927, 0, 119, 'STANav2467:STANav6377', true, 563, 560, 'STANav6377', 'Constant Mérel', 226, 225, 'STA107', 'Constant Mérel', true, 298395.17, 2344679.00, 298395.00, 2344679.00))->
						Add(self::makeRoutePoint(1929, 0, 119, 'STANav2467:STANav7168', true, 328, 328, 'STANav7168', 'Saint Joseph', 139, 138, 'STA2376', 'Saint Joseph', true, null, null, null, null));
				}
				else {
					$list->
						Add(self::makeRoutePoint(1930, 0, 120, 'STANav2468:STANav7170', true, 329, 327, 'STANav7170', 'Saint Joseph', 139, 138, 'STA2376', 'Saint Joseph', true, null, null, null, null))->
						Add(self::makeRoutePoint(1932, 0, 120, 'STANav2468:STANav7173', true, 561, 562, 'STANav7173', 'Constant Mérel', 226, 225, 'STA107', 'Constant Mérel', true, 298395.17, 2344679.00, 298395.17, 2344679.00))->
						Add(self::makeRoutePoint(1934, 0, 120, 'STANav2468:STANav7175', true, 548, 545, 'STANav7175', 'Argoat', 220, 219, 'STA111', 'Argoat', true, 298633.82, 2345102.88, 298633.82, 2345102.88))->
						Add(self::makeRoutePoint(1936, 0, 120, 'STANav2468:STANav7177', true, 580, 579, 'STANav7177', 'Vieux Bourg', 233, 232, 'STA113', 'Vieux Bourg', true, 299324.00, 2345325.00, 299324.00, 2345325.00));
				}
				break;
			}
			case 'STA1188':
			{
				if ($forward) {
					$list->
						Add(self::makeRoutePoint(2365, 0, 141, 'STA1814:STANav5912', true, 1730, 1731, 'STANav5912', 'Clos Courtel', 738, 737, 'STA303', 'Clos Courtel', true, 304950.50, 2355019.00, 304950.50, 2355019.00));
				}
				else {
					// $list->
						// Add'(self::makeRoutePoint(, , , '', , , , '', '', , , '', '', , , , , ))->
						// Add(self::makeRoutePoint(, , , '', , , , '', '', , , '', '', , , , , ))->
						// Add(self::makeRoutePoint(, , , '', , , , '', '', , , '', '', , , , , ))->
						// Add(self::makeRoutePoint(, , , '', , , , '', '', , , '', '', , , , , ));
				}
				break;
			}
		}
		return $list;
	}
	
	private function makeRoutePoint($ridx, $rid, $rridx, $rextCode, $rmain, $sidx, $sid, $sextCode, $sname, $saidx, $said, $saextCode, $saname, $samain, $sacx, $sacy, $rcx, $rcy) {
		$route = t3lib_div::makeInstance('tx_icslibnavitia_RoutePoint');
		$route->idx = $ridx;
		$route->id = $rid;
		$route->externalCode = $rextCode;
		$route->routeIdx = $rridx;
		$route->main = $rmain;
		$route->stopPoint->idx = $sidx;
		$route->stopPoint->id = $sid;
		$route->stopPoint->externalCode = $sextCode;
		$route->stopPoint->name = $sname;
		$route->stopPoint->stopArea->idx = $saidx;
		$route->stopPoint->stopArea->id = $said;
		$route->stopPoint->stopArea->externalCode = $saextCode;
		$route->stopPoint->stopArea->name = $saname;
		$route->stopPoint->stopArea->main = $samain;
		if ($sacx) {
			$route->stopPoint->stopArea->coord = t3lib_div::makeInstance('tx_icslibnavitia_Coord');
			$route->stopPoint->stopArea->coord->x = $sacx;
			$route->stopPoint->stopArea->coord->y = $sacy;
		}
		if ($rcx) {
			$route->stopPoint->coord = t3lib_div::makeInstance('tx_icslibnavitia_Coord');
			$route->stopPoint->coord->x = $rcx;
			$route->stopPoint->coord->y = $rcy;
		}
		return $route;
	}
	
	public function getDepartureBoardByStopPointForLine($stopPointExternalCode, $lineExternalCode, DateTime $date, $forward = true) {
		$stops = t3lib_div::makeInstance('tx_icslibnavitia_NodeList', 'tx_icslibnavitia_Stop');
		$stopPoints = t3lib_div::makeInstance('tx_icslibnavitia_NodeList', 'tx_icslibnavitia_StopPoint');
		$lines = t3lib_div::makeInstance('tx_icslibnavitia_NodeList', 'tx_icslibnavitia_Line');
		$destinations = t3lib_div::makeInstance('tx_icslibnavitia_NodeList', 'tx_icslibnavitia_StopArea');
		$result = array(
			'StopList' => $stops,
			'StopPointList' => $stopPoints,
			'LineList' => $lines,
			'DestinationList' => $destinations,
		);
		if ($lineExternalCode != 'STA1188')
			return $result;
		if (!in_array($stopPointExternalCode, in_array('STANav5912')))
			return $result;
		return $result;
	}
}
