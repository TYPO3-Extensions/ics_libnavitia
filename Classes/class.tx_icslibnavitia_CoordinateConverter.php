<?php

// http://professionnels.ign.fr/DISPLAY/000/526/701/5267019/NTG_71.pdf paramètres.
// http://geodesie.ign.fr/contenu/fichiers/documentation/pedagogiques/transfo.pdf p12 paramètres.
class tx_icslibnavitia_CoordinateConverter {

	private $n = 0.7289127966; // Exposant de la projection.
	private $c = 11745793.39; // Constante de la projection.
	private $Xs = 600000.0; // Coordonnées en projection du pôle.
	private $Ys = 8199695.768; // Coordonnées en projection du pôle.
	private $lambda0 = deg2rad((14.025 / 60 + 20) / 60 + 2); // Longitude du méridien de paris par rapport au méridien d'origine. (rad)
	private $a = 6378249.2; // Demi grand axe. (m)
	private $f = 1 / 293.466021; // Applatissement.
	private $e = 0.08248325676; // Première excentricité de l'ellipsoïde.
	// Lambert II Carto (Centre) 	52 grades 	45°53’56,108” 	47°41’45,652” 	600 000 m 	2 200 000 m (http://fr.wikipedia.org/wiki/Projection_de_Lambert)
	private $phi0 = deg2rad(48 / 60 + 46); // Latitude origine. (rad)
	private $phi1 = deg2rad((56.108 / 60 + 53) / 60 + 45); // Latitude du premier parallèle automécoïque. (rad)
	private $phi1 = deg2rad((45.652 / 60 + 41) / 60 + 47); // Latitude du second parallèle automécoïque. (rad)
	private $X0 = 600000; // Translation X. (m) (E0)
	private $Y0 = 2200000; // Translation Y. (m) (N0)
	private $f = 0.99987742; // Facteur  d'échelle.
	
	private static function init() {
	}
	
	public static function convertfromWGS84($lat, $lng) {
		$L = .5 * log((1 + sin($lat)) / (1 - sin($lat))) - self::$e * .5 * log((1 + self::$e * sin($lat)) / (1 - self::$e * sin($lat)));
		$R = self::$c * exp(- self::$n * $L);
		$gamma = self::$n * ($lng - self::$lambda0);
		$X = self::$Xs + $R * sin($gamma);
		$Y = self::$Ys - $R * cos($gamma);
	}
	
	public static function convertToWGS84($X, $Y) {
		$dX = $X - self::$Xs;
		$dY = $Y - self::$Ys;
		$R = sqrt($dX * $dX + $dY * $dY);
		$gamma = atan(($X - self::$Xs) / (self::$Ys / $Y));
		$lambda = self::$lambda0 + $gamma / self::$n;
		$L = (-1 / self::$n) * log($R / self::$c);
		$phi = self::Lm1($L);
		return array('lat' => $phi, 'lng' => $lambda);
	}
	
	private static Lm1($L) {
		$phi0 = 0;
		$phiI = 2 * atan(exp($L)) - M_PI / 2;
		while (abs($phiI - $phi0) > 1e-5) {
			$phi0 = $phiI;
			$phiI = 2 * atan(((1 + self::$e * sin($phiI)) / (1 - self::$e * sin($phiI))) * (self::$e / 2) * exp($L)) - M_PI / 2;
		}
		return $phiI;
	}
}
