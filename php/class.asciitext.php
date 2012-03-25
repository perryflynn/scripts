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
    * Erzeugt Ascii Art Text
    */

   /*
         _  _ _  _  _  __    ___                _  _  _  _  _____                  __
     /\ |_)/ | \|_ |_ /__ |_| |   ||/| |\/||\ |/ \|_)/ \|_)(_  | | |\  /\    /\/\_/ /
    /--\|_)\_|_/|_ |  \_| | |_|_\_||\|_|  || \|\_/|  \_X| \__) | |_| \/  \/\/ /\ | /_

   */
 
   class asciitext_mini extends asciitext {
 
      function __construct() {
         $this->type = 'upperchar';
 
         $this->charlist[' '][0] = '   ';
         $this->charlist[' '][1] = '   ';
         $this->charlist[' '][2] = '   ';
         $this->charlist['A'][0] = '    ';
         $this->charlist['A'][1] = ' /\ ';
         $this->charlist['A'][2] = '/--\\';
         $this->charlist['Ä'][0] = '.  .';
         $this->charlist['Ä'][1] = ' /\ ';
         $this->charlist['Ä'][2] = '/--\\';
         $this->charlist['B'][0] = ' _ ';
         $this->charlist['B'][1] = '|_)';
         $this->charlist['B'][2] = '|_)';
         $this->charlist['C'][0] = ' _';
         $this->charlist['C'][1] = '/ ';
         $this->charlist['C'][2] = '\_';
         $this->charlist['D'][0] = ' _ ';
         $this->charlist['D'][1] = '| \\';
         $this->charlist['D'][2] = '|_/';
         $this->charlist['E'][0] = ' _';
         $this->charlist['E'][1] = '|_';
         $this->charlist['E'][2] = '|_';
         $this->charlist['F'][0] = ' _';
         $this->charlist['F'][1] = '|_';
         $this->charlist['F'][2] = '| ';
         $this->charlist['G'][0] = ' __';
         $this->charlist['G'][1] = '/__';
         $this->charlist['G'][2] = '\_|';
         $this->charlist['H'][0] = '   ';
         $this->charlist['H'][1] = '|_|';
         $this->charlist['H'][2] = '| |';
         $this->charlist['I'][0] = '___';
         $this->charlist['I'][1] = ' | ';
         $this->charlist['I'][2] = '_|_';
         $this->charlist['J'][0] = '   ';
         $this->charlist['J'][1] = '  | ';
         $this->charlist['J'][2] = '\_|';
         $this->charlist['K'][0] = '  ';
         $this->charlist['K'][1] = '|/';
         $this->charlist['K'][2] = '|\\';
         $this->charlist['L'][0] = '  ';
         $this->charlist['L'][1] = '| ';
         $this->charlist['L'][2] = '|_';
         $this->charlist['M'][0] = '    ';
         $this->charlist['M'][1] = '|\/|';
         $this->charlist['M'][2] = '|  |';
         $this->charlist['N'][0] = '    ';
         $this->charlist['N'][1] = '|\ |';
         $this->charlist['N'][2] = '| \|';
         $this->charlist['O'][0] = ' _ ';
         $this->charlist['O'][1] = '/ \\';
         $this->charlist['O'][2] = '\_/';
         $this->charlist['Ö'][0] = '&middot;_&middot;';
         $this->charlist['Ö'][1] = '/ \\';
         $this->charlist['Ö'][2] = '\_/';
         $this->charlist['P'][0] = ' _ ';
         $this->charlist['P'][1] = '|_)';
         $this->charlist['P'][2] = '|  ';
         $this->charlist['Q'][0] = ' _ ';
         $this->charlist['Q'][1] = '/ \\';
         $this->charlist['Q'][2] = '\_X';
         $this->charlist['R'][0] = ' _ ';
         $this->charlist['R'][1] = '|_)';
         $this->charlist['R'][2] = '| \\';
         $this->charlist['S'][0] = ' _ ';
         $this->charlist['S'][1] = '(_ ';
         $this->charlist['S'][2] = '__)';
         $this->charlist['T'][0] = '___';
         $this->charlist['T'][1] = ' | ';
         $this->charlist['T'][2] = ' | ';
         $this->charlist['U'][0] = '   ';
         $this->charlist['U'][1] = '| |';
         $this->charlist['U'][2] = '|_|';
         $this->charlist['Ü'][0] = '. .';
         $this->charlist['Ü'][1] = '| |';
         $this->charlist['Ü'][2] = '|_|';
         $this->charlist['V'][0] = '    ';
         $this->charlist['V'][1] = '\  /';
         $this->charlist['V'][2] = ' \/';
         $this->charlist['W'][0] = '      ';
         $this->charlist['W'][1] = '\    /';
         $this->charlist['W'][2] = ' \/\/ ';
         $this->charlist['X'][0] = '  ';
         $this->charlist['X'][1] = '\/';
         $this->charlist['X'][2] = '/\\';
         $this->charlist['Y'][0] = '   ';
         $this->charlist['Y'][1] = '\_/';
         $this->charlist['Y'][2] = ' | ';
         $this->charlist['Z'][0] = '__';
         $this->charlist['Z'][1] = ' /';
         $this->charlist['Z'][2] = '/_';
      }
 
   }
 
?><?php
 
   abstract class asciitext {
 
      protected $charlist;
      protected $type = "both";
 
      private function get_char($char) {
         if($this->type=="upperchar") {
            $char = strtoupper($char);
         } elseif($this->type=="lowerchar") {
            $char = strtolower($char);
         }
 
         if(isset($this->charlist[$char])) {
            return $this->charlist[$char];
         } else {
            return false;
         }
      }
 
      public function get($text) {
         if(!empty($text)) {
            $asciitext = array();
            for($i=0; $i<strlen($text); $i++) {
               $ascii = $this->get_char(substr($text,$i, 1));
               if($ascii===false) {
                  $ascii = $this->get_char(substr($text,$i, 2));
                  if($ascii!==false) $i++;
               }
               if($ascii!==false) {
                  for($j=0; $j<count($ascii); $j++) {
                     if(!isset($asciitext[$j])) $asciitext[$j] = "";
                     $asciitext[$j] .= $ascii[$j]." ";
                  }
               }
            }
 
            return "<pre>".implode("\n", $asciitext)."</pre>";
         } else {
            return false;
         }
      }
 
   }
   
   