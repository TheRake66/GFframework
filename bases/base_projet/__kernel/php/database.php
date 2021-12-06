<?php
// Librairie DataBase
namespace Kernel;



class DataBase extends \PDO {
    
    /**
     * Instance PDO
     */
    private static $instance;


    /**
     * Retourne l'inctance PDO en cours, si aucune est
     * en cours on en creer une
     */
    static function getInstance() {
        if (!self::$instance) {
            self::$instance = new DataBase();
        }
        return self::$instance;
    }
    
    
    /**
     * Creer une instance PDO
     */
    function __construct() {
        try {
            $param = json_decode(file_get_contents('modele/database.json'));
            $dsn = $param->type . 
                ':host=' . $param->hote . 
                ';port=' . $param->port . 
                ';dbname=' . $param->baseDeDonnees . 
                ';charset=' . $param->encodage;
            parent::__construct(
                $dsn, 
                $param->identifiant, 
                $param->motDePasse);
        } catch (\Exception $e) {
            throw new \Exception('Impossible de se connecter à la base de données, message : "' . $e->getMessage() . '".');
            die;
        }
    }

    
    /**
     * Prepare et retourne une requete
     * 
     * @param string requete sql
     * @param array liste des parametres
     * @return object requete preparee
     */
    static function send($sql) {
        $rqt = self::getInstance()->prepare($sql);
        return $rqt;
    }

    
    /**
     * Execture une requete de mise a jour
     * 
     * @param string requete sql
     * @param array liste des parametres
     * @return bool si la requete a reussite
     */
    static function execute($sql, $params = []) {
        $rqt = self::send($sql);
        return $rqt->execute($params);
    }

    
    /**
     * Retourne une valeur
     * 
     * @param string requete sql
     * @param array liste des parametres
     * @return object valeur de la base
     */
    static function fetchCell($sql, $params = []) {
        $rqt = self::send($sql);
        $rqt->execute($params);
        return $rqt->fetch()[0];
    }

    
    /**
     * Retourne une ligne
     * 
     * @param string requete sql
     * @param array liste des parametres
     * @return array ligne de la base
     */
    static function fetchRow($sql, $params = []) {
        $rqt = self::send($sql);
        $rqt->execute($params);
        return $rqt->fetch();
    }

    
    /**
     * Retourne plusieurs lignes
     * 
     * @param string requete sql
     * @param array liste des parametres
     * @return array les lignes de la base
     */
    static function fetchAll($sql, $params = []) {
        $rqt = self::send($sql);
        $rqt->execute($params);
        return $rqt->fetchAll();
    }

    
    /**
     * Recupere une ligne et l'hydrate dans un objet
     * 
     * @param string requete sql
     * @param object type d'objet a retourne
     * @param array liste des parametres
     * @return object objet hydrate
     */
    static function fetchObjet($sql, $type, $params = []) {
        $rep = self::fetchRow($sql, $params);
        if (!is_null($rep) && !empty($rep)) {
            $obj = new $type();
            $obj->hydrate($rep);
            return $obj;
        }
    }

    
    /**
     * Recupere plusieurs lignes et les hydrate dans une liste d'objet
     * 
     * @param string requete sql
     * @param object type d'objet a retourne
     * @param array liste des parametres
     * @return array liste d'objets hydrate
     */
    static function fetchObjets($sql, $type, $params = []) {
        $rep = self::fetchAll($sql, $params);
        if (!is_null($rep) && !empty($rep)) {
            $arr = [];
			foreach ($rep as $r) {
				$obj = new $type();
				$obj->hydrate($r);
				$arr[] = $obj;
			}
            return $arr;
        }
    }

}

?>