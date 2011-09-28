<?php

// http://professionnels.ign.fr/DISPLAY/000/526/701/5267019/NTG_71.pdf paramètres.
// http://geodesie.ign.fr/contenu/fichiers/documentation/pedagogiques/transfo.pdf p12 paramètres.
// http://www.forumsig.org/showthread.php?p=64050#post64050
class tx_icslibnavitia_CoordinateConverter {

	private static $n = 0.72896862742141; // Exposant de la projection. n=sin(phi0)
	private static $c = 11745793.39; // Constante de la projection. c=s*N(phi0,a,e)*cotan(phi0)*exp(n*LatIso(phi0,e)) avec N(phi,a,e)=a/sqrt(1-e²*sin(phi)²) et LatIso(phi,e)=ln(tan(PI/4+phi/2)*((1-e*sin(phi))/(1+e*sin(phi)))^(e/2)) // N: grande normale
	private static $Xs = 600000.0; // Coordonnées en projection du pôle. Xs=X0
	private static $Ys = 8199695.768; // Coordonnées en projection du pôle. Ys=Y0+s*N(phi0, a, e)*cotan(phi0)
	private static $lambda0; // = deg2rad((14.025 / 60 + 20) / 60 + 2); // Longitude du méridien de paris par rapport au méridien d'origine. (rad)
	private static $e = 0.0824832567634177; // Première excentricité de l'ellipsoïde. ((SQRT(.006803487646))) e=sqrt((a²-b²)/a²)
	
	private static $a = 6378249.2; // Demi grand axe. (m)
	private static $b = 6356515; // Demi petit axe. (m) b=a*(1-f)
	private static $f; // = 1 / 293.466021; // Applatissement.
	private static $phi0; // = deg2rad(48 / 60 + 46); // Latitude origine. (rad)
	private static $phi1; // = deg2rad((56.108 / 60 + 53) / 60 + 45); // Latitude du premier parallèle automécoïque. (rad)
	private static $phi2; // = deg2rad((45.652 / 60 + 41) / 60 + 47); // Latitude du second parallèle automécoïque. (rad)
	private static $X0 = 600000; // Translation X. (m) (E0)
	private static $Y0 = 2200000; // Translation Y. (m) (N0)
	private static $s = 0.99987742; // Facteur d'échelle. (k0)
	
	public static function init() {
		self::$lambda0 = deg2rad((14.025 / 60 + 20) / 60 + 2);
		self::$f = 1 / 293.466021293627;
		self::$phi0 = deg2rad(48 / 60 + 46);
		self::$phi1 = deg2rad((56.108 / 60 + 53) / 60 + 45);
		self::$phi2 = deg2rad((45.652 / 60 + 41) / 60 + 47);
	}
	
	public static function convertfromWGS84($lat, $lng) {
		$phi = deg2rad($lat);
		$lambda = deg2rad($lng);
		
		// list($X, $Y, $Z) = convertToWGS84Cartesian($phi, $lambda);
		// list($X, $Y, $Z) = translateForNTF($X, $Y, $Z);
		// list($phi, $lambda) = convertFromNTFCartesian($X, $Y, $Z);
		// list($X, $Y) = convertToLambertIIExtended($phi, $lambda);
		
		$L = log(tan((M_PI / 4) + ($phi / 2))) * pow((1 - self::$e * sin($phi)) / (1 + self::$e * sin($phi)), self::$e / 2);
		// $L = .5 * log((1 + sin($lat)) / (1 - sin($lat))) - self::$e * .5 * log((1 + self::$e * sin($lat)) / (1 - self::$e * sin($lat)));
		$R = self::$c * exp(- self::$n * $L);
		$gamma = self::$n * ($lambda - self::$lambda0); // θ = n(?-?0)
		$X = self::$Xs + $R * sin($gamma);
		$Y = self::$Ys - $R * cos($gamma);
		return array('X' => $X, 'Y' => $Y);
	}
	
	public static function convertToWGS84($X, $Y) {
		// list($phi, $lambda) = convertFromLambertIIExtended($X, $Y);
		// list($X, $Y, $Z) = convertToNTFCartesian($phi, $lambda);
		// list($X, $Y, $Z) = translateForWGS84($X, $Y, $Z);
		// list($phi, $lambda) = convertFromWGS84Cartesian($X, $Y, $Z);
	
		$dX = $X - self::$Xs;
		$dY = $Y - self::$Ys;
		$R = sqrt($dX * $dX + $dY * $dY);
		$gamma = atan(($X - self::$Xs) / (self::$Ys - $Y));
		$lambda = self::$lambda0 + $gamma / self::$n;
		$L = (-1 / self::$n) * log(abs($R / self::$c));
		$phi = self::Linv($L);
		return array('lat' => rad2deg($phi), 'lng' => rad2deg($lambda));
	}
	
	private static function Linv($L) {
		$phi0 = 0;
		$phiI = 2 * atan(exp($L)) - M_PI / 2;
		while (abs($phiI - $phi0) > 1e-5) {
			$phi0 = $phiI;
			$phiI = 2 * atan(pow((1 + self::$e * sin($phi0)) / (1 - self::$e * sin($phi0)), self::$e / 2) * exp($L)) - M_PI / 2;
		}
		return $phiI;
	}
}
tx_icslibnavitia_CoordinateConverter::init();
