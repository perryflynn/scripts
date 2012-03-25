<?php

   /**
    * Simples Upload Script fuer Bilder
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
     * Beispielaufruf:
     * curl -F userfile=foo.png -F upload=true "http://somehost.de/somefolder/upload.php?secret=somesecret"
     */

define("SECRET_KEY", "79df364ddae7bd82f2579663dbfssa23"); // <-- somesecret als md5 hash
define("DIR", dirname(__FILE__)."/");

//--> Check Secret
$secret = (isset($_GET['secret']) ? $_GET['secret'] : "");
if(md5($secret)!=SECRET_KEY) die("Permission denied");

//--> Upload?
if(isset($_POST['upload']) && isset($_FILES['userfile'])) {
    
    //--> Is Image?
    $imagecheck = @getimagesize($_FILES['userfile']['tmp_name']);
    if(is_array($imagecheck) && count($imagecheck)>1 && $imagecheck[0]>0 && $imagecheck[1]>0) {
        
        //--> File exists?
        $name = $_FILES['userfile']['name'];
        $target = DIR.preg_replace("/[^A-Za-z0-9\-\.\_]/", "_", $name);
        
        if(!(file_exists($target) && is_file($target))) {
        
            //--> Move
            if (move_uploaded_file($_FILES['userfile']['tmp_name'], $target)) {
                die("success");
            } else {
                die("moveerror");
            }
            
        } else {
            die("fileexistserror");
        }
        
    } else {
        die("noimageerror");
    }
    
}

?>
<form enctype="multipart/form-data" method="POST" action="">
    <input name="userfile" type="file" />
    <input type="submit" name="upload" value="Send File" />
</form>