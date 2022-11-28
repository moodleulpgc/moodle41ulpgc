<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Plugin strings are defined here.
 *
 * @package     mod_examboard
 * @category    string
 * @copyright   2017 Enrique Castro @ ULPGC
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['areanotification'] = 'Notificaciones';
$string['assessment'] = 'Evaluación';
$string['assess'] = 'Evaluar';
$string['board'] = 'Tribunal';
$string['member'] = 'Miembro del tribunal';
$string['exemption'] = 'Eximido';
$string['exempted'] = 'Eximido';
$string['exempted_help'] = 'Marcar si este usuario ha sido eximido de sus deberes como miembro del Tribunal.';
$string['exclude'] = 'Excluir al estudiante de la evaluación';
$string['include'] = 'Incluir al estudiante en la evaluación';
$string['examhide'] = 'Ocultar un Examen impide a los usuarios sin capacidades extra el acceder al mismo (Miembros del Tribunal o estudiantes)';
$string['examshow'] = 'Mostrar un Examen oculto permite el acceso y trabajo por parte de Miembros del Tribunal y estudiantes';
$string['userhide'] = 'Excluir a un estudiante de la evaluación impide que los Profesores puedan emitir calificaciones, 
pero permite el acceso a los ítems entregados por el estudiante, si existen';
$string['usershow'] = 'Incluir a un usuario en la evaluación permite a los Profesores evaluar y calificar al estudiante';
$string['excluded'] = 'Excluido';
$string['excluded_help'] = 'El estudiante excluido es mostrado pero no puede ser calificado';
$string['approved'] = 'Conformidad';
$string['filtersheader'] = 'Filtros de selección';
$string['examboard:addinstance'] = 'Añadir una instancia de Tribunal';
$string['examboard:view'] = 'Ver actividades Tribunal';
$string['examboard:viewall'] = 'Ver todos los Tribunales y sesiones';
$string['examboard:viewothers'] = 'Ver otros participantes en sesión de Tribunal';
$string['examboard:submit'] = 'Entregar ítems evaluables a un Tribunal';
$string['examboard:grade'] = 'Calificar como Tribunal';
$string['examboard:viewgrades'] = 'Ver calificaciones';
$string['examboard:releasegrades'] = 'Publicar calificaciones para participantes';
$string['examboard:allocate'] = 'Distribuir Examinadores y estudiantes en Tribunales';
$string['examboard:manage'] = 'Gestionar Tribunales de examen, crear, asignar miembros y estudiantes.';
$string['examboard:notify'] = 'Realizar notificaciones a los participantes en un Tribunal.';
$string['gradeitem:examinations'] = 'Presentación y Defensa';
$string['messageprovider:examboard_notify'] = 'Notificación';
$string['modulename'] = 'Tribunal';
$string['modulenameplural'] = 'Tribunales';
$string['partialgrading'] = 'Incompleta';
$string['pluginname'] = 'Tribunal';
$string['pluginadministration'] = 'Administración de Tribunales';
$string['manageallocation'] = 'Gestionar asignaciones';
$string['notify'] = 'Notificar';
$string['notify_help'] = 'Permite enviar mensajes de notificación a los participantes en un examen por Tribunal.';
$string['addexam'] = 'Agregar examen';
$string['submit'] = 'Entregar';
$string['grade'] = 'Calificar';
$string['groupingname'] = 'Agrupamiento';
$string['groupingname_help'] = 'Si se emplean grupos para cada Examen, 
esos grupos se pueden asignar automáticamente a este agrupamiento. 

Si un agrupamiento con este nombre/código ya existe, se usa tal cual, si no, 
se crea un nuevo agrupamiento con este nombre para contener los grupos de examen.';
$string['boarddata'] = 'Datos del Tribunal';
$string['existingboard'] = 'Tribunal encargado';
$string['existingboard_help'] = 'El examen puede ser asignado a un Tribunal ya existente, 
seleccionándolo del menú. O puede crear una nueva entrada de Tribunal a partir de los datos introducidos.';
$string['newboard'] = 'Nuevo tribunal';
$string['boardidnumber'] = 'Código identificador';
$string['boardidnumber_help'] = 'El nuevo tribunal necesita un código identificador para usar en listados. 
Debe ser un código alfanumérico corto, sin espacios. Puede ser algo como T01 or B-101.';
$string['boardname'] = 'Nombre';
$string['boardname_help'] = 'Un nombre para referirse al tribunal. 
Puede usar una frase, varias palabras y espacios.';
$string['boardactivevis'] = 'Visibilidad (Tribunal)';
$string['boardactive'] = 'Visibilidad';
$string['boardactive_help'] = 'Indica si el Tribunal será visible por los usuarios no gestores.  
Puede crear tribunales inactivos como sustitutos, o mantenerlos invisibles hasta que se complete la asignación de miembros o estudiantes.';
$string['examdata'] = 'Datos del Examen';
$string['examgroups'] = 'Grupos por Examen';
$string['examgroups_help'] = 'Si se habilita se creará automáticamente un grupo separado para cada Examen definido en el módulo. 
El grupo tendrá por miembros a los estudiantes, tutores y examinadores asignados al examen.

Los grupos se actualizarán automáticamente cada vez que se asigna o des-asigna a una persona como estudiante, tutor o examinador en un examen.';
$string['examvenue'] = 'Local';
$string['examvenue_help'] = 'El aula, sala o auditorio donde tendrá lugar el examen.';
$string['examdate'] = 'Fecha del examen';
$string['examdate_help'] = 'La fecha en la que se realizará en examen.';
$string['examduration'] = 'Duración';
$string['examduration_help'] = 'El tiempo previsto para la realización del examen.';
$string['examactivevis'] = 'Visibilidad (Examen)';
$string['examactive'] = 'Visibilidad';
$string['examactive_help'] = 'Si el examen será visible por usuarios no gestores.
Puede crear exámenes invisibles para evitar el acceso de estudiantes, para mantener una reserva de sustitutos y otras opciones.';
$string['mandatoryifnew'] = 'La entrada no puede estar vacía';

$string['userassign'] = 'Asignación de Estudiantes/Tutores';
$string['userassign_help'] = 'Permite asignar estudiantes (y opcionalmente sus tutores) a un examen por Tribunal.
Cada estudiante, y sus tutores, será agregado a todas las sesiones de examen asignadas al Tribunal especificado.
';
$string['userassignation'] = 'Lista de asignaciones';
$string['userassignation_help'] = 'Asignaciones de Estudiante/Tutor.
Este es un TEXTO para la asignación  de estudiantes a un Tribunal. Cada línea es una asignación separada independiente.
La inclusión de datos de tutores es opcional, dependiendo de la configuración del Tribunal.

Los datos obligatorios son: 

   * ID de usuario: una ID única para cada estudiante, interpretada según el campo definido más abajo. Normalmente será el DNI.
   * ID del Tribunal: el código identificador único de cada Tribunal definido previamente. Este código ya debe existir, no se crea en el momento. 

