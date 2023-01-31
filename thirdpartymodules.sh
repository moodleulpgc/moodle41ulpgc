#!/bin/bash
#Local plugins
git submodule add https://github.com/catalyst/moodle-local_csp.git                             local/csp
git submodule add https://github.com/moodleuulm/moodle-local_boostnavigation.git   local/boostnavigation
git submodule add https://github.com/elearning-univie/moodle-local_contactlist.git      local/contactlist
git submodule add https://github.com/kiklop74/moodle-local_dompdf.git                      local/dompdf
git submodule add https://github.com/ecampbell/moodle-local_glossary_wordimport     local/glossary_wordimport
#git submodule add https://github.com/PoetOS/moodle-local_metadata.git               local/metadata
#cd local/metadata && git checkout MOODLE_38_STABLE && cd ../../
#git submodule add https://github.com/moodleuulm/moodle-local_navbarplus.git         local/navbarplus
git submodule add https://github.com/developerck/moodle-local_qsearchbytype          local/qsearchbytype
git submodule add https://github.com/vadimonus/moodle-local_questionbulkupdate   local/questionbulkupdate
git submodule add https://github.com/moodleuulm/moodle-local_sandbox.git             local/sandbox
#git submodule add https://github.com/MorrisR2/moodle_local_searchbytags.git         local/searchbytags
git submodule add https://github.com/MorrisR2/moodle_local_searchquestions.git      local/searchquestions
git submodule add https://github.com/catalyst/moodle-local_sitenotice.git                   local/sitenotice
#git submodule add https://github.com/moodleuulm/moodle-local_staticpage.git         local/staticpage
git submodule add https://github.com/MorrisR2/moodle_local_unusedquestions.git     local/unusedquestions
git submodule add https://github.com/vfremaux/moodle-local_vflibs.git                      local/vflibs
cd local/vflibs && git checkout MOODLE_38_WORKING && cd ../../
git submodule add https://github.com/valentineus/moodle-webhooks.git                     local/webhooks
cd local/webhooks && git checkout v3.0.0-stable && cd ../../



#Office365 plugins
git submodule add https://github.com/Microsoft/moodle-local_o365.git                local/o365
git submodule add https://github.com/Microsoft/moodle-local_onenote.git             local/onenote
git submodule add https://github.com/Microsoft/moodle-auth_oidc.git                 auth/oidc
git submodule add https://github.com/Microsoft/moodle-block_microsoft.git           blocks/microsoft
git submodule add https://github.com/PoetOS/moodle-filter_oembed.git                filter/oembed
#branch MOODLE_38_STABLE
cd filter/oembed && git checkout MOODLE_38_STABLE && cd ../../
#git submodule add https://github.com/ndunand/moodle-filter_html5avtomp4.git         filter/html5avtomp4

git submodule add https://github.com/Microsoft/moodle-repository_office365.git      repository/office365
git submodule add https://github.com/Microsoft/moodle-assignsubmission_onenote.git  mod/assign/submission/onenote
git submodule add https://github.com/Microsoft/moodle-assignfeedback_onenote.git    mod/assign/feedback/onenote
#moodleulpgc o365
#git submodule add https://github.com/moodleulpgc/local_o365teams.git                local/o365teams
#git submodule add https://github.com/moodleulpgc/report_o365channels.git            report/o365channels

