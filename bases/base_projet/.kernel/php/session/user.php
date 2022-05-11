<?php
namespace Kernel\Session;
use Kernel\Security\Vulnerability\CSRF;
use Kernel\Security\Cookie;
use Kernel\Debug;



/**
 * Librairie gerant la session de l'utilisateur
 */
class User {
    
    /**
     * Creer une session de connexion pour un utilisateur
     * 
     * @param object instance DTO de l'utilisateur a memoriser
     * @param string son jeton
     * @param int le nombre de jours de validite du jeton
     * @return bool si la creation a reussie
     */
    static function login($user, $token = null, $nbdays = 31) {
        if (Token::set($token, $nbdays)) {
            self::set($user);
            Debug::log('Utilisateur connecté : "' . print_r($user, true) . '".', Debug::LEVEL_GOOD);
            return true;
        } else {
            Debug::log('Impossible de créer une session de connexion pour l\'utilisateur !', Debug::LEVEL_ERROR);
            return false;
        }
    }


    /**
     * Detruit une session utilisateur
     * 
     * @return bool si la destruction a reussie
     */
    static function logout() {
        if (Token::remove()) {
            self::remove();
            Debug::log('Utilisateur déconnecté.', Debug::LEVEL_GOOD);
            return true;
        } else {
            Debug::log('Impossible de détruire la session de connexion !', Debug::LEVEL_ERROR);
            return false;
        }
    }

    
    /**
     * Defini une session utilisateur
     * 
     * @param object objet DTO de l'utilisateur a memoriser
     * @return void
     */
	static function set($user) {
		$_SESSION['session_user'] = $user;
	}


    /**
     * Recupere la session utilisateur
     * 
     * @return object instance DTO utilisateur en memoire
     */
	static function get() {
		return $_SESSION['session_user'] ?? null;
	}


    /**
     * Detruit la session utilisateur
     * 
     * @return void
     */
	static function remove() {
        unset($_SESSION['session_user']);
	}

    
    /**
     * Verifi si une session utilisateur existe
     * 
     * @return bool si elle existe
     */
	static function has() {
		return isset($_SESSION['session_user']) && !is_null($_SESSION['session_user']);
	}

}

?>