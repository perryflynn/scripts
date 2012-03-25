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
     * Die Klasse stellt die Verbindung zu einem MySQL Server her.
     * Auch das Logging, sowie das Absetzen von Querys wird hier abgewickelt.
     */


class DBConnect {

   private $connection;
   private $debug;
   private $logging;

   private $last_error;
   private $last_error_number;
   private $last_query;
   private $command_count;

   private $selected_db;

   function __construct() {
      $this->connection=false;
      $this->debug=false;
      $this->logging=array();
      $this->last_error = false;
      $this->last_error_number = false;
      $this->command_count = array();
      $this->selected_db = false;
      $this->last_query = null;

      if(!(function_exists("mysql_connect") && function_exists("mysql_query"))) {
         throw new DBConnect_Exception($this, "MySQL not found! Please check your PHP installation!");
      }
   }

   function  __destruct() {
      $this->disconnect();
   }


   /**
    * Debugging an- oder ausschalten
    * @param bool $b
    */
   function debug($b) {
      $this->debug = ($b===true ? true : false);
   }


   /**
    * Gibt das Log aus, was beim Debugging generiert wird
    * @return string
    */
   public function get_log_as_html() {
      $style_cell="border:1px solid black; font-size:12px;";

      if($this->debug===false) {
         throw new DBConnect_Exception($this, "Debug is not active!");
      } else {
         $r = "<h1 style='margin:0px;'>dbconnect log</h1>";
         foreach($this->command_count as $name => $count) {
            $r .= "<b>".$name.":</b> ".$count."; ";
         }
         $r .=  "<table style='".$style_cell." border-collapse:collapse; width:100%;'>";
            $r .= "<tr>";
               $r .= "<th style='".$style_cell." width:100px;'>Typ</th>";
               $r .= "<th style='".$style_cell." width:300px;'>File</th>";
               $r .= "<th style='".$style_cell."'>Message/Query</th>";
               $r .= "<th style='".$style_cell." width:100px;'>Rows</th>";
               $r .= "<th style='".$style_cell." width:100px;''>Fields</th>";
               $r .= "<th style='".$style_cell." width:100px;''>Aff. Rows</th>";
            $r .= "</tr>";
            foreach($this->logging as $l) {
               $r .= "<tr>";
                  $r .= "<td style='".$style_cell."'>".$l['type']."</td>";
                  $r .= "<td style='".$style_cell."'>".$l['file'].":".$l['file_line']."</td>";
                  $r .= "<td style='".$style_cell."'".($l['type']!="Query" && $l['type']!="Command" ? " colspan='4'" : "").">";
                     $r .= ($l['msg']===false ? $l['command'] : $l['msg']);
                  $r .= "</td>";
                  if($l['type']=="Query" || $l['type']=="Command") {
                     $r .= "<td style='".$style_cell."'>".($l['num_rows']===false ? "<em>false</em>" : $l['num_rows'])."</td>";
                     $r .= "<td style='".$style_cell."'>".($l['num_fields']===false ? "<em>false</em>" : $l['num_fields'])."</td>";
                     $r .= "<td style='".$style_cell."'>".($l['num_affected']===false ? "<em>false</em>" : $l['num_affected'])."</td>";
                  }
               $r .= "</tr>";
               if($l['errno']!=0) {
                  $r .= "<tr>";
                     $r .= "<td style='".$style_cell." color:red;'>Error</td>";
                     $r .= "<td style='".$style_cell." color:red;' colspan='4'>".$l['errno'].": ".$l['error']."</td>";
                  $r .= "</tr>";
               }
            }
         $r .= "</table>";

         return $r;
      }
   }


   /**
    * Gibt das Log aus, was beim Debugging generiert wird
    * @return string
    */
   public function get_log_as_array($fancy=false) {
      if($this->debug===false) {
         throw new DBConnect_Exception($this, "Debug is not active!");
      } else {
         return $this->logging;
      }
   }


