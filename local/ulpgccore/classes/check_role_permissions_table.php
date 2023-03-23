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
 * Library code used by the roles administration interfaces.
 *
 * @package    local_ulpgccore
 * @copyright  2023 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ulpgccore;

defined('MOODLE_INTERNAL') || die();

/**
 * Subclass of core_role_capability_table_base for use on the check Permissions page.
 */
class check_role_permissions_table extends \core_role_capability_table_base {
    protected $archetype;
    protected $rolearchetype;
    protected $roles;
    protected $icons = array();

    /**
     * Constructor.
     * @param context $context the context this table relates to.
     * @param string $archetype role to show 
     */
    public function __construct($context, $archetype) {
        parent::__construct($context, 'permissions');
        $this->archetype = $archetype;

        $roles = get_archetype_roles($archetype);
        $this->roles = role_fix_names($roles, $context, ROLENAME_ORIGINAL);
        
        foreach($this->roles as $rid => $role) {
            $caps = get_capabilities_from_role_on_context($role, $context);
            $role->capabilities = [];
            foreach($caps as $cap) {
                $role->capabilities[$cap->capability] = $cap->permission;
            }
            
            if($role->shortname == $archetype)  {
                $this->rolearchetype = $role;
                unset($this->roles[$rid]);
            } 
        }
        
    }

    public function has_derived_roles() {
        if(!empty($this->roles) && !empty($this->rolearchetype)) { 
            return true;
        }
        return false;
    }
    
    
    protected function add_header_cells() {
        //echo '<th>' . get_string('capabilities', 'core_role') . '</th>';
        echo '<th class="rolename" >' . get_string('archetype', 'core_role') . "<br /> ({$this->rolearchetype->shortname}) "  . '</th>';
        foreach($this->roles as $role) {
            echo '<th class="rolename">' . $role->localname . "<br /> ({$role->shortname}) " . '</th>';
        }
    }

    protected function num_extra_columns() {
        return 0;
    }

