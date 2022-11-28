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
 * Prints a list of courses from another Moodle instance.
 *
 * @package   block_remote_courses
 * @copyright 2015 Lafayette College ITS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['blockremotesite'] = 'Sitio remoto';
$string['blockwstoken'] = 'Webservice token';
$string['blocknumcourses'] = 'Nº Cursos a mostrar';
$string['blockintrotext'] = 'Texto adicional';
$string['blocktitle'] = 'Título del bloque';
$string['pluginname'] = 'Bloque Cursos remotos';
$string['privacy:metadata'] = 'El Bloque Cursos remotos no almacena ninguna información personal o de usuarios.';
$string['remote_courses'] = 'Cursos remotos';
$string['remote_courses:addinstance'] = 'Agregar un bloque Cursos Remotos';
$string['unconfigured'] = 'Por favor, configure el webservice para acceder a los cursos remotos';
$string['remote_courses:myaddinstance'] = 'Agregar un bloque Cursos Remotos al área personal (/my)';

// ecastro ULPGC
$string['blockcourselist'] = 'Lista de cursos';
$string['blockcourselist_help'] = 'Una lista, separada por comas, de valores para especificar cursos. 
Estso valores se buscarán en el campo indicado debajo. ';
$string['blockcoursefield'] = 'Campo del curso';
$string['blockcoursefield_help'] = 'Un campo de las propiedades del curso donde buscar los valores de arriba.';
$string['blockcoursesheader'] = 'Título de Cursos del usuario';
$string['blocklistheader'] = 'Título de la Lista de cursos';
$string['blockcatidnumber'] = 'IDnumber de categoría';
$string['blockcatidnumber_help'] = 'Si se especifica, entonces solo se mostrarán aquellos cursos donde 
el usuario esté matriculado (en le plataforma remota) y además pertenezcan a esta categoría.';
$string['blockusercourses'] = 'Mostrar cursos remotos del usuario';
$string['blockrecentactivity'] = 'Obtener y mostrar marcas de actividad para el usuario en la plataforma remota.';
$string['blockshowshortname'] = 'Mostrar el shortname';
$string['headercourses'] = 'Selección de cursos';
$string['headertext'] = 'Texto adicional';
