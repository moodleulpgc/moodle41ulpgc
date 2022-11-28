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
 *
 * @package local_sinculpgc
 * @author  Enrique Castro @ ULPGC
 * @copyright  2022 onwards ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_sinculpgc\output; 
defined('MOODLE_INTERNAL') || die();
//use local_sinculpgc\table\dismissed_rule;;
//use local_sinculpgc\table\acknowledged_rule;;
use local_sinculpgc\output\rulestable;
use \html_writer;
use \moodle_url;

class renderer extends \plugin_renderer_base {

    /**
     * Generates select enrol method and create rule button 
     *
     * @return false|string
     */
    public function print_new_rule_button() {
        $o = '';
        
        $editrule = '/local/sinculpgc/editrule.php';
        $newruleparams = ['ruleid' => 0, 'sesskey' => sesskey()];
        $url = new moodle_url($editrule, $newruleparams);
        
        $options = [];
        foreach(SINCULPGC_ENROL_METHODS as $method) {
            if(enrol_is_enabled($method)) {
                $options[$method] = get_string('pluginname','enrol_'.$method). '  ['. $method .']' ;
            }
        }
        
        $selectid = html_writer::random_id('single_select');
        $select = html_writer::select($options, 'enrol', '', 
                                            ['' => 'choosedots'], 
                                            ['id' => $selectid]);
        $label = html_writer::label(get_string('enrolmethod', 'local_sinculpgc'), $selectid);
        $hidden = html_writer::input_hidden_params($url);
        $attributes = ['id' => html_writer::random_id('single_button'), 
                        'class' => 'btn btn-secondary',
                        'type' => 'submit'];
        $button = html_writer::tag('button', get_string('rule:create', 'local_sinculpgc'), $attributes);
        
        $attributes = ['method' => 'post',
                        'action' => $url];
        $content = html_writer::tag('form', $label.$select.$hidden.$button, $attributes);
    
        $o .= $this->output->container($content, 'single_button');
    
        return $o;
    }





    /**
     * Render table.
     * @param rulestable $table dismissed rule table
     * @return false|string
     */
    public function render_rulestable(rulestable $table) {
        ob_start();
        $table->out($table->pagesize, false);
        $o = ob_get_contents();
        ob_end_clean();
        return $o;
    }

}
