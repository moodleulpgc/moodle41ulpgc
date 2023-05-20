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
 * Strings for component 'report_attendancetools', language 'en'
 *
 * @package   report_attendancetools
 * @copyright 2023 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['attendancetools'] = 'Herramientas de Asistencia';
$string['attendancetools:view'] = 'Ver Herramientas de Asistencia';
$string['pluginname'] = 'Herramientas de Asistencia';
$string['autosession'] = 'Sesión directa';
$string['instantconfig'] = 'Parámetros de sesión directa ';
$string['milista'] = 'Milista';
$string['asistencia'] = 'External CRUE';
$string['enabled'] = 'Habilitado';
$string['enabled_desc'] = 'If enabled, Instant sessions could be added and per session settings adjusted.';
$string['sessionstart'] = 'Inicio nominal de la sesión';
$string['sessionstart_desc'] = 'Las sesiones directas se registrarán como empezando a una hora nominal, no la hora actual. 
Ests ajuste indica el momento del círculo horario: horas enteras, medias horas o cuartos. ';
$string['sessionoffset'] = 'Intervalo de incio  ';
$string['sessionoffset_desc'] = 'Cómo determinar el inicio nominal a partir de la hora actual. 

 * Más próximo: el inicio nominal será el hito (determinado pro el ajuste anterior) más cercano. 
  Por ejemplo, si el inico son "Cuartos" entonces sesiones directas disparadas a las 11.10 u 11.20 se registrará como comenzando a las 11.15 horas. 
 * Siguiente: Redondeado al sigioneet hito horario. Si se establece en "Medias horas" y son las 11.10 u 11.20, el comienzo nominal será a las 11.30.
 * Previo: Redondeando al hito previo anterior a la hora actual. 

Por ejemplo, si se establece como inicio nominal "Horas enteras y medias", y un intervalo de "más proximo", 
si como profesor dispara la sesión a las 10.52 o las 11.07, en ambos casos quedará registrada nominalmente como la clasde de las 11.00 horas. ';
$string['off_nearest'] = 'Más próximo';
$string['off_next'] = 'Siguiente';
$string['off_prev'] = 'Previo';
$string['start_whole'] = 'Solo horas enteras';
$string['start_half'] = 'Horas enteras y medias';
$string['start_quarter'] = 'Cuartos';
