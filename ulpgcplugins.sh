#!/bin/bash
#Local plugins
git submodule add git@bitbucket.org:moodleulpgc/moodle-local_assigndata.git     local/assigndata
git submodule add git@bitbucket.org:moodleulpgc/moodle-local_sinculpgc.git      local/sinculpgc
git submodule add git@bitbucket.org:moodleulpgc/moodle-local_supervision.git    local/supervision
git submodule add git@bitbucket.org:moodleulpgc/moodle-local_ulpgcassign.git    local/ulpgcassign
git submodule add git@bitbucket.org:moodleulpgc/moodle-local_ulpgccore.git      local/ulpgccore
git submodule add git@bitbucket.org:moodleulpgc/moodle-local_ulpgcgroups.git    local/ulpgcgroups
git submodule add git@bitbucket.org:moodleulpgc/moodle-local_ulpgcquiz.git      local/ulpgcquiz

#Admin tool plugins
git submodule add git@bitbucket.org:moodleulpgc/moodle-tool_backuprestore.git   admin/tool/backuprestore
git submodule add git@bitbucket.org:moodleulpgc/moodle-tool_batchmanage.git     admin/tool/batchmanage

#auth plugins
git submodule add git@bitbucket.org:moodleulpgc/moodle-auth_casulpgc.git        auth/casulpgc

#Availavility plugins
git submodule add git@bitbucket.org:moodleulpgc/moodle-availability_response.git    availability/condition/response
git submodule add git@bitbucket.org:moodleulpgc/moodle-availability_timeelapsed.git availability/condition/timeelapsed

#Blocks plugins
git submodule add git@bitbucket.org:moodleulpgc/moodle-block_course_termlist.git    blocks/course_termlist
git submodule add git@bitbucket.org:moodleulpgc/moodle-block_examswarnings.git      blocks/examswarnings
git submodule add git@bitbucket.org:moodleulpgc/moodle-block_makeexam.git           blocks/makeexam
git submodule add git@bitbucket.org:moodleulpgc/moodle-block_supervision.git        blocks/supervision
git submodule add git@bitbucket.org:moodleulpgc/moodle-block_tracker.git            blocks/tracker

#Course formats plugins
git submodule add git@bitbucket.org:moodleulpgc/moodle-format_topicgroup.git    course/format/topicgroup

#Enrol plugins
git submodule add git@bitbucket.org:moodleulpgc/moodle-enrol_metacat.git        enrol/metacat
git submodule add git@bitbucket.org:moodleulpgc/moodle-enrol_metapattern.git    enrol/metapattern
git submodule add git@bitbucket.org:moodleulpgc/moodle-enrol_multicohort.git    enrol/multicohort
git submodule add git@bitbucket.org:moodleulpgc/moodle-enrol_sinculpgc.git      enrol/sinculpgc
git submodule add git@bitbucket.org:moodleulpgc/moodle-enrol_ulpgcunits.git   enrol/ulpgcunits

#Media plugins
git submodule add git@bitbucket.org:moodleulpgc/moodle-media_bustreaming.git    media/player/bustreaming

#Assign feedback plugins
git submodule add git@bitbucket.org:moodleulpgc/moodle-assignfeedback_archive.git   mod/assign/feedback/archive
git submodule add git@bitbucket.org:moodleulpgc/moodle-assignfeedback_copyset.git   mod/assign/feedback/copyset
git submodule add git@bitbucket.org:moodleulpgc/moodle-assignfeedback_historic.git  mod/assign/feedback/historic
git submodule add git@bitbucket.org:moodleulpgc/moodle-assignfeedback_wtpeer.git    mod/assign/feedback/wtpeer

#Assign submissions plugins
git submodule add git@bitbucket.org:moodleulpgc/moodle-assignsubmission_data.git    mod/assign/submission/data
git submodule add git@bitbucket.org:moodleulpgc/moodle-assignsubmission_exam.git    mod/assign/submission/exam
git submodule add git@bitbucket.org:moodleulpgc/moodle-assignsubmission_peers.git   mod/assign/submission/peers

#Activity module plugins
git submodule add git@bitbucket.org:moodleulpgc/moodle-mod_examboard.git        mod/examboard
git submodule add git@bitbucket.org:moodleulpgc/moodle-mod_examregistrar.git    mod/examregistrar
#git submodule add git@bitbucket.org:moodleulpgc/moodle-mod_islmeeting.git       mod/islmeeting
git submodule add git@bitbucket.org:moodleulpgc/moodle-mod_library.git          mod/library
git submodule add git@bitbucket.org:moodleulpgc/moodle-mod_registry.git         mod/registry
git submodule add git@bitbucket.org:moodleulpgc/moodle-mod_teamsmeeting.git     mod/teamsmeeting
git submodule add git@bitbucket.org:moodleulpgc/moodle-mod_videolib.git         mod/videolib

#Quiz plugins 
git submodule add git@bitbucket.org:moodleulpgc/moodle-quizaccess_makeexamlock.git  mod/quiz/accessrule/makeexamlock
git submodule add git@bitbucket.org:moodleulpgc/moodle-quiz_attemptstate.git        mod/quiz/report/attemptstate
git submodule add git@bitbucket.org:moodleulpgc/moodle-quiz_gradingempty.git        mod/quiz/report/gradingempty
git submodule add git@bitbucket.org:moodleulpgc/moodle-quiz_makeexam.git            mod/quiz/report/makeexam

#Question plugins
git submodule add git@bitbucket.org:moodleulpgc/moodle-qformat_ulpgctf.git      question/format/ulpgctf

#Report plugins
git submodule add git@bitbucket.org:moodleulpgc/moodle-report_autogroups.git    report/autogroups
git submodule add git@bitbucket.org:moodleulpgc/moodle-report_datacheck.git     report/datacheck
git submodule add git@bitbucket.org:moodleulpgc/moodle-report_supervision.git   report/supervision
git submodule add git@bitbucket.org:moodleulpgc/moodle-report_syncgroups.git    report/syncgroups
git submodule add git@bitbucket.org:moodleulpgc/moodle-report_trackertools.git  report/trackertools

#o365 plugins
git submodule add git@bitbucket.org:moodleulpgc/moodle-local_o365teams.git     local/o365teams
git submodule add git@bitbucket.org:moodleulpgc/moodle-report_o365channels.git report/o365channels

#NOT developed by ULPGC, but used as ULPGC, no other repo
#git submodule add git@bitbucket.org:moodleulpgc/moodle-gradingform_mcq.git     grade/grading/form/mcq
git submodule add git@bitbucket.org:moodleulpgc/moodle-atto_pastespecial.git   lib/editor/atto/plugins/pastespecial
git submodule add git@bitbucket.org:moodleulpgc/moodle-block_demostudent.git   blocks/demostudent
