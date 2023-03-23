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
 * Strings for component 'auth_casulpgc', language 'en'.
 *
 * @package   auth_casulpgc
 * @copyright 2023 Enrique Castro @ULPGC 
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'CAS ULPGC (SSO)';
$string['auth_casulpgcdescription'] = 'Este método usa un servidor CAS (Central Authentication Service) para autenticar usuarios en un entorno SSO.
Este plugin depende del método estándard CAS de moodle. <strong>La configuración de servidor CAS y las opciones de auth_cas deben ser establecidas en el plugin auth CAS estándard</strong>.';
$string['auth_casulpgc_settings'] = 'Opciones de CAS ULPGC ';
$string['auth_casulpgc_lockauth_key'] = 'Bloquear a solo CAS ';
$string['auth_casulpgc_lockauth'] = 'Si activado, los usuarios solo pueden autenticarse usando un servidor CAS. 
Si no son autenticados por CAS NO se ofrece otra posibilidad de login.';
$string['auth_casulpgc_caserror_key'] = 'CAS no configurado';
$string['auth_casulpgc_caserror'] = 'Algunos parámetros importantes para la conexión con CAS están ausentes.  
Este método de autenticación casulpgc NO funcionará. ';
$string['auth_casulpgc_nonexistent_return_url_key'] = 'URL de retorno para no exstentes';
$string['auth_casulpgc_nonexistent_return_url'] = 'Una RUR para redirigir a usuarios que si son autenticados en CAS pero que NO existen en esta plataforma. <br />
Es una url (sin el prefijo the https://) para redirigir a usuarios ULPGC. Si se deja vacía se usará el sitio web de la ULPGC.';
$string['nonexistentmsg'] = 'Un usuario con este nombre no está dado de alta en esta plataforma. Por favor, contacte con el Servicio de Soporte del Campus virtual (email campusvirtual@ulpgc.es).'; 
$string['noaccessreturn'] = 'Volver a ULPGC';
