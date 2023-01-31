#!/bin/bash
PLUGINS="local/sinculpgc local/supervision local/ulpgcassign local/ulpgccore local/ulpgcgroups local/ulpgcquiz admin/tool/backuprestore admin/tool/batchmanage auth/casulpgc availability/condition/response availability/condition/timeelapsed blocks/course_termlist blocks/examswarnings blocks/makeexam blocks/supervision blocks/tracker course/format/topicgroup enrol/metacat enrol/metapattern enrol/multicohort enrol/sinculpgc  enrol/ulpgcunits  media/player/bustreaming mod/assign/feedback/archive mod/assign/feedback/copyset mod/assign/feedback/historic mod/assign/feedback/wtpeer mod/assign/submission/data mod/assign/submission/exam mod/assign/submission/peers mod/examboard mod/examregistrar mod/islmeeting mod/library mod/registry mod/teamsmeeting mod/videolib mod/quiz/accessrule/makeexamlock mod/quiz/report/attemptstate mod/quiz/report/gradingempty mod/quiz/report/makeexam question/format/ulpgctf report/autogroups report/datacheck report/supervision report/syncgroups report/trackertools"

PLUGINS="mod/library mod/registry mod/teamsmeeting mod/videolib mod/quiz/accessrule/makeexamlock mod/quiz/report/attemptstate mod/quiz/report/gradingempty mod/quiz/report/makeexam question/format/ulpgctf report/autogroups report/datacheck report/supervision report/syncgroups report/trackertools"


for plugin in $PLUGINS
do
  git config --global --add safe.directory "/var/www/html/moodle41ulpgc/$plugin"
  cd $plugin
#  git add --all && git commit -am"Updated to 2022-11-26. First 4.1 version" && git push origin master && cd /var/www/html/moodle41ulpgc
#  git push origin master && cd /var/www/html/moodle41ulpgc
git status && echo  $plugin && cd /var/www/html/moodle41ulpgc
done


