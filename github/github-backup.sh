#!/bin/bash

# This script backups all public repos of a github account
# Author: Christian Blechert (christian@blechert.name)
# http://anwendungsentwickler.ws
# License: GPL v3

# BEGIN CONFIG

URL="https://api.github.com/users/agentp/repos"

# END CONFIG

CURR=$(pwd)
DIR="$(dirname "$0")/github-backups"
RES=$(echo "<?php \$data=file_get_contents(\"$URL\"); \$data = json_decode(\$data); foreach(\$data as \$item) echo \$item->full_name.\"|\".\$item->clone_url.\"\\n\";" | php)

if [ ! -d "$DIR" ]; then
   mkdir "$DIR"
fi

while read -r line; do

   NAME=$(echo $line | cut -d '|' -f 1 | sed 's/\\.git//g')
   CLONE=$(echo $line | cut -d '|' -f 2)

   echo -e "\n\n--> Backup $NAME...\n"

   # Change to repo dir
   if [ ! -d "$DIR/$NAME" ]; then
   
      # New clone
      mkdir -p "$DIR/$NAME"
      echo "Clone new repo..."
      git clone --quiet $CLONE "$DIR/$NAME"
      
      # Show latest changes
      cd "$DIR/$NAME"
      echo
      echo "Last changes:"
      PAGER=cat git log --oneline | head -n 10
      
      # Go back
      cd "$CURR"
      
   else
   
      # Refresh
      cd "$DIR/$NAME"

      # Last commit SHA1      
      LASTCOMMIT=$(git log HEAD^..HEAD | head -n 1 | cut -d ' ' -f 2)
      
      # Refresh
      echo "Pull changes..."
      git pull --quiet
      
      # Show lastes commits
      echo
      echo "Last changes:"
      if [ "$(PAGER=cat git log --oneline ${LASTCOMMIT}..HEAD | wc -l)" -gt 0 ]; then
         PAGER=cat git log --oneline ${LASTCOMMIT}..HEAD
      else
         echo "Nothing!"
      fi
      
      # Go back
      cd "$CURR"
      
   fi
   
done <<< "$RES"

exit 0

# EOF