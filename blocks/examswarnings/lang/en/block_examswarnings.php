<?PHP
      // block_examswarnings.php - created  © E. Castro 2008

$string['pluginname'] = 'Avisos de Exámenes';
$string['blockname'] = 'Exámenes Teleformación';
$string['blocktitle'] = 'Exámenes Teleformación';
$string['privacy:metadata'] = 'The Avisos de Exámenes block only shows data stored in other locations.';
$string['primaryreg'] = 'Registro de Exámenes principal';
$string['primaryreg_help'] = 'Registro de Exámenes principal';
$string['examswarnings:addinstance'] = 'Añadir un bloque Exámenes TF';
$string['examswarnings:myaddinstance'] = 'Añadir un bloque Exámenes TF a la página personal';
$string['examswarnings:manage'] = 'Gestionar Exámenes Teleformación';
$string['examswarnings:view'] = 'Ver Exámenes Teleformación';
$string['examswarnings:select'] = 'Seleccionar una fecha de examen';
$string['examswarnings:supervise'] = 'Supervisar exámenes';
$string['messageprovider:exam_staff_reminders'] = 'Exam reminders for room staff';
$string['messageprovider:exam_student_reminders'] = 'Exam reminders for students';
$string['messageprovider:exam_student_warnings'] = 'Non-booked exam warnings for students';
$string['messageprovider:exam_teacher_reminders'] = 'Exam reminders for teachers';

$string['examidnumber'] = 'Exam idnumber';
$string['examidnumber_help'] = 'Grade item idnumber for an activity holding Exam grade.';
$string['examlocations'] = 'Lugares';
$string['examcall'] = 'Convocatoria';
$string['examcalls'] = 'Convocatorias';
$string['examdates'] = 'Fechas';
$string['examadd'] = 'Añadir examen';
$string['exam_management'] = 'Gestión de exámenes';
$string['assigndate'] = 'Asignar examen';

// error messages
$string['onlyforteachers'] = 'Sólo los profesores pueden usar esta página';

// cadenas de admin_ulpgc (admin settings)

$string['repoexams'] = 'Exams';
$string['explainrepoexams'] = 'Directory to store exams PDFs (used by TF exams application)';
$string['examsettings'] = 'Exams settings ';
$string['explainexamsettings'] = 'Diverse settings that configure TF exams application behavior';
$string['examiners'] = 'Examiners course';
$string['explainexaminers'] = 'courseid for Sala de Examninadores course';
//exams
$string['examssitesmessage'] = 'Texto a mostrar en la pantalla de Selecci&oacute;n de Ex&aacute;menes';
$string['examssitesselect'] = 'D&iacute;as seleccionar';
$string['examssitesbloqueo'] = 'D&iacute;as bloqueo';
$string['examssiteswarning'] = 'D&iacute;as avisos';
$string['explainexamssitesselect'] = 'Se puede elegir el lugar y fecha del examen hasta estos d&iacute;as antes';
$string['explainexamssitesbloqueo'] = 'Estos d&iacute;as antes del examen, si est&aacute; elegido no se puede cambiar';
$string['examssitesextra1dia'] = 'Dia';
$string['explainexamssitesextra1dia'] = 'Fecha limite selección examen Extra-1: dia';
$string['examssitesextra1mes'] = 'Mes';
$string['explainexamssitesextra1mes'] = 'Fecha limite selección examen Extra-1: mes';

$string['examsupdate'] = 'Update Exams glossaries';
$string['explainexamsupdate'] = 'Id enabled, cron will include a routine to search for newly generated exam archives and create entries into Exams glossaries. ';
$string['examsglossary'] = 'ID for Exams Glossaries';
$string['explainexamsglossary'] = 'A search string to identify Exams Glossaries. This string must appear in cm.idnumber for the glossary.';
$string['annuality'] = 'Annuality';
$string['annuality_help'] = 'The annual period for exams, biannual, in short form e.g. 201011 ';
$string['validcategories'] = 'Valid categories';
$string['explainvalidcategories'] = 'Only courses in the the selected categories will be included in Exams processing';

$string['convo-0'] = 'Primer semestre';
$string['convo-1'] = 'Primer semestre';
$string['convo-2'] = 'Segundo semestre';
$string['convo-3'] = 'Extraordinaria 1';
$string['convo-4'] = 'Extraordinaria 2';
$string['convo-5'] = 'Extraordinaria 3';