    protected function add_row_cells($capability) {
        global $OUTPUT, $PAGE;
        
        //print_object($capability);
        
        /*
        $renderer = $PAGE->get_renderer('core');
        $adminurl = new moodle_url("/admin/");

        $context = $this->context;
        $contextid = $this->context->id;
        $allowoverrides = $this->allowoverrides;
        $allowsafeoverrides = $this->allowsafeoverrides;
        $overridableroles = $this->overridableroles;
        $roles = $this->roles;

        list($needed, $forbidden) = get_roles_with_cap_in_context($context, $capability->name);
        $neededroles    = array();
        $forbiddenroles = array();
        $allowable      = $overridableroles;
        $forbitable     = $overridableroles;
        foreach ($neededroles as $id => $unused) {
            unset($allowable[$id]);
        }
        foreach ($forbidden as $id => $unused) {
            unset($allowable[$id]);
            unset($forbitable[$id]);
        }

        foreach ($roles as $id => $name) {
            if (isset($needed[$id])) {
                $templatecontext = array("rolename" => $name, "roleid" => $id, "action" => "prevent", "spanclass" => "allowed",
                                  "linkclass" => "preventlink", "adminurl" => $adminurl->out(), "icon" => "", "iconalt" => "");
                if (isset($overridableroles[$id]) and ($allowoverrides or ($allowsafeoverrides and is_safe_capability($capability)))) {
                    $templatecontext['icon'] = 't/delete';
                    $templatecontext['iconalt'] = get_string('deletexrole', 'core_role', $name);
                }
                $neededroles[$id] = $renderer->render_from_template('core/permissionmanager_role', $templatecontext);
            }
        }
        $neededroles = implode(' ', $neededroles);
        foreach ($roles as $id => $name) {
            if (isset($forbidden[$id])  and ($allowoverrides or ($allowsafeoverrides and is_safe_capability($capability)))) {
                $templatecontext = array("rolename" => $name, "roleid" => $id, "action" => "unprohibit",
                                "spanclass" => "forbidden", "linkclass" => "unprohibitlink", "adminurl" => $adminurl->out(),
                                "icon" => "", "iconalt" => "");
                if (isset($overridableroles[$id]) and prohibit_is_removable($id, $context, $capability->name)) {
                    $templatecontext['icon'] = 't/delete';
                    $templatecontext['iconalt'] = get_string('deletexrole', 'core_role', $name);
                }
                $forbiddenroles[$id] = $renderer->render_from_template('core/permissionmanager_role', $templatecontext);
            }
        }
        $forbiddenroles = implode(' ', $forbiddenroles);

        if ($allowable and ($allowoverrides or ($allowsafeoverrides and is_safe_capability($capability)))) {
            $allowurl = new moodle_url($PAGE->url, array('contextid' => $contextid,
                                       'capability' => $capability->name, 'allow' => 1));
            $allowicon = $OUTPUT->action_icon($allowurl, new pix_icon('t/add', get_string('allow', 'core_role')), null,
                                            array('class' => 'allowlink', 'data-action' => 'allow'));
            $neededroles .= html_writer::div($allowicon, 'allowmore');
        }

        if ($forbitable and ($allowoverrides or ($allowsafeoverrides and is_safe_capability($capability)))) {
            $prohibiturl = new moodle_url($PAGE->url, array('contextid' => $contextid,
                                          'capability' => $capability->name, 'prohibit' => 1));
            $prohibiticon = $OUTPUT->action_icon($prohibiturl, new pix_icon('t/add', get_string('prohibit', 'core_role')), null,
                                                array('class' => 'prohibitlink', 'data-action' => 'prohibit'));
            $forbiddenroles .= html_writer::div($prohibiticon, 'prohibitmore');
        }

        $risks = $this->get_risks($capability);

        */
        
        $archetype = '';
        if(isset($this->rolearchetype->capabilities[$capability->name])) {
            $archetype =  $this->rolearchetype->capabilities[$capability->name];
        }
        
        if($risks = $this->get_risks($capability)) {
            $risks = '  &nbsp;  '.$risks;
        }
        
        
        $contents = \html_writer::tag('td', $archetype.$risks, array('class' => 'permission  archetype'));
        foreach($this->roles as $role) {
            $permission = '-';
            if(isset($role->capabilities[$capability->name])) {
                $permission =  $role->capabilities[$capability->name];
            }
            if($permission === $archetype) {
                $permission = '';
            }
            
            $contents .= \html_writer::tag('td', $permission, array('class' => 'permission role'));
        }        
        return $contents;
    }

    protected function get_risks($capability) {
        global $OUTPUT;

        $allrisks = get_all_risks();
        $risksurl = new \moodle_url(get_docs_url(s(get_string('risks', 'core_role'))));

        $return = '';

        foreach ($allrisks as $type => $risk) {
            if ($risk & (int)$capability->riskbitmask) {
                if (!isset($this->icons[$type])) {
                    $pixicon = new \pix_icon('/i/' . str_replace('risk', 'risk_', $type), get_string($type . 'short', 'admin'));
                    $this->icons[$type] = $OUTPUT->action_icon($risksurl, $pixicon, new \popup_action('click', $risksurl));
                }
                $return .= $this->icons[$type];
            }
        }

        return $return;
    }

    /**
     * For subclasses to override. Allows certain capabilties
     * to be left out of the table.
     *
     * @param object $capability the capability this row relates to.
     * @return boolean. If true, this row is omitted from the table.
     */
    protected function skip_row($capability) {
        $archetype = '';
        if(isset($this->rolearchetype->capabilities[$capability->name])) {
            $archetype =  $this->rolearchetype->capabilities[$capability->name];
        }
        $skip = true;
        foreach($this->roles as $role) {
            $permission = '';
            if(isset($role->capabilities[$capability->name])) {
                $permission =  $role->capabilities[$capability->name];
            }
                
            if($permission != $archetype) {
                $skip = false; 
                break;
            }
        }
        
        return $skip;
    }    
    
    
    /**
     * Add additional attributes to row
     *
     * @param stdClass $capability capability that this table row relates to.
     * @return array key value pairs of attribute names and values.
     */
    protected function get_row_attributes($capability) {
        return array(
                'data-id' => $capability->id,
                'data-name' => $capability->name,
                'data-humanname' => get_capability_string($capability->name),
        );
    }
}
