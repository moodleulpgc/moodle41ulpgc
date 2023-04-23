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
$string['sitesettings'] = 'ULPGC Site settings';
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

$string['recentactivity'] = 'Usar actividad reciente personalizada';
$string['explainrecentactivity'] = 'Si se activa, el sistema tratará de determinar la actividad reciente
y los items que requieren la atención del usuario y los marcará en la página del curso.';
$string['adminmods'] = 'Usar restricciones de administración';
$string['explainadminmods'] = 'Si se activa, los módulos pueden ser declarados
no-borrables o no-ocultables (o ambos) por un administrador, de forma que los usuarios no puedan modificarlos.';
$string['advancedgrades'] = 'Usar calificaciones personalizadas';
$string['explainadvancedgrades'] = 'Si se activa, se podrán en uso agregaciones de calificaciones propias de la ULPGC, así como algunas mejoras en el interfaz de calificaciones.';

$string['gradebooklocking'] = 'Bloquear libro de calificaciones';
$string['explaingradebooklocking'] = 'Si se activa, If enabled, se podrán en uso determinados bloqueos en la edición de categorías y agregaciones de calificaciones.';
$string['gradebooklockingdepth'] = 'Profundidad del bloqueo de categorías';
$string['explainlockingdepth'] = 'Las categorías de calificación hasta esta prefundidad serán bloqueadas y no se podrá modificar su nombre, idnumber y agregación,
excepto con usuarios con permiso de Gestión del sitio. ';
$string['gradebooknocal'] = 'Categoría para No Calificados';
$string['explaingradebooknocal'] = 'Si no está vacío, se intrepreta como el idnumber de una Categoría de calificación.
Será la cateoría que se use de forma predeterminada paar colocar los ítems que NO se asocicen explíctamente con una categoría.
Esto es, los "no categorizados" se colocarán en esta categoría identificada por este idnumber.';
$string['locknameword'] = 'Palabra para bloqueo de Categoría';
$string['explainlocknameword'] = 'Si esta palabra está presente en el campo Info de una Categoría de calificacion,
no se podrá modificar su nombre e idnumber salvo con permiso para editar las categorías de calificación. ';
$string['lockaggword'] = 'Palabra para bloqueo de Agregación';
$string['explainlockaggword'] = 'Si esta palabra está presente en el campo Info de una Categoría de calificacion,
no se podrá modificar el cálculo de Agregación salvo con permiso para editar las categorías de calificación. ';

$string['annuality'] = 'Anualidad';
$string['explainannuality'] = 'The course years for this annuality. Must be a six digit string. first year in 4-digit form followed by second year in 2-digit.  eg. 201213  ';
$string['nonlistedroles'] = 'Roles no listados';
$string['explainnonlistedroles'] = 'Los roles marcados NO se mostrarán en el listado de Otros roles, para el caso de roles asignados sin enrol.';

//capabilities
$string['category:review'] = 'Revisar categorías';
$string['site:manage'] = 'ULPGC gestión de sitio';

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

$string['userformpublic'] = 'Campos visibles públicamente';
$string['userformhidden'] = 'Campos de vista privada. Sólo el interesado y los miembros de la Institución pueden acceder a esta información.';
$string['userformwarning'] = 'Los cambios tendrán efecto sólo en esta plataforma. <br>
Estos cambios no alterarán ninguna información institucional o de MiULPGC, ni modificarán las opciones de otras plataformas del campus virtual ULPGC.';

$string['aggregateulpgcsum'] = 'Suma de Calificaciones (ULPGC)';
$string['aggregateulpgcmeanactv'] = 'Media de Calificaciones (ULPGC, Actividades)';
$string['aggregateulpgcmeanexam'] = 'Media de Calificaciones (ULPGC, Exámenes))';
$string['aggregateulpgcmeanconvo'] = 'Media Final de Calificaciones (ULPGC)';
$string['aggregateulpgfinal'] = 'Calificación final de Convocatorias(ULPGC)';
$string['aggregateulpgnone'] = 'No calificado (ULPGC)';

// course
$string['courseediton'] = 'Activar edición del curso';
$string['courseeditoff'] = 'Desactivar edición';
$string['editsettings'] = 'Editar configuración de {$a}';
$string['adminmoduleoptions'] = 'Opciones de restricción administrativa';
$string['adminmoduleexplain'] = 'Si se marca, el módudo se define como institucional no borrable.';
$string['unhideable'] = 'No-ocultable';
$string['unerasable'] = 'No-borrable';
$string['unmovable'] = 'No-movible';
$string['both'] = 'Ambas';
$string['all'] = 'Todas';
$string['unerasablewarning'] = 'Este ítem está protegido. No puede ser borrado. ';
$string['unhideablewarning'] = 'Este ítem está protegido. No puede ser ocultado. ';
$string['newactivity'] = '{$a} nuevas intervenciones';
$string['news'] = 'New course activity';
$string['ungradedactivity'] = '{$a} items requieren calificación';
$string['usersexportcsv'] = 'Exportar como CSV';
$string['exportusers'] = 'Exportar usuarios';

