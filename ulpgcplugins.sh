#!/bin/bash
#Local plugins
git submodule add git@bitbucket.org:moodleulpgc/moodle-local_assigndata.git     local/assigndata
git submodule set-branch -b moodle41 -- local/assigndata
git submodule add git@bitbucket.org:moodleulpgc/moodle-local_sinculpgc.git      local/sinculpgc
git submodule set-branch -b moodle41 -- local/sinculpgc
git submodule add git@bitbucket.org:moodleulpgc/moodle-local_supervision.git    local/supervision
git submodule set-branch -b moodle41 -- local/supervision
git submodule add git@bitbucket.org:moodleulpgc/moodle-local_ulpgcassign.git    local/ulpgcassign
git submodule set-branch -b moodle41 -- local/ulpgcassign
git submodule add git@bitbucket.org:moodleulpgc/moodle-local_ulpgccore.git      local/ulpgccore
git submodule set-branch -b moodle41 -- local/ulpgccore
git submodule add git@bitbucket.org:moodleulpgc/moodle-local_ulpgcgroups.git    local/ulpgcgroups
git submodule set-branch -b moodle41 -- local/ulpgcgroups
git submodule add git@bitbucket.org:moodleulpgc/moodle-local_ulpgcquiz.git      local/ulpgcquiz
git submodule set-branch -b moodle41 -- local/ulpgcquiz

#Admin tool plugins
git submodule add git@bitbucket.org:moodleulpgc/moodle-tool_backuprestore.git   admin/tool/backuprestore
git submodule set-branch -b moodle41 -- admin/tool/backuprestore
git submodule add git@bitbucket.org:moodleulpgc/moodle-tool_batchmanage.git     admin/tool/batchmanage
git submodule set-branch -b moodle41 -- admin/tool/batchmanage

#auth plugins
git submodule add git@bitbucket.org:moodleulpgc/moodle-auth_casulpgc.git        auth/casulpgc
git submodule set-branch -b moodle41 --  auth/casulpgc

#Availavility plugins
git submodule add git@bitbucket.org:moodleulpgc/moodle-availability_response.git    availability/condition/response
git submodule set-branch -b moodle41 --
git submodule add git@bitbucket.org:moodleulpgc/moodle-availability_timeelapsed.git availability/condition/timeelapsed
git submodule set-branch -b moodle41 --

#Blocks plugins
git submodule add git@bitbucket.org:moodleulpgc/moodle-block_course_termlist.git    blocks/course_termlist
git submodule set-branch -b moodle41 -- blocks/course_termlist
git submodule add git@bitbucket.org:moodleulpgc/moodle-block_examswarnings.git      blocks/examswarnings
git submodule set-branch -b moodle41 --  blocks/examswarnings
git submodule add git@bitbucket.org:moodleulpgc/moodle-block_makeexam.git           blocks/makeexam
git submodule set-branch -b moodle41 --  blocks/makeexam
git submodule add git@bitbucket.org:moodleulpgc/moodle-block_supervision.git        blocks/supervision
git submodule set-branch -b moodle41 --  blocks/supervision
git submodule add git@bitbucket.org:moodleulpgc/moodle-block_tracker.git            blocks/tracker
git submodule set-branch -b moodle41 -- blocks/tracker

#Course formats plugins
git submodule add git@bitbucket.org:moodleulpgc/moodle-format_topicgroup.git    course/format/topicgroup
git submodule set-branch -b moodle41 --  course/format/topicgroup

#Enrol plugins
git submodule add git@bitbucket.org:moodleulpgc/moodle-enrol_metacat.git        enrol/metacat
git submodule set-branch -b moodle41 --  enrol/metacat
git submodule add git@bitbucket.org:moodleulpgc/moodle-enrol_metapattern.git    enrol/metapattern
git submodule set-branch -b moodle41 --  enrol/metapattern
git submodule add git@bitbucket.org:moodleulpgc/moodle-enrol_multicohort.git    enrol/multicohort 
git submodule set-branch -b moodle41 -- enrol/multicohort
git submodule add git@bitbucket.org:moodleulpgc/moodle-enrol_sinculpgc.git      enrol/sinculpgc
git submodule set-branch -b moodle41 -- enrol/sinculpgc
git submodule add git@bitbucket.org:moodleulpgc/moodle-enrol_ulpgcunits.git   enrol/ulpgcunits
git submodule set-branch -b moodle41 -- enrol/ulpgcunits

#Media plugins
git submodule add git@bitbucket.org:moodleulpgc/moodle-media_bustreaming.git    media/player/bustreaming
git submodule set-branch -b moodle41 --  media/player/bustreaming

#Assign feedback plugins
git submodule add git@bitbucket.org:moodleulpgc/moodle-assignfeedback_archive.git   mod/assign/feedback/archive
git submodule set-branch -b moodle41 --   mod/assign/feedback/archive
git submodule add git@bitbucket.org:moodleulpgc/moodle-assignfeedback_copyset.git   mod/assign/feedback/copyset
git submodule set-branch -b moodle41 -- mod/assign/feedback/copyset
git submodule add git@bitbucket.org:moodleulpgc/moodle-assignfeedback_historic.git  mod/assign/feedback/historic
git submodule set-branch -b moodle41 -- mod/assign/feedback/historic
git submodule add git@bitbucket.org:moodleulpgc/moodle-assignfeedback_wtpeer.git    mod/assign/feedback/wtpeer
git submodule set-branch -b moodle41 -- mod/assign/feedback/wtpeer