#Activities, mods
git submodule add https://github.com/jhoopes/moodle-mod_activequiz.git                  mod/activequiz
git submodule add https://github.com/bytebang/moodle-mod_activitymap                    mod/activitymap
git submodule add https://github.com/ctchanandy/moodle-mod_advmindmap.git               mod/advmindmap
git submodule add https://github.com/danmarsden/moodle-mod_attendance.git               mod/attendance
#cd mod/attendance && git checkout main && cd ../../
#git submodule add https://github.com/blindsidenetworks/moodle-mod_bigbluebuttonbn.git   mod/bigbluebuttonbn
git submodule add https://bitbucket.org/covuni/moodle-mod_bootstrapelements.git         mod/bootstrapelements
#Fix install.xml 
#git submodule add https://github.com/oohoo/moodle-mod_chairman.git                      mod/chairman
git submodule add https://github.com/davosmith/moodle-checklist.git                     mod/checklist
git submodule add https://github.com/open-lms-open-source/moodle-mod_collaborate.git    mod/collaborate
git submodule add https://github.com/markn86/moodle-mod_customcert.git                  mod/customcert
#cd mod/customcer && git checkout MOODLE_38_STABLE && cd ../../
git submodule add https://github.com/dasistwas/moodle-mod_datalynx.git                  mod/datalynx
#git submodule add https://github.com/troywilliams/moodle-mod_dialogue.git               mod/dialogue
git submodule add https://github.com/danmarsden/moodle-mod_dialogue.git                 mod/dialogue
git submodule add https://github.com/bdaloukas/moodle-mod_game.git                      mod/game
git submodule add https://github.com/projectestac/moodle-mod_geogebra.git               mod/geogebra
git submodule add https://github.com/rogerbaba/moodle-mod_groupselect.git               mod/groupselect
git submodule add https://github.com/gbateson/moodle-mod_hotpot.git                     mod/hotpot
git submodule add https://github.com/drachels/moodle-mod_hotquestion.git                mod/hotquestion
cd mod/hotquestion && git checkout MOODLE_402_STABLE && cd ../../
#git submodule add https://github.com/h5p/h5p-moodle-plugin.git                          mod/hvp
git submodule add https://github.com/netspotau/moodle-mod_lightboxgallery.git           mod/lightboxgallery
git submodule add https://github.com/Edunao/moodle-mod_masks.git                        mod/masks
git submodule add https://github.com/learnweb/moodle-mod_moodleoverflow.git             mod/moodleoverflow
git submodule add https://github.com/center-for-learning-management/moodle-mod_msteams  mod/msteams
git submodule add https://github.com/academic-moodle-cooperation/moodle-mod_offlinequiz.git  mod/offlinequiz
git submodule add https://github.com/rwthmoodle/moodle-mod_pdfannotator.git             mod/pdfannotator
git submodule add https://github.com/mudrd8mz/moodle-mod_poster.git                     mod/poster
git submodule add https://github.com/jmvedrine/moodle-mod_qcreate.git                   mod/qcreate
git submodule add https://github.com/learnweb/moodle-mod_ratingallocate.git             mod/ratingallocate
git submodule add https://github.com/bostelm/moodle-mod_scheduler.git                   mod/scheduler
#branch MOODLE_37_STABLE
cd mod/scheduler && git checkout MOODLE_37_STABLE && cd ../../

git submodule add https://github.com/yedidiaklein/moodle-mod_securepdf.git              mod/securepdf
git submodule add https://github.com/bozoh/moodle-mod_simplecertificate.git             mod/simplecertificate
#Branch MOODLE_35
cd mod/simplecertificate && git checkout MOODLE_35 && cd ../../

git submodule add https://github.com/frankkoch/moodle-mod_studentquiz.git               mod/studentquiz
git submodule add https://github.com/mudrd8mz/moodle-mod_subcourse.git                  mod/subcourse
git submodule add https://github.com/oohoo/moodle-mod_tab.git                           mod/tab
#Branch MOODLE_37_STABLE  fix manually to 2.4.1
cd mod/tab && git checkout MOODLE_37_STABLE && cd ../../

git submodule add https://github.com/vfremaux/moodle-mod_tracker.git                    mod/tracker
cd mod/tracker && git checkout MOODLE_39_WORKING && cd ../../
#Fix dependencies 
git submodule add https://github.com/turnitin/moodle-mod_turnitintooltwo.git            mod/turnitintooltwo
git submodule add https://github.com/jcastello46/moodle-mod_unedtrivial.git             mod/unedtrivial
git submodule add https://github.com/jcrodriguez-dis/moodle-mod_vpl.git                 mod/vpl
#branch v3.5.0
#cd mod/vpl && git checkout V3.5.0 && cd ../../

