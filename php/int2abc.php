<?php

   /**
   // (c) by Christian Blechert (http://anwendungsentwickler.ws)
   // Created: 2011-10-24
   // License: http://creativecommons.org/licenses/by/3.0/

      Wandelt eine Zahl in eine zahlen/buchstaben code um wie man ihn von youtube kennt
   */



   function dec2char($dez) {
      $dez = (int)$dez;
      $char = "";

      $chars = "0123456789AaBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz";
      $num_chars = strlen($chars);

      if(!($dez<1)) {
         while($dez>0) {
            $rest = $dez%$num_chars;
            if($rest>0) {
               $temp = substr($chars, $rest, 1);
               $char = $temp.$char;
            }
            $dez /= $num_chars;
         }
      } else {
         return substr($chars, 0, 1);
      }

      return $char;
   }



?>

<html>
   <head>
      <title>Shortlinker Dez =&gt; Char</title>
   </head>
   <body>

      <?php
         if(isset($_POST['send'])) {
            echo "Eingabe: ".$_POST['int']."<br>";
            echo "Als Integer: ".((int)$_POST['int'])."<br>";
            echo "Als Char: ".dec2char(((int)$_POST['int']));
            echo "<hr>";
         }
      ?>

      <form method="POST" action="">
         <input type="text" name="int" value="<?php echo (isset($_POST['int']) ? (int)$_POST['int'] : ""); ?>">&nbsp;<input type="submit" name="send" value="OK">
      </form>

   </body>
</html>
