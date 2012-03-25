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
     * Enthaelt alle Informationen einer SQL Abfrage
     */


class DBConnect_Result {

   private $result;
   private $num_rows;
   private $num_fields;
   private $resource_fetched;

   function  __construct($res) {
      $this->resource_fetched=false;
      if(is_resource($res)) {
         $this->result = $res;
         $this->num_rows = @mysql_num_rows($this->result);
         $this->num_fields = @mysql_num_fields($this->result);
      } else {
         throw new DBConnect_Exception(null, "No valid mysql resource given");
      }
   }

   function  __destruct() {
      if($this->resource_fetched===false) {
         @mysql_free_result($this->result);
      }
   }

   function __toString() {
      echo "Rows: ".$this->num_rows."; Fields: ".$this->num_fields;
   }


   /**
    * Anzahl der Datensaetze
    * @return int
    */
   public function num_rows() {
      return $this->num_rows;
   }


   /**
    * Anzahl der Spalten eines Datensatzes
    * @return int
    */
   public function num_fields() {
      return $this->num_fields;
   }

   /**
    * Liefert das Ergebnis einer Abfrage als Objekt
    * @return dbconnect_rowset
    */
   public function fetch() {
      $this->resource_fetched=true;
      return new dbconnect_rowset($this, $this->result);
   }


}

?>