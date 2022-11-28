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
 * Library dedicated do documentation bridges with editor documentation
 * @package local_vflibs
 * @author valery.fremaux@gmail.com
 */
defined('MOODLE_INTERNAL') || die();

function local_vflibs_doc_make_ticket() {

    $config = get_config('local_vflibs');

    $ticket = new StdClass;
    $ticket->clientid = @$config->doccustomerid;
    $ticket->date = time();

    if (!empty($config->doccustomerpublickey)) {

        $res = openssl_get_publickey($config->doccustomerpublickey);
        if (!$res) {
            echo " --FAILED GETTING KEY-- ";
        }

        $decrypted = json_encode($ticket);
        $res = openssl_public_encrypt($decrypted, $encrypted, $res);

        $encoded = urlencode(base64_encode($encrypted));

        return $encoded;
    }
}

/**
 * Computes a specific documentation links for a plugin.
 */
function local_vflibs_make_doc_url($pluginname) {
    global $SESSION, $CFG;

    $config = get_config('local_vflibs');

    if (empty($config->docbaseurl)) {
        // No external doc configured.
        return null;
    }

    $editorplugins = array();
    if (!empty($config->editorplugins)) {
        $pluginlist = str_replace("\n", '', $config->editorplugins);
        $pluginlist = str_replace(' ', '', $pluginlist);
        $editorplugins = explode(',', $pluginlist);
    } else {
        if (!empty($CFG->integratorplugins)) {
            $pluginlist = str_replace("\n", '', $CFG->integratorplugins);
            $pluginlist = str_replace(' ', '', $pluginlist);
            $editorplugins = explode(',', $CFG->integratorplugins);
        }
    }

    if (strpos($pluginname, '_') === false) {
        // Normalize name.
        $pluginname = 'mod_'.$pluginname;
    }

    if (!in_array($pluginname, $editorplugins)) {
        return false;
    }

    $ticket = local_vflibs_doc_make_ticket();

    $docbaseurl = str_replace('{lang}', current_language(), $config->docbaseurl);

    // Process plugin name for dokuwikis
    $pluginnamearr = explode('_', $pluginname);
    $first = array_shift($pluginnamearr);
    $pluginpath = $first.':'.implode('', $pluginnamearr);

    return $docbaseurl.$pluginpath.':userguide&cryptoken='.$ticket;
}

