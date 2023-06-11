<?php
/**
 * ULPGC specific customizations lang strings fro ulpgccore
 *
 * @package    local
 * @subpackage ulpgccore
 * @copyright  2012 Enrique Castro, ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'ULPGC core mods';
$string['ulpgcsettings'] = 'ULPGC Settings';
$string['configsettings'] = 'ULPGC Configuration Settings';
$string['coresettings'] = 'ULPGC Core settings';
$string['gradesettings'] = 'Grade settings';

$string['sitesettings'] = 'Site settings';
$string['userssettings'] = 'User details settings';
$string['uisettings'] = 'User interface settings';

$string['modssettings'] = 'ULPGC Modules &amp; Blocks settings';
$string['fullsitename'] = 'Nombre del sitio';
$string['urlsiteroot'] = 'Site root url';
$string['mymessage'] = 'Mensaje a mostrar en MyMoodle';
$string['reposettings'] = 'Internal Moodle ULPGC repositories';
$string['explainreposettings'] = 'Directories pending from site course dir';
$string['repomanuals'] = 'Manuals';
$string['explainrepomanuals'] = 'Directory to store course manuals (used by sitefile)';
$string['repoexams'] = 'Exams';
$string['explainrepoexams'] = 'Directory to store exams PDFs (used by TF exams application)';
$string['examsettings'] = 'Exams settings ';
$string['explainexamsettings'] = 'Diverse settings that configure TF exams application behavior';
$string['examinadores'] = 'Examiners course';
$string['explainexaminadores'] = 'courseid for Sala de Examninadores course';
$string['forumsettings'] = 'ULPGC additional forum settings';
$string['threadmaxposts'] = 'Thread post limit';
$string['explainthreadmaxposts'] = 'Maximum numbre of posts allowed in a thread';
$string['coursestartdate'] = 'Courses start date';
$string['explaincoursestartdate'] = 'If set, ULPGC admin tools can used this date as a reference.<br>
Use ISO 8601 format YYYY-MM-DAY or any other format parseable by PHP <i>strtotime</i> function ';

$string['recentactivity'] = 'Enable custom recent activity';
$string['explainrecentactivity'] = 'If enabled, the system will try to record
new activity changes and items requiring user attention and mark them in course page.';
$string['adminmods'] = 'Enable admin restrictions';
$string['explainadminmods'] = 'If enabled, any module can be declared as un-erasable, or un-hideable (or both) so the users may not alter them.';
$string['advancedgrades'] = 'Enable advanced grades';
$string['explainadvancedgrades'] = 'If enabled, custom ULPGC grade aggregations & interface advancemente will be available.';

$string['gradebooklocking'] = 'Enable gradebook locking';
$string['explaingradebooklocking'] = 'If enabled, custom ULPGC grade lockings on category grades & aggregations will be enforced.';
$string['gradebooklockingdepth'] = 'Gradebook locking depth';
$string['explainlockingdepth'] = 'Grade categories up to this depth will be locked for changes. 
Only users with Manage capability will be able to move or modify name and aggregation settings. ';
$string['gradebooknocal'] = 'Grade category for Non calificated';
$string['explaingradebooknocal'] = 'If non-empty then the grade category with this idnumber will be the default one 
to store grade items of non-categorized items. That\'s it, non categorized items are grouped into this grade category.';
$string['locknameword'] = 'Grade category locking word';
$string['explainlocknameword'] = 'If this word is present in Info field then the Grade category is locked and its name and idnumber cannot be modified, 
except with category edit permissions. ';
$string['lockaggword'] = 'Aggregation locking word';
$string['explainlockaggword'] = 'If this word is present in Info field then the Grade category aggregation is locked and cannot be modified, 
except with category edit permissions.';

$string['updateldap'] = 'Enable update LDAP';
$string['explainupdateldap'] = 'If enabled, when creating users or editing user accounts the data will be forwarded to ULPGC LDAP.';
$string['annuality'] = 'Annuality';
$string['explainannuality'] = 'The course years for this annuality. Must be a six digit string. first year in 4-digit form followed by second year in 2-digit.  eg. 201213  ';
$string['nonlistedroles'] = 'Non listed roles';
$string['explainnonlistedroles'] = 'Those roles marked here will not be listed on Other roles, on cases or role assigned without course enrol.';

//capabilities
$string['ulpgccore:manage'] = 'ULPGC site management';
$string['ulpgccore:categoryreview'] = 'Category review';
$string['ulpgccore:gradecategoryedit'] = 'Grade category edit';
$string['ulpgccore:editsection0'] = 'Edit course section 0';
$string['ulpgccore:managesection0'] = 'Manage mods in course section 0';
$string['ulpgccore:modedit'] = 'Edit module settings';
$string['ulpgccore:moddelete'] = 'Delete a module instance';
$string['ulpgccore:modmove'] = 'Move a module instance';
$string['ulpgccore:modduplicate'] = 'Duplicate a module instance';
$string['ulpgccore:modpermissions'] = 'Set roles with permission in module';
$string['ulpgccore:modroles'] = 'Assign roles to users in module';
$string['ulpgccore:upload'] = 'Upload files or manuals';

//exams
$string['examssitesmessage'] = 'Texto a mostrar en la pantalla de Selecci&oacute;n de Ex&aacute;menes';
$string['examssitesselect'] = 'D&iacute;as seleccionar';
$string['examssitesbloqueo'] = 'D&iacute;as bloqueo';
$string['explainexamssitesselect'] = 'Se puede elegir el lugar y fecha del examen hasta estos d&iacute;as antes';
$string['explainexamssitesbloqueo'] = 'Estos d&iacute;as antes del examen, si est&aacute; elegido no se puede cambiar';
$string['examssitesextra1dia'] = 'Dia';
$string['explainexamssitesextra1dia'] = 'Fecha limite seleccion examen Extra-1: dia';
$string['examssitesextra1mes'] = 'Mes';
$string['explainexamssitesextra1mes'] = 'Fecha limite seleccion examen Extra-1: mes';
$string['examsupdate'] = 'Update exams glossary';
$string['explainexamsupdate'] = 'Activate a cron task to update exams glossary from exams PDFs appearing in exam repository';

$string['applytemplate'] = 'Apply course template';
$string['applyconfig'] = 'Apply course config';
$string['uploadcoursesulpgc'] = 'Crear cursos ULPGC';
$string['uploadcoursescsv'] = 'Crear cursos de texto CSV';
$string['deletemod'] = 'Delete module instances';
$string['fullbackup'] = 'Full Backup';
$string['fullrestore'] = 'Full Restore';
$string['consultar_ldap'] = 'Query LDAP';
$string['usuarios_ulpgc'] = 'ULPGC users';

$string['userformpublic'] = 'Public view fields';
$string['userformhidden'] = 'Restricted view fields. Only institution staff can see this information';
$string['userformwarning'] = 'The changes will take effect only in this platform. <br>
These data will not alter any institutional or MiULPGC data, nor change settings in other sections.';

$string['aggregateulpgcsum'] = 'Sum of grades (ULPGC-TF rules)';
$string['aggregateulpgcmeanactv'] = 'Mean of grades (ULPGC-TF activity rules)';
$string['aggregateulpgcmeanexam'] = 'Mean of grades (ULPGC-TF exam rules)';
$string['aggregateulpgcmeanconvo'] = 'Final Mean of grades (ULPGC-TF)';
$string['aggregateulpgfinal'] = 'Final grade of convocatory (ULPGC)';
$string['aggregateulpgnone'] = 'No grade (ULPGC)';

// course
$string['courseediton'] = 'Turn course editing on';
$string['courseeditoff'] = 'Turn course editing off';
$string['editsettings'] = 'Edit {$a} settings';
$string['adminmoduleoptions'] = 'Admin restriction options';
$string['adminmoduleexplain'] = 'If checked, the module is marked as unerasable.';
$string['unhideable'] = 'Non-hideable';
$string['unerasable'] = 'Non-erasable';
$string['unmovable'] = 'Non-movable';
$string['both'] = 'Both';
$string['all'] = 'All';
$string['unerasablewarning'] = 'This module instance is protected. Cannot be deleted! ';
$string['unhideablewarning'] = 'This module instance is protected. Cannot be hidden! ';
$string['newactivity'] = '{$a} new items';
$string['news'] = 'New course activity';
$string['ungradedactivity'] = '{$a} items need grading';
$string['usersexportcsv'] = 'Export as CSV';
$string['exportusers'] = 'Export users';

//backup
$string['rootsettinggroups'] = 'Include Groups';
$string['rootsettinggroupings'] = 'Include Groupings';
$string['rootsettingadminmods'] = 'Restore admin modules';
$string['rootsettingcoursedata'] = 'Include course ULPGC data';

//filters
$string['notrole'] = 'Not';
$string['courselist'] = 'use as course list';
$string['inlist'] = 'is in list';
$string['notinlist'] = 'not in list';
$string['userfield'] = 'custom user field';
$string['userfieldlabel'] = 'User field Label';
$string['userfieldlabelnovalue'] = 'label';

// centers
$string['faculty'] = 'Faculty';
$string['degree'] = 'Degree';
$string['department'] = 'Department';

// term
$string['term'] = 'Semestre';
$string['term0'] = 'Anuals';
$string['term1'] = 'First term';
$string['term2'] = 'Second term';
$string['term3'] = 'Both terms';

// admin
$string['customnavnodes'] = 'Custom nav nodes';
$string['explaincustomnavnodes'] = 'Custom links to appear in the nav drawer, in the first position. To access other platforms.';

$string['privatedetails'] = 'Private details';
$string['showuserdetails'] = 'Show user details';
$string['showuserdetails_desc'] = 'When displaying user profile data, these fields may be shown in addition to their full name.
The fields are only shown to users who have the moodle/course:viewhiddenuserfields capability; by default, teachers and managers.';

/// START DETAILED SCALE GRADES
$string['scaledisplaymode'] = 'Scale display type';
$string['scaledisplaymode_help'] = 'Detailed type displays the number of times a participant has obtained each of the scale values ​​in forum type and glossary type activities (these are the types of activities that allow a participant to have multiple grades at the same time). For example, if the scale is "Bad,Normal,Fine" a value "0/3/1" indicates that a participant has obtained the Bad value 0 times, the Normal value 3 times and the Fine value 1 time';
$string['normalscaledisplay'] = 'Normal';
$string['detailedscaledisplay'] = 'Detailed';
$string['configscaledisplaymode'] = 'Detailed type displays the number of times a participant has obtained each of the scale values ​​in forum type and glossary type activities (these are the types of activities that allow a participant to have multiple grades at the same time). For example, if the scale is \"Bad,Normal,Fine\" a value \"0/3/1\" indicates that a participant has obtained the Bad value 0 times, the Normal value 3 times and the Fine value 1 time';

$string['aim'] = 'Zip code';

$string['exportuserselector'] = 'Select users to export';
$string['exportdataselector'] = 'Select data to collect for each user';

$string['exportusergroupmember'] = 'Group member';
$string['exportusersingroup'] = 'Members of group';
$string['exportusersingroup_help'] = 'If specified then only users that are members of this particular group will be exported.

 * Any: all users, whether members of groups or not 
 * Group member: all users that are members of at least one group.
 * None: only export users that are not members of any group, users that has no group membership.
';
$string['exportgroupsgrouping'] = 'Members of Grouping';
$string['exportgroupsgrouping_help'] = 'If specified, only users that are members of any groups 
of the the selected grouping will be exported.

 * None: Exported only users that are members of groups that happen to NOT belong to ayy grouping.
 * Any: No limitation by grouping.
';
$string['exportuserroles'] = 'With roles';
$string['exportuserroles_help'] = 'Only enrolled users with any of these roles will be include in exportation.';
$string['exportincludeusergroups'] = 'Include groups membership';
$string['exportincludeusergroups_help'] = 'If checked, the groups each user is member of will we exported. ';
$string['exportincludeuserroles'] = 'Include user roles';
$string['exportincludeuserroles_help'] = 'If checked, a column will hold the roles each user has in course. ';
$string['exportonlygrouping'] = 'Groups of Grouping';
$string['exportonlygrouping_help'] = 'If specified, only membership to groups of the the selected grouping will be exported.';
$string['groupingsameabove'] = 'Same as above';
$string['exportusersdetails'] = 'User details to export';
$string['exportusersdetails_help'] = 'Extra user data to include in exportation. 

firstname, lastname and idnumber will be allways included for all users.';
$string['exportsort'] = 'User order';
$string['exportsort_help'] = 'The field employed for sorting users in listing.';

$string['exportfileselector'] = 'Select export file name & format';
$string['exportformatselector'] = 'Export format';
$string['exportdownload'] = 'Download';
$string['exportfilename'] = 'File name (no ext)';
$string['exportfilename_help'] = 'Name for the file that will hold the exported data. 

The extension will be determined by the export format.';
$string['errorheaderssent'] = 'Errors on output, the file cannot be generated.';

$string['shortennavbar'] = 'Shorter navbar strings';
$string['explainshortennavbar'] = 'Makes abbreviations and shortens breadcrumb & navbar strings';
$string['shortenitems'] = '
Escuela de Arquitectura,EA
Escuela de Ingeniería Informática,EII
Escuela de Ingenierias Industriales y Civiles,EIIC
Escuela de Ingeniería de Telecomunicación y Electrónica,EITE
Facultad de Ciencias de la Actividad Física y el Deporte,FCAFD
Facultad de Ciencias de la Educación,FCCE
Facultad de Ciencias de La Salud,FCCS
Facultad de Ciencias de la Salud - Sección Fuerteventura,FCCS-FV
Facultad de Ciencias de la Salud - Sección Lanzarote,FCCS-LZ
Facultad de Ciencias del Mar,FCM
Facultad de Ciencias Jurídicas,FCCJJ
Facultad de Economia, Empresa y Turismo,FEET
Facultad de Filología,FF
Facultad de Geografía e Historia,FGH
Facultad de Traducción e Interpretación,FTI
Facultad de Veterinaria,FVet
Escuela Universitaria Adscrita de Turismo de Lanzarote,EUTL
Escuela de Doctorado,ED
Instituto Universitario de Sistemas Inteligentes y Aplicaciones Numéricas en Ingeniería,SIANI
Instituto Universitario de Microelectrónica Aplicada,IUMA
Instituto Universitario de Sanidad Animal y Seguridad Alimentaria,IUSA
Instituto Universitario de Turismo y Desarrollo Económico Sostenible,TIDES
Instituto Universitario para el Desarrollo Tecnológico y la Innovación en Comunicaciones,IDETIC
Cursos Armonización de Conocimientos,CAC
Escuela de,E.
Escuela Universitaria,E.U.
Experto Universitario en,E.U.
Facultad de,F.
Formación Universitaria Especializada de,FUE
Instituto Universitario de,I.U.
Programa de doble titulación:,Doble
Programa de Doctorado en,P.D.
Programas Formativos Especiales,PFE
Maestrias y Expertos,MyE
Diploma De Estudios,D.E.
Diploma de Estudios,D.E.
Máster Universitario en,M.U.
Grado en,G.
Universidad de las Palmas de Gran Canaria,ULPGC
Universidad,U.
Por ,por 
De ,de  
Peritia Et Doctrina,PeD
Peritia et Doctrina,PeD
';
$string['enableadvchooser'] = 'Enable advanced chooser';
$string['explainenableadvchooser'] = 'If enabled,
the javascript mod chooser will show modules grouped in categories ';
$string['sortadvchooser'] = 'Sort items in Chooser';
$string['explainsortadvchooser'] = 'If active,
the javascript mod chooser will show modules sorted in the same order as listed here within each category. ';
$string['actv_communication'] = 'Communication';
$string['actv_collaboration'] = 'Collaboration';
$string['actv_adminwork'] = 'Management';
$string['actv_assessment'] = 'Assessment';
$string['actv_structured'] = 'Structured activities';
$string['actv_games'] = 'Games';
$string['actv_other'] = 'Other';
$string['res_files'] = 'Files';
$string['res_text'] = 'Text';
$string['res_structured'] = 'Structured resources';
$string['explainmodsgroup'] = 'Each module in a line. The order is how mods will appear in chooser.';

$string['croncheck'] = 'Cron delay';
$string['explaincroncheck'] = 'Check cron delays. If time from last cron is longer that this value, in hours, an email is emitted.';
$string['croncheckemail'] = 'Cron check emails';
$string['explaincroncheckemail'] = 'If not empty, the cron check is performed and sent to emails in this list (comma separated).';

$string['footersettings'] = 'Footer settings';
$string['footerblock1'] = 'Footer block 1';
$string['footerblock2'] = 'Footer block 2';
$string['footerblock3'] = 'Footer block 3';
$string['footerblock_desc'] = 'An structured text, with h3 and p tags to list items.';

$string['alerts'] = 'Global alerts';
$string['showglobalalert'] = 'Show alerts';
$string['explainshowglobalalert'] = 'If enabled, the message below will be displayed in header for matching pages.';
$string['alertstart'] = 'Starting on';
$string['explainalertstart'] = 'Date to start showing alert message. YYYY-mm-dd format. ';
$string['alertend'] = 'Ending on';
$string['explainalertend'] = 'Date to end showing alert message. YYYY-mm-dd format. ';
$string['alertroles'] = 'Show for roles';
$string['explainalertroles'] = 'The message will be displayed to users with any of this roles in course context.';
$string['alerttype'] = 'Alert type';
$string['explainalerttype'] = 'One of the standard Bootstrap alert classes';
$string['alertdismiss'] = 'Dismiss button';
$string['explainalertdismiss'] = 'If enabled, a button is shown to permanently dismiss the message by setting a user preference.';
$string['alertmessage'] = 'Alert message';
$string['danger'] = 'Danger';
$string['dismissalert'] = 'Permanent dismiss';
$string['mailednotviewed'] = 'Forum mailed NOT viewed';
$string['explainmailednotviewed'] = 'If checked, then mailed post will not be treated as viewed (as moodle does), 
and forum post mailed will be presented as unread when first displaying the forum after post mailed.';
$string['checkcrontrask'] = 'Check cron is running task';
$string['blockalert'] = 'Right panel open on alert';
$string['explainblockalert'] = 'When enabled, if some right panel blocks has content that must be seen, 
the right panel is set open at beginning';
$string['activityindentation'] = 'Indent Activiies/Resources';
$string['activityindentation_help'] = 'If enabled, activities or resources in the course page may be indented horizontally.';
$string['activityindentationenabled'] = 'Enable activity indentation';
$string['explainactivityindentationenabled'] = 'If enabled, then activity cards in course page may be indentated, 
and edit menu will show indent tools.';
$string['eventroleimported'] = 'Role imported from ULPGC preset';
$string['eventpresetimported'] = 'Preset imported from file';
$string['eventpresetexported'] = 'Preset exported to file';
$string['customroles'] = 'Manage custom roles';
$string['rolecapscheck'] = 'Check capabilities for custom roles';
$string['presetrolestable'] = 'ULPGC preset roles';
$string['myindexcleaned'] = 'My Index user pages cleaned (no custom blocks)';
$string['presetblockstable'] = 'ULPGC preset blocks';
$string['otherrolestable'] = 'Other custom roles';
$string['otherblockstable'] = 'Other sticky blocks';
$string['presetname'] = 'Preset file name';
$string['presetdateload'] = 'Date loaded';
$string['presetdatechanged'] = 'Date changed';
$string['presetdel'] = 'Delete preset';
$string['confirmpresetdelete'] = 'About to <strong>delete</strong> file containing item definition & settings from ULPGC preset "{$a}".
<br />Confirm to proceed? ';
$string['presetimport'] = 'Import preset';
$string['presetreset'] = 'Reset item from preset';
$string['confirmpresetimport'] = 'About to import item definition & settings from ULPGC preset "{$a}".
<br />Confirm to proceed? ';
$string['presetexport'] = 'Save data as preset';
$string['confirmpresetexport'] = 'About to export definition & settings as ULPGC preset "{$a}".
<br />Confirm to proceed? ';
$string['presetsinstall'] = 'Install presets';
$string['sitepreset'] = 'Site preset';
$string['explainsitepreset'] = 'An XML file that contains default values for site settings specific for this platform. <br />
Will be used to check & keep configuration changes. ';
$string['rolepresets'] = 'Default Additional Roles';
$string['explainrolepresets'] = 'List or role preset names that the system will try to install & check.
 A comma-separated list of shortnames. ';
$string['blockpresets'] = 'Default Sticky Blocks ';
$string['explainblockpresets'] = 'List or Block preset names that the system will try to install & check.
A comma-separated list of shortnames. ';
$string['profilefieldpresets'] = 'Custom profile fields';
$string['explainprofilefieldpresets'] = 'List of Custom profile fields that the system will try to install & check.
A comma-separated list of shortnames. ';
$string['hidepicture'] = 'Hide user picture';
$string['hidepicture_desc'] = 'If enabled, user pictures will be hidden for users without viewhiddenfields capability,
unless the user allows acces by writing any text into imagealt property.';
$string['hidepicwarning'] = 'Image description & control of image visibility';
$string['hidepicwarning_help'] = 'You picture will be visible only for Teachers in your courses
(or other Administrative roles with access to course). Other students won\'t see your picture unless you grant explicit permission
by writing any text into this alternate text box.
If this box is empty, only teachers will see you picture across all the platform pages and activities. ';
$string['cpostal'] = 'Postal code';
$string['preseterrorxml'] = 'Error parsing XML / Required fields missing.';
$string['rolepreseterrorarchetype'] = 'Error parsing XML';
$string['presetloaded'] = 'Loaded {$a} preset file.';
$string['presetloaderror'] = 'Error on loading {$a} preset file.';
$string['presetsaved'] = 'Saved preset file "{$a}".';
$string['presetsaveerror'] = 'Error on saving preset file "{$a}".';
$string['presetremoved'] = 'Deleted preset file "{$a}".';
$string['presetremoveerror'] = 'Error on deleting preset file "{$a}".  Preset file NOT removed.';
$string['referencecourse'] = 'Reference course';
$string['explainreferencecourse'] = 'The shortname of a course to use as template or reference
(for looking for default course settings or modules).';
$string['maintemplate'] = 'Main template';
$string['explainmaintemplate'] = 'An MBZ file containing a complete course to use as default template.
If configured and installed, the shortname of this course will be used as referencce course.';
$string['archivereuse'] = 'Archive/Reuse';
$string['rolepermissions'] = 'Permissions';
$string['participants'] = 'Participants list';
$string['blocksubpage'] = 'sub: {$a}';
$string['blocknewregion'] = 'Here: {$a}';
$string['blockregionchanged'] = 'Block Region in preset is NOT known: set to default region "{$a}".';
$string['bsetounilabel'] = 'BSE to Unilabel';
$string['bsetounilabel_desc'] = 'BSE to Unilabel adds a new Unilabel mod for each Bootstrapelements instance.';
$string['unilabeladded'] = 'Unilabel added in course {$a->course} with name {$a->name}.';
$string['unilabeladdederror'] = 'Unilabel adding error in course {$a->course} with name {$a->name}.';

$string['check_customroles'] = 'Custom roles';
$string['check_customroles_notset'] = 'Not defined custom roles setting';
$string['check_customroles_missing'] = 'Some required custom role is missing';
$string['check_customroles_missinglist'] = 
$string['check_customroles_details'] = 'Review custom roles';
