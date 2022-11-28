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
 * Table to show list of existing rules.
 * @package local_sinculpgc
 * @author  Enrique Castro @ ULPGC
 * @copyright  2022 onwards ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_sinculpgc\output;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/tablelib.php');
use table_sql;
use renderable;
use local_sinculpgc\helper;
use moodle_url;
use html_writer;
use pix_icon;
use confirm_action;

class rulestable extends table_sql implements renderable {

    /**
     * rulestable constructor.
     * @param $uniqueid table unique id
     * @param \moodle_url $url base url
     * @param int $page current page
     * @param int $perpage number of records per page
     * @throws \coding_exception
     * @throws \coding_exception
     */
    public function __construct($uniqueid, \moodle_url $url, $page = 0, $perpage = 20) {
        parent::__construct($uniqueid);

        $this->set_attribute('class', 'local_sinculpgc sinculpgcrules');

        // Set protected properties.
        $this->pagesize = $perpage;
        $this->page = $page;

        // Define columns in the table.
        $this->define_table_columns();

        // Define configs.
        $this->define_table_configs($url);
    }

    /**
     * Table columns and corresponding headers.
     * @throws \coding_exception
     */
    protected function define_table_columns() {
        $cols = array(
            'id' => get_string('rulenum', 'local_sinculpgc'),
            'enrol' => get_string('enrolmethod', 'local_sinculpgc'),
            'roleid' => get_string('enrolas', 'local_sinculpgc'),
            'numused' => get_string('numused', 'local_sinculpgc'), 
            'searchfield' => get_string('searchfield', 'local_sinculpgc'),
            'searchpattern' => get_string('searchpattern', 'local_sinculpgc'),
            'enrolparams' => get_string('enrolparams', 'local_sinculpgc'),
            'group' => get_string('rulegroup', 'local_sinculpgc'), 
            'actions' => get_string('actions'),
        );

        $this->define_columns(array_keys($cols));
        $this->define_headers(array_values($cols));
    }

    /**
     * Define table configuration.
     * @param \moodle_url $url
     * @throws \coding_exception
     */
    protected function define_table_configs(\moodle_url $url) {
        // Set table url.
        $this->define_baseurl($url);

        // Set table configs.
        $this->collapsible(false);
        $this->pageable(true);
        $this->sortable(true, 'id, enrol', SORT_ASC);
        $this->no_sorting('enrolparams');
        $this->no_sorting('searchpattern');
        $this->no_sorting('group');
        $this->no_sorting('actions');        
        
    }