//backup
$string['rootsettinggroups'] = 'Incluir Grupos';
$string['rootsettinggroupings'] = 'Incluir Agrupamientos';
$string['rootsettingadminmods'] = 'Restaurar módulos restringidos';

//filters
$string['notrole'] = 'Not';
$string['courselist'] = 'use as course list';
$string['inlist'] = 'is in list';
$string['notinlist'] = 'not in list';
$string['userfield'] = 'custom user field';
$string['userfieldlabel'] = 'User field Label';
$string['userfieldlabelnovalue'] = 'label';

// centers
$string['faculty'] = 'Cod. Facultad';
$string['degree'] = 'Cod. Titulación';
$string['department'] = 'Cod. Departamento';

// term
$string['term'] = 'Semestre';
$string['term1'] = 'Primer semestre';
$string['term2'] = 'Segundo semestre';
$string['term3'] = 'Ambos semestres';

// admin
$string['privatedetails'] = 'Datos privados';
$string['showuserdetails'] = 'Show user details';
$string['showuserdetails_desc'] = 'When displaying user profile data, these fields may be shown in addition to their full name.
The fields are only shown to users who have the moodle/course:viewhiddenuserfields capability; by default, teachers and managers.';

/// START DETAILED SCALE GRADES
$string['scaledisplaymode'] = 'Modo de mostrar escalas';
$string['scaledisplaymode_help'] = 'El modo Detallado muestra el número de veces que un participante ha obtenido cada uno de los valores de la escala en las actividades de tipo foro y los glosario (estos son los tipos de actividades que permiten que un participante tenga varias calificaciones al mismo tiempo). Por ejemplo, si la escala es "Mal,Normal,Muy bien" un valor "0/3/1" indica que un participante tiene 0 elementos calificados con Mal, 3 elementos calificados con Normal y 1 elemento calificado con Muy bien';
$string['normalscaledisplay'] = 'Normal';
$string['detailedscaledisplay'] = 'Detallado';
$string['configscaledisplaymode'] = 'El modo Detallado muestra el número de veces que un participante ha obtenido cada uno de los valores de la escala en las actividades de tipo foro y los glosario (estos son los tipos de actividades que permiten que un participante tenga varias calificaciones al mismo tiempo). Por ejemplo, si la escala es \"Mal,Normal,Muy bien\" un valor \"0/3/1\" indica que un participante tiene 0 elementos calificados con Mal, 3 elementos calificados con Normal y 1 elemento calificado con Muy bien';
/// END DETAILED SCALE GRADES

$string['aim'] = 'Código postal';
$string['exportuserselector'] = 'Indicar usuarios a exportar';
$string['exportdataselector'] = 'Indicar datos a exportar para cada usuario';
$string['exportusergroupmember'] = 'Miembro';
$string['exportusersingroup'] = 'Miembros del grupo';
$string['exportusersingroup_help'] = 'Si se especifica, solo los usuariso que sean miembros de este grupo en particular se incluirán en la exportación.
If specified then only users that are members of this particular group will be exported.

 * Cualquiera: Todos los usuarios, ya sean miembros de un grupo  o no
 * Miembro: Usuarios que sean miembros de algún grupo, no se exportan usuarios que no pertenecen a al menos un grupo.
 * Ninguno: solo se exportan usuarios que NO son miembros de ningún grupo.
';
$string['exportgroupsgrouping'] = 'Miembros del agrupamiento';
$string['exportgroupsgrouping_help'] = 'Si se indica, sólo se incluirán usuarios que sean miembros
de alguno de los grupos del agrupamiento especificado.

 * Ninguno: Solo se exportan usuarios que son miembros de grupos que NO pertenecen a ningún agrupamiento.
 * Cualquiera: Sin limitación de usuarios por aprupamiento, indiferente.
';
$string['exportuserroles'] = 'Con roles';
$string['exportuserroles_help'] = 'Solo los usuarios que ostenten alguno de estos roles se incluirán en la exportación.';
$string['exportincludeusergroups'] = 'Incluir pertenencia a grupos';
$string['exportincludeusergroups_help'] = 'Si se marca, se incluirán columnas con los grupos a los que pertenece el usuario. ';
$string['exportincludeuserroles'] = 'Incluir roles';
$string['exportincludeuserroles_help'] = 'Si se marca, se incluirá una columna con los roles del usuario en el curso. ';
$string['exportonlygrouping'] = 'Grupos del agrupamiento';
$string['exportonlygrouping_help'] = 'Si se indica, sólo se incluirán los grupos del agrupamiento especificado.';
$string['groupingsameabove'] = 'Indicado arriba';
$string['exportusersdetails'] = 'Campos adicionales';
$string['exportusersdetails_help'] = 'Información adicional de cada usuario a incluir en la exportación.