#mod Assign plugins
git submodule add  https://github.com/bostelm/moodle-assignfeedback_solutionsheet.git       mod/assign/feedback/solutionsheet
#git checkout MOODLE_37_STABLE
#cd mod/assign/feedback/solutionsheet && git checkout MOODLE_37_STABLE && cd ../../../../
git submodule add  https://github.com/geogebra/moodle-assignsubmission_geogebra.git         mod/assign/submission/geogebra
git submodule add  https://github.com/gjb2048/moodle-assignsubmission_gradereviews.git      mod/assign/submission/gradereviews
git submodule add  https://github.com/MaharaProject/moodle-assignsubmission_mahara.git      mod/assign/submission/mahara

#mod Book plugins
git submodule add  https://github.com/ecampbell/moodle-booktool_wordimport.git          mod/book/tool/wordimport

#mod Quiz plugins
git submodule add https://github.com/moodleou/moodle-quiz_answersheets.git              mod/quiz/report/answersheets
git submodule add https://github.com/bfh/moodle-quiz_archive.git                        mod/quiz/report/archive
git submodule add https://github.com/IITBombayWeb/moodle-quiz_downloadsubmissions.git   mod/quiz/report/downloadsubmissions
git submodule add https://github.com/moodleou/moodle-quiz_gradingstudents.git           mod/quiz/report/gradingstudents
git submodule add https://github.com/juacas/quizaccess_activatedelayedattempt.git       mod/quiz/accessrule/delayed
git submodule add https://github.com/moodleou/moodle-quizaccess_honestycheck.git        mod/quiz/accessrule/honestycheck
git submodule add https://github.com/timhunt/moodle-quizaccess_offlinemode              mod/quiz/accessrule/offlinemode
git submodule add https://github.com/catalyst/moodle-quizaccess_passgrade               mod/quiz/accessrule/passgrade
# no more needed git submodule add https://github.com/moodleou/moodle-quizaccess_safeexambrowser.git     mod/quiz/accessrule/safeexambrowser  
git submodule add https://github.com/daveyboond/moodle-quiz_mcq.git                     mod/quiz/report/mcq
#branch master
cd mod/quiz/report/mcq && git checkout MOODLE_27_STABLE && cd ../../../../
git submodule add https://github.com/danmarsden/moodle-quiz_randomsummary.git           mod/quiz/report/randomsummary
git submodule add https://github.com/maths/quiz_stack.git                               mod/quiz/report/stack  
git submodule add https://github.com/StudiUM/moodle-quiz_markspersection.git mod/quiz/report/markspersection

#Atto editor plugins
git submodule add https://github.com/geoffrowland/moodle-editor_atto-chemistry.git  lib/editor/atto/plugins/chemistry
git submodule add https://github.com/ucla/moodle-atto_chemrender.git                lib/editor/atto/plugins/chemrender
git submodule add https://github.com/dthies/moodle-atto_cloze.git                   lib/editor/atto/plugins/cloze

git submodule add https://github.com/damyon/moodle-atto_count.git                   lib/editor/atto/plugins/count
git submodule add https://github.com/moodleou/moodle-atto_embedquestion.git         lib/editor/atto/plugins/embedquestion
git submodule add https://github.com/andrewnicols/moodle-atto_fontsize              lib/editor/atto/plugins/fontsize
git submodule add https://github.com/dthies/moodle-atto_fullscreen                  lib/editor/atto/plugins/fullscreen
git submodule add https://github.com/justinhunt/moodle-atto_generico.git            lib/editor/atto/plugins/generico
git submodule add https://github.com/damyon/moodle-atto_hr.git                      lib/editor/atto/plugins/hr
git submodule add https://github.com/ndunand/moodle-atto_morebackcolors.git         lib/editor/atto/plugins/morebackcolors
git submodule add https://github.com/ndunand/moodle-atto_morefontcolors.git         lib/editor/atto/plugins/morefontcolors
#git submodule add https://github.com/cdsmith-umn/pastespecial.git                  lib/editor/atto/plugins/pastespecial #No more active
git submodule add https://github.com/dthies/moodle-atto_preview.git                 lib/editor/atto/plugins/preview
git submodule add https://github.com/Syxton/atto_sketch.git                         lib/editor/atto/plugins/sketch
git submodule add https://github.com/moodleuulm/moodle-atto_styles.git              lib/editor/atto/plugins/styles
git submodule add https://github.com/ecampbell/moodle-atto_wordimport.git           lib/editor/atto/plugins/wordimport
git submodule add https://github.com/moodleuulm/moodle-atto_styles.git              lib/editor/atto/plugins/styles
git submodule add https://github.com/enovation/moodle-atto_teamsmeeting.git         lib/editor/atto/plugins/teamsmeeting
git submodule add https://github.com/ecampbell/moodle-atto_wordimport.git           lib/editor/atto/plugins/wordimport

