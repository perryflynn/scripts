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
    * Demoscript
    */


include("class.dbconnect.php");
include("class.dataaccessobject.php");

$db = new dbconnect();

try {
    $db->connect("root", "toor", "mysqlhost");
} catch(DBConnect_Exception $e) {
    die("Connect failed. ".$e->getMessage());
}


try {
    $result = $db->query("SELECT * FROM table");
    
    if($result->num_rows()>0) {

        $resultset = $result->fetch();
        
        while($row = $resultset->next()) {
            
            // Daten auslesen
            echo "Column 0: ".$row->get(0)."\n";
            echo "Named Column: ".$row->get("Namedcolumn")."\n\n";
            $columnarray = $row->get_array();
            var_dump($columnarray);
            echo "\n\n\n";
            
            // Daten veraendern
            $dao = new DataAccessObject("table", $row, "uid");
            $dao->somecolumn = "foobar";
            $dao->set("somesecoundcolumn", "barfoo");
            
            try {
                $dao->commit();
            } catch(DBConnect_Exception $e2) {
                die("Query Error: ".$e2->getMessage());
            }
            
        }
        
    } else {
        echo "Result empty";
    }
    
} catch(DBConnect_Exception $e) {
    die("Query Error: ".$e->getMessage());
}

