<?php
namespace Kernel;



/**
 * Librairie gerant la configuration du noyau
 */
class Configuration {

	/**
	 * Configuration actuelle
	 * 
	 * @var array la configuration
	 */
	private static $current;


	/**
	 * Charge la configuration
	 * 
	 * @return void
	 * @throws \Exception Si le fichier de configuration n'est pas trouvé
	 */
	static function load() {
		try {
			self::$current = json_decode(file_get_contents('.kernel/configuration.json'));
		} catch (\Exception $e) {
            trigger_error('Impossible de charger la configuration, message : "' . $e->getMessage() . '" !');
		}
	}
	

	/**
	 * Retourne la configuration actuelle
	 * 
	 * @return object la configuration
	 * @throws \Exception Si la configuration n'est pas chargee
	 */
	static function get() {
		if (!is_null(self::$current)) {
			return self::$current;
		} else {
			throw new \Exception('La configuration n\'est pas chargée !');
		}
	}
	
}
