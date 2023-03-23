<?php
if (! defined('CLI_SCRIPT')) {
    define('CLI_SCRIPT', true);
}
require_once (__DIR__ . '/../../../config.php');
require_once ('../locallib.php');

/*
// now get cli options
list($options, $unrecognized) = cli_get_params(array('noupdate'=>false, 'verbose'=>false, 'help'=>false, 'fecha'=>false), array('n'=>'noupdate', 'v'=>'verbose', 'h'=>'help', 'f'=>'fecha'));

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    $help =
"Execute user account sync with external database.
The auth_db plugin must be enabled and properly configured.

Options:
-n, --noupdate        Skip update of existing users
-v, --verbose         Print verbose progess information
-h, --help            Print out this help

Example:
sync.php
";

    echo $help;
    die;
}

if (!is_enabled_auth('casulpgc')) {
    echo "Plugin not enabled!";
    exit(1);
}

$verbose = !empty($options['verbose']);
$update = empty($options['noupdate']);
*/

$verbose = false;
$update = false;

$casulpgcauth = get_auth_plugin('casulpgc');
$removeuser = $casulpgcauth->config->removeuser

    /**
     * Sincroniza los usuarios de la BBDD de moodle con los de la BBDD
     * corporativa de la ULPGC
     * La sincronización se realiza en base al campo username
     * La sincronización supende los usuarios que se han dado de baja en la BBDD
     * corporativa
     */

        // Obtención de registros en BBDD externa (A efectos de demostración se restrigen a la categoría 111_4036)
        $extdb = db_init();
        $sqlusuariosulpgc = "SELECT lower(u.username) AS username, MAX(up.estado) AS estado
		                       FROM tmousuariosplataforma up, tmousuarios u, tmoplataformasactivas p
		                      WHERE p.plataforma = '{$CFG->plataforma}'
		                            AND p.aacada = '{$CFG->aacada}'
                                    AND up.plataformaid = p.id
		                            AND u.id = up.usuarioid";

        $sqlusuariosulpgc = "SELECT lower(u.username) AS username, MAX(up.estado) AS estado, min(m.rol) AS rol
                               FROM tmousuariosplataforma up, tmousuarios u, tmoplataformasactivas p, tmomatriculas m
                              WHERE p.plataforma = '{$CFG->plataforma}'
                                    AND p.aacada = '{$CFG->aacada}'
                                    AND up.plataformaid = p.id
                                    AND u.id = up.usuarioid
                                    AND m.usuarioid = u.id";

        /* Solo se crean las cuentas de los usuarios cuando no está habilitada la carga de alumnos */
        if (! $CFG->cargaalumnos) {
            $sqlusuariosulpgc .= " AND EXISTS (select id from tmomatriculas m where m.usuarioid = u.id AND m.rol like '%teacher%' and estado='I')";
        }

        $sqlusuariosulpgc .= " GROUP BY lower(u.username)";

        $usuariosulpgc = get_rows_external_db($extdb, $sqlusuariosulpgc, 'username');

        // Obtención de registros en Campus Virtual
        $usuarioscv = $DB->get_fieldset_select('user', 'username', 'auth="casulpgc"');

        /* Registros a tratar (Insertar o Eliminar) */
        // Registros que están en la bbdd externa y no en Moodle
        $usuariosadd = array_diff(array_keys(array_filter($usuariosulpgc, function ($obj) {
            if ($obj->estado == 'I')
                return true;
        })), $usuarioscv);

        // Registros a eliminar en la bbdd externa que están en Moodle
        $usuariosdel = array_intersect(array_keys(array_filter($usuariosulpgc, function ($obj) {
            if ($obj->estado == 'D')
                return true;
        })), $usuarioscv);

        // Combinación de registros
        $usuarioskeys = array_merge($usuariosadd, $usuariosdel);
        $userlist = array_intersect_key($usuariosulpgc, array_flip($usuarioskeys));

        if ((! isset($userlist)) or (count($userlist) == 0)) {
            // add_to_log(SITEID, 'grupos', 'sync', '', 'No hay usuarios a tratar', 0, $USER->id);
            return;
        } else {
            // add_to_log(SITEID, 'grupos', 'sync', '', 'Usuarios a tratar: ' . count($userlist), 0, $USER->id);

            /*
             * Preferencias de usuario para enviar envío de notificaciones a los profesores
             */

            /*$teacher_preferences = array();
            $user_preferences = new stdclass();
            $user_preferences->name = 'message_provider_mod_quiz_submission_loggedin';
            $user_preferences->value = 'none';
            $teacher_preferences[] = $user_preferences;
            unset($user_preferences);
            $user_preferences = new stdclass();
            $user_preferences->name = 'message_provider_mod_quiz_submission_loggedoff';
            $user_preferences->value = 'none';
            $teacher_preferences[] = $user_preferences;
            unset($user_preferences);
            $user_preferences = new stdclass();
            $user_preferences->name = 'message_provider_moodle_badgecreatornotice_loggedin';
            $user_preferences->value = 'none';
            $teacher_preferences[] = $user_preferences;
            unset($user_preferences);
            $user_preferences = new stdclass();
            $user_preferences->name = 'message_provider_moodle_instantmessage_loggedoff';
            $user_preferences->value = 'email';
            $teacher_preferences[] = $user_preferences;
            unset($user_preferences);*/

            foreach ($userlist as $user) {
                // Verificar existencia de la cuenta del usuario
                $existing_user = $DB->get_record('user', array(
                    'username' => $user->username
                ));

                // Añadir usuario
                if ($user->estado == 'I') {

                    if ($existing_user) {
                        if ($existing_user->auth != 'casulpgc') {
                            /* Si no está abierto el acceso a alumnos y es un estudiante no se modifica el registro */
                            if (! $CFG->accesoalumnos && $user->rol == 'student') {
                                continue;
                            }
                            $existing_user->auth = 'casulpgc';
                            $existing_user->deleted = 0;
                            $existing_user->timemodified = time();
                            if ($DB->update_record('user', $existing_user)) {
                                if ($verbose) {
                                    mtrace(print_string('auth_casulpgc_reviveduser', 'auth_casulpgc', $existing_user->username));
                                }
                            } else {
                                if ($verbose) {
                                    mtrace(print_string('auth_casulpgc_revivedusererror', 'auth_casulpgc', $existing_user->username));
                                }
                            }
                        }
                    } else {
                        $sqldatos = "SELECT *
                                   FROM vmodatospersonales v
                                  WHERE lower(username) = '{$user->username}'";
                        $newuser = get_rows_external_db($extdb, $sqldatos, 'username');

                        $newuser = array_shift($newuser);
                        if (! isset($newuser->username)) {
                            continue;
                        }

                        $newuser->username = strtolower($newuser->username);

                        if (is_null($newuser->phone1)) {
                            $newuser->phone1 = '';
                        }
                        if (is_null($newuser->phone2)) {
                            $newuser->phone2 = '';
                        }
                        if (is_null($newuser->email)) {
                            $newuser->email = '';
                        }
                        if (is_null($newuser->aim)) {
                            $newuser->aim = '';
                        }
                        if (is_null($newuser->address)) {
                            $newuser->address = '';
                        }
                        if (is_null($newuser->city)) {
                            $newuser->city = $CFG->defaultcity;
                        }
                        
                        $newuser->timemodified = time();
                        $newuser->confirmed = 1;
                        /* Si no está abierto el acceso a los alumnos, se le deniega el acceso */
                        if (! $CFG->accesoalumnos && $user->rol == 'student') {
                            $newuser->auth = 'nologin';
                        } else
                            $newuser->auth = 'casulpgc';
                        $newuser->mnethostid = $CFG->mnet_localhost_id;
                        $newuser->password = 'd41d8cd98f00b204e9800998ecf8427e';
                        $newuser->lang = 'es';
                        $newuser->country = 'ES';
                        $newuser->timezone = 'Atlantic/Canary';
                        $newuser->emailstop = 0;
                        $newuser->maildigest = 0;
                        $newuser->maildisplay = 1;
                        $newuser->htmleditor = 1;
                        $newuser->ajax = 1;
                        $newuser->autosubscribe = 0;
                        $newuser->trackforums = 1;
                        if ($user->rol != 'student') {
                            $newuser->institution = 'ULPGC';
                            if (is_numeric($newuser->department)) {
                                $departamento = $DB->get_record('local_sinculpgc_units', array(
                                    'idnumber' => $newuser->department,
                                    'type' => 'departamento'
                                ));
                                if ($departamento) {
                                    $newuser->department = $departamento->name;
                                }
                            }
                        } else {
                            $newuser->institution = '';
                            $newuser->department = '';
                        }
                        $newuser->url = ! ($newuser->url) ? '' : $newuser->url;

                        if ($id = $DB->insert_record('user', $newuser)) {
                            if ($verbose) {
                                mtrace(print_string('auth_casulpgc_insertuser', 'auth_casulpgc', $newuser->username));
                            }
                            /*if ($user->rol != 'student') {
                                foreach ($teacher_preferences as $preference) {
                                    $preference->userid = $id;
                                    try {
                                    $DB->insert_record('user_preferences', $preference);
                                    } catch (Exception $e) {
                                        if ($verbose) {
                                            mtrace("Error inserting $id user preferences:". $e->getMessage());
                                        }
                                    }
                                }
                            }*/
                        } else {
                            if ($verbose) {
                                mtrace(print_string('auth_casulpgc_insertusererror', 'auth_casulpgc', $newuser->username));
                            }
                       }
                    }
                } else
            /*
             *
             * El valor del campo "Usuario externo eliminado", en la pantalla de configuración de la extensión,
             * determina que sucede cuando un alumno no está matriculado en ningún curso de una plataforma:
             * - Mantener interna (0): el alumno conserva el acceso a la plataforma
             * - Suspender interna(1): el alumno se mantiene en la plataforma pero se le niega el acceso
             * - Borrado completo (2): el alumno se elimina de la plataforma
             *
             */
            if ($user->estado == 'D' && (! empty($removeuser))) {
                        if (isset($existing_user) && $existing_user->auth == 'casulpgc') {
                            // la opción 2 es borrado completo
                            if ($removeuser == 2) {
                                if (delete_user($existing_user)) {
                                    if ($verbose) {
                                        mtrace(print_string('auth_casulpgc_deleteuser', 'auth_casulpgc', $existing_user->username));
                                    }
                                    // add_to_log(SITEID, 'user', 'del', '', get_string('auth_casulpgc_deleteuser', 'auth_casulpgc', $existing_user->username));
                                } else {
                                    if ($verbose) {
                                        mtrace(print_string('auth_casulpgc_deleteusererror', 'auth_casulpgc', $existing_user->username));
                                    }
                                    // add_to_log(SITEID, 'user', 'del', '', get_string('auth_casulpgc_deleteusererror', 'auth_casulpgc', $existing_user->username));
                                }
                            } else
                                if ($removeuser == 1) {
                                    $existing_user->auth = 'nologin';
                                    $existing_user->timemodified = time();
                                    if ($DB->update_record('user', $existing_user)) {
                                        if ($verbose) {
                                            mtrace(print_string('auth_casulpgc_suspenduser', 'auth_casulpgc', $existing_user->username));
                                        }
                                        // add_to_log(SITEID, 'user', 'del', '', get_string('auth_casulpgc_suspenduser', 'auth_casulpgc', $existing_user->username));
                                    } else {
                                        if ($verbose) {
                                            mtrace(print_string('auth_casulpgc_suspendusererror', 'auth_casulpgc', $existing_user->username));
                                        }
                                        // add_to_log(SITEID, 'user', 'del', '', get_string('auth_casulpgc_suspendusererror', 'auth_casulpgc', $existing_user->username));
                                    }
                                }
                        }
                    }
            }
        }
        db_close($extdb);
        $num = count($userlist);
    
?>
