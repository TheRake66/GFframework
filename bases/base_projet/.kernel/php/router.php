<?php
namespace Kernel;
use Kernel\Suppervisor;



// Librairie Router
class Router {

    /**
     * Liste des routes
     */
	private static $routes = [];

    /**
     * Route par defaut
     */
	private static $default;

    /**
     * Route si non trouve
     */
	private static $notfound;

    /**
     * Route actuelle
     */
	private static $current;


    /**
     * Configure la route par defaut
	 * 
	 * @param string nom de la route
     */
	static function default($defaut) {
		self::$default = $defaut;
	}


	/**
	 * Charge les routes
	 */
	static function load() {
		require_once 'debug/app/route.php';
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
	 * @param object classe du controleur
     */
	static function add($nom, $route) {
		self::$routes[$nom] = $route;
	}
	

	/**
	 * Retourne la route actuelle
	 * 
	 * @return string le nom de la route
	 */
	static function get() {
		if (is_null(self::$current)) {

			$r = null;
			if (isset($_GET['r'])) {
				if (self::exist($_GET['r'])) {
					$r = $_GET['r'];
				} elseif(self::exist(self::$notfound)) {
					$r = self::$notfound;
				}
			}
			if (is_null($r)) {
				if(self::exist(self::$default)) {
					$r = self::$default;
				} else {
					$r = self::getFirst();
					if (is_null($r)) {
						trigger_error("Aucune route n'a été définie.");
						die;
					}
				}
			}
			self::$current = $r;
			return $r;
		} else {
			return self::$current;
		}
	}
	

	/**
	 * Retourne le controleur actuel
	 * 
	 * @return object le controleur
	 */
	static function getController() {
		return self::$routes[self::get()];
	}


	/**
	 * Retourne la premiere route
	 * 
	 * @return string nom de la premiere route
	 */
	static function getFirst() {
		if (count(self::$routes) > 0) {
			return array_key_first(self::$routes);
		}
	}


	/**
	 * Verifi si une route existe
	 * 
	 * @param string nom de la route
	 * @return bool si existe
	 */
	static function exist($name) {
		return (!is_null($name) && array_key_exists($name, self::$routes));
	}


    /**
     * Appel la bonne route
     */
	static function routing() {
		$c = self::getController();
		new $c();
		Suppervisor::log('Routage fait.');
	}
	
}

?>