#Assign submissions plugins
git submodule add git@bitbucket.org:moodleulpgc/moodle-assignsubmission_data.git    mod/assign/submission/data
git submodule set-branch -b moodle41 -- mod/assign/submission/data
git submodule add git@bitbucket.org:moodleulpgc/moodle-assignsubmission_exam.git    mod/assign/submission/exam
git submodule set-branch -b moodle41 --  mod/assign/submission/exam
git submodule add git@bitbucket.org:moodleulpgc/moodle-assignsubmission_peers.git   mod/assign/submission/peers
git submodule set-branch -b moodle41 --  mod/assign/submission/peers

#Activity module plugins
git submodule add git@bitbucket.org:moodleulpgc/moodle-mod_examboard.git        mod/examboard
git submodule set-branch -b moodle41 --  mod/examboard
git submodule add git@bitbucket.org:moodleulpgc/moodle-mod_examregistrar.git    mod/examregistrar
git submodule set-branch -b moodle41 --  mod/examregistrar
#git submodule add git@bitbucket.org:moodleulpgc/moodle-mod_islmeeting.git       mod/islmeeting
git submodule add git@bitbucket.org:moodleulpgc/moodle-mod_library.git          mod/library
git submodule set-branch -b moodle41 --  mod/library
git submodule add git@bitbucket.org:moodleulpgc/moodle-mod_registry.git         mod/registry
git submodule set-branch -b moodle41 --  mod/registry
git submodule add git@bitbucket.org:moodleulpgc/moodle-mod_teamsmeeting.git     mod/teamsmeeting
git submodule set-branch -b moodle41 --  mod/teamsmeeting
git submodule add git@bitbucket.org:moodleulpgc/moodle-mod_videolib.git         mod/videolib
git submodule set-branch -b moodle41 --   mod/videolib

#Quiz plugins 
git submodule add git@bitbucket.org:moodleulpgc/moodle-quizaccess_makeexamlock.git  mod/quiz/accessrule/makeexamlock
git submodule set-branch -b moodle41 --  mod/quiz/accessrule/makeexamlock
git submodule add git@bitbucket.org:moodleulpgc/moodle-quiz_attemptstate.git        mod/quiz/report/attemptstate
git submodule set-branch -b moodle41 --  mod/quiz/report/attemptstate
git submodule add git@bitbucket.org:moodleulpgc/moodle-quiz_gradingempty.git        mod/quiz/report/gradingempty
git submodule set-branch -b moodle41 --  mod/quiz/report/gradingempty
git submodule add git@bitbucket.org:moodleulpgc/moodle-quiz_makeexam.git            mod/quiz/report/makeexam
git submodule set-branch -b moodle41 --  mod/quiz/report/makeexam

#Question plugins
git submodule add git@bitbucket.org:moodleulpgc/moodle-qformat_ulpgctf.git      question/format/ulpgctf
git submodule set-branch -b moodle41 --  question/format/ulpgctf

#Report plugins
git submodule add git@bitbucket.org:moodleulpgc/moodle-report_attendancetools.git    report/attendancetools
git submodule set-branch -b moodle41 -- report/attendancetools 
git submodule add git@bitbucket.org:moodleulpgc/moodle-report_autogroups.git    report/autogroups
git submodule set-branch -b moodle41 -- report/autogroups
git submodule add git@bitbucket.org:moodleulpgc/moodle-report_datacheck.git     report/datacheck
git submodule set-branch -b moodle41 -- report/datacheck
git submodule add git@bitbucket.org:moodleulpgc/moodle-report_supervision.git   report/supervision
git submodule set-branch -b moodle41 -- report/supervision
git submodule add git@bitbucket.org:moodleulpgc/moodle-report_syncgroups.git    report/syncgroups
git submodule set-branch -b moodle41 -- report/syncgroups
git submodule add git@bitbucket.org:moodleulpgc/moodle-report_trackertools.git  report/trackertools
git submodule set-branch -b moodle41 -- report/trackertools

#o365 plugins
git submodule add git@bitbucket.org:moodleulpgc/moodle-local_o365teams.git     local/o365teams
git submodule set-branch -b moodle41 --
git submodule add git@bitbucket.org:moodleulpgc/moodle-report_o365channels.git report/o365channels
git submodule set-branch -b moodle41 -- report/o365channels

#NOT developed by ULPGC, but used as ULPGC, no other repo
#git submodule add git@bitbucket.org:moodleulpgc/moodle-gradingform_mcq.git     grade/grading/form/mcq
git submodule add git@bitbucket.org:moodleulpgc/moodle-atto_pastespecial.git   lib/editor/atto/plugins/pastespecial
git submodule set-branch -b moodle41 -- lib/editor/atto/plugins/pastespecial
git submodule add git@bitbucket.org:moodleulpgc/moodle-block_demostudent.git   blocks/demostudent
git submodule set-branch -b moodle41 -- blocks/demostudent