$string['term'] = 'Semestre';
$string['term-a'] = 'Anual';
$string['term-c1'] = 'Primer semestre';
$string['term-c2'] = 'Segundo semestre';
$string['term-c3'] = 'Ambos semestres';
$string['term-c4'] = 'Ambos semestres';
$string['term03'] = 'Ambos semestres';
$string['term04'] = 'Cuarto semestre';
$string['warnings'] = 'Avisos de Exámenes';
$string['warningduedate'] = '¡Apuntarse a examen!';
$string['warningupcoming'] = 'Próximos exámenes';
$string['roomcallupcoming'] = 'Room Staff';
$string['warningduedate'] = 'Book {$a} exams!';
$string['warningupcoming'] = '{$a} Upcoming exams';
$string['roomcallupcoming'] = '{$a} Rooms with exam';
$string['configurewarnings'] = 'Configure alerts';
$string['defaultsettings'] = 'Default parameters in children blocks';
$string['defaultsettings_help'] = 'These parameteres will set the initial, default, value for config items in new configured instances of this block type';
$string['enablereminders'] = 'Enable exam reminders';
$string['enablereminders_help'] = 'If active, an email message will be sent to all teachers of courses with an exam scheduled in next days.';
$string['reminderdays'] = 'Days behorehand';
$string['reminderdays_help'] = 'How many days before the scheduled exam date the reminder will be issued';
$string['reminderroles'] = 'Reminder roles';
$string['reminderroles_help'] = 'Roles of users to send teacher reminders.';
$string['remindermessage'] = 'Reminder message';
$string['remindermessage_help'] = 'Text of the email message. The placeholders %%roomname%%, %%roomidnumber%, %%date%%, %%examlist%% may be used to substitute for actual info ';
$string['examremindersubject'] = 'TF Exam reminder. Course: {$a}. ';

$string['enableroomcalls'] = 'Enable room staff reminders';
$string['enableroomcalls_help'] = 'If active, an email message will be sent to all Staff assigned to a room in an exam session scheduled in next days.';
$string['roomcalldays'] = 'Days behorehand';
$string['roomcalldays_help'] = 'How many days before the scheduled exam session date the Room reminder will be issued';
$string['roomcallroles'] = 'Staff Reminder roles';
$string['roomcallroles_help'] = 'Roles of users to send room staff reminders.';
$string['roomcallmessage'] = 'Staff Reminder message';
$string['roomcallmessage_help'] = 'Text of the email message. The placeholders %%course%% and %%date%% may be used to substitute for actual info ';
$string['roomcallsubject'] = 'TF Staff reminder. Room: {$a}. ';

$string['enablewarnings'] = 'Enable exam warnings';
$string['enablewarnings_help'] = 'If active, an email message will be sent to all students of courses with an exam scheduled in next days.';
$string['warningdays'] = 'Days behorehand for warnings';
$string['warningdays_help'] = 'How many days before the scheduled exam date the warning will be issued';
$string['warningdaysextra'] = 'Days behorehand for Extra';
$string['warningdaysextra_help'] = 'How many days before the scheduled exam date the warning will be issued, in case on Extraordinary calls';
$string['examconfirmdays'] = 'Days behorehand for reminders';
$string['examconfirmdays_help'] = 'How many days before the scheduled exam date the reminder for users with booked exams will be issued';
$string['warningroles'] = 'Warnings roles';
$string['warningroles_help'] = 'Roles of users to send student warnings.';
$string['headerreminders'] = 'Reminders to teachers';
$string['headerroomcalls'] = 'Reminders to staff';
$string['headerwarnings'] = 'Warnings to students';
$string['headercontrol'] = 'Control';

$string['warningsubject'] = 'TF Exam warning: {$a}. ';
$string['warningmessage'] = 'Warning message';
$string['warningmessage_help'] = 'Text of the email message. The placeholders %%course%% and %%date%% may be used to substitute for actual info ';
$string['confirmsubject'] = 'TF Exam confirmation: {$a}. ';
$string['confirmmessage'] = 'Warning message';
$string['confirmmessage_help'] = 'Text of the email message. The placeholders %%course%%, %%place%%, %%registered%% and %%date%% may be used to substitute for actual info ';

$string['extrarules'] = 'Enable Extra rules';
$string['extrarules_help'] = 'If activated, extra rules will be applied to select exams and users. ';
$string['examreminderfrom'] = 'TF Exams reminder';
$string['remindersenderror'] = 'Fail';
$string['controlemail'] = 'Control email';
$string['controlemail_help'] = 'If set, an email message will be sent to this address with a list of all reminders issued to teachers.';
$string['controlmailsubject'] = 'TF Exam reminder for {$a} ';
$string['controlmailtxt'] = '{$a->num} Exam reminders for the exams scheduled for {$a->date} have been sent.';
$string['controlmailhtml'] = '{$a->num} Exam reminders for the exams scheduled for {$a->date} have been sent.';
$string['sendstudentreminders'] = 'Send confirmation to students with exams';
$string['sendstudentwarnings'] = 'Send warnings to students with non-booked exams';
$string['sendstaffreminders'] = 'Send reminders to Staff with exams';
$string['sendteacherreminders'] = 'Send reminders to Teachers with exams';
$string['noemail'] = 'No e-mail';
$string['noemail_help'] = 'Do NOT send e-mails, only testing';
