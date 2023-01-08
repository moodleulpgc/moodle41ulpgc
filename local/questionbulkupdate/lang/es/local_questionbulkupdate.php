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
 * Strings for component 'local_questionbulkupdate', language 'es_mx', version '3.9'.
 *
 * @package     local_questionbulkupdate
 * @category    string
 * @copyright   1999 Martin Dougiamas and contributors
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['commonoptionsheader'] = 'Opciones comunes';
$string['donotupdate'] = 'No actualizar';
$string['navandheader'] = 'Actualizar';
$string['pluginname'] = 'Actualización masiva de preguntas';
$string['privacy:metadata'] = 'Este plugin no almacena datos personales';
$string['selectcategoryheader'] = 'Seleccionar categoría para actualizar preguntas';
$string['updatequestions'] = 'Actualizar preguntas';

// ecastro ULPGC 
$string['status'] = 'Estado';
$string['status_help'] = 'Cambia el estado de lista/borrador/oculta de todas las preguntas. 

"Invertir" simplemente alterna el estado actual. Si oculta, muetra y al revés. ';
$string['statushidden'] = 'Ocultar';
$string['statusready'] = 'Mostrar';
$string['statusdraft'] = 'Borrador';
$string['statustoggle'] = 'Invertir';
$string['ownership'] = 'Asignación';
$string['ownership_help'] = 'El usuario al que se atribuye la creación de la pregunta. 
La operación solo cambia el usuario creador para preguntas que carecen de el o bien ya no está matriculado en el curso,
salvo que se especifique otra cosa. 

El campo "modificado por" será siempre actualizado con el usuario que realiza la operación.
Se necesita la capacidad para editar todas las preguntas para cambiar el usuario asignado como creador. ';
$string['applyenrolled'] = 'Aplicar a profesores matriculados'; 
$string['applyenrolled_help'] = 'Si se marca, entonces el usuario especificado arriba será asignado como 
creador de las preguntas incluso para aquellas que tienen como creador a un usuario matriculado actualmenet en el curso. ';
$string['answergrade'] = 'Puntuación de respuestas incorrectas';
$string['answergrade_help'] = 'Esta herramienta modifica la puntuación fraccionaria de las respuestas que tengan los valores seleccionados. 

A esas respuestas se les asignará una puntuación común. 
Bien un valor fijo (especificado más abajo, en "Calificación"), 
o bien una puntuación negativa basada en el nº de opciones incorrectas (Fórmula de corrección de azar): 
puntuación = -1/n, donde n es el nº de opciones de respuesta incorrectas. 
Se consideran respuestas correctas las que tienen asignado una puntuación fraccionaria no nula y positiva. 

El efecto es que si se marcan todas las opciones, la puntuación final de la pregunta será cero.
Es equivalente a la fórmula de corrección  -1 / (n-1) donde n es el nº de opciones totales y hay una única respuesta correcta. ';
$string['answerwrong'] = 'Respuestas consideradas incorrectas';
$string['answerwrong_help'] = 'Las puntuaciones que se considerarán incorrectas durante la operación. 
Esto es, aquellas respuestas cuya puntuación será recalculada o reasignada. 

Se pueden seleccionar varios valores simultáneamente. ';
$string['tagsadd'] = 'Agregar';
$string['tagsremove'] = 'Eliminar';
$string['tagsmanage'] = 'Etiquetas';
$string['tagsmanage_help'] = 'Gestión de etiquetas de las preguntas. 
Puede Agregar o Eliminar etiquetas (si existen) en todas las preguntas de la categoría afectada. 

Aquellas etiquetas que serán agregadas o eliminadas son las que se especifican debajo. ';
$string['weightfixed'] = 'Valor fijo';
$string['weightformula'] = 'Corrección de azar 1/n';
$string['updatedquestions'] = '{$a} preguntas actualizadas';
$string['updatedoptions'] = 'Actualizadas opciones en {$a} preguntas';
$string['updatedanswers'] = 'Actualizadas puntuaciones de respuestas en {$a} preguntas';
$string['updatedtags'] = 'Actualizadas etiquetas en {$a} preguntas';
$string['nothingupdated'] = 'NO hay preguntas actualizadas con estos parámetros. ';