   /**
    * Generiert einen neuen Logeintrag und liefert dessen Referenz zurueck
    * @param String $command
    * @return Array
    */
   private function &log_newentry($command=false) {
      if($command!==false) {
         $keyword = strtolower(reset(explode(" ", $command)));
         if(!isset($this->command_count['all'])) $this->command_count['all']=0;
         if(!isset($this->command_count[$keyword])) $this->command_count[$keyword]=0;
         $this->command_count['all']++;
         $this->command_count[$keyword]++;
      }

      try {
         $errstr = $this->get_error_string();
         $errno = $this->get_error_code();
      } catch(DBConnect_Exception $e) {
         $errstr = false;
         $errno = false;
      }

      $backtrace = debug_backtrace();
      $temp = array(
         "type" => false,
         "msg" => false,
         "command" => $command,
         "num_rows" => false,
         "num_fields" => false,
         "num_affected" => false,
         "error" => $errstr,
         "errno" => $errno,
         "file" => $backtrace[2]['file'],
         "file_line" => $backtrace[2]['line'],
         "time" => microtime()
      );
      $this->logging[] = $temp;
      return $this->logging[(count($this->logging)-1)];
   }


   /**
    * Loggt ein SQL Query
    * @param String $query
    * @param Object $result
    */
   private function log_query($query, $result=false) {
      if($this->debug===true) {
         $log =& $this->log_newentry($query);
         $log['type'] = "Query";
         if($result instanceof dbconnect_result) {
            $log['num_rows'] = $result->get_num_rows();
            $log['num_fields'] = $result->get_num_fields();
         }
      }
   }


   /**
    * Loggt einen SQL Command
    * @param String $command
    */
   private function log_command($command) {
      if($this->debug===true) {
         $log =& $this->log_newentry($command);
         $log['type'] = "Command";
         $log['num_affected'] = $this->get_affected_rows();
      }
   }


   /**
    * Zu einem MySQL Server verbinden
    * @param String $username
    * @param String $password
    * @param String $hostname
    * @param int $port
    * @return false
    */
   public function connect($username, $password, $hostname="localhost", $port=3306) {
      if($this->connection===false) {
         $this->connection = @mysql_connect($hostname.":".((int)$port), $username, $password);
         if($this->connection===false) {
            throw new DBConnect_Exception($this, "Could not connect to ".$hostname.":".$port);
         } else {
            return true;
         }
      } else {
         return false;
      }
   }


   /**
    * MySQL Verbindung trennen
    * @return bool
    */
   public function disconnect() {
      if($this->connection!==false) {
         @mysql_close($this->connection);
         $this->connection = false;
         return true;
      } else {
         return false;
      }
   }


   /**
    * Prueft ob eine Verbindung hergestellt ist
    * @return bool
    */
   public function is_connected() {
      if($this->connection!==false && @mysql_get_server_info($this->connection)!==false) {
         return true;
      } else {
         return false;
      }
   }


   /**
    * Falls vorhanden, wird die letzte Fehlermeldung zurueck gegeben
    * @return string, bool
    */
   public function get_error_string() {
      $error = @mysql_error($this->connection);
      if(!empty($error)) {
         return $error;
      } else {
         return "No Error found...";
      }
   }


   /**
    * Falls vorhanden, wird der letzte Error Code zurueck gegeben
    * @return int, bool
    */
   public function get_error_code() {
      $error = @mysql_errno($this->connection);
      return $error;
   }


   /**
    * Informationen ueber den MySQL Host
    * @return String
    */
   public function get_hostinfo() {
      return @mysql_get_host_info($this->connection);
   }


   /**
    * Liefert die Server Version
    * @return String
    */
   public function get_serverversion() {
      $info = @mysql_get_server_info($this->connection);
      if($info!==false && is_string($info)) {
         $info = explode(": ", $info);
         $info = end($info);
         return $info;
      } else {
         return false;
      }
   }


   /**
    * Eine Datenbank auswaehlen
    * @param String $db
    * @return bool
    */
   public function set_db($db) {
      if(@mysql_select_db($db, $this->connection)) {
         $this->selected_db = $db;
         return true;
      } else {
         throw new DBConnect_Exception($this, "Could not set `".$db."` as Database!");
      }
   }


