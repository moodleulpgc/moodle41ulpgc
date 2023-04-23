#!/bin/bash
while IFS= read -r line; do
  git config --global --add safe.directory /var/www/html/moodle41ulpgc/$line
  cd $line &&  echo  $line
  git push -u origin moodle41  && cd /var/www/html/moodle41ulpgc
done < "$1"