La primera línea DEBE contener los nombres de cada campo, como se indica.     
Dentro de cada línea cada dato puede ser separado de sus vecinos por un carácter de "|", ",", ";". No por espacios.
Si hay varios Tutores como "Otros", se lista cada ID (DNI) separada por ESPACIO de los otros tutores, 
y la lista entera se separa de los otros datos por uno de los caracteres de arriba.. 

Por ejemplo:

DNIestudiante| CódigoTribunal, DNItutor, DNIotrotutor1 DNIotrotutor2 DNIotrotutor3

';
$string['tutorcheck'] = 'Comprobar conflictos';
$string['tutorcheck_help'] = 'Si se habilita, se comprobará si la lista de tutores incluye alguno de los miembros del Tribunal.

Si existe alguna coincidencia entre Tutores y miembros del Tribunal, entonces esa línea NO se procesa y NO se asignará al estudiante al Tribunal. 
';
/*
$string['useridfield'] = 'User ID code';
$string['useridfield_help'] = 'How to interpret the user ID values in the user assignation list. 
Which user field to match to that values to perfoem user identification.';
*/
$string['userallocation'] = 'Distribución de estudiantes';
$string['boardallocation'] = 'Distribución de miembros';
$string['chooseexam'] = 'Con el examen: ';
$string['chooseusertype'] = 'A los usuarios: ';
$string['addexam'] = 'Añadir examen';
$string['addexam_help'] = 'Aquí puede añadir los datos básicos de una sesión de examen por Tribunal. 
Tanto los datos de la sesión (nombre, aula, fecha) como los datos identificadores del Tribunal.

Un nuevo Examen por Tribunal debe consistir en un Tribunal (un grupo de examinadores) y los datos específicos de la sesión de examen (aula, fecha).

Puede usar un Tribunal ya existente (y así asignar un nuevo examen a esos profesores) o puede crear un nuevo Tribunal para este examen.
Cada Tribunal es definido por un <strong>código identificador</strong> esencial. Puede además especificar un título y nombre si es conveniente para mayor definición.';
$string['updateexam'] = 'Actualizar examen';
$string['updateexam_help'] = 'Aquí puede editar, cambiar o borrar los datos básicos de una sesión de examen por Tribunal. 
Tanto los datos de la sesión (nombre, aula, fecha) como los datos identificadores del Tribunal pueden ser cambiados.
';
$string['addmembers'] = 'Añadir miembros';
$string['editmembers'] = 'Actualizar Tribunal';
$string['editmembers_help'] = 'Permite editar, añadir y cambiar los miembros de un Tribunal. 

Estos cambios afectarán a TODAS las sesiones de de examen adscritas a este Tribunal. 

Para eliminar a una persona y dejar el puesto vacante debe seleccionar "ninguno" en la posición deseada.
';
$string['assignexam'] = 'Asignar examen';
$string['boardconfirm'] = 'Confirmación de asistencia';
$string['boardconfirm_help'] = 'Aquí puede confirmar o rechazar su participación en el Tribunal';
$string['toggleconfirm'] = 'Modificar confirmación';
$string['confirmation'] = 'Confirmación';
$string['confirmation_help'] = 'Marcar para confirmar su participación en el tribunal. Desmarcar en otro caso.';
$string['confirmexam'] = 'Confirmada participación como miembro de Tribunal.';
$string['unconfirmexam'] = 'Declinada participación como miembro de Tribunal.';
$string['noconfirmsave'] = 'No guardada la confirmación debido a un problema de la base de datos.';
$string['memberrole'] = 'Rol';
$string['membername'] = 'Nombre';
$string['boardstatus'] = 'Confirmaciones';
$string['boardnotify'] = 'Notificaciones';
$string['deputymembers'] = 'Miembros sustitutos';
$string['sesssionlabel'] = 'Sesión :';
$string['order'] = 'Nº orden';
$string['userlabel'] = 'Etiqueta';
$string['userlabel_help'] = 'Un texto identificativo personalizado para cada estudiantes. 
Por ejemplo, su código de intervención o el código de colocación del póster.';
$string['maintutor'] = 'Primer tutor';
$string['othertutors'] = 'Otros tutores';
$string['updateuser'] = 'Actualizar estudiante';
$string['updateuser_help'] = 'Aquí puede agregar un estudiante a un examen, o actualizar sus datos. 

Se pueden asignar o eliminar tutores, según la configuración del módulo en cuanto a los Tutores (requeridos, permitidos o no empleados).

También puede cambiar el método de ordenamiento de los  estudiantes en el examen.
';
$string['userdeleteconfirm'] = 'Borrar un usuario es permanente. <br />
Desea continuar? ';
$string['deleteallconfirm'] = 'Borrar a todos los estudiantes es permanente. <br />
Desea continuar? ';
$string['examdeleteconfirm'] = 'Borrar un examen es permanente. <br />
Desea continuar? ';
$string['deleteexam'] = 'Borrar examen';
$string['deleteexam_help'] = 'Borra una sesión de examen por Tribunal. Una vez eliminada, una sesión de examen no puede ser recuperada, en su caso debe ser creada de nuevo.

Cuando borra una sesión de examen puede optar por eliminar también la definición del Tribunal evaluador (típicamente, si solo se encargaba de esta sesión). 
O bien puede optar por mantener el Tribunal para asignarle otras sesiones de examen. 
<br />En cualquier caso, un Tribunal que tiene otras sesiones de examen asignadas NO será eliminado. ';
$string['deleteuser'] = 'Borrar  estudiante';
$string['deleteuser_help'] = 'Al borrar un estudiante de un examen se elimina también la asociación de sus tutores con ese Tribunal. 
El estudiante puede luego ser vuelto a asignar a este u otro Tribunal. 

