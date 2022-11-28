#!/bin/bash
PLUGINS="admin/tool/crawler admin/tool/generator/classes/course_backend.php admin/tool/lockstats admin/tool/objectfs admin/tool/opcache admin/tool/redis blocks/admin_presets blocks/news_slider blocks/remote_courses blocks/section course/format/flexsections course/format/menutopic course/format/multitopic course/format/onetopic enrol/groupsync enrol/waitlist grade/grading/form/checklist lib/editor/atto/plugins/generico lib/editor/atto/plugins/styles local/boostnavigation local/questionbulkupdate local/searchquestions local/unusedquestions mod/assign/feedback/solutionsheet mod/assign/submission/gradereviews mod/attendance mod/datalynx mod/dialogue mod/moodleoverflow mod/offlinequiz mod/pdfannotator mod/qcreate mod/quiz/accessrule/delayed mod/quiz/accessrule/offlinemode mod/quiz/report/mcq mod/scheduler mod/tab mod/tracker mod/unedtrivial plagiarism/turnitin plagiarism/turnitinsim question/type/pmatch question/type/regexp question/type/type_calc_sheet report/forumgraph repository/office365 repository/searchable theme/adaptable theme/boost_campus"
 
for plugin in $PLUGINS
do
 cd $plugin
 git config --global --add safe.directory "/var/www/html/moodle41ulpgc/$plugin"
 git add --all && git commit -am"ULPGC customizations on plugin" && cd /var/www/html/moodle41ulpgc
done
