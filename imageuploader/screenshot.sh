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

#
# Erstellt einen Screenshot und lädt ihn nach einer Abfrage hoch
# Spezielles Script fuer die Pandora
#


. functions.sh

target="/home/sim/Documents/screenshots"
date=`date +%y-%m-%d_%H-%M-%S`
filename="$target/screenie_$(hostname)_${date}.png"
icon="mimetypes/image.svg"

if [ ! -d "$target" ]; then
   mkdir -p "$target"
   showmsg "$icon" "Screenshot" "Create $target. Try again."
   exit 0;
fi

sleep 5
fbgrab "$filename"
showmsg "$icon" "Screenshot" "Create $filename"

if `zenity --question --text="Upload?"`; then
   RESULT="$(curl -F userfile=@$filename -F upload=true "http://stuff.fiae.ws/screenies/upload.php?secret=somesecret" || echo connection error)"
   showmsg "$icon" "Screenshot" "$RESULT"
fi

