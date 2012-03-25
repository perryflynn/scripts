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
     * Exception Klasse zur verarbeitung von SQL Fehlern
     */


class DBConnect_Exception extends Exception {

   private $sql_errorcode;
   private $sql_errormessage;

   public function __construct(DBConnect $db=null, $optionalmessage="", $optionalcode=-1) {
      if($db instanceof dbconnect) {
         $this->sql_errorcode = $db->get_error_code();
         $this->sql_errormessage = $db->get_error_string();
      } else {
         $this->sql_errorcode = false;
         $this->sql_errormessage = false;
      }

      if($this->sql_errorcode!==false && $this->sql_errormessage!==false) {
         $message = "MySQL Error ".$this->sql_errorcode.": ".$this->sql_errormessage;
      } elseif(!empty($optionalmessage)) {
         $message = $optionalmessage;
      } else {
         $message = "Unbekannter Fehler";
      }

      if($this->sql_errorcode!==false && $optionalcode==-1) {
         $code = $this->sql_errorcode;
      } elseif($optionalcode>=0) {
         $code = $optionalcode;
      } else {
         $code=0;
      }

      parent::__construct($message, $code);
   }

}

?>