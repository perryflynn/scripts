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
    * Beispielaufruf:
    * 
    * $names = array("", "a", "b", "c", "d", "e");
    * $tbl = new asciitable($names);
    * 
    * $tabledata = array(
    *    array("1", "1a", "1b", "1c", "1d", "1e", ),
    *     array("2", "2a", "2b", "2c", "2d", "2e", ),
    *     array("3", "3a", "3b", "3c", "3d", "3e", ),
    *     array("4", "4a", "4b", "4c", "4d", "4e", ),
    *     array("5", "5a", "5b", "5c", "5d", "5e", ),
    * );
    * 
    * echo "<pre>".$tbl->generate($tabledata)."</pre>";
    */
 
   /**
    * Ausgabe:
    * 
    * +---+----+----+----+----+----+
    * |   | a  | b  | c  | d  | e  |
    * +---+----+----+----+----+----+
    * | 1 | 1a | 1b | 1c | 1d | 1e |
    * | 2 | 2a | 2b | 2c | 2d | 2e |
    * | 3 | 3a | 3b | 3c | 3d | 3e |
    * | 4 | 4a | 4b | 4c | 4d | 4e |
    * | 5 | 5a | 5b | 5c | 5d | 5e |
    * +---+----+----+----+----+----+
    */
 
class asciitable {
 
   /**
    * Anzahl der Spalten
    * @var int
    */
   private $numcols;
   
   /**
    * Die Titel der Spalten
    * @var Array
    */
   private $columnames;
 
 
   /**
    * Konstruktor
    * @param Array $columnames 
    */
   function __construct($columnames) {
      if(is_array($columnames) && count($columnames)>0) {
         $this->columnames = array();
         foreach($columnames as $col) {
            $this->columnames[] = $col;
         }
         $this->numcols = count($this->columnames);
      } else {
         trigger_error("Kein gueltiges Array uebergeben!", E_USER_ERROR);
      }
   }
 
 
   /**
    * Wandelt verschiedenste Datentypen in einen String um
    * @param mixed $string
    * @return String
    */
   private function asString($string) {
      if($string===true) {
         return "true";
      } elseif($string===false) {
         return "false";
      } elseif($string===null) {
         return "null";
      } elseif(is_array($string)) {
         return "Array";
      } elseif(is_object($string)) {
         return "Object";
      } else {
         return (string)$string;
      }
   }
 
 
   /**
    * Bereitet das Datenarray fuer die Tabelle auf
    * @param Array $tabledata
    * @param Array $colwith 
    */
   private function prepareDataArray(&$tabledata, &$colwith) {
      $newdata = array();
      $colwith = array();
 
      foreach($this->columnames as $col) {
         $colwith[] = strlen($col);
      }
 
      //--> Spaltenbreite berechnen
      $i=0;
      foreach($tabledata as $row) {
         $j=0;
         $newdata[$i] = array();
         foreach($row as $col) {
            if(strlen($col)>$colwith[$j]) {
               $colwith[$j] = strlen($col);
            }
            $j++;
         }
         $i++;
      }
 
      //--> Spaltendaten aufbereiten
      $i=0;
      foreach($tabledata as $row) {
         $j=0;
         $newdata[$i] = array();
         foreach($row as $col) {
            $col = $this->asString($col);
            $newdata[$i][$j] = $col.str_repeat(" ", ($colwith[$j]-strlen($col)));
            $j++;
         }
         while($j<$this->numcols) {
            $newdata[$i][$j] = str_repeat(" ", $colwith[$j]);
            $j++;
         }
         $i++;
      }
 
      $tabledata = $newdata;
   }
 
 
   /**
    * Liefert die Horizontale Linie fuer die Tabelle
    * @param Array $colwith
    * @return String
    */
   private function getHorizontalLine($colwith) {
      $cols = array();
      foreach($colwith as $w) {
         $cols[] = str_repeat("-", $w);
      }
 
      return "+-".implode("-+-", $cols)."-+";
   }
 
 
   /**
    * Erzeugt die Spaltentitel
    * @param Array $colwith
    * @return String
    */
   private function getHeaderLine($colwith) {
      $newdata = array();
      $j=0;
      foreach($this->columnames as $col) {
         $col = $this->asString($col);
         $newdata[] = $col.str_repeat(" ", ($colwith[$j]-strlen($col)));
         $j++;
      }
 
      return "| ".implode(" | ", $newdata)." |";
   }
 
 
   /**
    * Generiert die Tabelle
    * @param Array $tabledata
    * @return String 
    */
   public function generate($tabledata) {
      $table="";
      $colwidth = array();
      $this->prepareDataArray($tabledata, $colwith);
 
      $table .= $this->getHorizontalLine($colwith)."\n";
      $table .= $this->getHeaderLine($colwith)."\n";
      $table .= $this->getHorizontalLine($colwith)."\n";
 
      foreach($tabledata as $row) {
         $table .= "| ".implode(" | ", $row)." |\n";
      }
 
      $table .= $this->getHorizontalLine($colwith)."\n";
 
      return $table;
   }
 
 
}