#Admin tools
git submodule add https://github.com/unikent/moodle-tool_adhoc.git                      admin/tool/adhoc
#Branch MOODLE_30_STABLE
#cd admin/tool/adhoc && git checkout MOODLE_30_STABLE && cd ../../../

git submodule add https://github.com/andreev-artem/moodle_admin_tool_advuserbulk.git    admin/tool/advuserbulk
git submodule add https://github.com/simoncoggins/moodle-tool_capexplorer.git           admin/tool/capexplorer
git submodule add https://github.com/moodleuulm/moodle-tool_coursefields.git            admin/tool/coursefields
git submodule add https://github.com/central-queensland-uni/moodle-tool_crawler.git     admin/tool/crawler
git submodule add https://github.com/moodleou/moodle-tool_editrolesbycap.git            admin/tool/editrolesbycap
#git submodule add https://github.com/moodlehq/moodle-tool_migratehvp2h5p.git            admin/tool/migratehvp2h5p
git submodule add https://github.com/catalyst/moodle-tool_lockstats.git                 admin/tool/lockstats
#Fix install.xml 
git submodule add https://github.com/ndunand/moodle-tool_mergeusers.git                 admin/tool/mergeusers
git submodule add https://github.com/ferranrecio/tool_navdb.git                         admin/tool/navdb
git submodule add https://github.com/catalyst/moodle-tool_objectfs.git                  admin/tool/objectfs
git submodule add https://github.com/moodleuulm/moodle-tool_opcache.git                 admin/tool/opcache
git submodule add https://github.com/mudrd8mz/moodle-tool_pluginskel.git                admin/tool/pluginskel
git submodule add https://github.com/agrowe/moodle-tool_rebuildcoursecache.git          admin/tool/rebuildcoursecache
git submodule add https://github.com/moodleuulm/moodle-tool_redis.git                   admin/tool/redis
git submodule add https://github.com/piersharding/moodle-tool_uploadcoursecategory.git  admin/tool/uploadcoursecategory
git submodule add https://github.com/grabs/moodle-tools_userdebug.git                   admin/tool/userdebug
cd admin/tool/userdebug && git checkout MOODLE_39_STABLE && cd ../../../

#Authentication methods 
git submodule add https://github.com/catalyst/moodle-auth_basic.git auth/basic
#required by admin/tool/crawler

#Availability conditions 
git submodule add https://github.com/tlock/moodle-availability_badge.git                availability/condition/badge
git submodule add https://github.com/moodleuulm/moodle-availability_cohort.git          availability/condition/cohort
git submodule add https://github.com/ewallah/moodle-availability_coursecompleted.git    availability/condition/coursecompleted
#cd availability/condition/coursecompleted && git checkout main && cd ../../../
git submodule add https://github.com/ewallah/moodle-availability_language.git           availability/condition/language
#cd availability/condition/language && git checkout main && cd ../../../
git submodule add https://github.com/MUDOTMY/moodle-availability_othercompleted.git     availability/condition/othercompleted
git submodule add https://github.com/timhunt/moodle-availability_quizquestion.git       availability/condition/quizquestion
git submodule add https://github.com/bdecentgmbh/moodle-availability_sectioncompleted.git   availability/condition/sectioncompleted

