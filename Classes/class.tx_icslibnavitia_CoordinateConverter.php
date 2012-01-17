<?php

// http://professionnels.ign.fr/DISPLAY/000/526/701/5267019/NTG_71.pdf paramètres.
// http://professionnels.ign.fr/DISPLAY/000/526/702/5267024/NTG_80.pdf conversions de coordonnées.
// http://geodesie.ign.fr/contenu/fichiers/documentation/pedagogiques/transfo.pdf p12 paramètres.
// http://www.forumsig.org/showthread.php?p=64050#post64050
class tx_icslibnavitia_CoordinateConverter {
	// Paramètres de l'ellipsoïde de Clarke de 1880 utilisé pour le système géodésique NTF.
	public static $clarkeEllipsoid;
	
	public static $wgs84Ellipsoid;
	
	// Paramètres de la projection Lambert II étendue dans le système géodésique NTF.
	public static $lambertIIE;
	
	// Initialisation des constantes.
	public static function init() {
		self::$clarkeEllipsoid = new CoordinateConverterParameters( // Valeurs provenant du fichier Data.txt ligne 6 de Circé France 4.
			'a', 6378249.2,
			'b', 6356515,
			'f', 1 / 293.4660213,
			'e2', .006803487646,
			'e', sqrt(.006803487646),
			'T', new CoordinateConverterParameters( // Valeurs provenant du fichier Data.txt ligne 44 de Circé France 4.
				'x', -168,
				'y', -60,
				'z', 320
			)
		);
		
		self::$wgs84Ellipsoid = new CoordinateConverterParameters( // Valeurs provenant du fichier Data.txt ligne 7 de Circé France 4.
			'a', 6378137,
			'b', 6356752.3141,
			'f', 1 / 298.257223563,
			'e2', .006694380025,
			'e', sqrt(.006694380025)
		);
		
		self::$lambertIIE = new CoordinateConverterParameters( // Valeurs provenant du fichier Data.txt ligne 57 de Circé France 4.
			'X0', 600000,
			'Y0', 2200000,
			'phi0', deg2rad(52 * .9),
			'phi1', deg2rad(50.99879884 * .9),
			'phi2', deg2rad(52.99557167 * .9),
			'lambda0', deg2rad((14.025 / 60 + 20) / 60 + 2),
			'scale', 0.99987742
		);
		// Valeurs provenant de la dernière page de NTG_71.pdf
		self::$lambertIIE->n = .7289686274; // sin(self::$lambertIIE->phi0);
		self::$lambertIIE->c = 11745793.39; // $tmp * exp(self::$lambertIIE->n * self::L(self::$lambertIIE->phi0, self::$clarkeEllipsoid->e));
		self::$lambertIIE->Xs = 600000; // self::$lambertIIE->X0;
		self::$lambertIIE->Ys = 8199695.768; // self::$lambertIIE->Y0 + $tmp;
		$Lamb_vo1 = self::$clarkeEllipsoid->a / sqrt(1 - self::$clarkeEllipsoid->e2 * (pow(sin(self::$lambertIIE->phi1),2)));
		$Lamb_vo2 = self::$clarkeEllipsoid->a / sqrt(1 - self::$clarkeEllipsoid->e2 * (pow(sin(self::$lambertIIE->phi2),2)));
		$Lamb_po1 = self::$clarkeEllipsoid->a * (1 - self::$clarkeEllipsoid->e2) / pow(sqrt(1 - self::$clarkeEllipsoid->e2 * pow(sin(self::$lambertIIE->phi1),2)),3);
		$Lamb_po2 = self::$clarkeEllipsoid->a * (1 - self::$clarkeEllipsoid->e2) / pow(sqrt(1 - self::$clarkeEllipsoid->e2 * pow(sin(self::$lambertIIE->phi2),2)),3);
		$Lamb_m1 = 1 + $Lamb_po1 / 2 / $Lamb_vo1 * pow(((self::$lambertIIE->phi1 - self::$lambertIIE->phi0)),2); 
		$Lamb_m2 = 1 + $Lamb_po2 / 2 / $Lamb_vo2 * pow(((self::$lambertIIE->phi2 - self::$lambertIIE->phi0)),2);
		$Lamb_m = ($Lamb_m1 + $Lamb_m2) / 2;
		$Lamb_mL = 2 - $Lamb_m;
		$Lamb_v0 = self::$clarkeEllipsoid->a / sqrt(1 - self::$clarkeEllipsoid->e2 * (pow(sin(self::$lambertIIE->phi0),2)));
		$Lamb_R0 = $Lamb_v0 / tan(self::$lambertIIE->phi0);
		/* mLR0 est le Rayon du parallèle d origine après réduction d echelle */
		self::$lambertIIE->Lamb_mLR0 = $Lamb_mL * $Lamb_R0;
	}
	
