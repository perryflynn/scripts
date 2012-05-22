#!/bin/bash

# Simple CraftBukkit Adminpanel over SSH
# by Christian Blechert (http://fiae.ws)
#
# License: http://creativecommons.org/licenses/by-nc-sa/3.0/
#
# Created: 2012-05-21
# Last modified: 2012-05-22
#
# German Manual: http://fiae.ws/422
# 
# Usage:
# ./mcmanager.sh # Allow status, start, stop, logcat
# ./mcmanager.sh superadmin # Allow status, start, stop, logcat, screen
# 
# Use this script as custom shell for a ssh key
# A login with the key opens the manager automatically
# 
# Installation on server: 
# - Create a ssh key: ssh-keygen -t rsa
# - Put the id_rsa.pub into ~/.ssh/authorized_keys
# - Add the custom shell to beginning of the key. Example:
#   no-port-forwarding,no-agent-forwarding,command="/home/mc/srv/manager.sh" ssh-r...
# - or for superadmin
#   no-port-forwarding,no-agent-forwarding,command="/home/mc/srv/manager.sh superadmin" ssh-r...
#
# Installation on client
# - Rename id_rsa
# - chmod the key to 700
# - Note: on windows convert the key with PuTTYGen
# - Test the login:
#   ssh myminecraftuser@my.little.host.com -i myprivatekeyfile
# 
# Now the manager starts automatically.
#

# Config --------------------------------------------------------------------

#--> The screen session name
screenname="minecraft"

#--> CraftBukkit Directory
bukkitdir="/home/minecraft/bukkit/"

#--> CraftBukkit Filename
jarname="craftbukkit.jar"

#--> Memory option for java
mem="512M"


# END Config ----------------------------------------------------------------

USERSTATUS=$1

#--> Check folder
if [ ! -d "$bukkitdir" ]; then
   echo "Folder '$bukkitdir' not found."
   exit 1;
fi

cd "$bukkitdir"

#--> Check file
if [ ! -f "$jarfile" ]; then
   echo "Jar File not found."
   return 1;
fi


# Stop server
sendstop() {
   screen -s $screenname -X eval 'stuff "stop"\015'
}

# Display error message
msg_error() {
   dialog --title "Error" --msgbox "\n $1" 10 80;
}

# Display info message
msg_info() {
   dialog --title "Information" --msgbox "\n $1" 10 80
}

# Check server status
is_running() {
   PROC=$(ps aux | grep "SCREEN -d -m -S $screenname" | grep -v grep | wc -l)
   if [ "$PROC" -gt 0 ]; then
      echo 0;
   else
      echo 1;
   fi
}

# Display server status
do_status() {
   if [ "$(is_running)" == "0" ]; then
      msg_info "Minecraft is running"
   else
      msg_info "Minecraft is not running"
   fi
}

# Display server log
do_logcat() {
   dialog --scrollbar --msgbox "$(tail -n 200 server.log)" 30 80
}

# Start server
do_start() {
   if [ "$(is_running)" == "0" ]; then
      msg_info "Minecraft is running"
   else
      screen -d -m -S $screenname java -Xincgc -Xmx${mem} -jar $jarname
   fi
}

# Stop server
do_stop() {
   if [ "$(is_running)" == "0" ]; then
      sendstop
   else
      msg_info "Minecraft is not running"
   fi
}

# Get screen session if superadmin
do_screen() {
   if [ ! "$USERSTATUS" == "superadmin" ]; then
      msg_error "Permission denied!"
      return;
   fi
   msg_info "Exit screen with Strg+A+D!"
   screen -r $screenname
}

#--> Endless loop
while true; do

# Display option window
result=$(dialog --stdout \
   --backtitle "Minecraft Control over SSH" \
   --title " Options " \
   --cancel-label "Exit" \
   --menu "Select a option and press [Enter]" 0 0 0 \
status "Server Status" \
screen "Get screen session" \
logcat "Last 200 logfile lines" \
start "Start server" \
stop "Stop server" );

   code=$?;

   if [ "$code" == "1" ]; then
      break;
   fi

   # Do action
   case $result in

      status) 
         do_status;
         ;;

      screen)
         do_screen;
         ;;

      logcat)
         do_logcat;
         ;;

      start)
         do_start;
         ;;
      
      stop)
         do_stop;
         ;;

   esac;

done;
