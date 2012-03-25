<?php

   /**
    * Yet another sqllib
    *
    * LICENSE
    * This work is licensed under a
    * Creative Commons Attribution-NonCommercial-ShareAlike 3.0 Unported License
    *
    * @copyright  Copyright (c) 2012 Christian Blechert (www.blechert.name)
    * @license    http://creativecommons.org/licenses/by-nc-sa/3.0/
    * @author     Christian Blechert (christian@blechert.name)
    * @version    1.0.0
    */

    /**
     * Enthaelt einen Datensatz
     */


class DBConnect_Row {

   private $row;

   function __construct($row) {
      $this->row = $row;
   }

   function __get($name) {
      if($this->get($name)!==null) {
         return $this->get($name);
      } else {
         return null;
      }
   }

   function __toString() {
      return implode(", ", $this->row);
   }

   /**
    * Liefert ein Feld des Datensatzes anhand des Spaltennamens
    * @param String $name
    * @return String
    */
   public function get($name) {
      if(is_string($name)) {
         if(in_array($name, array_keys($this->row))) {
            return $this->row[$name];
         } else {
            return null;
         }
      } elseif(is_int($name) && $name>=0) {
         $keys = array_keys($this->row);
         if(isset($keys[$name]) && isset($this->row[$keys[$name]])) {
            return $this->row[$keys[$name]];
         } else {
            return null;
         }
      } else {
         return null;
      }
   }

   /**
    * Liefert den Datensatz als Array
    * @return array
    */
   public function get_array() {
      return $this->row;
   }

}

?>