	// Effectue les étapes de la transformation des coordonnées WGS84 géographiques vers la projection Lambert II étendue.
	public static function convertFromWGS84($lat, $lng) {
		$phi = deg2rad($lat);
		$lambda = deg2rad($lng);
		
		list($X, $Y, $Z) = self::convertGeographicToCartesian($phi, $lambda, self::$wgs84Ellipsoid);
		list($X, $Y, $Z) = self::translateForNTF($X, $Y, $Z);
		list($phi, $lambda) = self::convertCartesianToGeographic($X, $Y, $Z, self::$clarkeEllipsoid);
		list($X, $Y) = self::convertToLambertIIExtended($phi, $lambda);
		return array('X' => $X, 'Y' => $Y);
	}
	
	// Effectue les étapes de la transformation des coordonnées de la projection Lambert II étendue vers WGS84 géographiques.
	public static function convertToWGS84($X, $Y) {
		list($phi, $lambda) = self::convertFromLambertIIExtended($X, $Y);
		list($X, $Y, $Z) = self::convertGeographicToCartesian($phi, $lambda, self::$clarkeEllipsoid);
		list($X, $Y, $Z) = self::translateForWGS84($X, $Y, $Z);
		list($phi, $lambda) = self::convertCartesianToGeographic($X, $Y, $Z, self::$wgs84Ellipsoid);
		return array('lat' => rad2deg($phi), 'lng' => rad2deg($lambda));
	}
	
	// Translate les coordonnées cartésiennes de l'ellipsoïde du WGS84 vers l'ellipsoïde Clarke.
	public static function translateForNTF($X, $Y, $Z) {
		$X -= self::$clarkeEllipsoid->T->x;
		$Y -= self::$clarkeEllipsoid->T->y;
		$Z -= self::$clarkeEllipsoid->T->z;
		return array($X, $Y, $Z);
	}
	
	// Translate les coordonnées cartésiennes de l'ellipsoïde Clarke vers l'ellipsoïde du WGS84.
	public static function translateForWGS84($X, $Y, $Z) {
		$X += self::$clarkeEllipsoid->T->x;
		$Y += self::$clarkeEllipsoid->T->y;
		$Z += self::$clarkeEllipsoid->T->z;
		return array($X, $Y, $Z);
	}
	
	// Transformation de coordonnées géographiques en cartésiennes. ALG0009 de NTG_80.pdf
	public static function convertGeographicToCartesian($phi, $lambda, $ellipsoid) {
		$N = self::N($phi, $ellipsoid->a, $ellipsoid->e2);
		$X = $N * cos($phi) * cos($lambda);
		$Y = $N * cos($phi) * sin($lambda);
		$Z = $N * (1 - $ellipsoid->e2) * sin($phi);
		return array($X, $Y, $Z);
	}
	
	// Transformation de coordonnées cartésiennes en géographiques. Algorithme page 4 de transfo.pdf. Donne les mêmes résultats que ALG0012 de NTG_80.pdf
	public static function convertCartesianToGeographic($X, $Y, $Z, $ellipsoid) {
		$R = sqrt(pow($X, 2) + pow($Y, 2) + pow($Z, 2)); // Algo non itératif.
		$r = sqrt(pow($X, 2) + pow($Y, 2));
		$ae2 = $ellipsoid->a * $ellipsoid->e2;
		$lambda = atan($Y / $X);
		$mu = atan($Z * ((1 - $ellipsoid->f) + $ae2 / $R) / $r);
		$phi = atan(($Z * (1 - $ellipsoid->f) + $ae2 * pow(sin($mu), 3)) / ((1 - $ellipsoid->f) * ($r - $ae2 * pow(cos($mu), 3))));
		return array($phi, $lambda);
	}
	
