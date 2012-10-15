<?php
 
   /**
    * LICENSE
    * This work is licensed under a
    * Creative Commons Attribution-NonCommercial-ShareAlike 3.0 Unported License
    *
    * @copyright  Copyright (c) 2012 Christian Blechert (http://fiae.ws)
    * @license    http://creativecommons.org/licenses/by-nc-sa/3.0/
    * @author     Christian Blechert (christian@blechert.name)
    * @version    1.0.0
    * @link       http://gitweb.fiae.ws/scripts.git/tree/HEAD:/linux
    **/

   die("delete my line");

   $config = array(
      "directory" => dirname(__FILE__)."/", // The dir of this script, replace this with a another dir or whatever
      "maxFileAge" => "7d", // Examples: 7d, 24h, 30m, 120s
      "filemask" => "*.tar.gz" 
   );
   
   
   
   /**
    * Delete dir recursive
    * @param string $file 
    */
   function unlink_recursive($file) {
      if(file_exists($file)) {
         if(is_dir($file)) {
            
            $files = scandir($file);
            foreach($files as $cfile) {
               unlink_recursive($cfile);
            }
            
         } elseif(is_file($file)) {
            
            unlink($file);
            
         }
      }
   }
   
   
   //--> Check Dir
   if(!(file_exists($config['directory']) && is_dir($config['directory']))) {
      echo("Directory not found!");
      exit(1);
   }
   
   
   //--> Check max file age
   $result = preg_match('/^([0-9]{1,})([dDhHmMsS]{1})$/', $config['maxFileAge'], $match);
   if($result!==1) {
      echo "maxFileAge not correct!";
      exit(1);
   }
   
   
   //--> Calulate max age in secounds
   $age = null;
   $unit = strtolower($match[2]);
   $value = ((int)$match[1]);
   switch($unit) {
      case "d":
         $age = $value*24*60*60;
         break;
      case "h":
         $age = $value*60*60;
         break;
      case "m":
         $age = $value*60;
         break;
      case "s":
         $age = $value;
         break;
      default:
         echo "Unknown age unit!";
         exit(1);
         break;
   }
   
   $maxage = time()-$age;
   
   
   //--> Get files
   $files = glob($config['directory']."/".$config['filemask']);
   if(is_array($files) && count($files)>0) {
      
      // Iterate files
      $deleted_files = array();
      foreach($files as $file) {
         if(basename($file)=="." || basename($file)==".." || basename($file)==basename(__FILE__)) continue;
         
         // Check age
         $mtime = filemtime($file);
         if($maxage>$mtime) {
            $deleted_files[] = $file;
            unlink_recursive($file);
         }
         
      }
      
      echo "<pre>";
         echo count($deleted_files)." files deleted\n\n";
         foreach($deleted_files as $file) {
            echo "- ".$file."\n";
         }
      echo "</pre>";
      exit(0);
      
   } else {
      echo "No files found.";
      exit(1);
   }
   