En cualquier caso, un estudiante calificado no será eliminado.';
$string['deleteall'] = 'Eliminar todos';
$string['deleteall_help'] = 'Esta acción eliminará a <strong>TODOS</strong> los estudiantes de eset examen.';
$string['adduser'] = 'Añadir estudiante';
$string['saveuser'] = 'Guardar';
$string['reorder'] = 'Reordenar';
$string['orderkeepchosen'] = 'Mantener orden actual';
$string['orderrandomize'] = 'Reordenar al azar';
$string['orderalphabetic'] = 'Orden alfabético';
$string['orderalphatutor'] = 'Alfabético por tutor';
$string['orderalphalabel'] = 'Alfabético por etiqueta';
$string['codename'] = 'Código';
$string['session'] = 'Sesión';
$string['examinees'] = 'Estudiantes';
$string['tobepublishedafter'] = 'Se publicará el {$a}';
$string['viewboard'] = 'Tribunal';
$string['viewusers'] = 'Estudiantes';
$string['viewexam'] = 'Examen por Tribunal';
$string['returntoexam'] = 'Volver';
$string['returntoexams'] = 'Volver a Tribunales';
$string['examboardname'] = 'Nombre del ítem';
$string['examboardfieldset'] = 'Opciones para Tribunal';
$string['distributionfieldset'] = 'Distribución y grupos';
$string['notifyfieldset'] = 'Notificaciones y confirmaciones';
$string['publishfieldset'] = 'Fechas de operación';
$string['maxboardsize'] = 'Máximo nº de miembros';
$string['maxboardsize_help'] = 'El mayor número de miembros que pueden ser adscritos a un Tribunal.  
Pueden manejarse Tribunales con menos miembros, pero no con más de este valor.';
$string['usetutors'] = 'Participación de Tutores';
$string['usetutors_help'] = 'Si los tutores de los estudiantes juegan algún papel en la operativa de estos Tribunales. 
En general, si existen tutores de estudiantes éstos no pueden participar como miembros del tribunal que evalúa a los estudiantes tutorizados.
Las opciones pueden ser: 

 * No: No se consideran Tutores. No se gestiona la asignación de Tutores a los estudiantes, ni restricciones en los examinadores.
 * Si: Estos Tribunales tienen en cuenta los Tutores asignados a cada estudiante. Se gestiona su asignación y la restrición de ser examinador y tutor del mismo estudiante.
 * Requerido: Los estudiantes deben tener obligatoriamente un Tutor. No se puede asignar un estudiante a un Tribunal de examen careciendo de Tutor.

';
$string['tutoruseno'] = 'No';
$string['tutoruseyes'] = 'Si';
$string['tutoruserequired'] = 'Requerido';
$string['allocation'] = 'Distribución automática';
$string['allocation_help'] = 'Se puede distribuir a los usuarios y asignarlos a Tribunales de examen, 
ya sea como examinadores o estudiantes de forma semi-automatizada y aleatoria. 
Este parámetro controla la estrategia de distribución

 * Distribuir examinadores una vez los estudiantes (y sus tutores) han sido asignados a un tribunal. 
    El algoritmo de distribución tien en cuenta no asignar un examinador a un tribunal en el que son evaluados los estudiantes que tutoriza..

 * Distribuir estudiantes una vez han siso asignados los miembros de cada tribunal de examen.. 
    El algoritmo de distribución evita asignar un estudiante a un Tribunal en el uno de sus tutores actúa como examinador.

';
$string['allocmodenone'] = 'Sin distribución automática';
$string['allocmodemember'] = 'Distribuir examinadores dados estudiantes';
$string['allocmodeuser'] = 'Distribuir estudiantes dados examinadores';
$string['allocmodetutor'] = 'Distribuir estudiantes dados examinadores';
$string['allocnumusers'] = 'Ubicados {$a} estudiantes';
$string['allocnumexams'] = 'Asignados miembros en {$a->boards} Tribunales para {$a->exams} exámenes';
$string['allocemptied'] = 'No quedan usuarios que ubicar';
$string['allocprevious'] = 'Borrar existentes';
$string['allocprevious_help'] = 'Si se activa, se eliminarán todas las asignaciones previas 
antes de proceder a la distribución al azar de miembros de tribunal.';
$string['allocdeputy'] = 'Asignar sustitutos';
$string['allocdeputy_help'] = 'Si se activa, además de los miembros titulares se asignará un sustituto en cada posición.';
$string['allocrepeatable'] = 'Permitir repeticiones';
$string['allocrepeatable_help'] = 'Si se activa, un usuario asignado en un Examen como miembro del Tribunal podrá ser ubicado también en otros exámenes diferentes.';
$string['allocateboard'] = 'Ubicar miembros de Tribunal';
$string['allocateboard_help'] = 'Los usuarios que se asignaran como miembros en cada posición se escogerán de entre los grupos indicados en cada caso. 

Se pueden indicar los mismos grupos repetidos en varias posiciones, si se desea.';
$string['allocateusers'] = 'Ubicar estudiantes';
$string['allocateusers_help'] = 'Los usuarios de los grupos seleccionados serán asignados como estudiantes en los exámenes indicados.';
$string['allocationsettings'] = 'Opciones de distribución';
$string['allocatedboards'] = 'Tribunales diana';
$string['allocatedboards_help'] = 'Los usuarios disponibles se asignarán al azar entre los Tribunales seleccionados.';
$string['allocatedexams'] = 'Exámenes diana';
$string['allocatedexams_help'] = 'Los usuarios disponibles se asignarán al azar entre los exámenes seleccionados.';
$string['allocexcludeexisting'] = 'Exclusión de Miembros ya asignados';
$string['allocexcludeexisting_help'] = 'Define si los profesores ya designados como Miembros de Tribunal en otros tribunales (que no vayan a ser borrados debajo) 
se incluirán en esta distribución o no, esto es, si s e les asignarán nuevos exámenes adicionales alos que ya tienen. 
Las opciones son

Defines if existing board members (not to be deleted, below) 
will be included in this allocation or excluded from allocation, that is, they will not we assigned additional new exams here. 
The options are: 

 * Ninguno: No hay exclusiones, todos entran al reparto, incluso los Profesores que ya son Miembros de un tribunal con examen asigando serán considerados para nuevas asignaciones en este reparto.
 * Cualquiera : Se excluye a un Profesor si ya es Miembro del algún tribunal en alguna Convocatoria, da lo mismo cual. 
 * Exams: Se excluye a los profesores que ya son Miembros de uno de los tribunales cuyos miembros se van a repartir ahora. 
 * Convovatorias: Se excluye a los profesores que ya son Miembros de algún Tribunal con examen en esa Convocatoria.
 
';
$string['allocvacant'] = 'Profesores insuficientes. Puestos vacantes en: <br />
{$a}';
$string['examsallocated'] = 'Exámenes ubicados';
$string['choosegroup'] = 'Grupos para posición {$a}';
$string['allocatewarningboard'] = 'Los estudiantes pueden tener asignados tutores.
Se tomarán medidas para evitar que la misma persona aparezca como miembro del Tribunal y tutor de un estudiante en el mismo examen.'; 
$string['allocatewarningusers'] = 'Los estudiantes pueden tener asignados tutores.
Los usuarios con capacidad para calificar existentes en un grupo serán considerados los tutores de los estudiantes de ese grupo. 
La herramienta espera encontrar grupos que contengan solo estudiantes o bien grupos separados que contengan un tutor (o co-tutores) y los estudiantes que tutoriza (pero no otros).

