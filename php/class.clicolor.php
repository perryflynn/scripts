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
    * Farbige Ausgaben fuer CLI PHP Scripte
    */
 
class clicolor {
 
   static private $fg_colors = array(
         "black" => "30",
         "red" => "31",
         "green" => "32",
         "yellow" => "33",
         "blue" => "34",
         "purple" => "35",
         "cyan" => "36",
         "white" => "37"
      );
 
   static private $bg_colors = array(
         "black" => "40",
         "red" => "41",
         "green" => "42",
         "yellow" => "43",
         "blue" => "44",
         "purple" => "45",
         "cyan" => "46",
         "white" => "47"
      );
   
   public static function normal($color, $text, $bgcolor=null) {
      return "\033[0;".self::$fg_colors[$color].(!is_null($bgcolor) ? ";".self::$bg_colors[$bgcolor] : "")."m".$text."\033[0m";
   }
 
   public static function bold($color, $text, $bgcolor=null) {
      return "\033[1;".self::$fg_colors[$color].(!is_null($bgcolor) ? ";".self::$bg_colors[$bgcolor] : "")."m".$text."\033[0m";
   }
 
   public static function underline($color, $text, $bgcolor=null) {
      return "\033[4;".self::$fg_colors[$color].(!is_null($bgcolor) ? ";".self::$bg_colors[$bgcolor] : "")."m".$text."\033[0m";
   }
 
}
 
