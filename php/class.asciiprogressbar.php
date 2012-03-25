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
    * Erzeugt eine Progressbar wie man sie von wget kennt
    */
 

   class asciiprogressbar {
 
      private $width;
 
      function __construct($width) {
         $this->width = (int)$width;
      }
 
      public function get($percent) {
         $size = ((int)ceil($this->width/100*$percent));
         $spaces = (int)($this->width-$size);
         if($spaces>0) $spaces--; else $size--;
         $text = "[";
            if($size>0) $text .= str_repeat("=", ($spaces>0 ? ($size-1) : $size));
            if($spaces>0 && $size>0) $text.="&gt;";
            if($spaces>0) $text .= str_repeat("&nbsp;", $spaces);
         $text .= "] ".$percent."%";
         return $text;
      }
 
   }
   
   