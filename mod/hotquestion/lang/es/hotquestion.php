<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Strings for component 'hotquestion', language 'es', version '3.9'.
 *
 * @package     hotquestion
 * @category    string
 * @copyright   1999 Martin Dougiamas and contributors
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['ago'] = 'Hace {$a}';
$string['allowanonymouspost'] = 'Permitir publicar pregunta de forma anónima';
$string['allowanonymouspost_descr'] = 'Si se habilita, se pueden publicar preguntas de forma anónima, y si se aprueba que se vean, los votos de calor pueden ser realizados por todo el mundo.';
$string['allowanonymouspost_help'] = 'Si se habilita, se pueden publicar preguntas de forma anónima, y si se aprueba que se vean, los votos de calor pueden ser realizados por todo el mundo.';
$string['allowauthorinfohide'] = 'Permitir ocultar nombres de autores';
$string['allowauthorinfohide_help'] = 'Si se marca, entonces el nombre del autor de una pregunta puede estar oculto a los estudiantes, 
pero siempre visible para los profesores, ';
$string['allowauthorinfohide_descr'] = 'Si se marca, entonces el nombre del autor de una pregunta puede estar oculto a los estudiantes, 
pero siempre visible para los profesores, ';
$string['alwaysshowdescription'] = 'Mostrar siempre la descripción';
$string['alwaysshowdescription_help'] = 'Si se deshabilita, la Descripción de Pregunta Caliente no será visible para los estudiantes.';
$string['anonymous'] = 'Anónima';
$string['approvallabel'] = 'Título de columna de Aprobación requerida';
$string['approvallabel_descr'] = 'Especifique una etiqueta, un texto, a usar como Título en la columna de Aprobación.';
$string['approvedyes'] = 'Aprobada';
$string['approvedno'] = 'No aprobada';
$string['authorinfo'] = 'Publicada por {$a->user} en {$a->time}';
$string['calendarend'] = '{$a} cierra';
$string['calendarstart'] = '{$a} abre';
$string['connectionerror'] = 'Error de conexión';
$string['content'] = 'Contenido';
$string['csvexport'] = 'Exportar a .csv';
$string['deleteentryconfirm'] = 'Confirmar que desea eliminar entrada';
$string['deleteroundconfirm'] = 'Confirmar que desea eliminar ronda';
$string['description'] = 'Descripción';
$string['displayasanonymous'] = 'Mostrar como anónima';
$string['entries'] = 'Entradas';
$string['eventaddquestion'] = 'Añadir una pregunta';
$string['eventaddround'] = 'Abrió una nueva ronda';
$string['eventdownloadquestions'] = 'Descargar preguntas';
$string['eventremovequestion'] = 'Borrar pregunta';
$string['eventremoveround'] = 'Borrar ronda';
$string['eventremovevote'] = 'Voto eliminado';
$string['eventupdatevote'] = 'Voto actualizado';
$string['exportfilename'] = 'preguntas.csv';
$string['exportfilenamep1'] = 'Sitio_Completo';
$string['exportfilenamep2'] = '_HQ_Preguntas_Exportadas_En_';
$string['for'] = ' para el sitio: ';
$string['heat'] = 'Valoración';
$string['heaterror'] = 'Demasiados votos, supera límite máximo';
$string['heatlabel'] = 'Título de columna Valoración';
$string['heatlabel_descr'] = 'Especifique una etiqueta, un texto, 
a usar como Título en la columna de Valoración de las Cuestiones.';
$string['heatlimit'] = 'Límite por defecto de Votos';
$string['heatlimit_descr'] = 'Especifique el valor por defecto para el nº de votos que un usuario puede emitir (por ronda). 
Cero oculta la columna Valoración.';
$string['heatlimit_help'] = 'Especifique el valor por defecto para el nº de votos que un usuario puede emitir (por ronda). 
Cero oculta la columna Valoración.';
$string['heatvisibility'] = 'Visibilidad de columna de Valoración';
$string['heatvisibility_descr'] = 'Si se habilita, la columna de Calor es visible, en caso contrario está oculta.';
$string['heatvisibility_help'] = 'Si se habilita, la columna de Calor es visible.';
$string['hotquestion'] = 'Pregunta destacable';
$string['hotquestion:addinstance'] = 'Puede añadir nueva Pregunta Caliente';
$string['hotquestion:ask'] = 'Hacer preguntas';
$string['hotquestion:manage'] = 'Gestionar preguntas';
$string['hotquestion:manageentries'] = 'Ver lista de actividades';
$string['hotquestion:view'] = 'Ver preguntas';
$string['hotquestion:vote'] = 'Votar sobre preguntas';
$string['hotquestionclosed'] = 'Esta actividad se cerró en {$a}.';
$string['hotquestionclosetime'] = 'Hora de cierre';
$string['hotquestionintro'] = 'Tema';
$string['hotquestionname'] = 'Nombre de actividad';
$string['hotquestionopen'] = 'Esta actividad se abrirá en {$a}.';
$string['hotquestionopentime'] = 'Hora de apertura';
$string['id'] = 'ID';
$string['inputquestion'] = 'Escriba aquí su pregunta';
$string['inputquestion_descr'] = 'Cambiar Instrucciones para preguntar para poner las que quiera.';
$string['inputquestion_help'] = 'Cambiar Instrucciones para preguntar para poner las que quiera.';
$string['inputquestionlabel'] = 'Cuestiones';
$string['invalidquestion'] = 'Las preguntas vacías son ignoradas.';
$string['modulename'] = 'Cuestiones destacables';
$string['modulename_help'] = 'Una actividad "Cuestiones destacables" permite a los estudiantes publicar preguntas sobre temas de clase, 
en respuesta a solicitudes hechas por sus profesores del curso, y también votar las preguntas realizadas por otros compañeros para identificar las más relevantes, las más destacadas.';
$string['modulenameplural'] = 'Cuestiones destacables';
$string['newround'] = 'Abrir una nueva ronda';
$string['newroundconfirm'] = '¿Está seguro que quiere iniciar una nueva ronda? (¡las preguntas y votos existentes serán archivadas y no podrán ser añadidas!)';
$string['newroundsuccess'] = 'Usted ha abierto exitosamente una ronda nueva.';
$string['nextround'] = 'Ronda siguiente';
$string['noquestions'] = 'Todavía no hay preguntas.';
$string['notapproved'] = '<b>Esta entrada actualmente no está aprobada para verse.<br></b>';
$string['notavailable'] = '<b>¡Actualmente no disponible!<br></b>';
$string['pluginadministration'] = 'Administración de Cuestiones destacables';
$string['pluginname'] = 'Cuestiones destacables';
$string['postbutton'] = 'Publicar pregunta';
$string['previousround'] = 'Ronda anterior';
$string['privacy:metadata:hotquestion_questions'] = 'Información acerca de las entradas del usuario para una actividad Cuestiones destacables dada.';
$string['privacy:metadata:hotquestion_questions:anonymous'] = '¿Está publicada la entrada como anónima?';
$string['privacy:metadata:hotquestion_questions:approved'] = '¿Está aprobada la pregunta para vista general?';
$string['privacy:metadata:hotquestion_questions:content'] = 'El contenido de la pregunta';
$string['privacy:metadata:hotquestion_questions:hotquestion'] = 'La ID de la actividad Cuestiones destacables en la cual fue publicado el contenido.';
$string['privacy:metadata:hotquestion_questions:id'] = 'ID de la entrada.';
$string['privacy:metadata:hotquestion_questions:time'] = 'Hora a la cual fue publicada la pregunta.';
$string['privacy:metadata:hotquestion_questions:tpriority'] = '¿Le ha dado el profesor una prioridad a esta entrada?';
$string['privacy:metadata:hotquestion_questions:userid'] = 'La ID del usuario que publicó esta entrada.';
$string['privacy:metadata:hotquestion_votes'] = 'Información acerca de votos en preguntas.';
$string['privacy:metadata:hotquestion_votes:id'] = 'ID de la entrada.';
$string['privacy:metadata:hotquestion_votes:question'] = 'La ID de la entrada para este voto.';
$string['privacy:metadata:hotquestion_votes:voter'] = 'ID del usuario que votó.';
$string['question'] = 'Pregunta';
$string['questions'] = 'Preguntas';
$string['questionlabel'] = 'Título de columna Preguntas';
$string['questionlabel_descr'] = 'Especifique una etiqueta, un texto, a usar como Título en la columna de Preguntas (o Cuestiones).';
$string['questionsubmitted'] = 'Su pregunta ha sido guardada con éxito.';
$string['questionremove'] = 'Borrar';
$string['questionremovesuccess'] = 'Usted ha borrado exitosamente esa pregunta.';
$string['questionsubmitted'] = 'Su Pregunta ha sido enviada exitosamente.';
$string['removelabel'] = 'Título de columna Borrar';
$string['removelabel_descr'] = 'Especifique una etiqueta, un texto, a usar como Título en la columna de Borrar cuestiones.';
$string['removeround'] = 'Borrar esta ronda';
$string['removedround'] = 'Usted ha borrado exitosamente esta ronda';
$string['removevote'] = 'Eliminar mi voto';
$string['requireapproval'] = 'Aprobación requerida';
$string['requireapproval_descr'] = 'Si se habilita, las preguntas requieren aprobación por un profesor antes de que sean visibles para todos.';
$string['requireapproval_help'] = 'Si se habilita, las preguntas requieren aprobación por un profesor antes de que sean visibles para todos.';
$string['resethotquestion'] = 'Eliminar todas las preguntas y los votos';
$string['returnto'] = 'Regresar a {$a}';
$string['round'] = 'Ronda {$a}';
$string['showrecentactivity'] = 'Mostrar actividad reciente';
$string['showrecentactivityconfig'] = 'Todos pueden ver notificaciones en reportes de actividad reciente.';
$string['teacherpriority'] = 'Prioridad';
$string['teacherprioritylabel'] = 'Título de columna Proridad';
$string['teacherprioritylabel_descr'] = 'Especifique una etiqueta, un texto, a usar como Título en la columna de Prioridad de las Cuestiones.';
$string['teacherpriorityvisibility'] = 'Visibilidad de columna de Prioridad';
$string['teacherpriorityvisibility_descr'] = 'Si se habilita, la columna de Prioridad de profesor es visible, en caso contrario está oculta.';
$string['teacherpriorityvisibility_help'] = 'Si se habilita, la columna de Prioridad de profesor es visible.';
$string['time'] = 'Hora';
$string['userid'] = 'Userid';
$string['viewallentries'] = '{$a->ucount} usuario(s) publicó/publicaron {$a->qcount} pregunta(s).';
$string['viewentries'] = 'Participación en la ronda actual';
$string['vote'] = 'Votar';
$string['xofn'] = ' de ';


