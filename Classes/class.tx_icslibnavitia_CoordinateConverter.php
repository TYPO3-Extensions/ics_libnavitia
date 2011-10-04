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

	// private static $n = 0.72896862742141; // Exposant de la projection. n=sin(phi0)
	// private static $c = 11745793.39; // Constante de la projection. c=s*N(phi0,a,e)*cotan(phi0)*exp(n*LatIso(phi0,e)) avec N(phi,a,e)=a/sqrt(1-e²*sin(phi)²) et LatIso(phi,e)=ln(tan(PI/4+phi/2)*((1-e*sin(phi))/(1+e*sin(phi)))^(e/2)) // N: grande normale
	// private static $Xs = 600000.0; // Coordonnées en projection du pôle. Xs=X0
	// private static $Ys = 8199695.768; // Coordonnées en projection du pôle. Ys=Y0+s*N(phi0, a, e)*cotan(phi0)
	// private static $lambda0; // = deg2rad((14.025 / 60 + 20) / 60 + 2); // Longitude du méridien de paris par rapport au méridien d'origine. (rad)
	// private static $e = 0.0824832567634177; // Première excentricité de l'ellipsoïde. ((SQRT(.006803487646))) e=sqrt((a²-b²)/a²)
	
	// private static $a = 6378249.2; // Demi grand axe. (m)
	// private static $b = 6356515; // Demi petit axe. (m) b=a*(1-f)
	// private static $f; // = 1 / 293.466021; // Applatissement.
	// private static $phi0; // = deg2rad(48 / 60 + 46); // Latitude origine. (rad)
	// private static $phi1; // = deg2rad((56.108 / 60 + 53) / 60 + 45); // Latitude du premier parallèle automécoïque. (rad)
	// private static $phi2; // = deg2rad((45.652 / 60 + 41) / 60 + 47); // Latitude du second parallèle automécoïque. (rad)
	// private static $X0 = 600000; // Translation X. (m) (E0)
	// private static $Y0 = 2200000; // Translation Y. (m) (N0)
	// private static $s = 0.99987742; // Facteur d'échelle. (k0)
	
	// Initialisation des constantes.
	public static function init() {
		self::$clarkeEllipsoid = new CoordinateConverterParameters( // Valeurs provenant du fichier Data.txt ligne 6 de Circé France 4.
			'a', 6378249.2,
			'b', 6356515,
			'f', 1 / 293.466021,
			'e2', .006803487646,
			'e', sqrt(.006803487646),
			'T', new CoordinateConverterParameters( // Valeurs provenant du fichier Data.txt ligne 44 de Circé France 4.
				'x', -168,
				'y', -60,
				'z', 320
			)
		);
		// self::$clarkeEllipsoid->f = 1 - self::$clarkeEllipsoid->b / self::$clarkeEllipsoid->a;
		// self::$clarkeEllipsoid->e = sqrt(1 - pow(self::$clarkeEllipsoid->b, 2) / pow(self::$clarkeEllipsoid->a, 2));
		
		self::$wgs84Ellipsoid = new CoordinateConverterParameters( // Valeurs provenant du fichier Data.txt ligne 7 de Circé France 4.
			'a', 6378137,
			'b', 6356752.3141,
			'f', 1 / 298.257222101, //298.257223563
			'e2', .006694380025,
			'e', sqrt(.006694380025)
		);
		// self::$wgs84Ellipsoid->b = self::$wgs84Ellipsoid->a * (1 - self::$wgs84Ellipsoid->f);
		
		self::$lambertIIE = new CoordinateConverterParameters( // Valeurs provenant du fichier Data.txt ligne 57 de Circé France 4.
			'X0', 600000,
			'Y0', 2200000,
			'phi0', deg2rad(52 * .9),
			'phi1', deg2rad((56.108 / 60 + 53) / 60 + 45),
			'phi2', deg2rad((45.652 / 60 + 41) / 60 + 47),
			'lambda0', deg2rad((14.025 / 60 + 20) / 60 + 2),
			'scale', 0.99987742
		);
		// foreach (array('phi0', 'phi1', 'phi2', 'lambda0') as $name)
			// self::$lambertIIE->$name = deg2rad(self::$lambertIIE->$name);
		// $tmp = self::$lambertIIE->scale * self::N(self::$lambertIIE->phi0, self::$clarkeEllipsoid->a, self::$clarkeEllipsoid->e2) / tan(self::$lambertIIE->phi0);
		// Valeurs provenant de la dernière page de NTG_71.pdf
		self::$lambertIIE->n = .7289686274; // sin(self::$lambertIIE->phi0);
		self::$lambertIIE->c = 11745793.39; // $tmp * exp(self::$lambertIIE->n * self::L(self::$lambertIIE->phi0, self::$clarkeEllipsoid->e));
		self::$lambertIIE->Xs = 600000; // self::$lambertIIE->X0;
		self::$lambertIIE->Ys = 8199695.768; // self::$lambertIIE->Y0 + $tmp;
	}
	
	// Effectue les étapes de la transformation des coordonnées WGS84 géographiques vers la projection Lambert II étendue.
	public static function convertfromWGS84($lat, $lng) {
		$phi = deg2rad($lat);
		$lambda = deg2rad($lng);
		
		list($X, $Y, $Z) = self::convertGeographicToCartesian($phi, $lambda, self::$wgs84Ellipsoid->a, self::$wgs84Ellipsoid->b);
		list($X, $Y, $Z) = self::translateForNTF($X, $Y, $Z);
		list($phi, $lambda) = self::convertCartesianToGeographic($X, $Y, $Z, self::$clarkeEllipsoid->a, self::$clarkeEllipsoid->b);
		list($X, $Y) = self::convertToLambertIIExtended($phi, $lambda);
		return array('X' => $X, 'Y' => $Y);
	}
	
	// Effectue les étapes de la transformation des coordonnées de la projection Lambert II étendue vers WGS84 géographiques.
	public static function convertToWGS84($X, $Y) {
		list($phi, $lambda) = self::convertFromLambertIIExtended($X, $Y);
		list($X, $Y, $Z) = self::convertGeographicToCartesian($phi, $lambda, self::$clarkeEllipsoid->a, self::$clarkeEllipsoid->b);
		list($X, $Y, $Z) = self::translateForWGS84($X, $Y, $Z);
		list($phi, $lambda) = self::convertCartesianToGeographic($X, $Y, $Z, self::$wgs84Ellipsoid->a, self::$wgs84Ellipsoid->b);
		return array('lat' => rad2deg($phi), 'lng' => rad2deg($lambda));
	}
	
	// Translate les coordonnées cartésiennes de l'ellipsoïde du WGS84 vers l'ellipsoïde Clarke.
	public static function translateForNTF($X, $Y, $Z) {
		$X += self::$clarkeEllipsoid->T->x;
		$Y += self::$clarkeEllipsoid->T->y;
		$Z += self::$clarkeEllipsoid->T->z;
		return array($X, $Y, $Z);
	}
	
	// Translate les coordonnées cartésiennes de l'ellipsoïde Clarke vers l'ellipsoïde du WGS84.
	public static function translateForWGS84($X, $Y, $Z) {
		$X -= self::$clarkeEllipsoid->T->x;
		$Y -= self::$clarkeEllipsoid->T->y;
		$Z -= self::$clarkeEllipsoid->T->z;
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
		$R = sqrt(pow($X, 2) + pow($Y, 2) + pow($Z, 2));
		$r = sqrt(pow($X, 2) + pow($Y, 2));
		$ae2 = $ellipsoid->a * $ellipsoid->e2;
		$lambda = atan($Y / $X);
		$mu = atan($Z * ((1 - $ellipsoid->f) + $ae2 / $R) / $r);
		$phi = atan(($Z * (1 - $ellipsoid->f) + $ae2 * pow(sin($mu), 3)) / ((1 - $ellipsoid->f) * ($r - $ae2 * pow(cos($mu), 3))));
		return array($phi, $lambda);
	}
	
	// Transformation de coordonnées géographiques NTF en projection de Lambert. ALG0003 de NTG_71.pdf
	public static function convertToLambertIIExtended($phi, $lambda) {
		$L = self::L($phi, self::$clarkeEllipsoid->e);
		$R = self::$lambertIIE->c * exp(- self::$lambertIIE->n * $L);
		$gamma = self::$lambertIIE->n * ($lambda - self::$lambertIIE->lambda0);
		$X = self::$lambertIIE->Xs + $R * sin($gamma);
		$Y = self::$lambertIIE->Ys - $R * cos($gamma);
		return array($X, $Y);
	}
	
	// Transformation de coordonnées en projection de Lambert en géographiques. ALG0004 de NTG_71.pdf
	public static function convertFromLambertIIExtended($X, $Y) {
		$dX = $X - self::$lambertIIE->Xs;
		$dY = $Y - self::$lambertIIE->Ys;
		$R = sqrt($dX * $dX + $dY * $dY);
		$gamma = atan(($dX) / (-$dY));
		$lambda = self::$lambertIIE->lambda0 + $gamma / self::$lambertIIE->n;
		$L = (-1 / self::$lambertIIE->n) * log(abs($R / self::$lambertIIE->c));
		$phi = self::Linv($L, self::$clarkeEllipsoid->e);
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
		return $phiI;
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