#Blocks
#git submodule add https://github.com/DigiDago/moodle-block_admin_presets.git            blocks/admin_presets
git submodule add https://github.com/jleyva/moodle-block_configurablereports.git  blocks/configurable_reports
#Branch MOODLE_36_STABLE
cd blocks/configurable_reports && git checkout MOODLE_36_STABLE && cd ../../
git submodule add https://github.com/moodleuulm/moodle-block_cohortspecifichtml.git     blocks/cohortspecifichtml
#git submodule add https://github.com/moodleuulm/moodle-block_course_overview_campus.git blocks/course_overview_campus
#Demostudent from ZIP, repo is a complete moodle install
#git submodule add https://github.com/ualberta-eclass/moodle-block_demostudent.git       blocks/demostudent
git submodule add https://github.com/Arantor/moodle-block_multiblock.git                blocks/multiblock
git submodule add https://bitbucket.org/covuni/moodle-block_news_slider.git             blocks/news_slider
git submodule add https://github.com/LafColITS/moodle-block_remote_courses.git          blocks/remote_courses
git submodule add https://github.com/cwarwicker/moodle-block_quick_user.git             blocks/quick_user
git submodule add https://github.com/andil-elearning/moodle-block_section.git           blocks/section
git submodule add https://github.com/deraadt/moodle-block_simple_clock.git              blocks/simple_clock

git submodule add https://github.com/mudrd8mz/moodle-block_todo.git                     blocks/todo

#Course formats
git submodule add https://github.com/brandaorodrigo/moodle-format_board.git         course/format/board
git submodule add https://github.com/brandaorodrigo/moodle-format_buttons.git       course/format/buttons
git submodule add https://github.com/marinaglancy/moodle-format_flexsections.git    course/format/flexsections  
git submodule add https://github.com/davidherney/moodle-format_menutopic.git        course/format/menutopic
git submodule add https://github.com/james-cnz/moodle-format_multitopic.git         course/format/multitopic
git submodule add https://github.com/davidherney/moodle-format_onetopic.git         course/format/onetopic  
git submodule add https://github.com/gjb2048/moodle-format_topcoll.git              course/format/topcoll

# Enrolment methods
git submodule add https://github.com/emeneo/moodle-enrol_apply.git          enrol/apply
git submodule add https://github.com/ndunand/moodle-enrol_attributes.git    enrol/attributes
git submodule add https://github.com/bobopinna/moodle-enrol_autoenrol.git   enrol/autoenrol
git submodule add https://github.com/moodlehq/moodle-enrol_groupsync.git    enrol/groupsync
git submodule add https://github.com/emeneo/moodle-enrol_waitlist.git       enrol/waitlist  

#Filters
#git submodule add https://github.com/eberhardt/moodle-filter_collapsible.git        filter/collapsible
git submodule add https://github.com/moodleou/moodle-filter_embedquestion.git       filter/embedquestion
#git submodule add https://github.com/ffhs/moodle-filter_fontawesome.git             filter/fontawesome
git submodule add https://github.com/justinhunt/moodle-filter_generico.git          filter/generico
#must be upgraded v 4.7 manually, change repo when available
git submodule add https://github.com/gthomas2/moodle-filter_imageopt.git            filter/imageopt
git submodule add https://edugit.org/nik/moodle-filter_jmol.git                     filter/jmol
#git submodule add https://github.com/geoffrowland/moodle-filter_jmol.git            filter/jmol
#git submodule add https://github.com/frederic-nevers/moodle-filter_multiembed.git   filter/multiembed
#git submodule add https://github.com/JosePFs/moodle-filter_tabs.git                 filter/tabs


