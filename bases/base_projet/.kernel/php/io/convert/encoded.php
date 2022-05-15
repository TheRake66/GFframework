<?php
namespace Kernel\IO\Convert;



/**
 * Librairie de conversion de chaines de caracteres
 *
 * @author Thibault Bustos (TheRake66)
 * @version 1.0
 * @package Kernel\IO\Convert
 * @category Framework source
 * @license MIT License
 * @copyright © 2022 - Thibault BUSTOS (TheRake66)
 */
class Encoded {

	/**
	 * Coupe une chaine de caractere si elle est trop longue
	 * 
	 * @example cutTooLong('Lorem ipsum dolor sit amet', 10) => Lorem ipsum ...
	 * @example cutTooLong('Lorem', 10) => Lorem
	 * @param string la chaine a verifier
	 * @param int la taille max a couper
	 * @return string la chaine coupe ou non
	 */
	static function cutTooLong($text, $size = 50) {
		if (strlen($text) > $size) {
			return substr($text, 0, $size) . '...';
		} else {
			return $text;
		}
	} 


	/**
	 * Retourne un tiret si la valeur est vide
	 * 
	 * @example emptyToHyphen('Lorem ipsum dolor sit amet') => Lorem ipsum
	 * @example emptyToHyphen('') => -
	 * @param mixed la valeur
	 * @return string|mixed la valeur ou un tiret
	 */
	static function emptyToHyphen($value) {
		return empty($value) ? '-' : $value;
	}
	

    /**
     * Genere un une chaine de caractere aleatoire
     * 
	 * @example randomString(10) => 'a1b2c3d4e5f6g7h8i9j0'
	 * @example randomString(10, 'ABCD') => 'ADCBADBCDA'
     * @param int taille de la chaine
     * @param string le jeu de caracteres
     * @return string la chaine aleatoire
     */
	static function randomString($size = 32, $charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789') {
		$str = '';
		$max = strlen($charset) - 1;
		for ($i = 0; $i < $size; $i++) {
		   $str .= $charset[rand(0, $max)];
		}
		return $str;
	}


    /**
     * Retourne null si la valeur est vide, sinon retourne la valeur
     * 
	 * @example nullIfEmpty('Lorem ipsum dolor sit amet') => 'Lorem ipsum dolor sit amet'
	 * @example nullIfEmpty('') => null
     * @param mixed la valeur a verifier
     * @return mixed null ou la valeur
     */
    static function nullIfEmpty($value) {
        return empty($value) ? null : $value;
    }

}