Se tomarán medidas para evitar que la misma persona aparezca como miembro del Tribunal y tutor de un estudiante en el mismo examen.'; 
$string['sourcegroups'] = 'Grupos con estudiantes';
$string['usersperexam'] = 'Estudiantes por examen';
$string['usersperexam_help'] = 'El número máximo de estudiantes que será asignado a un Tribunal o Examen. 
Los usuarios ya asignados antes de la ubicación aleatoria también serán tenidos en cuenta para este máximo. ';
$string['nolimit'] = 'Sin límite';
$string['requireconfirm'] = 'Requiere confirmación';
$string['requireconfirm_help'] = 'Controla si los miembros del Tribunal deben confirmar su participación como examinadores. 
Si se habilita, entonces se realizan comprobaciones y avisos adicionales para el Tribunal y los gestores.';
$string['confirmtime'] = 'Plazo para confirmación';
$string['confirmtime_help'] = 'Antelación mínima, tiempo antes de la sesión de examen en el que se puede cambiar el estado de confirmación. 
Pasado este plazo ya no se puede cambiar la confirmación.';
$string['notifyconfirm'] = 'Notificar confirmaciones';
$string['notifyconfirm_help'] = 'Si los gestores del módulo Tribunal recibirán o no una notificación por e-mail 
cuando los miembros del Tribunal confirmen o declinen su participación. ';
$string['confirmdefault'] = 'Estado de confirmación predefinido';
$string['confirmdefault_help'] = 'Si están habilitadas las confirmaciones, define el estado inicial para los examinadores.

Si el estado inicial es "Si" entonces para todo los miembros del Tribunal el estado predefinido en "Confirmado", 
y en su caso podrán <strong>revocar</strong> la confirmación para indicar que NO pueden participar.

Si el estado inicial es "No" entonces se espera que cada miembro del tribunal confirme independientemente su participación (o no) en el Tribunal.
';
$string['usewarnings'] = 'Envío de recordatorios';
$string['usewarnings_help'] = 'Si el módulo ha de enviar recordatorios automáticos sobre un examen próximo y a quién se remiten. 
Cuando se acerca la fecha de un examen por Tribunal el módulo puede emitir mensajes a los usuarios como recordatorio de la sesión de examen que se avecina. 
Los mensajes se pueden emitir para:

 * Nadie, no se emplean recordatorios.
 * Estudiantes: sólo los estudiantes reciben el recordatorio del examen próximo.
 * Examinadores: Los miembros del Tribunal recibirán un mensaje sobre su participación en el examen próximo.
 * Tutores: los tutores de los estudiantes (pero no aquellos) reciben el recordatorio del examen próximo.
 * Docentes: los profesores, ya sea miembros de Tribunal o Tutores reciben el aviso sobre el examen por Tribunal que se aproxima.
 * Todos: todos los participantes, en cualquier rol, examinadores, estudiantes y tutores, todos recibien el aviso de examen próximo.

';
$string['usernone'] = 'No se emite ningún recordatorio';
$string['userexaminees'] = 'Estudiantes';
$string['usermembers'] = 'Examinadores';
$string['usertutors'] = 'Tutores';
$string['userstaff'] = 'Docentes (examinadores y tutores)';
$string['userall'] = 'Todos los participantes';
$string['warntime'] = 'Antelación del recordatorio';
$string['warntime_help'] = 'Cuánto tiempo antes del examen por Tribunal se emitirá el mensaje de recordatorio';
$string['inactive'] = 'Ocultar';
$string['active'] = 'Mostrar';
$string['assignedexams'] = 'Exámenes adscritos';
$string['assignedexams_help'] = 'Puede especificar aquí a qué sesiones de examen estará adscrito este Tribunal, 
esto es, en cuáles sesiones de examen estos miembros serán los examinadores responsables.';
$string['allowsubmissionsfromdate'] = 'Comienzo de entregas';
$string['allowsubmissionsfromdate_help'] = 'Si se activa, los estudiante podrán relizar entregas directamente en esta instancia, 
no solo a través de los módulos ayudantes.

Las entregas se realizarán desde la fecha aquí especificada hasta la fecha del examen. ';
$string['publishboard'] = 'Publicación del Tribunal';
$string['publishboard_help'] = 'Determina si o cuándo la composición del Tribunal, sus miembros, será visible por los estudiantes u otros usuarios.';
$string['publishondate'] = 'A partir de';
$string['publishboarddate'] = 'Fecha de publicación del Tribunal';
$string['publishboarddate_help'] = 'Determina la fecha a partir de la cual la composición del Tribunal 
será visible públicamente para el resto de participantes.';
$string['publishgrade'] = 'Publicación de calificaciones';
$string['publishgrade_help'] = 'Determina si o cuándo se muestran las calificaciones a los usuarios.';
$string['publishgradedate_help'] = 'Determina la fecha a partir de la cual las calificaciones otorgadas por el Tribunal 
serán visibles públicamente para el resto de participantes.';
$string['publishgradedate'] = 'Fecha de publicación de calificaciones';
$string['wordsfieldset'] = 'Palabras para roles';
$string['namechair'] = 'Presidente';
$string['namechair_help'] = 'Aquel rol que preside, coordina, controla y es el responsable último del Tribunal.';
$string['namesecretary'] = 'Secretario';
$string['namesecretary_help'] = 'Aquel rol encargado de los registros y administración';
$string['namevocal'] = 'Miembro del tribunal';
$string['namevocal_help'] = 'Otros docentes encargados de examinar y evaluar a los estudiantes.';
$string['nameexaminee'] = 'Examinando';
$string['nameexaminee_help'] = 'El rol de los que son sometidos a examen por el Tribunal y evaluados por el mismo.';
$string['nametutor'] = 'Tutor de estudiantes';
$string['nametutor_help'] = 'El papel de aquellos docentes que dirigen, supervisan o tutorizan a los examinados.';
$string['chairword'] = 'Presidente';
$string['secretaryword'] = 'Secretario';
$string['vocalword'] = 'Vocal';
$string['examineeword'] = 'Estudiante';
$string['tutorword'] = 'Tutor';
$string['gradeable'] = 'Entrega evaluable';
$string['gradeablemod'] = 'Actividad evaluada';
$string['gradeablemod_help'] = 'Un Tribunal es usualmente un mecanismo de calificación de otra actividad entregable. 
Esta opción permite especificar en qué otra actividad se encuentran las entregas calificadas aquí. 

Esa actividad <strong>debe</strong> ser un ítem coexistente en el mismo curso y que declare un <strong>código de calificación</strong> no vacío. 
';
$string['gradeablemods'] = 'Actividades complementarias';
$string['gradeablemods_help'] = 'Un Tribunal es usualmente un mecanismo de calificación de otra actividad entregable.
Esta opción permite especificar en qué otras actividades se encuentran los datos complementarios qu epueden sernecesariso para la actividad evaluadora del Tribunal.
Esto incluye tanto la actividad donde se realiza la entrega del material evaluable así como otros datos adicionales, la Propuesta de trabajo o la Solicitud de Defensa, en su caso.
';
$string['proposal'] = 'Propuesta de trabajo';
$string['proposalmod'] = 'Propuesta de trabajo';
$string['proposalmod_help'] = 'Un Tribunal es usualmente un mecanismo de calificación de otra actividad entregable. 
Esta opción permite especificar en qué otra actividad se encuentran las Propuestas de trabajo, en su caso.
Si los Exámenes en este Tribunal no necesitan un paso previo de Propuesta de trabajo, simplemente seleccione ninguno aquí.

