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

   //--> Bukkit server directory
   $bukkitdir = "/home/minecraft/bukkit/";
   
   //--> Name of screen session (screen -S minecraft java -jar ...)
   $screen_sessionname = "minecraft";
   
   //--> Target directory for tar files
   $tempdir = "/home/minecraft/backup/";
   
   //--> World folder names to backup
   $worldfolders = array(
      "world",
      "cube",
      "survival",
      "world_the_end",
      "world_nether",
       "mc"
   );

   //--> FTP Hostname
   $ftp_hostname = "nclabs01.netcup.net";
   
   //--> FTP Username
   $ftp_username = "";
   
   //--> FTP Password
   $ftp_password = "";
   
   //--> FTP Target Folder
   $ftp_target = "/worldbackups/";

   
//------------------------------------------------------------------------------
   

   //--> Check Bukkit Directory
   if(!(file_exists($bukkitdir) && is_dir($bukkitdir) && is_readable($bukkitdir) && 
      file_exists($bukkitdir."server.properties") && is_file($bukkitdir."server.properties"))) 
   {
      echo "Are you sure that '".$bukkitdir."' is your bukkit directory?\n";
      exit(1);
   }


   //--> Check temp dir
   if(!(file_exists($tempdir) && is_dir($tempdir))) {
      echo "Temp dir '".$tempdir."' does not exists!";
      exit(1);
   }


   //--> Find screen binary
   $screen_binaries = array("/usr/bin/screen", "/usr/local/bin/screen");
   $screen = null;
   foreach($screen_binaries as $tempscreen) {
      if(file_exists($tempscreen) && is_file($tempscreen) && is_executable($tempscreen)) {
         $screen = $tempscreen;
         break;
      }
   }

   if($screen==null) {
      echo "Screen binary not found!\n";
      exit(1);
   }


   //--> Find tar binary
   $tar_binaries = array("/bin/tar", "/usr/bin/tar", "/usr/local/bin/tar");
   $tar = null;
   foreach($tar_binaries as $temptar) {
      if(file_exists($temptar) && is_file($temptar) && is_executable($temptar)) {
         $tar = $temptar;
         break;
      }
   }

   if($tar==null) {
      echo "Tar binary not found!\n";
      exit(1);
   }


   //--> Fix Settings
   $screen_command = "$screen -S $screen_sessionname -X eval 'stuff \015\"%s\"\015'";
   $tar_command = "$tar czf \"%s\" \"%s\"";

   /**
    * EXecute command in bukkit
    * @global string $screen_command
    * @param string $c 
    */
   function bcmd($c) {
      global $screen_command;
      @exec(sprintf($screen_command, $c));
   }

   /**
    * Create a tar archive
    * @global string $tar_command
    * @param string $tarname
    * @param string $dir 
    */
   function wtar($tarname, $dir) {
      global $tar_command;
      @exec(sprintf($tar_command, $tarname, $dir)." 2> /dev/null");
   }


   bcmd("say -------------------------------");
   bcmd("say Starting world backup");
   bcmd("say Disable level saving");
   bcmd("save-off");


   //--> Delete old files in temp
   $files = glob($tempdir."*.tar.gz");
   if(is_array($files) && count($files)>0) {
      bcmd("say Delete ".count($files)." old files");
      foreach($files as $file) {
         @unlink($file);
      }
   }


   //--> Create tar packages
   $date = date("Y-m-d_H-i-s");
   foreach($worldfolders as $world) {
      $worlddir = $bukkitdir.$world;
      $target = $tempdir.$date."_".$world.".tar.gz";
      if(file_exists($worlddir) && is_dir($worlddir)) {
         bcmd("say Start Backup for ".$world);
         wtar($target, $worlddir);
      } else {
         bcmd("say World ".$world." does not exist");
      }
   }


   bcmd("say Enable level saving");
   bcmd("save-on");

   //--> Upload Files
   bcmd("say Upload backups to FTP");

   $files = glob($tempdir.$date."*.tar.gz");
   if(is_array($files) && count($files)>0) {

      // Connect to FTP
      $conn_id = ftp_connect($ftp_hostname);
      $login_result = ftp_login($conn_id, $ftp_username, $ftp_password);

      // Copy files to FTP
      foreach($files as $file) {
         $fp = fopen($file, 'r');

         if (ftp_fput($conn_id, $ftp_target.basename($file), $fp, FTP_BINARY)) {
            bcmd("say success ".basename($file));
         } else {
            bcmd("say failure ".basename($file));
         }

         fclose($fp);
      }

      // Close FTP connection
      ftp_close($conn_id);

   }

   bcmd("say Backups done");

   exit(0);
