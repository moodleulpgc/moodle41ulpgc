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
 * Strings for component 'offlinequiz', language 'es', branch 'MOODLE_35_STABLE'
 *
 * @package   offlinequiz
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['modulename'] = 'Examen escaneable';
$string['modulename_help'] = 'Este módulo permite al Profesor diseñar exámenes en papel usando el Banco de preguntas de Moodle.

Se añaden preguntas de opción múltiple a un cuestionario, pero en lugar de ser respondidas por los estudiantes en línea, en la web, se generan documentos (PDF, DOCX o LaTeX) que se pueden imprimir.

Se pueden generar varias planillas a partir de las mismas o distintas preguntas. 
Se generan tanto documentos con los enunciados de las preguntas como hojas de respuestas con casillas para marcar la respuesta correcta. 

Las hojas de respuesta pueden ser escaneadas, importadas al Campus y procesadas para la puntuación automatizada del examen. 
Se analizan las casillas marcadas y se les asigna una puntuación. 
El profesor controla cómo se presentan los resultados a los estudiantes, solo la puntuación o alguna infromación adicional.';
$string['modulenameplural'] = 'Exámenes escaneables';
$string['pluginname'] = 'Examen escaneable';
$string['pluginadministration'] = 'Administración de Examen escaneable';
$string['addarandomquestion_help'] = 'Moodle agrega una selección aleatoria de preguntas de opción múltiple (o preguntas de todo o nada) a la actual Planilla de prueba fuera de línea. 
Puede establecer el número de preguntas añadido. Las preguntas se eligen de la categoría actual de la cuestión (y si, sus subcategorías).';
$string['answerformforgroup'] = 'Formulario de respuesta para planilla {$a}';
$string['copyselectedtogroup'] = 'Añadir las preguntas seleccionadas a la planilla {$a}';
$string['correctforgroup'] = 'Respuestas correctas para la planilla {$a}';
$string['correctupdated'] = 'Formulario de corrección actualizado para la planilla {$a}.';
$string['createofflinequiz'] = 'Crear exámenes';
$string['createpdfforms'] = 'Generar documentos';
$string['createpdfs'] = 'Documentos imprimibles';
$string['createquiz'] = 'Crear formularios';
$string['createpdferror'] = 'El formulario para la planilla {$a} no pudo ser creado. Es posible que no haya preguntas en la planilla.';
$string['downloadpartpdf'] = 'Descargar el fichero PDF para la lista "{$a}" ';
$string['disableimgnewlines'] = 'Eliminar líneas alrededor de imágenes';
$string['disableimgnewlines_help'] = 'Este parámetro elimina las líneas en blanco antes y después de una imagen insertada en el texto de las preguntas.
Debe usarse con precaución y verificar el resultado.';
$string['editgroupquestions'] = 'Editar preguntas dla planilla';
$string['editgroups'] = 'Editar planillas offline';
$string['editingofflinequiz'] = 'Editando preguntas de la planilla';
$string['emptygroups'] = 'Algunas de los planillas de cuestionario offline (sin conexión) están vacíos. Por favor añada algunas preguntas.';
$string['forautoanalysis'] = 'Para corrección automatizada';
$string['formforcorrection'] = 'Formulario de correción para la planilla {$a}';
$string['formforgroup'] = 'Formulario de preguntas para la planilla {$a}';
$string['formforgroupdocx'] = 'Formulario de preguntas para la planilla {$a} (DOCX)';
$string['formforgrouplatex'] = 'Formulario de preguntas para la planilla {$a} (LATEX)';
$string['formspreview'] = 'Previsualización de preguntas';
$string['gradedscannedform'] = 'Planilla escaneada y puntos';
$string['group'] = 'Planilla';
$string['groupquestions'] = 'Planillas de preguntas';
$string['idnumber'] = 'Nº de Identificación';
$string['importerror13'] = 'No hay datos dla planilla';
$string['info'] = 'Información';
//instruction1 strings with <br /> AND LF are interpeted as with two LF, two lines.
$string['instruction1'] = 'Esta Hoja de Respuestas será escaneada automáticamente. Por favor, no la doble o la manche. 
Use un bolígrafo azul o negro para marcar las respuestas con una X:';
$string['instruction2'] = '¡Sólo las marcas claras pueden ser interpretadas correctamente! 
Si quiere invalidar una marca, anularla, desmarcarla, rellene completamente la casilla con color. Este campo será interpretado como una casilla vacía:';
$string['instruction3'] = 'Las casillas anuladas (rellenas, corregidas) no se pueden marcar otra vez. 
Por favor, no escriba <b>nada</b> fuera de las casillas.';
$string['invigilator'] = 'Profesor';
$string['marks'] = 'Puntuación';
$string['name'] = 'Examen';
$string['noattempts'] = '¡No se han importado resultados!';
$string['nopdfscreated'] = '¡No se han generado documentos!';
$string['noquestions'] = 'Algunas planillas de cuestionario offline estas vacíos. Por favor, añada algunas preguntas.';
$string['noquestionsfound'] = '¡No hay preguntas en la planilla {$a}!';
$string['numbergroups'] = 'Número de planillas';
$string['offlinequizisclosed'] = 'Examen cerrado';
$string['offlinequizisclosedwillopen'] = 'Examen cerrado (se abre el {$a})';
$string['offlinequizisopen'] = 'El Examen está abierto';
$string['offlinequizisopenwillclose'] = 'Examen abierto (se cierra el {$a})';

