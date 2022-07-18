<?php
namespace Cody\Console;

use Cody\Io\Environnement;
use Cody\Io\Thread;
use Kernel\Communication\Network\Download;



/**
 * Librairie gérant l'explorateur de fichiers.
 *
 * @author Thibault Bustos (TheRake66)
 * @version 1.0
 * @package Cody\Console
 * @category Framework source
 * @license MIT License
 * @copyright © 2022 - Thibault BUSTOS (TheRake66)
 */
abstract class Explorer {

    /**
     * Ouvre le projet dans Visual Studio Code.
     * 
     * @return void
     */
    static function vscode() {
        Output::printLn('Ouverture de Visual Studio Code...'); 
        if (Thread::open('code .')) {
            Output::printLn('Ouverture réussie.');
        } else {
            Output::printLn('Ouverture échouée.');
        }
    }


    /**
     * Ouvre le dossier courant dans l'explorateur de fichiers.
     * 
     * @return void
     */
    static function open() {
        Output::printLn('Ouverture de l\'explorateur de fichiers...');
        Thread::open('start .');
    }


    /**
     * Change le dossier courant par celui du projet.
     * 
     * @return void
     */
    static function root() {
        Output::printLn('Retour au dossier du projet...');
        chdir(Environnement::root());
    }


    /**
     * Change le dossier courant par celui spécifié. 
     * 
     * @param string $dir Le dossier à utiliser.
     * @return void
     */
    static function change($dir) {
        if (is_dir($dir)) {
            chdir($dir);
        } else {
            Output::printLn('Ce dossier n\'existe pas !');
        }
    }


    /**
     * Affiche la liste des fichiers et des dossiers du dossier courant.
     * 
     * @return void
     */
    static function list() {
        $dir = rtrim(getcwd(), '/') . '/*';
        $dirs = glob($dir, GLOB_ONLYDIR);
        $files = glob($dir);
        $alls = array_unique(array_merge($dirs, $files));
        
        $longest = 0;
        foreach ($alls as $element) {
            $base = basename($element);
            $len = strlen($base);
            if ($len > $longest) {
                $longest = $len;
            }
        }
        $longest += 3;

        $count = 0;
        foreach ($alls as $element) {
            $base = basename($element);
            $len = strlen($base);
            $margin = $longest - $len;
            $space = str_repeat(' ', $margin);
            $count += $len + $margin;
            $output = $base . $space;
            $color = is_dir($element) ? 
                (Project::is($element) ?
                    Output::COLOR_FORE_MAGENTA : 
                    Output::COLOR_FORE_BLUE) :
                Output::COLOR_FORE_CYAN;

            Output::print($output, $color);

            if ($count >= Output::MAX_WINDOW_WIDTH) {
                Output::break();
                $count = 0;
            }
        }
    }

    /**
     * Télécharge un fichier depuis l'URL spécifiée.
     * 
     * @param string $url L'URL du fichier à télécharger.
     * @param string $file Le chemin du fichier à télécharger.
     * @return void
     */
    static function download($url, $file) {
        Output::printLn('Téléchargement du fichier...');
        if (Download::get($url, $file)) {
            Output::printLn('Téléchargement réussi !');
        } else {
            Output::printLn('Téléchargement échoué !');
        }
    }
    
}


?>