#Advanced  Grading methods
#git submodule add  https://github.com/marcusgreen/moodle-gradingform_btec.git       grade/grading/form/btec
git submodule add  https://github.com/moodlerooms/moodle-gradingform_checklist.git  grade/grading/form/checklist
git submodule add  https://github.com/johndimopoulos/moodle-gradingform_erubric.git grade/grading/form/erubric

#Gradebook Exports 
git submodule add https://github.com/davosmith/moodle-grade_checklist.git grade/export/checklist

#Gradebook Reports 
git submodule add https://github.com/netspotau/moodle-gradereport_history.git       grade/report/history  
git submodule add https://github.com/dualcube/moodle-gradereport_quizanalytics.git  grade/report/quizanalytics 

#Plagiarism
git submodule add https://github.com/turnitin/moodle-plagiarism_turnitin.git            plagiarism/turnitin
#git submodule add https://github.com/turnitin/moodle-plagiarism_turnitinsim.git         plagiarism/turnitinsim
#git submodule add https://github.com/unicheck/moodle-plagiarism_unicheckcorp.git        plagiarism/unicheck
#git submodule add https://github.com/danmarsden/moodle-plagiarism_urkund.git            plagiarism/urkund

#Question behaviours
git submodule add https://github.com/trampgeek/moodle-qbehaviour_adaptive_adapted_for_coderunner.git    question/behaviour/adaptive_adapted_for_coderunner
git submodule add https://github.com/maths/moodle-qbehaviour_adaptivemultipart.git          question/behaviour/adaptivemultipart
git submodule add https://github.com/dthies/moodle-qbehaviour_deferredallnothing.git        question/behaviour/deferredallnothing
git submodule add https://github.com/timhunt/moodle-qbehaviour_deferredfeedbackexplain.git  question/behaviour/deferredfeedbackexplain
git submodule add https://github.com/moodleulpgc/moodle-qbehaviour_deferrednonegatives.git  question/behaviour/deferrednonegatives
git submodule add https://github.com/maths/moodle-qbehaviour_dfcbmexplicitvaildate.git      question/behaviour/dfcbmexplicitvaildate
git submodule add https://github.com/maths/moodle-qbehaviour_dfexplicitvaildate.git         question/behaviour/dfexplicitvaildate
git submodule add https://github.com/rezeau/moodle-qbehaviour_regexpadaptivewithhelp.git            question/behaviour/regexpadaptivewithhelp
git submodule add https://github.com/rezeau/moodle-qbehaviour_regexpadaptivewithhelpnopenalty.git   question/behaviour/regexpadaptivewithhelpnopenalty

#Question formats
git submodule add https://github.com/dthies/moodle-qformat_crossxml.git     question/format/crossxml
git submodule add https://github.com/jmvedrine/moodle-qformat_giftmedia.git question/format/giftmedia
git submodule add https://github.com/dthies/moodle-qformat_glossary.git     question/format/glossary
git submodule add https://github.com/dthies/moodle-qformat_h5p.git          question/format/h5p
git submodule add https://github.com/gbateson/moodle-qformat_hotpot.git     question/format/hotpot
git submodule add https://github.com/stemiwe/moodle-qformat_printout.git    question/format/printout
git submodule add https://github.com/maths/moodle-qformat_stack.git         question/format/stack