$string['participants_help'] = '<p>Las Listas de participantes se han diseñado para gestionar Exámenes con multitud de estudiantes. 
Pueden ayudar al profesor a comprobar qué estudiantes se han presentado y si todos lso resultados se han importado correctamente.

Se pueden añadir estudiantes a varias listas para el mismo Examen. Por ejemplo, cada lista puede corresponder a los convocados en un Aula concreta. 
O pueden ser miembros de un grupo especial. Las listas de partcipantes se pueden descargar, imprimir y marcar con cruces (como la planilla de respuestas). 
Posteriormente pueden ser escanedas e importadas para registrar la asistencia de cada estudiante. 

Por favor, evite escribir en los códigos de barras usados para identificar a cada estudiante, o no se podrá establecer correctamente la correspondencia a un usuario  de la plataforma.</p>';
$string['partimportnew_help'] = '<p>
Aquí puede subir las Listas de participantes rellenadas (mercadas). Puede subi runa única página como una imagen o múltiples páginas en un archivo ZIP. 
Los nombres de ficheros NO son relevantes, pero NO deben contener espacios, letras acentuadas o caracteres especiales. 

Las imágenes deben ser GIFs, PNGs o TIFs. Se recomienda una resolución entre 200 y 300 ppp (dpi).</p>';
$string['preventsamequestion'] = 'Prevenir el uso múltiple de la misma pregunta en planillas diferentes';
$string['preview'] = 'Previsualización';
$string['previewforgroup'] = 'Previsualización para la planilla {$a}';
$string['printstudycodefield'] = 'Campo para código asignatura en la hoja de preguntas';
$string['printstudycodefield_help'] = 'Si se marca, se añadirá espacio para incluir el código de asignatura la primera página de la hoja de preguntas.';
$string['questioninfoanswers'] = 'Nª de respuestas correctas';
$string['questioninfonone'] = 'Ninguna';
$string['questionsingroup'] = 'Preguntas en planilla';
$string['removeselected'] = 'Eliminar seleccionadas';
$string['reordergroupquestions'] = 'Reordenar Planilla de Preguntas';
$string['repaginatecommand'] = 'Repaginar';
$string['reviewbefore'] = 'Permite revisión cuando el Cuestionario está abierto';
$string['reviewclosed'] = 'Solo después del cierre del cuestionario';
$string['reviewimmediately'] = 'Inmediatamente después del intento';
$string['reviewincludes'] = 'Visualización de ';
$string['reviewofresult'] = 'Revisión del resultado';
$string['reviewoptions'] = 'Información a estudiantes';
$string['reviewoptionsheading'] = 'Opciones de revisión';
$string['reviewoptions_help'] = 'Con estos parámetros puede controlar qué información se muestra a los estduiantes después de que se importen y procesen los resultados.
También puede definir el periodo de aprtura y cierre del Informe de resultados. 
Estas casillas controlan: 

