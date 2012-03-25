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
    * Mit den beiden Funktionen kann man Zahlen in ein größeres Zahlensystem umwandeln. So wird aus 999 beispielsweise C7.
    * Dieses Verfahren kennt man zB von Youtube (http://www.youtube.com/watch?v=XIQ2lXlgAMk) um lange IDs kürzer zu machen.
    */
 
    function dec2char($dez) {
        $dez = (int)$dez;
        $char = "";

        $chars = "0123456789AaBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz";
        $num_chars = strlen($chars);

        if(!($dez<1)) {
        while($dez>0) {
            $rest = $dez%$num_chars;
            $temp = $chars{$rest};
            if($temp=="0" && $char!="") $temp="";
            $char = $temp.$char;
            $dez /= $num_chars;
        }
        } else {
        return substr($chars, 0, 1);
        }

        return $char;
    }

    function char2dec($char) {
        $chars = "0123456789AaBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz";
        $numchars = strlen($chars);
        $dez=0;
        $num = strlen(trim($char))-1;
        while($num>=0) {
        $temp = strpos($chars, $char{((strlen($char)-1)-$num)});
        $dez += ($temp*pow($numchars,$num));
        $num--;
        }
        return $dez;
    }
      
      