// ecastro ULPGC
$string['gradeslist'] = 'Puntuaciones';
$string['postmaxgrade'] = 'Nº Cuestiones para calificación máxima';
$string['postmaxgrade_help'] = 'El numero de preguntas requeridas para obtener la calificación máxima. 

Esto es nominalmente un contaje de preguntas realizadas, pero el valor alcanzable por un estudiante puede ser incrementado por otros factores. 
Por ejemplo, las preguntas con mayor Valoración puntuan más. 

También por el hecho de votar a otras preguntas. Un estudiante puede mejorar su propia puntuación votando a preguntas realizadas por otras compañeros.';
$string['factorheat'] = 'Factor por Valoración';
$string['factorheat_help'] = 'Un factor a aplicar al contaje de preguntas para puntuación basado en la Valoración (votos recibidos) de cada pregunta. 
Cuantos más votos de otros compañeros reciba una pregunta, más pesa en la puntuación global de su autor.   

Una pregunta realizada pro un estudiante puntúa como 1 + crédito adicional 

    crédito adicional = (votos recibidos) · factor/100. 

Normalmente un factor de valoración del 5% parace apropiado. Esto se traduce en que si 20 compañeros votan una pregunta su valor se duplica para su autor. 
Puede necesitar ajustar esta factor dependiendo del número de estudiantes en su asignatura.
'; 
$string['factorvote'] = 'Factor por Voto';
$string['factorvote_help'] = 'Un factor a aplicar a la suma de votos emitidos por un estudiante al calcular su puntuación. 

