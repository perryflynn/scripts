<?php
 
   /**
    * LICENSE
    * This work is licensed under a
    * Creative Commons Attribution-NonCommercial-ShareAlike 3.0 Unported License
    *
    * @copyright  Copyright (c) 2010 Christian Blechert (www.blechert.name)
    * @license    http://creativecommons.org/licenses/by-nc-sa/3.0/
    * @author     Christian Blechert (christian@blechert.name)
    * @version    1.0.0
    **/
 
   /**
    * Die Klasse stellt einen Globalen Speicher fuer Eigenenschaften zur Verfuegung.
    * Aufruf zum Speichern: Globals::einbeliebigername("ein beliebiger wert");
    * Aufruf zum Auslesen: Globals::einbeliebigername();
    * Aufruf zum Loeschen: Globals::__unset("einbeliebigername");
    * 
    * Die Klasse Funktioniert nur mit PHP >= 5.3.0
    * Bei aelteren Versionen ist die Methode __callStatic() nicht implementiert!
    */
 
class Globals {
 
   
   /**
    * Datenspeicher
    * @var array
    */
   private static $_store = array();
 
   
   /**
    * Magische Methode zum Speichern von Eigenschaften
    * @param String $name
    * @param mixed $arguments
    * @return mixed
    */
   public static function __callStatic($name, $arguments) {
      if(isset($arguments[0])) {
         self::$_store[$name] = $arguments[0];
         return self::$_store[$name];
      } elseif(isset(self::$_store[$name])) {
         return self::$_store[$name];
      } else {
         return null;
      }
   }
 
   
   /**
    * Liefert die Namen aller Eigenschaften als String
    * @return String
    */
   public static function __toString() {
      return implode("\n", array_keys(self::$_store));
   }
   
   
   /**
    * Entfernt eine Eigenschaft aus dem Speicher sofern diese vorhanden ist
    * @param String $name 
    */
   public static function __unset($name) {
      if(isset(self::$_store[$name])) {
         unset(self::$_store[$name]);
      }
   }
 
 
}
