#!/bin/bash


#   /**
#    * Simples Upload Script fuer Bilder
#    *
#    * LICENSE
#    * This work is licensed under a
#    * Creative Commons Attribution-NonCommercial-ShareAlike 3.0 Unported License
#    *
#    * @copyright  Copyright (c) 2012 Christian Blechert (www.blechert.name)
#    * @license    http://creativecommons.org/licenses/by-nc-sa/3.0/
#    * @author     Christian Blechert (christian@blechert.name)
#    * @version    1.0.0
#    */

# Bash Funktionen fuer die Pandora

showmsg() {
   path="/usr/share/icons/gnome/scalable"
   icon="$1"
   title="$2"
   msg="$3"

   notify-send -u normal "$title" "$msg" -i "$path/$icon" -t 5000
   return 0;
}



