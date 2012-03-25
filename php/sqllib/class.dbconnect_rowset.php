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
     * Enthaelt alle Datensaetze einer SQL Abfrage
     */


class DBConnect_Rowset {

   private $result;
   private $rowset;
   private $rowset_pointer;

   function __construct(DBConnect_Result $result, $rowset) {
      $this->result = $result;
      if(is_resource($rowset)) {
         $this->rowset = $rowset;
         $this->rowset_pointer = 0;
      } else {
         throw new DBConnect_Exception(null, "No valid mysql resource given");
      }
   }

   function  __destruct() {
      @mysql_free_result($this->rowset);
   }

   function __toString() {
      return ((string)$this->rowset);
   }



   /**
    * Prueft ob noch ein Datensatz 
    * @return bool
    */
   public function has_previous() {
      if($this->rowset_pointer>0) {
         return true;
      } else {
         return false;
      }
   }


   /**
    * Prueft ob noch ein weiterer Datensatz vorhanden ist
    * @return bool
    */
   public function has_next() {
      if($this->rowset_pointer<($this->result->num_rows())) {
         return true;
      } else {
         return false;
      }
   }


   /**
    * Setzt den Row-Pointer im MySQL Result
    * @param int $i Position
    * @return bool
    */
   public function set_result_pointer($i=0) {
      if(@mysql_data_seek($this->rowset, $i)===true) {
         return true;
      } else {
         throw new DBConnect_Exception(null, "Pointer out of bounds");
      }
   }


   /**
    * Liefert einen Datensatz als Array
    * @param int $position
    * @param int $type MYSQL_NUM=>numerisches Array, MYSQL_ASSOC=>assoziatives Array, MYSQL_BOTH=>beides
    * @return array
    */
   private function _row($position, $type=MYSQL_BOTH) {
      if(!($type==MYSQL_BOTH || $type==MYSQL_ASSOC || $type==MYSQL_NUM)) {
         throw new DBConnect_Exception(null, "No valid type given");
      }
      if($position<0) {
         $position=$this->rowset_pointer;
      }
      try {
         $this->set_result_pointer(((int)$position));
      } catch(DBConnect_Exception $e) {
         throw $e;
      }
      $result = null;
      switch($type) {
         case MYSQL_NUM: $result =  @mysql_fetch_array($this->rowset, MYSQL_NUM); break;
         case MYSQL_ASSOC: $result =  @mysql_fetch_array($this->rowset, MYSQL_ASSOC); break;
         case MYSQL_BOTH: default: $result =  @mysql_fetch_array($this->rowset, MYSQL_BOTH); break;
      }
      return $result;
   }


   /**
    * Liefert einen Datensatz als Array
    * @param int $position
    * @param int $type MYSQL_NUM=>numerisches Array, MYSQL_ASSOC=>assoziatives Array, MYSQL_BOTH=>beides
    * @return DBConnect_Row
    */
   public function row($position, $type=MYSQL_BOTH) {
      try {
         return new DBConnect_Row($this->_row($position, $type));
      } catch(DBConnect_Exception $e) {
         throw $e;
      }
   }


   /**
    * Setzt den Rowcount zurueck und gibt den vorherigen Datensatz zurueck
    * @return DBConnect_Row
    */
   public function previous() {
      try {
         $this->rowset_pointer--;
         return $this->row($this->rowset_pointer, MYSQL_ASSOC);
      } catch(DBConnect_Exception $e) {
         throw $e;
      }
   }


   /**
    * Gibt den aktuellen Datensatz zurueck
    * @return DBConnect_Row
    */
   public function current() {
      try {
         return $this->row($this->rowset_pointer, MYSQL_ASSOC);
      } catch(DBConnect_Exception $e) {
         throw $e;
      }
   }


   /**
    * Gibt den naechsten Datensatz zureuck
    * @return DBConnect_Row
    */
   public function next() {
      try {
         if($this->has_next()) {
            $result = $this->row($this->rowset_pointer, MYSQL_ASSOC);
            $this->rowset_pointer++;
            return $result;
         } else {
            return false;
         }
      } catch(DBConnect_Exception $e) {
         throw $e;
      }
   }


   /**
    * Liefert ein zweidimensionales Array aller Datensaetze
    * @param int $type MYSQL_NUM=>numerisches Array, MYSQL_ASSOC=>assoziatives Array, MYSQL_BOTH=>beides
    * @return array
    */
   public function all($type=MYSQL_BOTH) {
      if(!($type==MYSQL_BOTH || $type==MYSQL_ASSOC || $type==MYSQL_NUM)) {
         throw new DBConnect_Exception(null, "No valid type given");
      }
      try {
         $this->set_result_pointer();
      } catch(DBConnect_Exception $e) {
         throw $e;
      }

      $return = array();

      while($row = $this->next()) {
         $return[] = $row;
      }
      return $return;
   }



}

?>