    /**
     * Get sql query
     * @param bool $count whether count or get records.
     * @return array
     */
    protected function get_sql_and_params($count = false) {
        if ($count) {
            $select = "COUNT(1)";
        } else {
            $select = "su.*, ";
            $select .= " (SELECT COUNT(e.id) 
                            FROM {enrol} e 
                           WHERE e.enrol = su.enrol AND e.customint8 = su.id 
                         ) AS numused,  ";
            $select .= " (SELECT COUNT(e.id) 
                            FROM {enrol} e 
                           WHERE e.enrol = su.enrol AND e.customint8 = su.id AND e.status != 0
                         ) AS numdisabled ";
        }
        

        $sql = "SELECT {$select}
                  FROM {local_sinculpgc_rules} su ";

        if (!$count ) {
            $sort = $this->get_sql_sort(); 
            if(!$sort) {
                $sort = 'id ASC';
            }
        
            $sql .= "  ORDER BY $sort";
        }

        return array($sql, []);
    }

    /**
     * Get data.
     * @param int $pagesize number of records to fetch
     * @param bool $useinitialsbar initial bar
     * @throws \dml_exception
     */
    public function query_db($pagesize, $useinitialsbar = true) {
        global $DB;

        list($countsql, $countparams) = $this->get_sql_and_params(true);
        list($sql, $params) = $this->get_sql_and_params();
        $total = $DB->count_records_sql($countsql, $countparams);
        $this->pagesize($pagesize, $total);
        $records = $DB->get_records_sql($sql, $params, $this->pagesize * $this->page, $this->pagesize);
        foreach ($records as $history) {
            $this->rawdata[] = $history;
        }
        // Set initial bars.
        if ($useinitialsbar) {
            $this->initialbars($total > $pagesize);
        }
    }

    /**
     * Custom actions column.
     * @param $row a rule record.
     * @return string
     */
    protected function col_actions($row) {
        global $OUTPUT;
        $links = [];
        $editrule = '/local/sinculpgc/editrule.php';
        $managerules = '/local/sinculpgc/managerules.php';
        
        // Edit.
        $editparams = ['ruleid' => $row->id, 'enrol' => $row->enrol, 'action' => 'edit', 'sesskey' => sesskey()];
        $url = new moodle_url($editrule, $editparams);
        $icon = $OUTPUT->pix_icon('t/edit', get_string('rule:edit', 'local_sinculpgc'));
        $editlink = html_writer::link($url, $icon);
        $links[] = $editlink;


        // Enable/Disable.
        // TODO check auto run task to confirm action
        
        if ($row->enabled) {
            $editparams = ['ruleid' => $row->id, 'action' => 'disable', 'sesskey' => sesskey()];
            $url = new moodle_url($managerules, $editparams);
            $icon = $OUTPUT->pix_icon('t/hide', get_string('rule:disable', 'local_sinculpgc'));
            $editlink = html_writer::link($url, $icon);
        } else {
            $editparams = ['ruleid' => $row->id, 'action' => 'enable', 'sesskey' => sesskey()];
            $url = new moodle_url($managerules, $editparams);
            $icon = $OUTPUT->pix_icon('t/show', get_string('rule:enable', 'local_sinculpgc'));
            $editlink = html_writer::link($url, $icon);
        }
        $links[] = $editlink.'<br />';
        
        

        // Reset / delete disabled
        $editlink = '';
        if ($row->enabled) {        
            $editparams = ['ruleid' => $row->id, 'action' => 'run', 'sesskey' => sesskey()];
            $url = new moodle_url($managerules, $editparams);
            $confirmaction = new confirm_action(get_string('confirm:run', 'local_sinculpgc', $row));
            $icon = new pix_icon('i/addblock', get_string('rule:run', 'local_sinculpgc'), 'core', array());
            $links[] =  $OUTPUT->action_icon($url, $icon, $confirmaction);
            
            
            $editparams = ['ruleid' => $row->id, 'action' => 'reset', 'sesskey' => sesskey()];
            $url = new moodle_url($managerules, $editparams);
            $confirmaction = new confirm_action(get_string('confirm:reset', 'local_sinculpgc', $row));
            $icon = new pix_icon('t/reset', get_string('rule:reset', 'local_sinculpgc'), 'core', array());
            $editlink =  $OUTPUT->action_icon($url, $icon, $confirmaction);
            
            /*
            $icon = $OUTPUT->pix_icon('t/reset', get_string('rule:run', 'local_sinculpgc'));
            $editlink = html_writer::link($url, $icon);
            */
        } elseif($row->numused) {
            $editparams = ['ruleid' => $row->id, 'action' => 'remove', 'sesskey' => sesskey()];
            $url = new moodle_url($managerules, $editparams);
            $confirmaction = new confirm_action(get_string('confirm:remove', 'local_sinculpgc', $row));
            $icon = new pix_icon('i/window_close', get_string('rule:remove', 'local_sinculpgc'), 'core', array());
            $editlink =  $OUTPUT->action_icon($url, $icon, $confirmaction);
/*            
            $icon = $OUTPUT->pix_icon('t/show', get_string('rule:remove', 'local_sinculpgc'));
            $editlink = html_writer::link($url, $icon);
        */
        }
        if($editlink) {
            $links[] = $editlink;
        }

        // Delete.
        $editparams = ['ruleid' => $row->id, 'action' => 'delete', 'sesskey' => sesskey()];
        $url = new moodle_url($managerules, $editparams);
        $confirmaction = new confirm_action(get_string('confirm:delete', 'local_sinculpgc', $row));
        $icon = new pix_icon('i/delete', get_string('rule:delete', 'local_sinculpgc'), 'core', array());
        $editlink =  $OUTPUT->action_icon($url, $icon, $confirmaction);

        $links[] = '<br />'.$editlink;
        
        
        return implode(' &nbsp; ', $links);
    }

    /**
     * Custom num column.
     * @param $row a rule record.
     * @return string
     * @throws \coding_exception
     */
    protected function col_id($row) {
        return ($row->id);
    }

    /**
     * Custom enrol column.
     * @param $row a rule record.
     * @return mixed
     * @throws \coding_exception
     */
    protected function col_enrol($row) {
        if($this->is_downloading()) {
            return $row->enrol;
        }
    
        $label = get_string('pluginname', 'enrol_' . $row->enrol);
        $label .= '<br /> ['.$row->enrol.']'; 
        return $label;
    }

    /**
     * Custom roleid column.
     * @param $row a rule record.
     * @return mixed
     * @throws \coding_exception
     */
    protected function col_roleid($row) {
        $roles = get_all_roles();
        if($this->is_downloading()) {
            if($row->roleid > 0) {
                return $roles[$row->roleid]->shortname;
            }
            return $row->roleid;
        }

        if($row->roleid == 0) {
            return get_string('syncedrole', 'local_sinculpgc');
        } elseif($row->roleid > 0) {

            $roles = role_fix_names($roles, null, ROLENAME_ORIGINAL, true);
            return $roles[$row->roleid];
        }    
        return ($row->roleid);
    }

    /**
     * Custom reset time modified column.
     * @param $row a rule record.
     * @return mixed
     * @throws \coding_exception
     */
    protected function col_timemodified($row) {
        if ($row->timemodified) {
            return userdate($row->timemodified);
        } else {
            return '-';
        }
    }

    /**
     * Custome searchfield column.
     * @param $row $row a rule record.
     * @return string
     * @throws \coding_exception
     */
    protected function col_searchfield($row) {
        return ($row->searchfield);
    }
    
    /**
     * Custome searchpattern column.
     * @param $row $row a rule record.
     * @return string
     * @throws \coding_exception
     */
    protected function col_searchpattern($row) {
        if($this->is_downloading()) {
            return ($row->searchpattern);
        }
        return (nl2br($row->searchpattern));
    }

    /**
     * Custome ruleparams column.
     * @param $row $row a rule record.
     * @return string
     * @throws \coding_exception
     */
    protected function col_enrolparams($row) {
        if($this->is_downloading()) {
            return ($row->enrolparams);
        }    
        $html = '';
        if(!empty($row->enrolparams)) {
            $html = [];
            $data = json_decode($row->enrolparams);
            foreach($data as $key => $value) {
                if(is_array($value)) {
                    $value = implode(', ', $value);
                }
                $html[] = "$key:  $value"; 
            }
            $html = implode('<br />', $html);
        }
        return $html;
    }

    /**
     * Custome group column.
     * @param $row $row a rule record.
     * @return string
     * @throws \coding_exception
     */
    protected function col_group($row) {
        $cell = $row->groupto;
        
        if($row->useidnumber) {
            $cell .= '<br /> ['.get_string('useidnumber', 'local_sinculpgc').']';
        }
        return $cell;
    }

    /**
     * Custome group column.
     * @param $row $row a rule record.
     * @return string
     */
    protected function col_groupto($row) {
        return ($row->groupto);
    }
    
    /**
     * Custome group column.
     * @param $row $row a rule record.
     * @return int
     */
    protected function col_useidnumber($row) {
        return ($row->useidnumber);
    }
    
    /**
     * Custome group column.
     * @param $row $row a rule record.
     * @return int
     */
    protected function col_numused($row) {
        global $OUTPUT;
        
        $managerules = '/local/sinculpgc/managerules.php';
        $used = get_string('numinstances', 'local_sinculpgc', $row->numused);
        $row->numenabled = $row->numused - $row->numdisabled;
        if($row->numenabled) {

            $editparams = ['ruleid' => $row->id, 'action' => 'statusoff', 'sesskey' => sesskey()];
            $url = new moodle_url($managerules, $editparams);
            $confirmaction = new confirm_action(get_string('confirm:statusoff', 'local_sinculpgc', $row));
            $icon = new pix_icon('t/hide', get_string('status:off', 'local_sinculpgc'), 'core', array());
            $used .= '  ' . $OUTPUT->action_icon($url, $icon, $confirmaction);
        }
    
        $disabled = '';
        if($row->numdisabled) {
            $disabled = get_string('numdisabled', 'local_sinculpgc', $row->numdisabled);
            $editparams = ['ruleid' => $row->id, 'action' => 'statuson', 'sesskey' => sesskey()];
            $url = new moodle_url($managerules, $editparams);
            $confirmaction = new confirm_action(get_string('confirm:statuson', 'local_sinculpgc', $row));
            $icon = new pix_icon('t/show', get_string('status:on', 'local_sinculpgc'), 'core', array());
            $disabled .= '  ' . $OUTPUT->action_icon($url, $icon, $confirmaction);
        
        }
    
    
        return $used . '<br />' . $disabled ;
    }
    
    function get_row_class($row) {
        $class = '';
        if(!$row->enabled) {
            $class = ' dimmed ';
        }
        return $class;
    }
    
    
}
