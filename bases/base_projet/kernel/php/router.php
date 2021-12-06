<?php
// Librairie Router
namespace Kernel;



class Router {

    /**
     * Liste des routes
     */
	private static $routes = [];

    /**
     * Route par defaut
     */
	private static $defaut;

    /**
     * Route si non trouve
     */
	private static $notfound;


    /**
     * Configure la route par defaut
	 * 
	 * @param string nom de la route
     */
	static function default($defaut) {
		self::$defaut = $defaut;
	}


    /**
     * Configure la route en cas de route non trouvee (404)
	 * 
	 * @param string nom de la route
     */
	static function notfound($notfound) {
		self::$notfound = $notfound;
	}


    /**
     * Ajoute une route
	 * 
	 * @param string nom de la route
	 * @param function fonction anonyme contenant la route
     */
	static function add($nom, $route) {
		self::$routes[$nom] = $route;
	}


    /**
     * Appel la bonne route
     */
	static function routing() {
		require_once 'composant/route.php';

		if (isset($_GET['redirect'])) {
			if (array_key_exists($_GET['redirect'], self::$routes)) {
				self::$routes[$_GET['redirect']]();
			} else if (isset(self::$notfound)) {
				self::$routes[self::$notfound]();
			} else if (isset(self::$defaut)){
				self::$routes[self::$defaut]();
			} else {
				self::$routes[array_key_first(self::$routes)]();
			}
		} else if (isset(self::$defaut)) {
			self::$routes[self::$defaut]();
		} else {
			if (count(self::$routes) > 0) {
				self::$routes[array_key_first(self::$routes)]();
			} else {
				throw new \Exception("Aucune route n'a été définie.");
				die;
			}
		}
	}
	
}

?>