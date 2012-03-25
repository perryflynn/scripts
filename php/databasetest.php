<?php

   /**
   // (c) by Christian Blechert (http://anwendungsentwickler.ws)
   // Created: 2011-10-24
   // License: http://creativecommons.org/licenses/by/3.0/

      Testet die Datenbankverbindung um Probleme beim Einrichten von
      Blogs oder CM-Systemen zu analysieren

      Einfach die Zugangsdaten in die Variablen eintragen und via Webbrowser ausfuehren
   */


   // --> Hier die Zugangsdaten eintragen

   $mysql_hostname = "hier hostnamen eintragen";
   $mysql_database = "hier datenbanknamen eintragen";
   $mysql_username = "hier benutzernamen eintragen";
   $mysql_password = "hier passwort eintragen";

   //--> AB HIER NICHTS MEHR AENDERN!!!!

   ini_set('error_reporting', E_ALL);
   ini_set('display_errors', 1);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
        "http://www.w3.org/TR/html4/loose.dtd">

<html>
   <head>
      <meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
      <meta http-equiv="Content-Style-Type" content="text/css">
      <title>MySQL Datenbanktester</title>
   </head>
   <body>
      <h1>MySQL Datenbanktester</h1>
      <pre><?php

            echo "Stelle Verbindung zu '".$mysql_hostname."' als Benutzer '".$mysql_username."' her...\n";

            // Verbindung mit Datenbankserver
            $connection = mysql_connect($mysql_hostname, $mysql_username, $mysql_password);
            if($connection!==false) {
               echo "Verbindung erfolgreich!\n\n";
            
               // Datenbank auslesen
               $result = mysql_query("SHOW DATABASES");
               if($result!==false) {
                  echo "Fuer den Benutzer verfuegbare Datenbanken:\n";
                  while($row = mysql_fetch_row($result)) {
                     echo "-&gt; ".$row[0]."\n";
                  }
                  echo "\n";

                  // Datenbank auswaehlen
                  echo "Datenbank auswaehlen...\n";
                  $select_db = mysql_select_db($mysql_database, $connection);
                  if($select_db===true) {
                     echo "Datenbank erfolgreich ausgewaehlt!\n\n";

                     // Tabellen auslesen
                     $result = mysql_query("SHOW TABLES");
                     if($result!==false) {
                        echo "Tabellen in der Datenbank '".$mysql_database."':\n";

                        while($row = mysql_fetch_row($result)) {
                           echo "-&gt; ".$row[0]."\n";
                        }
                        echo "\n\n";
                        echo "Alle Aktionen erfolgreich durchgefuehrt! Funzt alles! :-)\n\n";

                     } else {
                        echo "Fehler beim auslesen der Tabellen in Datenbank '".$mysql_database."'";
                     }
                     
                  } else {
                     echo "Fehler beim auswaehlen der Datenbank '".$mysql_database."'!\n\n";
                  }
               } else {
                  echo "Fehler beim auslesen der Datenbanken!\n\n";
               }
            } else {
               echo "Verbindung fehlgeschlagen!\n\n";
            }

            // MySQL Fehler ausgeben
            echo "<b>Letzte MySQL Fehlernummer:</b> ".mysql_errno()."\n";
            echo "<b>Letzte MySQL Fehlermeldung:</b> ".mysql_error()."\n\n";
            echo "<b>Ausfuehrung beendet.</b>";

         ?>
      </pre>
   </body>
</html>
