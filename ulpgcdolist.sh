#!/bin/bash
PLUGINS="local/assigndata local/ulpgcassign local/ulpgccore local/ulpgcquiz mod/examregistrar mod/quiz/report/attemptstate"

for plugin in $PLUGINS
do
  git config --global --add safe.directory "/var/www/html/moodle41ulpgc/$plugin"
  cd $plugin  && echo  $plugin
#  git add --all && git commit -am"Updated to 2022-11-26. First 4.1 version" && git push origin master && cd /var/www/html/moodle41ulpgc
  git push origin master && cd /var/www/html/moodle41ulpgc
done