Si se usa, <strong>debe</strong> ser un ítem coexistente en el mismo curso y que declare un <strong>código de calificación</strong> no vacío. 

Puede ser el mismo ítem que la Actividad evaluada o la Solicitud de Defensa.
';
$string['defense'] = 'Solicitud de Defensa';
$string['defensemod'] = 'Solicitud de Defensa';
$string['defensemod_help'] = 'Un Tribunal es usualmente un mecanismo de calificación de otra actividad entregable. 
Esta opción permite especificar en qué otra actividad se encuentran las Solicitudes de defensa, en su caso.
Si los Exámenes en este Tribunal no necesitan un paso previo de Solicitud de Defensa, simplemente seleccione ninguno aquí.

Si se usa, <strong>debe</strong> ser un ítem coexistente en el mismo curso y que declare un <strong>código de calificación</strong> no vacío. 

Puede ser el mismo ítem que la Actividad evaluada o la Solicitud de Defensa.
';
$string['grademode'] = 'Cómputo de calificación';
$string['grademode_help'] = 'Cómo se calcula la calificación final de un estudiante a partir de las puntuaciones individuales de cada miembro del Tribunal. 
Los métodos disponibles son:

 * Promedio: la calificación final es la media aritmética de la puntuaciones otorgadas por cada examinador por separado.
 * Mayor: la calificación final es la puntuación más alta de las otorgadas por cada examinador por separado.
 * Menor: la calificación final es la puntuación más baja de las otorgadas por cada examinador por separado.

Adicionalmente, se puede establecer un número mínimo de puntuaciones para calcular la calificación final. 
Si un estudiante no ha sido evaluado por al menos es nº mínimo de examinadores, cada uno con su puntuación, 
entonces la calificación final no es calculada y el estudiante queda no calificado.

';
$string['grades'] = 'Calificaciones de Tribunal'; 
$string['gradinguser'] = 'Calificar estudiante en {$a}';
$string['gradingaverage'] = 'Media';
$string['gradingmax'] = 'Mayor';
$string['gradingmin'] = 'Menor';
$string['mingraders'] = 'Mínimo nº de evaluaciones';
$string['notifiedexams'] = 'Exámenes a notificar';
$string['notifiedexams_help'] = 'Los participantes en estos exámenes mediante Tribunal seleccionados recibiran la notificación sobre los mismos.
Puede seleccionar varios o todos los ítems de la lista. 

El tipo de participación, qué usuarios serán notificados, se define más abajo.';
$string['notifiedusers'] = 'Participantes a notificar';
$string['notifiedusers_help'] = 'El tipo de participación, su papel en el examen por Tribunal, de los usuarios que recibirán la notificación.
Las opciones disponibles son:  

 * Estudiantes: sólo los estudiantes a examinar recibirán la notificación sobre el Tribunal.
 * Examinadores: los miembros del Tribunal evaluador recibirán la notificación sobre el Tribunal.
 * Tutores: los tutores de los estudiantes serán notificados..
 * Docentes: todos los profesores, ya sean miembros evaluadores del Tribunal o tutores de los estudiantes examinados, serán notificados.
 * Todos: todos los participantes, ya sean estudiantes, tutores o evaluadores recibirán la notificación sobre el Tribunal.

';
$string['includedeputy'] = 'Incluir sustitutos';
$string['includedeputy_help'] = 'Si se incluyen, no solo los miembros oficiales del Tribunal, 
sino también los miembrso sustitutos recibirán las notificaciones.';
$string['includepdf'] = 'Generar PDF adjunto';
$string['includepdf_help'] = 'Si se activa, entonces el mensaje de email contendrá un archivo PDF adjunto 
con el texto de la notificación en un documento formal.';
$string['attachname'] = 'Nombre del adjunto';
$string['attachname_help'] = 'El nombre del archivo PDF generado que se utilizará como adjunto en el email. 

debe ser un nombre de archivo válido, sin extensión. La extensión ".pdf" se añadirá automáticamente.';
$string['attachment'] = 'Notificación de Tribunal';
$string['messagesubject'] = 'Asunto';
$string['messagesubject_help'] = 'La línea de "Asunto:" que aparecerá en el mensaje de correo. 

El texto llevará como prefijo el código de la asignatura en la que se encuentra esta actividad Tribunal.

';
$string['messagebody'] = 'Texto del mensaje';
$string['messagebody_explain'] = '<p>El mensaje incluirá automáticamente un enlace a la actividad 
Tribunal que gestiona estos exámenes y notificaciones. </p>
<p>Puede personalizar el mensaje con una serie de elementos codificados %%CLAVE%% que serán sustituidos 
por el valor correspondiente al procesar el envío de la notificación a cada usuario y cada examen. 
Puede ver las claves en el botón de ayuda. Por ejemplo, para que cada persona vea su nombre en el encabezado puede escribir: 

<p>
"Estimado %%NOMBRE%%:   <br >
El examen del Tribunal %%CÓDIGO%% se realizará ..."

</p>';
$string['messagebody_help'] = "
El cuerpo principal del mensaje de notificación.  
El mensaje incluirá automáticamente un enlace a la actividad Tribunal que gestiona estos exámenes y notificaciones. 

Puede personalizar el mensaje con una serie de elementos que serán sustituidos por el valor correspondiente 
al procesar el envío de la notificación a cada usuario y cada examen. Las mayúsculas son importantes, las negrillas no.

 * <strong>%%NOMBREPILA%%</strong>: El nombre de pila del usuario. 
 * <strong>%%APELLIDOS%%</strong>: Los apellidos del usuario. 
 * <strong>%%NOMBRE%%</strong>: Nombre completo del usuario. 
 
 * <strong>%%ROL%%</strong>: El rol o tipo de participación del usuario, bien como examinador, examinando o tutor. 
 * <strong>%%CÓDIGO%%</strong>: La etiqueta o código identificador del Tribunal . 
 * <strong>%%SESIÓN%%</strong>: La sesión de este examen.  
 * <strong>%%FECHA%%</strong>: La fecha en la que se realizará el examen por Tribunal.
 * <strong>%%HORA%%</strong>: La hora del día (HH::MM) a la que se realizará el examen por Tribunal.
 * <strong>%%DÍAHORA%%</strong>: La fecha, día y hora, en la que se realizará el examen por Tribunal.
 
 * <strong>%%DURACIÓN%%</strong>: La duración declarada de la sesión de examen.
 * <strong>%%AULA%%</strong>: El aula, sala o local en el que se realizará el examen.
 * <strong>%%URL%%</strong>: El enlace web, sitio o sala de videoconferencia que se realizará el examen telemático.
 * <strong>%%HOY%%</strong>: La fecha de hoy.
 
 * <strong>%%ESTUDIANTES%%</strong>: La lista de estudiantes a ser examinados por el Tribunal. 
 * <strong>%%TRIBUNAL%%</strong>: La lista de los profesores miembros del Tribunal.
 