<table>
<tr><td style="vertical-align: top;"><b>El intento</b></td><td>
En texto de las cuestiones y respuestas se mostrará a los estudiantes. Podrá ver cada uno qué opción marcó, pero no se indicará la respuesta correcta.</td>
</td></tr>
<tr><td style="vertical-align: top;"><b>Si fuese correcta</b></td><td>
Esta opción solo puede activarse si previamente se ha marcado "El intento". 
Si se marca, los estudiantes verán cuál de las respuestas marcadas es correcta (fondo verde) o incorrecta (fondo rojo).
</td></tr>
<tr><td style="vertical-align: top;"><b>Puntuación</b></td><td>
Se muestran la planilla usada (e.g. B), puntuaciones (puntuación bruta obtenida, total para todas las preguntas, porcentaje, e.g. 40/80 (50)) y la calificación final 
(e.g. 5 de 10).
Además, si también se ha marcado "El intento", entonces se muestra la puntuación alcanzada y la máxima posible para cada pregunta.
</td></tr>
<tr><td style="vertical-align: top;"><b>Respuesta correcta</b></td><td>
Se muestran que opciones son correctas o incorrectas para cada pregunta. Este ajuste solo está disponible si se ha marcado previamente "El intento".
</td></tr>
<tr><td style="vertical-align: top;"><b>Planilla escaneada</b></td><td>
Se muestra la hoja de respuestas escaneada. Las casillas marcadas se muestran con cuadrados verdes.
</td></tr>
<tr><td style="vertical-align: top;"><b>Planilla escaneada y puntos</b></td><td>
Se muestra la hoja de respuestas escaneada. Las casillas marcadas se muestran con cuadrados verdes.
Se resaltan las casillas marcadas incorrectas y las dejadas en blanco. 
Además, una tabla muestra la punetuación máxima y la obtenida en cada una de las preguntas.
</td></tr>
</table>';

$string['review'] = 'Revisión';
$string['save'] = 'Guardar';
$string['scannedform'] = 'Planilla escaneada';
$string['selectagroup'] = 'Seleccione una planilla';
$string['showquestioninfo_help'] = 'Con este parámetro puede especifiar qué información adicional se imprime en la hoja de preguntas.
Puede ser una de estas opciones:

<ul>
<li> Ninguna
<li> Tipo de pregunta - Dependiendo del tipo de pregunta, se imprimirá si se trata de una única respuesta correcta, varias posibles respuestas correctas o todo o nada.
<li> Nº de respuestas correctas - El número exacto de opciones correctas.
</ul>';
$string['shufflequestionsselected'] = 'Se ha configurado "Barajar preguntas", 
por lo tanto algunas acciones relativas a la paginación no están activas. 
Para cambiar la configuración,  {$a}.';
$string['statsoverview'] = 'Statistics Overview';
$string['studycode'] = 'Cód. Asignatura';
$string['studycodecourse'] = '{$a->shortname} - {$a->fullname}';
// ecastro ULPGC
$string['samefformat'] = 'Aplicar a hoja de respuestas correctas';
$string['samefformat_help'] = 'Si se activa, la Hoja de Respuestas correctas estará en el mismo formato de archivos que la hoja de preguntas. 
Si no, esta hoja será siempre un archivo PDF.';
$string['questionspercolumn'] = 'Preguntas por columna';
$string['questionspercolumn_help'] = 'El nº máximo de preguntas (filas de casillas) en cada columna de la Hoja de respuestas. 

Si se emplea rotulación en bloques es mejor usar en número de preguntas divisible en el número de bloques empleado.';
$string['labelblocks'] = 'Rotulación en bloques';
$string['labelblocks_help'] = 'Agrupa las casillas en bloques con una fila de etiquetas identificadoras para facilitar el marcado correcto de las casillas. 
El parámetro especifica el nº de bloques rotulados en los que se dividirá la columna. 

En nº de preguntas por columna condiciona el nº de bloques rotulados que caben en la página. 
Si hay 25 preguntas por columnas solo puede haber un (1) bloque. 2 bloques si 24 preguntas por columna,  3 si 21, o 4 si 20 preguntas por columna
Para otras combinaciones aparecerá un mensaje de error si el nº de bloques es demasiado alto. ';
$string['labelblockstoohigh'] = 'Nº de bloques demasiado elevado, reducir';
$string['qsheetcols'] = 'Columnas en hoja de preguntas';
$string['qsheetcols_help'] = 'Si se especifica, el número de columnas separadas empleadas para componer la hoja de preguntas.';
$string['markcorrect'] = 'Correcto';
$string['markinvalid'] = 'Anulada';
$string['useridnumber'] = 'DNI';
$string['usermid'] = 'MoodleID';
$string['correctionsheet'] = 'Hoja de Preguntas con respuestas correctas';
