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
    * Zugriff auf einen Datensatz als Object 
    */


class DataAccessObject {

   protected $_insert;
   protected $_tablename;
   protected $_originaldata;
   protected $_rowdata;
   protected $_fieldlist;
   protected $_uidfield;

   function  __construct($table, $row=array(), $uidfield=null) {
      $this->init($table, $row, $uidfield);
   }
   
   
   private function init($table, $row=array(), $uidfield=null) {
      if($row instanceof DBConnect_Row) {
         $row = $row->get_array();
      }
      
      if($row===null) {
         $row = array();
      }

      $this->_tablename = trim($table);
      $this->_rowdata = $row;
      $this->_originaldata = $row;
      $this->_uidfield = $uidfield;

      $this->_fieldlist = array();
      if(count($this->_rowdata)>0) {
         foreach($this->_rowdata as $fieldname => $data) {
            $this->_fieldlist[] = $fieldname;
         }
      } else {
         $dbfields = Globals::db()->query("DESC `".$this->_tablename."`")->fetch()->all();
         foreach($dbfields as $field) {
            $this->_fieldlist[] = $field->get(0);
            
            if(!is_null($this->_uidfield) && $field->get(3)=="PRI") {
               $this->_uidfield = $field->get(0);
            }
            
         }
      }

      if(is_array($row) && count($row)<1) {
         $this->_insert = true;
      } else {
         $this->_insert = false;
      }
   }
   
   
   public static function now() {
      return date("Y-m-d H:i:s");
   }
   

   function __isset($name) {
      if(isset($this->_rowdata[$name])) {
         return true;
      } else {
         return false;
      }
   }

   function __set($name, $value) {
      return $this->set($name, $value);
   }

   public function set($name, $value) {
      if(in_array($name, $this->_fieldlist)) {
         if($this->_uidfield===null || $this->_uidfield!==$name) {
            $this->_rowdata[$name] = $value;
            return true;
         } else {
            return false;
         }
      } else {
         return false;
      }
   }

   function __get($name) {
      return $this->get($name);
   }

   public function get($name) {
      if(in_array($name, $this->_fieldlist)) {
         if(isset($this->_rowdata[$name])) {
            return $this->_rowdata[$name];
         } else {
            return null;
         }
      } else {
         return false;
      }
   }
   
   
   
   public function getUid() {
      if(isset($this->_originaldata[$this->_uidfield])) {
         return $this->_originaldata[$this->_uidfield];
      } else {
         return null;
      }
   }



   public function commit() {
      if($this->_insert) {
         $query = "INSERT INTO `".$this->_tablename."` SET ";
      } else {
         $query = "UPDATE `".$this->_tablename."` SET ";
      }

      if($this->_insert===true && in_array("create_date", $this->_fieldlist)) {
         $this->_rowdata['create_date'] = date("Y-m-d H:i:s");
      }

      if($this->_insert===false && in_array("change_date", $this->_fieldlist)) {
         $this->_rowdata['change_date'] = date("Y-m-d H:i:s");
      }

      $escapedata = array();

      $setter = array();
      foreach($this->_rowdata as $field => $value) {
         if($this->_insert===false || ($this->_insert===true && $field!=$this->_uidfield)) {
            if($value!==null) {
               $setter[] = "`".$field."`='%s'";
               $escapedata[] = $value;
            } else {
               $setter[] = "`".$field."`=NULL";
            }
         }
      }

      $wheres = array();
      if($this->_uidfield===null) {
         foreach($this->_originaldata as $field => $value) {
            $wheres[] = "`".$field."`='%s'";
            $escapedata[] = $value;
         }
      } else {
         $wheres[] = "`".$this->_uidfield."`='%s'";
         $escapedata[] = $this->_originaldata[$this->_uidfield];
      }

      $query .= implode(", ", $setter);

      if($this->_insert===false) {
         $query .= " WHERE ".implode(" AND ", $wheres);
      }

      /*var_dump($query);
      echo "\n";
      var_dump($escapedata);*/

      Globals::db()->query($query, $escapedata);
      $insert_id = Globals::db()->get_insert_id();
      
      if($insert_id!==false && $insert_id>0) {
         $this->_insert = false;
         $this->_originaldata[$this->_uidfield] = $insert_id;
         $this->refresh();
         return true;
      } else {
         return false;
      }

   }


   public function refresh($uid=null) {
      if($this->_uidfield!==null) {
         if($uid===null) {
            $uid = $this->_originaldata[$this->_uidfield];
         } else {
            return false;
         }
         
         $query = "SELECT * FROM ".$this->_tablename." WHERE `".$this->_uidfield."`='%s'";
         $result = Globals::db()->query($query, $uid);
         if($result->num_rows()<1) {
            throw new DBConnect_Exception("Result was empty");
         }

         $result = $result->fetch()->next();
         $this->init($this->_tablename, $result, $this->_uidfield);

         return true;
      } else {
         return false;
      }
   }

   public function load($uid) {
      $this->refresh($uid);
   }


}