";
$string['replace_firstname'] = 'NOMBREPILA';
$string['replace_lastname'] = 'APELLIDOS';
$string['replace_fullname'] = 'NOMBRE';
$string['replace_role'] = 'ROL';
$string['replace_idnumber'] = 'CÓDIGO';
$string['replace_sessionname'] = 'SESIÓN';
$string['replace_examdate'] = 'FECHA';
$string['replace_examtime'] = 'HORA';
$string['replace_examdatetime'] = 'DÍAHORA';
$string['replace_venue'] = 'AULA';
$string['replace_accessurl'] = 'URL';
$string['replace_duration'] = 'DURACIÓN';
$string['replace_students'] = 'ESTUDIANTES';
$string['replace_committee'] = 'TRIBUNAL';
$string['replace_date'] = 'HOY';
$string['logofile'] = 'Archivo de Logo';
$string['logofile_help'] = 'Un archivo gráfico con un logotipo o sello que se incluirá en la esquina superior izquierda del documento.

Debe ser una archivo de imagen (png, jpg etc.).

';
$string['logowidth'] = 'Anchura del Logo';
$string['logowidth_help'] = 'La anchura deseada del logo en la imagen impresa, en mm.';
$string['messagesender'] = 'Firma formal';
$string['messagesender_help'] = 'Una línea de cierre, después del texto principal y del enlace automático a la actividad. 
Debería incluir el cargo o rol de la persona que emite la notificación. 

Por ejemplo, "El secretario de la Comisión de TFG" o cosa similar.

';
$string['signaturefile'] = 'Archivo de firma';
$string['signaturefile_help'] = 'Un archivo de imagen conteniendo una rúbrica, o sello o logo a incluir debajo de la firma formal anterior.';
$string['defaultsubject'] = 'Nombramiento para Tribunal';
$string['defaultbody'] = '<p>Estimado %%NOMBRE%%:</p>
<p>La sesión %%SESIÓN%% del Tribunal %%CÓDIGO%% tendrá lugar ... </p>

<p>Puede personalizar el mensaje con una serie de elementos que serán sustituidos por el valor correspondiente 
al procesar el envío de la notificación a cada usuario y cada examen. <br />Las mayúsculas son importantes, las negrillas no.</p>
<p>Debajo tiene unos campos para especificar una línea de firma y una imagen rúbrica o sello.</p>';
$string['searchprompt'] = 'Teclee o pinche flecha';
$string['nothingselected'] = 'Ninguna selección';
$string['deputy'] = 'Sustituto';
$string['deputytag'] = ' [Sustituto]';
$string['roletag'] = ' ({$a})';
$string['tutortag'] = ' (Tutores: {$a})';
$string['unknowngrader'] = 'Examinador desconocido';
$string['savemembers'] = 'Guardar Tribunal';
$string['confirmdeleteexam'] = 'Ha solicitado borrar la sesión de examen por el Tribunal <strong> {$a} </strong> <br /> 
Borrar una sesión de esamen es permanente, no hay "deshacer".  <br /> <br />
<p>Puede borrar solo la sesión de examen, conservando el Tribunal, sus miembros, para asignarles otras sesiones de examen. 
O bien puede eliminar completamente el Tribunal y su composición de miembros.</p>

';
$string['deleteexamboard'] = 'Eliminar Tribunal';
$string['confirmdeleteuser'] = 'Ha solicitado eliminar del examen <strong>{$a->exam}</strong> al estudiante <strong>{$a->name}</strong>.
<br />Eliminar a una estudiante de la lista de examinados es permanente, pero siempre puede volverlo a añadir en el futuro si es necesario.<br /> 
Al eliminar un estudiante de la lista de examinados en un examen se borran también los vínculos de sus tutores con este examen (no en otros).
<br />
<p>En cualquier caso, un estudiante calificado no será eliminado.</p>';
$string['examhasgrades'] = 'El examen incluye estudiantes que ya han sido calificados.';
$string['examplacedate'] = 'Lugar y Fecha';
$string['updateboard'] = 'Actualizar Tribunal';
$string['boardtitle'] = 'Título';
$string['boardtitle_help'] = 'El título del Tribunal es la palabra usada para refererirse al conjunto de miembros del mismo. 

Si no se desea otra cosa, usar la palabra "Tribunal".';
$string['accessgroup'] = 'Grupo';
$string['accessgroup_help'] = 'Si se establece un grupo concreto entonces solo usuarios pertenecientes a ese grupo, 
tanto estudiante, examinadores o tutores, podrán acceder al mismo. 

Solo se podrá asignar como miembros del tribunal o como estudiantes examinandos (o sus tutores) a personas que pertenezcan a ese grupo. ';
$string['periodlabel'] = 'Convocatoria: {$a}';
$string['examperiod'] = 'Convocatoria';
$string['examperiod_help'] = 'Una de las convocatorias oficiales para exámenes por Tribunal. 

Los tipos de convocatorias y sus nombres se definen a nivel institucional y no se pueden cambiar aquí. 
El profesor solo puede escoger la más apropiada al examen en cuestión.';
$string['examperiods'] = 'Convocatorias';
$string['examperiods_help'] = 'Las convocatorias definidas institucionalmente. 
Los tipos de convocatorias y sus nombres se definen a nivel institucional y no se pueden cambiar aquí. El profesor solo puede escoger la más apropiada al examen en cuestión.
Debe especificar cada Convocatoria en una línea como "código:nombre visible" (sin comillas). El código debe ser una etiqueta de menos de 30 caracteres. 
Puede añadir tantas líneas como Convocatorias necesite. Es recomendable tener una pseudoconvocatoria "-:Otra" para englobar todos los casos especiales. ';
$string['examsession'] = 'Sesión';
$string['examsession_help'] = 'El nombre de la sesión o convocatoria de examen. 

Cada Tribunal puede reunirse varias veces en diferentes sesiones para examinar a los mismos o diferentes estudiantes. 
Cada reunión separada, cada sesión del Tribunal debe tener un nombre distintivo.';
$string['import'] = 'Importar';
$string['import_help'] = 'Permite importar datos de Tribunales de Examen desde un archivo de texto CSV.
Los datos a importar pueden incluir los nombres es identificación del examen y el tribunal, 
así como sus miembros, estudiantes examinados y, en su caso, tutores de los mismos. 

El archivo importado debe contar con una primera file con título que datos se importan en esa columna. 
Los nombres de las columnas deben coincidir con los indicados en esta página más abajo.

Las fechas y tiempos deben introducirse en un formato estándar ISO 8601 o RFC entendible por "strtotime". 
Por ejemplo 01-02-2018 10:00 

Las duraciones deben indicarse en horas, opcionalmente con minutos y segundos, por ejemplo 2:30:05.

