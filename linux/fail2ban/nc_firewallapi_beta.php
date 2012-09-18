<?php

   /**
   // (c) by Christian Blechert (http://anwendungsentwickler.ws)
   // Created: 2012-02-25
   // Last update: 2012-09-18
   // License: http://creativecommons.org/licenses/by/3.0/

      Command Line Interface for Netcup VCP Firewall

      Usage:
      php -f firewallapi.php add INPUT "42.42.42.42" DROP
      php -f firewallapi.php add INPUT "42.42.42.42" DROP "a comment"
      php -f firewallapi.php delete INPUT "42.42.42.42" DROP

   */

   //--> Check for external Settings File
   $dir = dirname(__FILE__)."/";
   $file = $dir."nc_vcp_settings.php";
   
   if(file_exists($file) && is_file($file) && is_readable($file)) {
      include($file);
      
   //--> Settings if external file not exists
   } else {

      /* DEFINES **************************************************************/

      //--> enable/disable debug output
      define("DEBUG_OUTPUT", true);

      //--> API URL
      define("SOAP_URL", "https://www.vservercontrolpanel.de/WSEndUser?wsdl");

      //--> VCP logindata
      define("VCP_USERNAME", "<placehere>");
      define("VCP_PASSWORD", "<placehere>");

      //--> vServer name (vXXXXXXXXXXXXXXXX)
      define("VCP_SERVERNAME", "<placehere>");

      //--> Blacklisted IPs will not blocked
      define("IP_BLACKLIST", "0.0.0.0,127.0.0.1");

      /* END DEFINES **********************************************************/
      
      line("External configuration not found");
      
   }
   
   
   
   /* FUNCTIONS ***************************************************************/
   
   
   //--> Print line to stdout
   function line($str, $debug_override=false) {
      if((defined('DEBUG_OUTPUT') && DEBUG_OUTPUT) || $debug_override===true) echo $str."\n";
   }
   
   //--> Print usage
   function usage() {
      global $argv;
      line("\n\nIPTables wrapper script for netcup VCP firewall");
      line("Version: 0.1.0");
      line("(c) 2012 by Christian Blechert (http://fiae.ws)");
      line("-------------------------------------------------------------\n");
      line("php -f ".$argv[0]." add INPUT|OUTPUT sourceIP ACCEPT|REJECT|DROP");
      line("php -f ".$argv[0]." add INPUT|OUTPUT sourceIP ACCEPT|REJECT|DROP \"a comment\"");
      line("php -f ".$argv[0]." delete INPUT|OUTPUT sourceIP ACCEPT|REJECT|DROP");
   }
   
   //--> Valid IP?
   function check_ip($ip) {
      $ip = trim($ip);
      $check = @gethostbyaddr($ip);
      if($check!==false) {
         return true;
      } else {
         return false;
      }
   }
   
   
   /* FUNCTIONS END ***********************************************************/
   
   
   
   //--> Check parameters
   $initsuccess = true;
   $action = null;
   $chain = null;
   $sourceip = null;
   $target = null;
   $comment = null;
   
   if($argc>=5) {
      $action = $argv[1];
      $chain = $argv[2];
      $sourceip = $argv[3];
      $target = $argv[4];
      
      if(!($action=="add" || $action=="delete")) {
         line("Invalid action given: ".$action);
         $initsuccess = false;
      }
      
      if(!($chain=="INPUT" || $chain=="OUTPUT")) {
         line("Invalid chain given: ".$chain);
         $initsuccess = false;
      }
      
      if(check_ip($sourceip)===false) {
         line("Invalid sourceip given: ".$sourceip);
         $initsuccess = false;
      }
      
      if(!($target=="ACCEPT" || $target=="REJECT" || $target=="DROP")) {
         line("Invalid target given: ".$target);
         $initsuccess = false;
      }
      
   } else {
      line("Not enough parameters given.");
      $initsuccess = false;
   }

   // Get Comment and filter illegal strings out
   if($argc>=6) {
      $comment = $argv[5];
      $comment = preg_replace("/\&[a-zA-Z0-9]{1,5}\;/", "", $comment);
      $comment = preg_replace("/[\<\>]/", "", $comment);
   }

   if($initsuccess===false) {
      usage();
      exit(1);
   }
   
   
   //--> IP on blacklist?
   $blacklistips = explode(",", IP_BLACKLIST);
   array_walk($blacklistips, "trim");
   if(in_array($sourceip, $blacklistips)) {
      line("Source IP in blacklist, skip...");
      exit(0);
   }
   
   
   //--> Create SOAP object
   $vcp = null;
   try {
      $vcp = new SoapClient(SOAP_URL, array('cache_wsdl' => 0));
   } catch(Exception $e) {
      line("Could not connect to VCP API server: ". $e->getMessage());
      exit(0);
   }
   
   
   //--> Select action
   $method = null;
   $rule = array();
   
   switch($action) {
      
      // Add a rule ------------------------------------------------------------
      case 'add': 
         line("Add firewall rule for ".$sourceip);
         $method = "addFirewallRule";
         
         $rule[] = array(
            'direction' => $chain,
            'sort' => "1",
            'proto' => "any",
            'srcIP' => $sourceip,
            'destIP'=>'0.0.0.0/0',
            'target' => $target,
            'match' => 'STATE',
            'matchValue' => 'NEW,ESTABLISHED,RELATED',
            'comment' => 'Added by Fail2Ban at '.date("Y-m-d H:i:s").' from Host '.php_uname('n').
               ($comment!=null ? " - ".$comment : "")
         );
         
         break;
      
      // Delete a rule ---------------------------------------------------------
      case 'delete':
         line("Delete firewall rule for ".$sourceip);
         $method = "deleteFirewallRule";
         
         $params = array(
            'loginName' => VCP_USERNAME,
            'password' => VCP_PASSWORD,
            'vserverName' => VCP_SERVERNAME
         );

         $result = $vcp->getFirewall($params);
         $ruleset = $result->return;

         // If rule count <=1, no array given
         if(is_object($ruleset) && !is_array($ruleset)) {
            $ruleset = array($ruleset);
         }

         $deleteset = array();
         foreach($ruleset as $rule) {
            if(is_object($rule) && 
               (isset($rule->direction) && $rule->direction==$chain) && 
               (isset($rule->srcIP) && $rule->srcIP==$sourceip) && 
               (isset($rule->target) && $rule->target==$target)) 
            {
               $deleteset[] = array("id"=>$rule->id);
            }
         }
         
         if(is_array($deleteset) && count($deleteset)>0) {
            $rule = $deleteset;
         }
         
         break;
         
   }
   
   
   //--> Execute SOAP Request
   if(is_array($rule) && count($rule)>0 && $method!=null) {
      
      $params = array(
         'loginName' => VCP_USERNAME,
         'password' => VCP_PASSWORD,
         'vserverName' => VCP_SERVERNAME,
         'rule' => $rule
      );
      
      $result = $vcp->$method($params);
      
      if(isset($result->return->success) && $result->return->success===true) {
         line("Success!");
         exit(0);
      } else {
         line("Failure!");
         if(defined('DEBUG_OUTPUT') && DEBUG_OUTPUT) {
            var_dump($result);
         }
      }
      
   } else {
      line("Noting to do...");
   }
   
   exit(1);
   