<?php
namespace Kernel;
use Kernel\Error;



/**
 * Librairie gerant la securite
 */
class Security {

	/**
	 * Verifie et active le protocole SSL
	 * 
	 * @return void
	 */
	static function enableSsl() {
		if (Configuration::get()->security->redirect_to_https) {
			if($_SERVER['SERVER_PORT'] !== 443 &&
				(empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off')) {
				Url::location('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
			} else {
				Debug::log('SSL actif.');
			}
		}
	}


	/**
     * Defini de maniere securise un cookie
     * 
     * @param string le nom du cookie
     * @param string la valeur du cookie
     * @param string le timestamp correspondant a l'expiration du cookie
	 * @return bool si l'ecriture du cookie a reussie
	 */
	static function setCookie($name, $value = '', $time = 0) {
        $conf = Configuration::get()->security;
		return setcookie(
            self::getRealCookieName($name), 
            $value, 
            $time, 
            $conf->token_path,
            $conf->token_domain,
            $conf->token_only_https,
            $conf->token_prevent_xss
        );
	}


	/**
     * Supprime un cookie
     * 
     * @param string le nom du cookie
	 * @return bool|null si le la suppression a reussie, null si le cookie n'existe pas
	 */
	static function removeCookie($name) {
		$name = self::getRealCookieName($name);
		if (isset($_COOKIE[$name])) {
			unset($_COOKIE[$name]); 
			return setcookie($name, null, -1, '/'); 
		}
	}


	/**
     * Recupere un cookie
     * 
     * @param string le nom du cookie
     * @return mixed la valeur du cookie
	 */
	static function getCookie($name) {
		return $_COOKIE[self::getRealCookieName($name)] ?? null;
	}


	/**
     * Verifie si un cookie existe
     * 
     * @param string le nom du cookie
	 * @return bool si le cookie existe
	 */
	static function hasCookie($name) {
		$name = self::getRealCookieName($name);
		return isset($_COOKIE[$name]);
	}


	/**
     * Recupere le nom complet d'un cookie
     * 
     * @param string le nom du cookie
     * @return string le nom complet du cookie
	 */
	static function getRealCookieName($name) {
		return session_name() . '_' . str_replace(' ', '', $name);
	}
	
	
    /**
     * Genere un jeton aleatoire de taille n
     * 
     * @param int taille du token
     * @param string le jeu de caracteres
     * @return string le token
     */
	static function makeSimpleToken($size = 32, $charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789') {
		$token = '';
		$max = strlen($charset) - 1;
		for ($i = 0; $i < $size; $i++) {
		   $token .= $charset[rand(0, $max)];
		}
		return $token;
	}


	/**
	 * Genere un jeton aleatoire de taille n cryptographiquement securisee
	 * 
	 * @param int taille du token en octets
	 * @return string le token
	 */
	static function makeCSRFToken($size = 32) {
		if (function_exists('random_bytes')) {
			return bin2hex(random_bytes($size));
		} elseif (function_exists('openssl_random_pseudo_bytes')) {
			return bin2hex(openssl_random_pseudo_bytes($size));
		} else {
			return Error::trigger('Version de PHP trop ancienne, veuillez activer l\'extension "openssl" ou utilisez la fonction "makeSimpleToken".');
		}
	}


	/**
	 * Initialise un champ cache pour la protection CSRF
	 * 
	 * @return void
	 */
    static function initCSRF() {
        $token = Security::makeCSRFToken();
        $_SESSION['csrf_token'] = $token;
		Debug::log('Génération d\'un jeton CSRF : "' . $token . '".');
        Html::add(
			Html::createElement('input', [
                'type' => 'hidden',
                'name' => 'csrf_token',
                'value' => $token  
        ]));
    }


	/**
	 * Verifie si le jeton CSRF est valide passe
	 * 
	 * @return bool si le jeton est valide
	 */
	static function checkCSRF() {
		if (isset($_SESSION['csrf_token']) && isset($_POST['csrf_token']) &&
			$_SESSION['csrf_token'] === $_POST['csrf_token']) {
			Debug::log('Le jeton CSRF est valide.');
			return true;
		} else {
			Debug::log('Le jeton CSRF est invalide.', Debug::LEVEL_ERROR);
			http_response_code(405);
			return false;
		}
	}

	
	/**
	 * Detruit le jeton CSRF
	 * 
	 * @return void
	 */
	static function destroyCSRF() {
		unset($_SESSION['csrf_token']);
	}


	/**
	 * Authentifie un utilisateur via un serveur LDAP
	 * 
	 * @param string l'identifiant de l'utilisateur
	 * @param string le mot de passe de l'utilisateur
	 * @param string le dn (distinguished name)
	 * @param string le serveur
	 * @param int le port
	 * @return bool si les identifiants sont bon
	 * @throws Error si l'extension LDAP n'est pas installee
	 */
	static function authLDAP($login, $password, $dn, $host, $port = 389) {
		if (extension_loaded('ldap') && extension_loaded('openssl')) {
			$response = false;
			if ($con = ldap_connect($host, $port)) {
				ldap_set_option($con, LDAP_OPT_PROTOCOL_VERSION, 3);
				ldap_set_option($con, LDAP_OPT_REFERRALS, 0);
				Error::remove();
				$response = ldap_bind($con, $dn . '\\' . $login, $password);
				Error::handler();
				ldap_close($con);
			}
			return $response;
		} else {
			Error::trigger('Les extensions "ldap" et "openssl" ne sont pas activées !');
		}
	}

}

?>