';
$string['importedrecords'] = 'Procesados {$a} registros para importación o actualización.';
$string['cannotreadtmpfile'] = 'Archivo inválido o no leído con las columnas CSV correctas.';
$string['export'] = 'Exportar';
$string['export_help'] = 'Permite exportar todos o algunos de los datos sobre Exámenes por Tribunal almacenados en este módulo. 

Puede seleccionar qué columnas de datos exportar, así como el formato de archivo generado. 
';
$string['fixedfields'] = 'Campos obligatorios';
$string['fixedfields_help'] = 'Estos campos serán exportados incondicionalmente en cada entrada. 

Puede considerar incluir el DNI del usuario además de su nombre completo para aquellos campos que hacen referencia a usuarios.
';
$string['optionalfields'] = 'Campos opcionales';
$string['ignoremodified'] = 'Forzar importación';
$string['ignoremodified_help'] = 'Cómo se comportará la importación cuando los datos importados coinciden con entradas existentes.

La opción predefinida (sin marcar) es preservar el contenido ya existente y NO actualizar, ignorando los datos del archivo importado.
Si se marca esta opción, los datos presentes en el archivo importado sobre-escribirán a los existentes previamente en la misma entrada.
';
$string['ignoremodifiedexplain'] = ' desmarcado para ignorar datos importados si ya existen.';
$string['deleteprevious'] = 'Borrar usuarios previos';
$string['deleteprevious_help'] = 'Si se marca, cuando se importe un usuario, sea un miembro del Tribubal o un estudiante a examinar, 
todos los miembros o usuarios anteriores para ese examen serán eliminados antes de importar los nuevos.';
$string['deletepreviousexplain'] = 'Marcar para borrar usuarios anteriores antes de importar.';
$string['userencoding'] = 'Identificador de usuario';
$string['userencoding_help'] = 'El parámetro usado en el archivo de importación para especificar a cada usuario. Puede ser uno de:
 
  * Moodle ID
  * DNI
  * nombre de usuario

El nombre y apellidos NO pueden ser usados. Los valores importados se cotejarán con los valores correspondientes 
almacenados en las tablas de usuarios para identificar a la persona en cuestión.';
$string['exportfields'] = 'Datos a exportar';
$string['userid'] = 'ID de usuario';
$string['useridnumber'] = 'Incluir DNI';
$string['useridnumbercsv'] = 'DNI';
$string['exportfileselector'] = 'Archivo a generar';
$string['exportfilename'] = 'Nombre del archivo (sin ext.) ';
$string['exportformatselector'] = 'Formato de exportación';
$string['exportedexams'] = 'Tribunales a exportar';
$string['exportedexams_help'] = 'Los datos de qué Tribunales de todos los existentes se incluirán en la exportación. 
Se debe seleccionar al menos uno.';
$string['exportlistby'] = 'Modo de listado';
$string['exportlistby_help'] = 'Cómo se organizará el listado de líneas exportadas. 
Hay varias posibilidades:

 * Por Tribunal y sesión: cada sesión de examen por Tribunal ocupara una fila. Varios de los elementos (miembros, examinados) pueden ser datos multilínea anidados.
 * Por Estudiante examinado: habrá una fila separada para cada estudiante en cada Tribunal de examen.
 * Por Miembro del tribunal: en cada sesión de examen, habrá una línea separada para cada miembro del tribunal. 

';
$string['listbyexam'] = 'Por Tribunal y sesión';
$string['listbyuser'] = 'Por Estudiante examinado ';
$string['listbymember'] = 'Por Miembro del tribunal';
$string['allocmemberorder'] = 'Orden de estudiantes';
$string['allocmemberorder_help'] = 'Cómo se ordena la lista de estudiantes y se asigna un nº de orden a cada uno para su participación en el Tribunal.';
$string['tutorasother'] = 'No se puede duplicar un Tutor como principal o cotutor';
$string['choosereorder'] = 'Ordenamiento';
$string['noinputdata'] = 'NO hay datos de entrada';
$string['errorfieldcolumns'] = 'Nomber de datos no identificados o falta un dato obligatorio.';
$string['skippedlines'] = 'Esta líneas NO fuero procesadas debido a errores o conflictos.';
$string['assignednusers'] = 'Se han realizado {$a->count} asignaciones de {$a->users} estudiantes en {$a->exams} exámenes';
$string['exportexams'] = 'Exámenes a exportar';
$string['notification_moreinfo'] = 'Puede verificar los detalles en el Campus virtual en {$a}';
$string['controlemailsubject'] = '[{$a->shortname}]: Resumen de notificaciones de {$a->modname} para {$a->usertype} ';
$string['controlemailbody'] = 'Se han enviado {$a->count} notificaciones a {$a->usertype} en {$a->modname}';
$string['examiners'] = 'Examinadores';
$string['tutors'] = 'Tutores';
$string['staff'] = 'Profesores';
$string['allusers'] = 'Todos los participantes';
$string['downloadfile'] = 'Descargar archivo {$a}';
$string['gradeusers'] = 'Calificar a los estudiantes';
$string['discharge'] = 'Motivo de la indisponibilidad';
$string['discharge_help'] = 'Los miembros del tribunal deben indicar una justificación para validar su no participación en un Tribunal.';
$string['dischargeexplain'] = 'Justificación';
$string['dischargeexplain_help'] = 'Explicaciones adicionales que pueden ser necesarias para justificar la no disponibilidad para ser mimbro del Tribunal.';
$string['confirmavailable'] = 'Disponible en otras sesiones';
$string['confirmavailable_help'] = 'Indica si el usuario, que no está disponible la sesión indicada, 
podría asistir como miembro del tribunal en otras de las sesiones programadas, o bien su no-disponibilidad se extiende a todo el periodo.';
$string['discharges'] = 'Motivos de no confirmación';
$string['discharges_help'] = 'Los motivos válidos para NO confirmar la participación como miembro de un Tribunal de Examen.';
$string['discharge_holidays'] = 'Vacaciones';
$string['discharge_illness'] = 'Enfermedad';
$string['discharge_study'] = 'Licencia de estudios';
$string['discharge_maternal'] = 'Baja parental';
$string['discharge_congress'] = 'Asistencia a Congreso';
$string['discharge_service'] = 'Servicio inexcusable';
$string['discharge_leave'] = 'Baja laboral';
$string['discharge_other'] = 'Otro';
$string['discharge_other1'] = 'Otro';
$string['discharge_other2'] = 'Otro';
$string['discharge_other3'] = 'Otro';
$string['confirm'] = 'Confirmar asistencia';
$string['unconfirm'] = 'Justificar ausencia';
$string['remindertask'] = 'Recordatorios de examen por Tribunal';
$string['remindername'] = 'Estimado {$a}: <br />';
$string['remindersubject'] = '[{$a->shortname}]: Exam reminder {$a->shortname}';
$string['reminderas'] = ' (as {$a})';
$string['reminderbody'] = '<p>Este es un mensaje automático para recordarle su participación en un examen mediante Tribunal. <br /></p>
<p>
Examen: {$a->title} {$a->idnumber} <br />
Rol: {$a->role} <br />
Fecha: {$a->examdate} <br />
Lugar: {$a->venue} <br />
</p>
<p>
Puede consultar los detalles en el Campus virtual, en la actividad {$a->link}.
</p>
';
$string['remindercontrolsubject'] = '[{$a->shortname}]: Resumen de recordatorios de Tribunal en {$a->modname}';
$string['remindercontrolbody'] = 'Se han enviado {$a->count} recordatorios de participación en Tribunales para {$a->usertype} en {$a->modname}';
$string['bulkaddexam'] = 'Agregar lote de exámenes';
$string['bulkaddnum'] = 'Nº de exámenes a añadir';
$string['bulkaddnum_help'] = 'Esta herramienta agregará una serie de exámenes denominados igual y numerados correlativamente. 
Para ello se debe añadir un marcador al texto del Código identificador. Por ejemplo, #. 
Los números serán añadidos allá donde aparezca el carácter o elemento de marcador de sustitución en el texto del Código identificador.

