<?php

/**
 * @package mod-tracker
 * @category mod
 * @author Clifford Tham, Valery Fremaux > 1.8
 * @date 02/12/2007
 *
 * From for showing element list
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    // It must be included from view.php in mod/tracker
}

$id = optional_param('id', 0, PARAM_INT); // Course Module ID, or
$a  = optional_param('a', 0, PARAM_INT);  // course ID

echo $OUTPUT->box_start('generalbox bugreport', null, array('width'=>'100%'));

tracker_loadelements($tracker, $elements);

echo $OUTPUT->heading(tracker_getstring('elements', 'tracker'));

$localstr = tracker_getstring('local', 'tracker');
$namestr = tracker_getstring('name');
$typestr = tracker_getstring('type', 'tracker');
$cmdstr = tracker_getstring('action', 'tracker');

unset($table);
$table = new html_table();
$table->head = array("<b>$cmdstr</b>", "<b>$namestr</b>", "<b>$localstr</b>", "<b>$typestr</b>");
$table->width = '100%';
$table->size = array(100, 250, 50, 50);
$table->align = array('left', 'center', 'center', 'center');

if (!empty($elements)) {
    // clean list from used elements
    foreach ($elements as $id => $element) {
        if (in_array($element->id, array_keys($used))) {
            unset($elements[$id]);
        }
    }
    // Make list.
    foreach ($elements as $element) {

        $name = format_string($element->description);
        $name .= '<br />';
        $name .= '<span style="font-size:70%">';
        $name .= $element->name;
        $name .= '</span>';
        if ($element->hasoptions() && empty($element->options)) {
            $name .= ' <span class="error">('.tracker_getstring('nooptions', 'tracker').')</span>';
        }

        $params = array('id' => $cm->id, 'view' => 'admin', 'what' => 'addelement', 'elementid' => $element->id);
        $url = new moodle_url('/mod/tracker/view.php', $params);
        $actions = '&nbsp;<a href="'.$url.'" title="'.tracker_getstring('addtothetracker', 'tracker').'" >'.$OUTPUT->pix_icon('t/moveleft', '') .'</a>';

        if ($element->type_has_options()) {
            $params = array('id' => $cm->id, 'view' => 'admin', 'what' => 'viewelementoptions', 'elementid' => $element->id);
            $url = new moodle_url('/mod/tracker/view.php', $params);
            $actions .= '&nbsp;<a href="'.$url.'" title="'.tracker_getstring('editoptions', 'tracker').'">'.$OUTPUT->pix_icon('editoptions', '', 'mod_tracker').'</a>';
        }

        $params = array('id' => $cm->id, 'view' => 'admin', 'what' => 'editelement', 'elementid' => $element->id, 'type' => $element->type);
        $url = new moodle_url('/mod/tracker/view.php', $params);
        $actions .= '&nbsp;<a href="'.$url.'" title="'.tracker_getstring('editproperties', 'tracker').'">'.$OUTPUT->pix_icon('t/edit', '') .'</a>';

        $params = array('id' => $cm->id, 'view' => 'admin', 'what' => 'deleteelement', 'elementid' => $element->id);
        $url = new moodle_url('/mod/tracker/view.php', $params);
        //$actions .= '&nbsp;<a href="'.$url.'" title="'.tracker_getstring('delete').'">'.$OUTPUT->pix_icon('t/delete', '') .'</a>';
        $confirmaction = new \confirm_action(get_string('confirmelementdelete', 'tracker', $element->name));
        $icon = new pix_icon('t/delete', get_string('delete'), 'core', array());
        $actions .=  '&nbsp; '.$OUTPUT->action_icon($url, $icon, $confirmaction);

        $local = '';
        if ($element->course == $COURSE->id) {
            $local = ''.$OUTPUT->pix_icon('i/course', '');
        }
        $type = $OUTPUT->pix_icon("types/{$element->type}", '', 'mod_tracker');
        $table->data[] = array($actions, $name, $local, $type);
    }
    $PAGE->requires->strings_for_js(array('confirmelementdelete'), 'tracker');    
    echo html_writer::table($table);
} else {
    echo '<center>';
    print_string('noelements', 'tracker');
    echo '<br /></center>';
}
echo $OUTPUT->box_end();


echo $OUTPUT->box_start('generalbox bugreport', null, array('width'=>'100%'));
$elementtypesmenu = array();

?>
<form name="addelement" method="post" action="<?php echo $CFG->wwwroot ?>/mod/tracker/view.php">
<table border="0" width="100%">
    <tr>
        <td valign="top">
            <b><?php print_string('createnewelement', 'tracker') ?>:</b>
        </td>
        <td valign="top">
                <?php
                    echo '<input type="hidden" name="view" value="admin" />';
                    echo '<input type="hidden" name="id" value="'.$cm->id.'" />';
                    echo '<input type="hidden" name="what" value="createelement" />';
                    $types = tracker_getelementtypes();
                    foreach ($types as $type) {
                        $elementtypesmenu[$type] = get_string($type, 'tracker');
                    }

                    echo html_writer::select($elementtypesmenu, 'type', '', array('' => 'choose'), array('onchange' => 'document.forms[\'addelement\'].submit();'));
                ?>
        </td>
    </tr>
</table>
</form>

<?php
echo $OUTPUT->box_end();

