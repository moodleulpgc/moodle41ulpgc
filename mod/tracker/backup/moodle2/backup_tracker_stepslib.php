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
 * @package    mod
 * @subpackage tracker
 * @copyright  2010 onwards Valery Fremaux {valery.fremaux@club-internet.fr}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define all the backup steps that will be used by the backup_vodclic_activity_task
 */

/**
 * Define the complete label structure for backup, with file and id annotations
 */
class backup_tracker_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated
        $tracker = new backup_nested_element('tracker', array('id'), array(
            'name', 'intro', 'introformat', 'requirelogin', 'allownotifications', 'enablecomments', 'ticketprefix',
            'timemodified', 'parent', 'supportmode', 'defaultassignee', 'subtrackers', 'enablestates',
            'thanksmessage', 'strictworkflow', 'networkable', 'duedate', 'allowsubmissionsfromdate', 'statenonrepeat')); // ecastro ULPGC

        $elements = new backup_nested_element('elements');

        $element = new backup_nested_element('element', array('id'), array(
            'name', 'description', 'type', 'paramint1', 'paramint2', 'paramchar1', 'paramchar2')); // ecastro ULPGC

        $elementitems = new backup_nested_element('elementitems');

        $item = new backup_nested_element('elementitem', array('id'), array(
            'elementid', 'name', 'description', 'sortorder', 'active', 'autoresponse'));  // ecastro ULPGC

        $usedelements = new backup_nested_element('usedelements');

        $usedelement = new backup_nested_element('usedelement', array('id'), array(
            'trackerid', 'elementid', 'sortorder', 'canbemodifiedby', 'active', 'mandatory', 'private')); // ecastro ULPGC

        $issues = new backup_nested_element('issues');

        $issue = new backup_nested_element('issue', array('id'), array(
            'trackerid', 'summary', 'description', 'descriptionformat', 'datereported', 'reportedby', 'status', 'assignedto', 'bywhomid', 'timecreated', 'timemodified', 'timeassigned', 'resolution', 'resolutionformat', 'resolutionpriority', 'downlink', 'uplink', 'usermodified', 'resolvermodified', 'userlastseen'));

        $attribs = new backup_nested_element('issueattributes');

        $attrib = new backup_nested_element('issueattribute', array('id'), array(
            'trackerid', 'issueid', 'elementid', 'elementitemid', 'timemodified'));

        $ccs = new backup_nested_element('ccs');

        $cc = new backup_nested_element('cc', array('id'), array(
            'trackerid', 'userid', 'issueid', 'events'));

        $comments = new backup_nested_element('comments');

        $comment = new backup_nested_element('comment', array('id'), array(
            'trackerid', 'userid', 'issueid', 'comment', 'commentformat', 'datecreated'));

        $dependancies = new backup_nested_element('dependancies');

        $dependancy = new backup_nested_element('dependancy', array('id'), array(
            'trackerid', 'parentid', 'chilid', 'comment', 'commentformat'));

        $ownerships = new backup_nested_element('ownerships');

        $ownership = new backup_nested_element('ownership', array('id'), array(
            'trackerid', 'userid', 'issueid', 'bywhomid', 'timeassigned'));

        $preferences = new backup_nested_element('preferences');

        $preference = new backup_nested_element('preference', array('id'), array(
            'trackerid', 'userid', 'name', 'value'));

        $queries = new backup_nested_element('queries');

        $query = new backup_nested_element('query', array('id'), array(
            'trackerid', 'userid', 'name', 'description', 'published', 'fieldnames', 'fieldvalues'));

        $statechanges = new backup_nested_element('statechanges');

        $state = new backup_nested_element('change', array('id'), array(
            'trackerid', 'issueid', 'userid', 'timechange', 'statusfrom', 'statusto'));

        $translations = new backup_nested_element('translations');
        $translation = new backup_nested_element('translation', array('id'), array(
            'trackerid', 'issueword', 'assigntoword', 'summaryword', 'descriptionword', 'statuswords', 'forcedlang'));


        // Build the tree
        // (love this)
        $tracker->add_child($elements);
        $elements->add_child($element);
		$element->add_child($elementitems);
		$elementitems->add_child($item);

        $tracker->add_child($usedelements);
        $usedelements->add_child($usedelement);

        $tracker->add_child($issues);
        $issues->add_child($issue);

		$issue->add_child($attribs);
		$attribs->add_child($attrib);
		$issue->add_child($ccs);
		$ccs->add_child($cc);
		$issue->add_child($comments);
		$comments->add_child($comment);
		$issue->add_child($ownerships);
		$ownerships->add_child($ownership);
		$issue->add_child($statechanges);
		$statechanges->add_child($state);

		$tracker->add_child($dependancies);
		$dependancies->add_child($dependancy);

        $tracker->add_child($queries);
        $queries->add_child($query);

        $tracker->add_child($preferences);
        $preferences->add_child($preference);

        $tracker->add_child($translations);
        $preferences->add_child($translation);

        // Define sources
        $tracker->set_source_table('tracker', array('id' => backup::VAR_ACTIVITYID));
        $element->set_source_table('tracker_element', array('course' => backup::VAR_COURSEID));
        $item->set_source_table('tracker_elementitem', array('elementid' => backup::VAR_PARENTID));
        $usedelement->set_source_table('tracker_elementused', array('trackerid' => backup::VAR_ACTIVITYID));
        $translation->set_source_table('tracker_translation', array('trackerid' => backup::VAR_ACTIVITYID));

        if ($userinfo) {
            $issue->set_source_table('tracker_issue', array('trackerid' => backup::VAR_PARENTID));
            $attrib->set_source_table('tracker_issueattribute', array('trackerid' => backup::VAR_ACTIVITYID, 'issueid' => backup::VAR_PARENTID));
            $cc->set_source_table('tracker_issuecc', array('trackerid' => backup::VAR_ACTIVITYID, 'issueid' => backup::VAR_PARENTID));
            $comment->set_source_table('tracker_issuecomment', array('trackerid' => backup::VAR_ACTIVITYID, 'issueid' => backup::VAR_PARENTID));
            $dependancy->set_source_table('tracker_issuedependancy', array('trackerid' => backup::VAR_ACTIVITYID));
            $ownership->set_source_table('tracker_issueownership', array('trackerid' => backup::VAR_ACTIVITYID, 'issueid' => backup::VAR_PARENTID));
            $state->set_source_table('tracker_state_change', array('trackerid' => backup::VAR_ACTIVITYID, 'issueid' => backup::VAR_PARENTID));
            $query->set_source_table('tracker_query', array('trackerid' => backup::VAR_ACTIVITYID));
            $preference->set_source_table('tracker_preferences', array('trackerid' => backup::VAR_ACTIVITYID));
        }

        // Define id annotations
        // (none)
        $issue->annotate_ids('user', 'reportedby');
        $issue->annotate_ids('user', 'assignedto');
        $issue->annotate_ids('user', 'bywhomid');
        $cc->annotate_ids('user', 'userid');
        $comment->annotate_ids('user', 'userid');
        $ownership->annotate_ids('user', 'userid');
        $ownership->annotate_ids('user', 'bywhomid');
        $preference->annotate_ids('user', 'userid');
        $query->annotate_ids('user', 'userid');
        $state->annotate_ids('user', 'userid');

        // Define file annotations
        $tracker->annotate_files('mod_tracker', 'intro', null); // This file area hasn't itemid
        $comment->annotate_files('mod_tracker', 'issuecomment', 'id');
        $issue->annotate_files('mod_tracker', 'issuedescription', 'id');
        $issue->annotate_files('mod_tracker', 'issueresolution', 'id');
		$attrib->annotate_files('mod_tracker', 'issueattribute', 'id');

        // Return the root element (tracker), wrapped into standard activity structure
        return $this->prepare_activity_structure($tracker);
    }
}