Se debe indicar el número total de exámenes de exámenes a añadir en el lote y el primer número de la serie. 
';
$string['bulkaddstart'] = 'Empezar en';
$string['bulkaddreplace'] = 'Marcador';
$string['submitbulkaddexam'] = 'Agregar lote';
$string['submissionstatus'] = 'Ítem evaluable y datos complementarios';
$string['viewgraded'] = 'Detalles de calificación';
$string['viewgrading'] = 'Calificar estudiante';
$string['gradeoutof'] = 'Calificación sobre {$a}';
$string['gradesaved'] = 'Calificacón guardada';
$string['visibility_explain'] = 'Los ítems ocultos están inactivos, visibles sólo para los gestores.';
$string['viewgradingdetails'] = 'Clic para ver detalles de la calificación por criterio.';
$string['usergrades'] = 'Calificaciones de usuarios';
$string['synchusers'] = 'Actualizar grupos y accesos';
$string['foruser'] = 'Participante';
$string['editchangetext'] = 'Cambiar este dato';
$string['editchangenewvalue'] = 'Nuevo valor para {$a}';
$string['moveusers'] = 'Cambiar estudiantes de sesión de examen';
$string['moveusers_help'] = 'Esta página permite cambiar la sesión de examen asignada a varios estudiantes de este exaem. 
En la práctica eso significa transferirlos a otra sesión diferente del Tribunal. 

Si este módulo emplea Tutores, la transferencia solo tendrá lugar efectivamente si no hay conflictos 
entre los Miembros existentes del Tribunal y los tutores del estudiante (si los hay). 
Esto está garantizado para movimientos entre sesiones del mismo Tribunal. 
';
$string['moveto'] = 'Mover estudiantes';
$string['movetoexam'] = 'Sesión de destino';
$string['movetoexam_help'] = 'Debe seleccionar una Sesión de Examen, según código de tribunal, convocatoria y sesión. 

Los estudiante serán transferidos a esta sesión';
$string['movetoreturn'] = 'Volver a';
$string['movetoreturn_help'] = 'La página de Examen que mostrar tras hacer la transferencia de los estudiantes a la The Exam page to view after the Students are transfererd to a the new Exam session';
$string['movetokeep'] = 'Página de la sesión original';
$string['movetonew'] = 'Página de la sesión de destino';
$string['movetoconflicts'] = 'Algunos estudianet no han sido transferidos debido a conflictos: <br /> ';
$string['movetoerror'] = 'Datos para la transferencia de estduiantes inválidos o ausentes. No se ha ejecutado.';
$string['movetomoved'] = 'Transferidos {$a} estudiantes a otra session de examen';

$string['newexamsession'] = 'Nueva sesión para el Tribunal';
$string['url'] = 'URL';
$string['accessurl'] = 'Url para acceso web';
$string['accessurl_help'] = 'Escriba aquí la URL completa, con la parte http(s)://, y válida,  
del sitio o canal web en el que tendrá lugar em Examen vía web o videoconferencia.  . ';
$string['accessurltext'] = 'Acceso Web';
$string['accessurllabel'] = 'Para acceso telemático: {$a}';

$string['upload_board'] = 'Informes del Tribunal (privado)';
$string['upload_board_help'] = 'Archivos accesibles por todos los miembros del Tribunal, 
pero NO por por otros participantes como estudiante sot utores.  

Por ejemplo Informes de Similitud, Plagio u otros infomes privados
';
$string['upload_examination'] = 'Archivos del examen (público)';
$string['upload_examination_help'] = 'Archivos públicos, accesibles por cualquier particpante, 
asociados a un Examen particular para un estudiante individualizado. 

Por ejemplo, el Actilla de TFG.';
$string['upload_member'] = 'Informe de evaluador (privado)';
$string['upload_member_help'] = 'Archivos accesibles solo por para miembro individual de un Tribunal evaluador, 
de forma separada, no editable por otros. 

Por ejemplo, el Baremo de cada miembro del Tribunal sobre un estudiante.
';
$string['upload_tutor'] = 'Archivos del Tutor';
$string['upload_tutor_help'] = 'Archivos depositados por el Tutor. 

Son accesibles por los Miembros del Tribunal (que NO pueden cambiarlo) pero no por los estudiantes concernidos. ';

$string['uploadmaxfiles'] = 'Máximo de ficheros';
$string['uploadmaxfiles_help'] = 'Número de ficheros que se pueden depositar como máximo en cada área de almacenamiento de este módulo.';
$string['uploadmanagefiles'] = 'Gestionar {$a}';
$string['filesfor'] = 'Ficheros asociados al estudiante: ';
$string['files'] = 'Ficheros depositados';
$string['files_help'] = 'Puede subir nuevos ficheros o gestionar los existentes, borrándolos, 
renombrándolos o editándolos.  

Por favor, recuerde que hay un límite par el número de archivos que puede contener cada area de almacenamiento,
indicado por los parámetros mostrados encima de la caja de arrastre de archivos.';
$string['settingsgeneral'] = 'General';
$string['examaccess'] = 'Acceder';
$string['viewsubmit'] = 'Entrega';
$string['viewsubmission'] = 'Ver ítems evaluables';
$string['examsubmit'] = 'Entregar';
$string['uploadtutorfiles'] = 'Subir Informe';
$string['usersubmission'] = 'Ítems evaluables en {$a}';
$string['submissionsaved'] = 'Entrega guardada';
$string['submissionnotsaved'] = 'Entrega NO guardada';
$string['submissiontext'] = 'Texto de la presentación';
$string['submissiontext_help'] = 'Un texto que acompaña a los ítems evaluables. 
Puede contener enlaces a elementos externos o vídesos embebidos, por ejemplo.';
$string['gradeableexternal'] = 'Ítems externos: ';
$string['gradeableinternal'] = 'Ítems locales: ';
$string['upload_user'] = 'Archivos de estudiante';

