#!/bin/bash

URL="http://freedns.afraid.org/dynamic/update.php?<APIKEY>"

cd "$(dirname "$0")"

if [ ! -f ".lastip" ]; then
   echo "0.0.0.0" > .lastip
fi

CURRIP="$(curl -L -s http://myip.fiae.ws/)"
LASTIP="$(head -n 1 .lastip)"

if [ ! "$CURRIP" == "$LASTIP" ]; then
   date
   echo "Update IP!"
   RES=$(curl -s "$URL")
   if [ "$(echo $RES | grep "Updated" | wc -l)" -gt 0 ] || [ "$(echo $RES | grep "not changed" | wc -l)" -gt 0 ]; then
      echo $CURRIP > .lastip
   fi
   echo -e $RES
fi

# EOF
