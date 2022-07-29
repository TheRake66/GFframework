<?php
namespace Kernel\Communication;

use Kernel\Debug\Error;
use Kernel\Debug\Log;
use Kernel\Io\Autoloader;
use Kernel\Io\Convert\Encoded;
use Kernel\Io\Stream;
use Kernel\Environnement\Configuration;
use Kernel\Security\Vulnerability\Xss;
use Kernel\Url\Parser;
use Kernel\Url\Router;


/**
 * Librairie gérant les appels d'API REST.
 *
 * @author Thibault Bustos (TheRake66)
 * @version 1.0
 * @package Kernel\Communication
 * @category Framework source
 * @license MIT License
 * @copyright © 2022 - Thibault BUSTOS (TheRake66)
 */
abstract class Rest {

    /**
     * @var int Temps UNIX en MS a l'exécution de la requête.
	 */
    private $started;


	/**
	 * Appel la fonction API de la route demandée.
	 * 
	 * @return void
	 */
	static function check() {
		$entry = Router::entry();
		Log::add('Vérification de l\'appel API...', Log::LEVEL_PROGRESS);
		if (Autoloader::typeof($entry) === Autoloader::TYPE_API) {
			Log::add('Appel API identifié : "' . $entry . '".');
			Log::add('Traitement de l\'appel API...', Log::LEVEL_PROGRESS);

			$object = new $entry();
			$object->started = microtime(true);
			$method = $_SERVER['REQUEST_METHOD'];
			$methods = Router::methods();
			if ((is_array($methods) && (in_array($method, $methods) || in_array(Router::METHOD_ALL, $methods))) || 
				(!is_array($methods) && ($methods === $method || $methods === Router::METHOD_ALL))) {
				$route = (object)Router::params();
				$query = (object)$_GET;
				$body = (object)json_decode(file_get_contents('php://input'));

				Log::add('Exécution de la requête REST (méthode : "' . $method . '", url : "' . Parser::current() . '")...',
					Log::LEVEL_PROGRESS, Log::TYPE_QUERY);

				Log::add('Paramètres de la requête REST (route) : "' . print_r($route, true) . '".',
					Log::LEVEL_INFO, Log::TYPE_QUERY_PARAMETERS);

				Log::add('Paramètres de la requête REST (query) : "' . print_r($query, true) . '".',
					Log::LEVEL_INFO, Log::TYPE_QUERY_PARAMETERS);

				Log::add('Paramètres de la requête REST (body) : "' . print_r($body, true) . '".',
					Log::LEVEL_INFO, Log::TYPE_QUERY_PARAMETERS);

				$function = strtolower($method);
				if (method_exists($object, $function)) {
					$object->$function($route, $query, $body);
					$object->send();
				} else {
					$object->send(null,1, 'La méthode d\'API "' . $function . '" n\'existe pas dans la ressource !', 500);
				}
			} else {
				$object->send(null, 1, 'La méthode "' . $method . '" n\'est pas supportée par cette ressource !', 405);
			}
		} else {
			Log::add('Aucun appel API.', Log::LEVEL_GOOD);
		}
	}


	/**
	 * Formate et envoie la réponse au client.
	 * 
	 * @param mixed $content Le contenu à envoyer.
	 * @param int $code Le code de retour.
	 * @param string $message Le message de retour.
	 * @param int $status Le statut HTTP.
	 * @return void
	 */
	protected function send($content = null, $code = 0, $message = '', $status = 200) {
		$ended = microtime(true);
		$time = round(($ended - $this->started) * 1000);
		$response = (object)[
			'status' => $status,
			'message' => $message,
			'code' => $code,
			'time' => $time,
			'content' => $content
		];

		Log::add('Requête REST exécutée.',
			Log::LEVEL_GOOD, Log::TYPE_QUERY);
			
		Log::add('Résultat de la requête REST : "' . print_r($response, true) . '".',
			Log::LEVEL_INFO, Log::TYPE_QUERY_RESULTS);
		
		$beauty = Configuration::get()->render->api_beautify_json;
		$flags = (!$beauty ? 0 : JSON_PRETTY_PRINT) | JSON_PARTIAL_OUTPUT_ON_ERROR;
		Stream::reset();
		http_response_code($status);
		header('Content-Type: application/json; charset=utf-8');
		echo(json_encode($response, $flags));
		Stream::close();

		exit();
	}


	/**
	 * Vérifie si une route correspond à la route demandée, 
	 * si oui, on exécute la fonction correspondante.
	 * 
	 * @param string $route La route à vérifier.
	 * @param function $callback La fonction à exécuter.
	 * @return void
	 */
	protected function match($route, $callback) {
		if (Router::current() === $route) {
			$callback();
		}
	}


	/**
	 * Retourne un paramètre d'un objet, s'il n'est pas trouvé, 
	 * on renvoie une erreur.
	 * 
	 * @param object $object L'objet de paramètres.
	 * @param string $name Le nom du paramètre.
	 * @param bool $convert Si on doit convertir une valeur vide en NULL.
	 * @param bool $trim Si on doit supprimer les espaces en début et fin de la valeur.
	 * @param bool $filter Si on doit filtrer la valeur contre la vulnérabilité XSS.
	 * @return any La valeur du paramètre.
	 */
	protected function data($object, $name, $convert = false, $trim = true, $filter = true) {
		if (property_exists($object, $name)) {
			$value = $object->$name;
			if ($filter) $value = Xss::filter($value);
			if ($trim) $value = trim($value);
			if ($convert) $value = Encoded::null($value);
			return $value;
		} else {
			$this->send(null, 1, 'Le paramètre "' . $name . '" n\'est pas défini !', 400);
		}
	}
	

	/**
	 * Génère un composant pour l'envoyer.
	 * 
	 * @param string $class La class du composant.
	 * @param array|mixed $args La ou les paramètres du composant.
	 * @return string Le composant généré.
	 */
	protected function generate($class, $args = []) {
		if (Autoloader::typeof($class) === Autoloader::TYPE_CONTROLLER) {
			return Stream::record(function() use ($class, $args) {
				if (is_array($args)) {
					(new \ReflectionClass($class))
						->newInstanceArgs($args);
				} else {
					new $class($args);
				}
			});
		} else {
			Error::trigger('La classe "' . $class . '" n\'est pas un composant !');
		}
	}
	
}

?>