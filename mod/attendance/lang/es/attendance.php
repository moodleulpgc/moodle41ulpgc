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
 * Strings for component 'attendance', language 'es', branch 'MOODLE_39_STABLE'
 *
 * @package   attendance
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['Lacronym'] = 'R';
$string['Lfull'] = 'Retrasado';
$string['place'] = 'Ubicación';
$string['submitplace'] = 'Reservar plaza';
$string['updateplace'] = 'Actualizar plaza';
$string['seatcol'] = 'Columna';
$string['seatrow'] = 'Fila {$a}';
$string['seated'] = 'Ocupado';
$string['setunmarked'] = 'Configurado automáticamente cuando no calificado';
$string['setunmarked_help'] = 'Si se habilita en la sesión, configura este estatus si es que un estudiante no ha calificado sus propia asistencia.';
$string['statussetsettings'] = 'Config. Estados';
$string['statusunselected'] = 'no seleccionados';
$string['studentavailability'] = 'Disponible para estudiantes (minutos)';
$string['studentavailability_help'] = 'Cuando los estudiantes marcan sus propia asistencia en un formulario, el número de minutos después de que inicia la sesión en los cuales este Estado está disponible. <br/>
Si estuviera vacío, este Estado siempre estará disponible. Si se configura a 0 siempre estará oculto a los estudiantes.';
$string['seatbookingadvance'] = 'Antelación de reserva de plaza';
$string['seatbookingadvance_desc'] = 'El número de dias de antelación para la reserva de plazas. 
Un estudiante podrá reservar plazas en sesiones futuras NO más distantes de este nº de días. Evita reservas masivas por un largo periodo. ';
$string['uploadattendance'] = 'Actualizar asistencia por CSV';
$string['marksessionimportcsvhelp'] = 'Esta página permite importar un archivo de text CSV conteniendo un identificador de usuario y un estado - 
El Estado puede ser un etiqueta de estado o el mometo (tiempo) en que se registró la asistencia del usuario. 

Si se importa una marca de tiempo se asignará en Estado más alto disponible en ese momento.';
$string['useseating'] = 'Reserva de plazas';
$string['useseating_help'] = 'Si se activa, los estudiantes podrán ver un esquema 
de ubicaciones/plazas en el Aula de clase y podrán resevar una plaza donde ubicarse.';
$string['set_by_student'] = 'por estudiante';
$string['seatlabel'] = 'F{$a->row} C{$a->col}';
$string['seattaken'] = 'Plaza ya ocupada, por favor seleccione otra diferente';
$string['seatblackboard'] = 'Pizarra / Pantalla';