   /**
    * Listet alle verfuegbaren Datenbanken auf
    * @return array, bool
    */
   public function get_db_list() {
      $dbs = @mysql_list_dbs($this->connection);
      $dblist = array();
      if($dbs!==false) {
         while ($row = mysql_fetch_object($dbs)) {
            $dblist[] = $row->Database;
         }
         return $dblist;
      } else {
         return false;
      }
   }


   /**
    * Gibt die aktuell verwendete Datenbank zurueck
    * @return string, bool
    */
   public function get_selected_db() {
      if($this->selected_db!==false) {
         return $this->selected_db;
      } else {
         return false;
      }
   }


   /**
    * Prueft ob eine Datenbank ausgewaehlt ist
    * @return bool
    */
   public function is_db_selected() {
      if($this->selected_db!==false) {
         return true;
      } else {
         return false;
      }
   }


   /**
    * Liefert eine Liste aller Views der aktuellen Datenbank
    * @return String
    */
   public function get_view_list() {
      $views = $this->query("SELECT TABLE_NAME FROM information_schema.VIEWS WHERE TABLE_SCHEMA='".$this->get_selected_db()."'");
      if($views->num_rows()>0) {
         $viewlist = array();
         $views = $views->fetch();
         while($row = $views->next()) {
            $viewlist[] = $row->get(0);
         }
         return $viewlist;
      } else {
         return false;
      }
   }


   /**
    * Listet alle verfuegbaren Tabellen auf
    * @param $except_views Views ausschliessen?
    * @return array, bool
    */
   public function get_table_list($except_views=false) {
      $tbl = @mysql_list_tables($this->selected_db, $this->connection);
      $views = array();
      if($except_views===true) {
         $views = $this->get_view_list();
      }
      if($tbl!==false) {
         while ($row = mysql_fetch_object($tbl)) {
            if($except_views!==true || !in_array($row->{"Tables_in_".$this->selected_db}, $views)) {
               $tbllist[] = $row->{"Tables_in_".$this->selected_db};
            }
         }
         return $tbllist;
      } else {
         return false;
      }
   }


   /**
    * Prueft ob eine Tabelle existiert
    * @param string $table
    * @return bool
    */
   public function table_exists($table) {
      if(in_array($table, $this->get_table_list())) {
         return true;
      } else {
         return false;
      }
   }


   /**
    * Fuehrt ein Query aus
    * Beispiel: $db->query("select * from table where field='%s'", "name");
    * @param String $query
    * @return dbconnect_result
    */
   public function query($query) {
      $args = func_get_args();
      unset($args[0]);

      if(isset($args[1]) && is_array($args[1])) {
         $args = $args[1];
      }

      if(count($args)>0) {
         $args = array_map("mysql_real_escape_string", $args);
         $query = vsprintf($query, $args);
      }
      if($query!==false) {
         $query = trim($query);
         $this->last_query = $query;

         $result = @mysql_query($query, $this->connection);

         if($this->get_error_code()>0) {
            throw new DBConnect_Exception($this, "Error while execute Query");
         }
         if(is_resource($result)) {
            $dbres = new dbconnect_result($result);
            $this->log_query($query, $dbres);
            return $dbres;
         } else {
            $this->log_command($query);
            return $result;
         }
      } else {
         throw new DBConnect_Exception($this, "Error while build query string");
      }
   }
   
   
   public function get_last_query() {
      return $this->last_query;
   }


   /**
    * Escaped alle SQL-Zeichen
    * @param String $string
    * @return String
    */
   public function escape($string) {
      return mysql_real_escape_string($string, $this->connection);
   }
   

   /**
    * Liefert die Anzahl betroffener Datensaetze beim letzten SQL Command zurueck
    * @return int
    */
   public function get_affected_rows() {
      return @mysql_affected_rows($this->connection);
   }

   public function get_insert_id() {
      return @mysql_insert_id($this->connection);
   }

}

?>