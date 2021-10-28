<?php

namespace Modele\dao{NAMESPACE_SLASH};
use Librairie\DataBase;
use Modele\dto{NAMESPACE_SLASH}\{NAME_UPPER} as dto;



class {NAME_UPPER} {

    /**
     * Recupere un objet {NAME_UPPER} via son id
     * 
     * @param string l'id
     * @return {NAME_UPPER} le {NAME_LOWER}
     */
    public static function {NAME_LOWER} ($id) {
        $rqt = DataBase::getInstance()->prepare(
			"SELECT * 
			FROM {NAME_LOWER} 
			WHERE id{NAME_UPPER} = :id");
        $rqt->bindParam(":id", $id);
        $rqt->execute();
        
        $liste = $rqt->fetch();

        if (!empty($liste)) {
            $obj = new dto();
            $obj->hydrate($liste);
            return $obj;
        }
    }

}

?>