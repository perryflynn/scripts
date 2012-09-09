<?php

   /**
   // (c) by Christian Blechert (http://anwendungsentwickler.ws)
   // Created: 2012-02-25
   // Last update: 2012-04-24
   // License: http://creativecommons.org/licenses/by/3.0/

      Use the netcup firewall API with fail2ban

      Usage:
      php -f firewallapi.php add INPUT "42.42.42.42" DROP
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

   
   line("\n\nIPTables wrapper script for netcup VCP firewall");
   line("Version: 0.0.3");
   line("(c) 2012 by Christian Blechert (http://fiae.ws)");
   line("-------------------------------------------------------------\n");

   
   /* FUNCTIONS ***************************************************************/

   
   //--> Print usage
   function usage() {
      global $argv;
      echo "\nUsage: ".$argv[0]." add|delete INPUT|OUTPUT sourceIP ACCEPT|REJECT|DROP\n";
   }
   
   //--> Print line to stdout
   function line($str, $debug_override=false) {
      if((defined('DEBUG_OUTPUT') && DEBUG_OUTPUT) || $debug_override===true) echo $str."\n";
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
   
   //--> Add rule
   function action_add($chain, $sourceip, $target) {
      global $vcp;
      
      $rule = array(
         'direction' => $chain,
         'sort' => "0",
         'proto' => "any",
         'srcIP' => $sourceip,
         'target' => $target,
         'match' => 'STATE',
         'matchValue' => 'NEW,ESTABLISHED,RELATED',
         'comment' => 'Added by Fail2Ban at '.date("Y-m-d H:i:s").' from Host '.php_uname('n')
      );

      $params = array(
         'loginName' => VCP_USERNAME,
         'password' => VCP_PASSWORD,
         'vserverName' => VCP_SERVERNAME,
         'rule' => array($rule)
      );
      
      $result = $vcp->addFirewallRule($params);
   
      if($result->return->success===true) {
         line("Firewall rule added.");
         return 0;
      } else {
         line("Firewall API error:");
         if(DEBUG_OUTPUT) var_dump($result);
         return 1;
      }

   }
   
   //--> Delete rule
   function action_delete($chain, $sourceip, $target) {
      global $vcp;

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
      
      if(count($deleteset)>0) {

         $params['rule'] = $deleteset;
         $result = $vcp->deleteFirewallRule($params);

         if($result->return->success===true) {
            line(count($deleteset)." Firewall rule(s) deleted.");
            return 0;
         } else {
            line("Firewall API error:");
            if(DEBUG_OUTPUT) var_dump($result);
         }

      } else {
         line("Rule not found in firewall.");
      }
      
      return 1;
   }
   
   
   /* END FUNCTIONS ***********************************************************/
   

   //--> Check parameters
   $initsuccess = true;
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
   try {
      $vcp = new SoapClient(SOAP_URL, array('cache_wsdl' => 0));
   } catch(Exception $e) {
      line("Could not connect to VCP API server: ". $e->getMessage());
      exit(0);
   }
   
   
   //--> Select action
   switch($action) {
      case 'add': exit(action_add($chain, $sourceip, $target)); break;
      case 'delete': exit(action_delete($chain, $sourceip, $target)); break;
      default: line("Invalid action given."); break;
   }


