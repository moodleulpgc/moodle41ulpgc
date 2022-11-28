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
 * Strings for the quizaccess_makeexamlock plugin.
 *
 * @package    quizaccess
 * @subpackage makeexamlock
 * @copyright  2016 Enrique Castro
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

$string['makeexamlock:viewdesc'] = 'Ver explicación para profesores';
$string['makeexamlock:manage'] = 'Gestionar bloqueo por examen';
$string['makeexamlock:editquestions'] = 'Editar pregutas pese al bloqueo';
$string['makeexamlock:unlock'] = 'Desbloquear el cuestionario';
$string['makeexamlockingmsg'] = 'Cuestionario asociado a un Examen oficial de convocatoria. 
<br />Acceso controlado por el Registro de Exámenes.';
$string['pluginname'] = 'Bloqueo por Crear Examen';
$string['gotomakeexam'] = 'Use <strong>Crear Examen</strong> para generar una versión de examen';
$string['makeexamlock'] = 'Bloqueo Crear Examen';
$string['makeexamlock_help'] = 'El bloqueo Crear Examen permite prevenir cualquier acceso de estudiantes a este cuestionario y sus preguntas.
Crear Examen es un ayudante para generar Exámenes validados (trabajando con el módulo Registro de Exámenes). 
Si se activa, entonces se limitará el acceso a lo sintentos de responder al Cuestionario.

 * NO: no hay bloqueo.
 * Todos: se impide el acceso a todos los intentos, con cualquier tipo de preguntas.
 * Versiones de Examen: si se elige una versión de Examen, 
 solo se podrán contestar cuestionarios que contengan las preguntas validadas de esa particular versión del Examen aprobado por la Junta. 
 

Los Profesores podrán acceder para añadir y editar preguntas y componer exámenes vía Crear Exámenes.';
$string['explainmakeexamlock'] = 'NO se permiten intentos por estudiantes. Cuestionario usado sólo para generar exámenes con Crear Examen.';
$string['enabled'] = 'Bloqueo Generar examen habilitado ';
$string['allowdisable'] = 'Profesores pueden desactivar la regla';
$string['enabledbydefault'] = 'Activado por defecto en nuevas instancias de cuestionario';
$string['examregmode'] = 'Modo de búsqueda del Registro de exámenes';
$string['examregmode_desc'] = 'Cómo encontrar el Registro de Exámenes primario asociado a la gestión de estos exámenes';
$string['modeexamreg'] = 'ID única del Registro primario a usar';
$string['modeidnumber'] = 'Mod IDnumber de instancia de Registro de exámenes en el mismo curso';
$string['examreginstance'] = 'Instancia del Registro de exámenes';
$string['examreginstance_desc'] = 'Un identificador para la búsqueda, 
bien una ID única de un Registro primario o el idnumber de una instancia del registro de exámenes en el missmo curso.';
$string['examprefix'] = 'Prefijo en idnumber de Cuestionario';
$string['examprefix_desc'] = 'Si se usa, un prefijo para identificar idnumbers de cuetionarios normalizados con convocatoria y turno. ';
$string['notbookedlockingmsg'] = 'No existe una inscripción a examen válida a su nombre para este examen. {$a}';
$string['notreadylockingmsg'] = 'El Cuestionario no está validado para Examen oficial. No disponible. {$a}';
$string['singleversionlockingmsg'] = 'Cuestionario asociado al Registro de Exámenes. No disponible. {$a}';
$string['wrongexammsg'] = 'El Cuestionario NO corresponde al Examen oficial asociado. {$a}';
$string['examchangedmsg'] = 'El Cuestionario ha sido modificado después de la validación como Examen oficial. No disponible.';
$string['requirebooking'] = 'Requerir inscripción';
$string['requirebooking_desc'] = 'Si se activa, los estudiantes deberán contar con una inscripción a Examen válida en Registro de Eámenes para poder acceder a las preguntas del Cuestionario y realizar un intento.';