	// Transformation de coordonnées géographiques NTF en projection de Lambert. ALG0003 de NTG_71.pdf
	public static function convertToLambertIIExtended($phi, $lambda) {
		$Lamb_LatIso = log(tan(M_PI / 4 + $phi / 2)) - self::$clarkeEllipsoid->e / 2 * log((1 + self::$clarkeEllipsoid->e * sin($phi))/(1 - self::$clarkeEllipsoid->e * sin($phi)));
		$Lamb_LatIso0 = log(tan(M_PI / 4 + self::$lambertIIE->phi0 / 2)) - self::$clarkeEllipsoid->e / 2 * log((1 + self::$clarkeEllipsoid->e * sin(self::$lambertIIE->phi0))/(1 - self::$clarkeEllipsoid->e * sin(self::$lambertIIE->phi0)));
		if ($lambda < M_PI) {
			$Lamb_Gamma = ($lambda - self::$lambertIIE->lambda0) * sin(self::$lambertIIE->phi0);
		}
		if ($lambda > M_PI) {
			$Lamb_Gamma = ($lambda - self::$lambertIIE->lambda0 - 2 * M_PI) * sin(self::$lambertIIE->phi0);
		}
		$Lamb_R = self::$lambertIIE->Lamb_mLR0 * exp(- sin(self::$lambertIIE->phi0) * ($Lamb_LatIso - $Lamb_LatIso0));
		$Lamb_E1 = $Lamb_R * sin($Lamb_Gamma);
		$X = $Lamb_E1 + self::$lambertIIE->X0;
		$Y = self::$lambertIIE->Lamb_mLR0 - $Lamb_R + $Lamb_E1 * tan($Lamb_Gamma / 2) + self::$lambertIIE->Y0;
		return array($X, $Y);
	}
	
	// Transformation de coordonnées en projection de Lambert en géographiques. ALG0004 de NTG_71.pdf
	public static function convertFromLambertIIExtended($X, $Y) {
		$Lamb_Ls = log(tan(M_PI / 4 + self::$lambertIIE->phi0 / 2)) - self::$clarkeEllipsoid->e / 2 * log((1 + self::$clarkeEllipsoid->e * sin(self::$lambertIIE->phi0)) / (1 - self::$clarkeEllipsoid->e * sin(self::$lambertIIE->phi0)));
		$dX = $X - self::$lambertIIE->X0;
		$dY = $Y - self::$lambertIIE->Y0;
		$gamma = atan($dX / (self::$lambertIIE->Lamb_mLR0 - $dY));
		$lambda = ($gamma / sin(self::$lambertIIE->phi0) + self::$lambertIIE->lambda0);
		$Lamb_R = (self::$lambertIIE->Lamb_mLR0 - $dY) / cos($gamma);
		$Lamb_L0 = log(tan(M_PI / 4 + self::$lambertIIE->phi0 / 2)) - (self::$clarkeEllipsoid->e / 2) * log((1 + self::$clarkeEllipsoid->e * sin(self::$lambertIIE->phi0)) / (1 - self::$clarkeEllipsoid->e * sin(self::$lambertIIE->phi0)));
		$Lamb_L = $Lamb_L0 + log(self::$lambertIIE->Lamb_mLR0 / $Lamb_R) / sin(self::$lambertIIE->phi0);
		$epsilon = 1;
		$phi = 2 * atan(exp($Lamb_L)) - M_PI / 2;
		while ($epsilon > 1e-10) {
			$phi1 = 2 * (atan(exp($Lamb_L + self::$clarkeEllipsoid->e / 2 * log((1 + self::$clarkeEllipsoid->e * sin($phi)) / (1 - self::$clarkeEllipsoid->e * sin($phi)))))) - M_PI / 2;
			$epsilon = abs($phi1 - $phi);
			$phi = $phi1;
		}
		return array($phi, $lambda);
	}
	
	// Calcul de la réciproque de la latitude isométrique. ALG0002 de NTG_71.pdf 
	private static function Linv($L, $e) {
		$phi0 = 2 * atan(exp($L)) - M_PI / 2;
		$phiI = 2 * atan(pow((1 + $e * sin($phi0)) / (1 - $e * sin($phi0)), $e / 2) * exp($L)) - M_PI / 2;
		while (abs($phiI - $phi0) > 1e-10) {
			$phi0 = $phiI;
			$phiI = 2 * atan(pow((1 + $e * sin($phi0)) / (1 - $e * sin($phi0)), $e / 2) * exp($L)) - M_PI / 2;
		}
		return $result;
	}

	// Calcul de la grande normale. ALG0021 de NTG_71.pdf
	private static function N($phi, $a, $e2) {
		return $a / sqrt(1 - $e2 * pow(sin($phi), 2));
	}

	// Calcul de la latitude isométrique. ALG0001 de NTG_71.pdf
	private static function L($phi, $e) {
		return log(tan((M_PI / 4) + ($phi / 2))) * pow((1 - $e * sin($phi)) / (1 + $e * sin($phi)), $e / 2);
	}
}
tx_icslibnavitia_CoordinateConverter::init();

class CoordinateConverterParameters {
	public function __construct() {
		$args = func_get_args();
		for ($i = 0; $i < count($args); $i += 2) {
			$name = $args[$i];
			$this->$name = $args[$i + 1];
		}
	}
}