#Question types
git submodule add https://github.com/jmvedrine/moodle-qtype_algebra.git     question/type/algebra
git submodule add https://github.com/trampgeek/moodle-qtype_coderunner.git  question/type/coderunner
git submodule add https://github.com/moodleou/moodle-qtype_combined.git     question/type/combined
git submodule add https://github.com/villalon/qtype_conceptmap.git          question/type/conceptmap
git submodule add https://github.com/ethz-let/moodle-qtype_drawing          question/type/drawing
git submodule add https://github.com/cleblond/moodle-qtype_easyofischer     question/type/easyofischer
git submodule add https://github.com/cleblond/moodle-qtype_easyonewman.git  question/type/easyonewman
git submodule add https://github.com/gbateson/moodle-qtype_essayautograde.git question/type/essayautograde
git submodule add https://github.com/jmvedrine/moodle-qtype_formulas.git    question/type/formulas
git submodule add https://github.com/marcusgreen/moodle-qtype_gapfill.git   question/type/gapfill
git submodule add https://github.com/geogebra/moodle-qtype_geogebra.git     question/type/geogebra
git submodule add https://github.com/ethz-let/moodle-qtype_kprime           question/type/kprime
git submodule add https://github.com/moodleou/moodle-qtype_pmatch.git       question/type/pmatch
#need fix install.xml 
git submodule add https://github.com/timhunt/moodle-qtype_pmatchreverse     question/type/pmatchreverse
git submodule add https://github.com/ethz-let/qtype_mtf.git                 question/type/mtf
git submodule add https://github.com/gbateson/moodle-qtype_ordering.git     question/type/ordering
#Fix install.xml 
git submodule add https://github.com/moodleou/moodle-qtype_oumultiresponse.git  question/type/oumultiresponse
git submodule add https://github.com/rezeau/moodle-qtype_regexp.git         question/type/regexp
git submodule add https://github.com/moodleou/moodle-qtype_recordrtc.git     question/type/recordrtc
git submodule add https://github.com/maths/moodle-qtype_stack.git           question/type/stack
git submodule add https://github.com/StudiUM/moodle-qtype_tcs               question/type/tcs
git submodule add https://github.com/lechunche/type_calc_sheet.git          question/type/type_calc_sheet
#need to be upgraded v 2013061200 + backup ULPGC 
git submodule add https://github.com/moodleou/moodle-qtype_varnumeric.git       question/type/varnumeric
git submodule add https://github.com/moodleou/moodle-qtype_varnumericset.git    question/type/varnumericset
git submodule add https://github.com/moodleou/moodle-qtype_varnumunit.git       question/type/varnumunit

#Reports
git submodule add https://github.com/marcusgreen/moodle-report_advancedgrading.git      report/advancedgrading
git submodule add https://github.com/thepurpleblob/moodle-report_assign.git             report/assign
git submodule add https://github.com/mikasmart/benchmark.git                            report/benchmark
git submodule add https://github.com/pauln/moodle-report_componentgrades.git            report/componentgrades
git submodule add https://github.com/catalyst/moodle-report_coursesize.git              report/coursesize
git submodule add https://github.com/moodleou/moodle-report_customsql.git               report/customsql
git submodule add https://github.com/markheumueller/moodle-report_deviceanalytics.git   report/deviceanalytics
git submodule add https://github.com/moodleou/moodle-report_embedquestion.git           report/embedquestion
git submodule add https://github.com/vadimonus/moodle-report_extendedlog.git            report/extendedlog
git submodule add https://github.com/ctchanandy/moodle-report_forumgraph.git            report/forumgraph
git submodule add https://github.com/dired-ufla/moodle-report_modstats.git              report/modstats
git submodule add https://github.com/academic-moodle-cooperation/moodle-report_offlinequizcron.git report/offlinequizcron
git submodule add https://github.com/dired-ufla/moodle-report_questionstats.git         report/questionstats

#Repository
git submodule add https://github.com/geogebra/moodle-repository_geogebratube.git    repository/geogebratube 
git submodule add https://github.com/jpahullo/moodle-repository_searchable.git      repository/searchable

#Web Services
git submodule add https://github.com/moodlehq/moodle-webservice_xmlrpc.git      webservice/xmlrpc

#Themes
#git submodule add https://gitlab.com/jezhops/moodle-theme_adaptable.git             theme/adaptable
git submodule add https://github.com/moodleuulm/moodle-theme_boost_campus.git       theme/boost_campus
git submodule add https://github.com/microsoft/moodle-theme_boost_o365teams.git     theme/boost_o365teams
git submodule add https://github.com/willianmano/moodle-theme_moove.git             theme/moove
git submodule add https://github.com/dbnschools/moodle-theme_learnr.git             theme/learnr
git submodule add https://github.com/moodle-an-hochschulen/moodle-theme_boost_union.git     theme/boost_union
#git submodule add 
