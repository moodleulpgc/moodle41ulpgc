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
 * English strings for examdelivery_quiz
 *
 * @package    examdelivery_quiz
 * @copyright  2023 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Impartición de examen como Cuestionario';
$string['quiz:manage'] = 'Gestionar exámenes mediante Cuestionario';
$string['quiz:view'] = 'Ver y usar exámenes mediante Cuestionario';
$string['enabled'] = 'Habilitado';
$string['enabled_help'] = 'Si se activa, este método de impartición de exámenes mediante Cuestionario 
estará disponible para los Exámenes de Registro de Exámenes.. ';
$string['examprefix'] = 'Prefijo en idnumber para Cuestionarios';
$string['examprefix_help'] = 'Si se usa, permite localizar instancias de Cuestionario asociadas a Exámenes oficiales. 
Los módulos Cuestionario identificados con un idnumber que empieza por este texto se encontrarán y se vincularan a un Examen del registro. ';
$string['examafter'] = 'Prolongación después de cerrado';
$string['examafter_help'] = 'Un breve periodo de tiempo adicional añadiddo a la ventana de tiempo (cierre) para permitir acomodar entradas retrasadas 
y permitir el uso del botón manual de "Terminar todo y enviar" después de agotado el tiempo límite.';
$string['insertcontrolq'] = 'Usar pregunta de control';
$string['insertcontrolq_help'] = 'Si se activa, el chequeo de preguntas existentes incluirá la presencia de la pregunta de control. 
Si está activo, cuando se cargan las preguntas en Exámenes en línea se incluirá automáticamente la pregunta de control. ';
$string['controlquestion'] = 'Pregunta de control';
$string['controlquestion_help'] = 'Si se introduce un valor no nulo (dígitos, no texto), 
la pregunta con esa ID (questionid) será añadida a todos los Cuestionarios de Examen en línea de forma automática. 
La adición ocurre desde Gestión de Sesión cuando se llama a cargar la spreguntas del examen.  ';
$string['optionsinstance'] = 'Instancia de Opciones';
$string['optionsinstance_help'] = 'Si se introduce un valor no nulo (dígitos, no texto), 
las opciones de configuración de los Cuestionarios de Examen se ajustarán a las de esta instancia. ';
