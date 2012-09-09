<?php

   /**
   // (c) by Christian Blechert (http://anwendungsentwickler.ws)
   // Created: 2012-04-24
   // Last update: 2012-04-24
   // License: http://creativecommons.org/licenses/by/3.0/

      External Configuration for Netcup Firewall API Script

   */

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
