#!/bin/bash

# Bash Script um Bilder hochzuladen

curl -F userfile=@$1 -F upload=true "http://stuff.fiae.ws/screenies/upload.php?secret=somesecret"