El nombre, apellidos y DNI de cada usuario siempre son exportados. Adicionalmente se pueden marcar estos campos para ser incluidos como extra.';
$string['exportsort'] = 'Ordenar según';
$string['exportsort_help'] = 'El campo usado para ordenar a los usuarios en el listado exportado.';
$string['exportfileselector'] = 'Indicar nombre y formato de fichero';
$string['exportformatselector'] = 'Formato de exportación';
$string['exportdownload'] = 'Descargar';
$string['exportfilename'] = 'Nombre del archivo (sin ext.)';
$string['exportfilename_help'] = 'Nombre del archivo que contendrá los datos exportados.

La extensión será determinada por el formato de exportación.';
$string['errorheaderssent'] = 'Errores en salida, el archivo no puede generarse.';
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
Facultad de Veterinaria,Vet
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
Facultad de,F.
Instituto Universitario de, I.U.
Programa de doble titulación:,Doble
Programa de Doctorado en,P.D.
Máster Universitario en,M.U.
Grado en, G.
Universidad de las Palmas de Gran Canaria,ULPGC
Universidad,U.
 Por , por
';

$string['actv_communication'] = 'Comunicación';
$string['actv_collaboration'] = 'Colaboración';
$string['actv_adminwork'] = 'Gestión';
$string['actv_assessment'] = 'Trabajo y Evaluación';
$string['actv_structured'] = 'Activ. estructuradas';
$string['actv_games'] = 'Juegos';
$string['actv_other'] = 'Otros';
$string['res_files'] = 'Enlaces y Archivos';
$string['res_text'] = 'Textos';
$string['res_structured'] = 'Rec. estructurados';

$string['alerts'] = 'Alertas globales';
$string['showglobalalert'] = 'Mostrar alertas';
$string['explainshowglobalalert'] = 'Si se activa, se mostrará el mensaje de más abajo en el cabecero de la páginas concordantes.';
$string['alertstart'] = 'Fecha de comienzo';
$string['explainalertstart'] = 'Fecha en la que se empezará a mostrar el mensaje. Vacío para mostrar. En formato AAAA-mm-dd.';
$string['alertend'] = 'Fecha de terminación';
$string['explainalertend'] = 'fecha en la que se terminará de mostrar el mensaje. Vacío para mostrar. En formato AAAA-mm-dd.';
$string['alertroles'] = 'Mostrar para roles';
$string['explainalertroles'] = 'El mensaje se mostrará para los usuarios que tengan alguno de estos roles en el contexto del curso. Dejar vacío para NO chequear.';
$string['alerttype'] = 'Tipo de alerta';
$string['explainalerttype'] = 'Una de las clases de alerta de estandar de Bootstrap.';
$string['alertdismiss'] = 'Botón de cancelación';
$string['explainalertdismiss'] = 'Si se activa, se muestra un botón para cancelar permanentemente la visualización de la alerta para ese usuario.
Se crea una preferencia de usuario para llevar un registro.';
$string['alertmessage'] = 'Mensaje global';
$string['danger'] = 'Aviso';
$string['dismissalert'] = 'Confirmar visualización';
$string['mailednotviewed'] = 'Foros: correo NO leído';
$string['explainmailednotviewed'] = 'Si se activa, entonces NO se considerará un mensaje en foro como leído cuando se envía por e-mail (como hace Moodle),
sino que se presentará como no leído en el interfaz aunque se haya enviado antes por e-mail, hasta que sea visto en el interfaz.';
$string['blockalert'] = 'Alertas en Panel derecho';
$string['explainblockalert'] = 'Si se activa, cuando alguno de los bloques del panel derecho contenga mensajes importantes que deben ser vistos,
el panel derecho se configura abierto desde el inicio. ';
$string['activityindentation'] = 'Sangría de Actividades/Recursos';
$string['activityindentation_help'] = 'Si se activa, será posible indentar horizontalmente Actividades/Recursos en la página del curso';
$string['activityindentationenabled'] = 'Indentación de actividades';
$string['explainactivityindentationenabled'] = 'Si se activa, los nombres de actividades en la págiba de curso mostrarán indentación,
y el menú de edición de cada módulo de actividad mostrará flechas de indentación.';
$string['profilefieldpresets'] = 'Custom profile fields';
$string['profilefieldpresets_desc'] = 'Custom profile fields';
$string['hidepicture'] = 'Ocultar fotos de usarios';
$string['hidepicture_desc'] = 'Si se activa, las fotos de usarios estarán ocultas por defecto para todo usuario sin el permiso "viewhiddnefields".
Salvo que el propio usuario permita el acceso escribiendo un texto en la propiedad de texto alternativo de su imagen de perfil.';
$string['hidepicwarning'] = 'Descripción de la imagen y control de la visibilidad de la imagen de perfil.';
$string['hidepicwarning_help'] = 'Su foto de perfil será visible solo por los Profesores de sus asignaturas (o Administrativos con acceso a cada asignatura).
Otros estudiantes NO podrán ver su foto de perfil a no ser que usted de permiso expresamente. <br />
Para ello basta escribir un texto (cualquiera) en el campo "Descripción de la imagen".
Si este campo queda vacío solo los profesores podrán ver la imagen. ';
$string['archivereuse'] = 'Archivo/Reuso';
$string['rolepermissions'] = 'Permisos';
$string['participants'] = 'Lista de Participantes matriculados';