Además de escribiendo nuevas preguntas, un estudiante puede puntuar votando las preguntas realizadas por otros, 
para así identificar las más interesantes o relevantes, o problemáticas. 

Esta puntuación es el número de votos emitidos multiplicado por este factor/100. 

    crédito adicional = (votos emitidos) · factor/100. 

El factor debe ser ajustado en función del nº total de votos que un estudiante puede emitir, 
así como el peso deseado para este tipo de participación en la actividad. 
Por ejemplo, si un estudiante puede emitir 5 votos en total, 
un factor de 20(%) significa que votar a 5 compañeros 
tiene el mísmo crédito o puntuación que escribir una nueva pregunta. 
Un factor de 100(%) implica que votar una cuestión planteada por otro rinde el mismo crédito que realizar una nueva pregunta. 
Según lo que se desee se debe ajustar este factor. ';
$string['factorpriority'] = 'Factor por Prioridad';
$string['factorpriority_help'] = 'Un factor para tener en cuenta la Prioridad de la pregunta al calcular la puntuación otorgaqda a su autor. 

La Prioridad es asignada por el profesor. En el cómputo normal cada pregunta "vale" en puntos lo que su factor de prioridad (1, 2, 3 ...).
Para las preguntas sin prioridad (0) se asigna una puntuación fraccionaria correcpondienet a esta afctor en %. 
Esto es un valor de 100 (%) significa que una pregunte sin prioridad (0) cuenta igual que una pregunat de prioridad = 1. ';
$string['rating'] = 'Puntuaciones';
$string['rawgrade'] = 'Puntuación {$a->rawgrade} / {$a->max}';
$string['valueinterror'] = 'El Factor debe ser un número entero positivo.';
$string['votes'] = 'Votos';



