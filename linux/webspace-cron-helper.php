<?php

/**
 * Cron Wrapper Script for Webspace Users
 *
 * License: General Public License 3.0
 * Author: Christian Blechert <christian@blechert.name>
 * Web: http://anwendungsentwickler.ws
 * Git: http://gitweb.fiae.ws/scripts.git
 *
 * Created: 2013-03-01
 * Last Edit: 2013-03-04
 * Revision: 3
 *
 */


/**
 * Call this over Webhosters Admin Panel:
 * http://cron.example.org/webspace-cron-helper.php?securekey=XXXXXXXXXXXXXXX&cron=uax_daily_report&action=run
 *
 * Important:
 * Protect this script with BASIC authentication (.htaccess)!
 *
 * Tested providers:
 * all-inkl.com
 *
 */


/**
 * MD5 Hashed Securekey
 * @var string
 */
$securekey = "XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX";


/**
 * Definition of Cron Commands
 * @var array
 */
$crons = array(

   "uax_daily_report" => array(
      "cmd" => 'curl -F foo=bar -F bar=foo -F foobar=huhu "http://example.com/api/xxxxxxxxxxxxxxxxxxxxxxx/xml/report/"',
      "nohup" => false,
      "logfile" => true,
   ),

   // Cron name for URL
   "piwik_genstats" => array(

      // The command for this cron
      "cmd" => '/usr/bin/php /www/htdocs/w0000000/vhosts/org.mystats.foobar/misc/cron/archive.php -- url=http://foobar.mystats.org/',

      // Prevent process killing after php thread end
      "nohup" => true,

      // Write thread output to file
      "logfile" => true

   ),

);

/*** END CONFIG ***/


//--> Check secure key
if(! (isset($_GET['securekey']) && md5($_GET['securekey'])==$securekey) ) {
   header("HTTP/1.0 403 Forbidden", true, 403);
   die("<h1>403 Forbidden</h1><p>Access denied!</p>");
}

//--> Check Cron name
if(! (isset($_GET['cron']) && strlen(trim($_GET['cron']))>0) ) {
   header("HTTP/1.0 400 Bad Request", true, 400);
   die("<h1>400 Bad Request</h1><p>No valid cronjob name in request defined!</p>");
}

//--> Check action
if(! (isset($_GET['action']) && ($_GET['action']=="run")) ) {
   header("HTTP/1.0 400 Bad Request", true, 400);
   die("<h1>400 Bad Request</h1><p>No valid action in request defined!</p>");
}

//--> Save in constants
define("CRON", trim($_GET['cron']));
define("ACTION", trim($_GET['action']));

//--> Check if cron name exists
if(! (isset($crons[CRON]) && is_array($crons[CRON])) ) {
   header("HTTP/1.0 404 Not Found", true, 404);
   die("<h1>404 Not Found</h1><p>The requested cronjob does not exists!</p>");
}

/*** END SECURITY ***/

define("BASE", dirname(__FILE__)."/");
define("LOG", BASE."log/");

if(ACTION=="run") {

   //--> Build command
   $command = trim($crons[CRON]['cmd']);
   $nohup = ($crons[CRON]['nohup']===true ? true : false);
   $log = ($crons[CRON]['logfile']===true ? true : false);

   if($log) {
      // Save last output in two (error and stdout) files
      $command = $command." > ".LOG.CRON.".out.txt 2> ".LOG.CRON.".err.txt";
   } else {
      // Silent run
      $command = $command." > /dev/null 2> /dev/null";
   }

   // Use nohup?
   if($nohup) {
      $command = "nohup ".$command." &";
   }

   //--> Execute command
   echo "<fieldset><legend>Command</legend><pre>".$command."</pre></fieldset>";
   @exec($command);

}


