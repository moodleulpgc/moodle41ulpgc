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
 * Theme functions.
 *
 * @package    theme_moove
 * @copyright 2017 Willian Mano - http://conecti.me
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


// Constants which are use throughout this theme. // mainly from moove theme
define('THEME_MOOVE_SETTING_SELECT_YES', 'yes');
define('THEME_MOOVE_SETTING_SELECT_NO', 'no');

define('THEME_MOOVE_SETTING_STATICPAGELINKPOSITION_NONE', 'none');
define('THEME_MOOVE_SETTING_STATICPAGELINKPOSITION_FOOTNOTE', 'footnote');
define('THEME_MOOVE_SETTING_STATICPAGELINKPOSITION_FOOTER', 'footer');
define('THEME_MOOVE_SETTING_STATICPAGELINKPOSITION_BOTH', 'both');

define('THEME_MOOVE_SETTING_HIDENODESPRIMARYNAVIGATION_HOME', 'home');
define('THEME_MOOVE_SETTING_HIDENODESPRIMARYNAVIGATION_MYHOME', 'myhome');
define('THEME_MOOVE_SETTING_HIDENODESPRIMARYNAVIGATION_MYCOURSES', 'courses');
define('THEME_MOOVE_SETTING_HIDENODESPRIMARYNAVIGATION_SITEADMIN', 'siteadmin');

define('THEME_MOOVE_SETTING_COURSEBREADCRUMBS_DONTCHANGE', 'dontchange');

/**
 * Returns the main SCSS content.
 *
 * @param theme_config $theme The theme config object.
 * @return string
 */
function theme_moove_get_main_scss_content($theme) {
    global $CFG;

    $scss = '';
    $filename = !empty($theme->settings->preset) ? $theme->settings->preset : null;
    $fs = get_file_storage();

    $context = context_system::instance();
    if ($filename == 'default.scss') {
        $scss .= file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss');
    } else if ($filename == 'plain.scss') {
        $scss .= file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/plain.scss');
    } else if ($filename == 'ulpgc.scss') {
        $scss .= file_get_contents($CFG->dirroot . '/theme/moove/scss/preset/defaultulpgc.scss');
    } else if ($filename && ($presetfile = $fs->get_file($context->id, 'theme_moove', 'preset', 0, '/', $filename))) {
        $scss .= $presetfile->get_content();
    } else {
        // Safety fallback - maybe new installs etc.
        $scss .= file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss');
    }

    // Moove scss.
    $moovevariables = file_get_contents($CFG->dirroot . '/theme/moove/scss/moove/_variables.scss');

    // ensure add ULPGC variables after standard ones but before regular css
    $ulpgcvariables = file_get_contents($CFG->dirroot . '/theme/moove/scss/ulpgc/variables.scss');
    $moovevariables = $moovevariables . "\n" . $ulpgcvariables  ;

    $moove = file_get_contents($CFG->dirroot . '/theme/moove/scss/default.scss');


    $security = file_get_contents($CFG->dirroot . '/theme/moove/scss/moove/_security.scss');

    // Combine them together.
    $allscss = $moovevariables . "\n" . $scss . "\n" . $moove . "\n" . $security;

    // now load specific ULPGC scss
    $ulpgcbuttons = file_get_contents($CFG->dirroot . '/theme/moove/scss/ulpgc/buttons.scss');
    $ulpgc = file_get_contents($CFG->dirroot . '/theme/moove/scss/ulpgc.scss');
    $allscss = $allscss . "\n" . $ulpgcbuttons . "\n"  . $ulpgc;

    return $allscss;
}

/**
 * Inject additional SCSS.
 *
 * @param theme_config $theme The theme config object.
 * @return string
 */
function theme_moove_get_extra_scss($theme) {
    global $CFG;
    // Require the necessary libraries.
    require_once($CFG->dirroot . '/course/lib.php');

    $content = '';

    // Sets the login background image.
    $loginbgimgurl = $theme->setting_file_url('loginbgimg', 'loginbgimg');
    if (!empty($loginbgimgurl)) {
        $content .= 'body.pagelayout-login #page { ';
        $content .= "background-image: url('$loginbgimgurl'); background-size: cover;";
        $content .= ' }';
    }

    // Setting: Activity icon purpose.
    // Get installed activity modules.
    $installedactivities = get_module_types_names();
    // Iterate over all existing activities.
    foreach ($installedactivities as $modname => $modinfo) {
        // Get default purpose of activity module.
        $defaultpurpose = plugin_supports('mod', $modname, FEATURE_MOD_PURPOSE, MOD_PURPOSE_OTHER);
        // If the plugin does not have any default purpose.
        if (!$defaultpurpose) {
            // Fallback to "other" purpose.
            $defaultpurpose = MOD_PURPOSE_OTHER;
        }
        // If the activity purpose setting is set and differs from the activity's default purpose.
        $configname = 'activitypurpose'.$modname;
        if (isset($theme->settings->{$configname}) && $theme->settings->{$configname} != $defaultpurpose) {
            // Add CSS to modify the activity purpose color in the activity chooser and the activity icon.
            $content .= '.activity.modtype_'.$modname.' .activityiconcontainer.courseicon,';
            $content .= '.modchoosercontainer .modicon_'.$modname.'.activityiconcontainer,';
            $content .= '#page-header .modicon_'.$modname.'.activityiconcontainer,';
            $content .= '.block_recentlyaccesseditems .theme-boost-union-'.$modname.'.activityiconcontainer,';
            $content .= '.block_timeline .theme-boost-union-mod_'.$modname.'.activityiconcontainer {';
            // If the purpose is now different than 'other', change the background color to the new color.
            if ($theme->settings->{$configname} != MOD_PURPOSE_OTHER) {
                $content .= 'background-color: var(--activity' . $theme->settings->{$configname} . ') !important;';

                // Otherwise, the background color is set to light grey (as there is no '--activityother' variable).
            } else {
                $content .= 'background-color: $light !important;';
            }
            // If the default purpose originally was 'other' and now is overridden, make the icon white.
            if ($defaultpurpose == MOD_PURPOSE_OTHER) {
                //$content .= '.activityicon, .icon { filter: brightness(0) invert(1); }';
                $content .= '.activityicon, .icon { filter: none; }';
            }
            // If the default purpose was not 'other' and now it is, make the icon black.
            if ($theme->settings->{$configname} == MOD_PURPOSE_OTHER) {
                $content .= '.activityicon, .icon { filter: none; }';
            }
            $content .= '}';
        }
    }

    // Always return the background image with the scss when we have it.
    return !empty($theme->settings->scss) ? $theme->settings->scss . ' ' . $content : $content;
}

/**
 * Get SCSS to prepend.
 *
 * @param theme_config $theme The theme config object.
 * @return string
 */
function theme_moove_get_pre_scss($theme) {
    $scss = '';
    $configurable = [
        // Config key => [variableName, ...].
        'brandcolor' => ['brand-primary'],
        'secondarymenucolor' => 'secondary-menu-color',
        'fontsite' => 'font-family-sans-serif',
        'showsettingsincourse' => ['showsettingsincourse'],
        'incoursesettingsswitchtoroleposition' => ['incoursesettingsswitchtoroleposition'],
        'blockicon' => ['blockicon'],
        'blockwidthdashboard' => ['blockwidthdashboard'],
    ];

    // Prepend variables first.
    foreach ($configurable as $configkey => $targets) {
        $value = isset($theme->settings->{$configkey}) ? $theme->settings->{$configkey} : null;
        if (empty($value)) {
            continue;
        }
        array_map(function($target) use (&$scss, $value) {
            if ($target == 'fontsite') {
                $scss .= '$' . $target . ': "' . $value . '", sans-serif !default' .";\n";
            } else {
                $scss .= '$' . $target . ': ' . $value . ";\n";
            }
        }, (array) $targets);
    }

    // Prepend pre-scss.
    if (!empty($theme->settings->scsspre)) {
        $scss .= $theme->settings->scsspre;
    }


    if (isset($theme->settings->blockwidthdashboard)) {
        $scss .= '$blocks-width-dashboard: ' . $theme->settings->blockwidthdashboard . "px;\n";
    }


    return $scss;
}

/**
 * Get compiled css.
 *
 * @return string compiled css
 */
function theme_moove_get_precompiled_css() {
    global $CFG;

    return file_get_contents($CFG->dirroot . '/theme/moove/style/moodle.css');
}

/**
 * Serves any files associated with the theme settings.
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return mixed
 */
function theme_moove_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    $theme = theme_config::load('moove');

    if ($context->contextlevel == CONTEXT_SYSTEM &&
        ($filearea === 'logo' || $filearea === 'loginbgimg' || $filearea == 'favicon')) {
        $theme = theme_config::load('moove');
        // By default, theme files must be cache-able by both browsers and proxies.
        if (!array_key_exists('cacheability', $options)) {
            $options['cacheability'] = 'public';
        }
        return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);
    }

    if ($context->contextlevel == CONTEXT_SYSTEM && preg_match("/^sliderimage[1-9][0-9]?$/", $filearea) !== false) {
        return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);
    }

    if ($context->contextlevel == CONTEXT_SYSTEM && $filearea === 'marketing1icon') {
        return $theme->setting_file_serve('marketing1icon', $args, $forcedownload, $options);
    }

    if ($context->contextlevel == CONTEXT_SYSTEM && $filearea === 'marketing2icon') {
        return $theme->setting_file_serve('marketing2icon', $args, $forcedownload, $options);
    }

    if ($context->contextlevel == CONTEXT_SYSTEM && $filearea === 'marketing3icon') {
        return $theme->setting_file_serve('marketing3icon', $args, $forcedownload, $options);
    }

    if ($context->contextlevel == CONTEXT_SYSTEM && $filearea === 'marketing4icon') {
        return $theme->setting_file_serve('marketing4icon', $args, $forcedownload, $options);
    }

    send_file_not_found();
}


////////////////////////////////////////////////////////////////////////////////////////////////
// ecastro ULPGC

/**
 * Provides the node for the in-course course or activity settings.
 *
 * @return navigation_node.
 */
function theme_moove_get_incourse_settings() {
    global $COURSE, $PAGE;
    // Initialize the node with false to prevent problems on pages that do not have a courseadmin node.
    $node = false;

    // If setting showsettingsincourse is enabled.
    if (get_config('theme_moove', 'showsettingsincourse') == 'yes') { // TODO CHANGE when setting available
        // Only search for the courseadmin node if we are within a course or a module context.
        if ($PAGE->context->contextlevel == CONTEXT_COURSE || $PAGE->context->contextlevel == CONTEXT_MODULE) {
            // Get the courseadmin node for the current page.
            $node = $PAGE->settingsnav->find('courseadmin', navigation_node::TYPE_COURSE);
            // Check if $node is not empty for other pages like for example the langauge customization page.

            /*
            $siblibgs = $node->get_siblings();
            foreach($siblibgs as $n) {
                print_object($n->get_children_key_list());
                print_object(' SIB node coursesettings');
            }
            */

            //print_object($node->get_children_key_list());

            //print_object(local_ulpgccore_boostnav_get_all_childrenkeys($node));
            //print_object(" MOOVE local_ulpgccore_boostnav_get_all_childrenkeys");
			//echo "<script>alert('ok0');</script>";
			//print_object($node);
            return $node;

            if (!empty($node)) {
                // If the setting 'incoursesettingsswitchtoroleposition' is set either set to the option 'yes'
                // or to the option 'both', then add these to the $node.
                if (((get_config('theme_moove', 'switchtoroleposition') == 'yes') ||
                    (get_config('theme_moove', 'switchtoroleposition') == 'both'))
                    && !is_role_switched($COURSE->id)) {
                    // Build switch role link
                    // We could only access the existing menu item by creating the user menu and traversing it.
                    // So we decided to create this node from scratch with the values copied from Moodle core.
                    $roles = get_switchable_roles($PAGE->context);
                    if (is_array($roles) && (count($roles) > 0)) {
                        // Define the properties for a new tab.
                        $properties = array('text' => get_string('switchroleto', 'theme_moove'),
                                            'type' => navigation_node::TYPE_CONTAINER,
                                            'key'  => 'switchroletotab');
                        // Create the node.
                        $switchroletabnode = new navigation_node($properties);
                        // Add the tab to the course administration node.
                        $node->add_node($switchroletabnode);
                        // Add the available roles as children nodes to the tab content.
                        foreach ($roles as $key => $role) {
                            $properties = array('action' => new moodle_url('/course/switchrole.php',
                                array('id'         => $COURSE->id,
                                      'switchrole' => $key,
                                      'returnurl'  => $PAGE->url->out_as_local_url(false),
                                      'sesskey'    => sesskey())),
                                                'type'   => navigation_node::TYPE_CUSTOM,
                                                'text'   => $role);
                            $switchroletabnode->add_node(new navigation_node($properties));
                        }
                    }
                }
            }
        }
        return $node;
    }
}

/**
 * Provides the node for the in-course settings for other contexts.
 *
 * @return navigation_node.
 */
function theme_moove_get_incourse_activity_settings() {
    global $PAGE;
    $context = $PAGE->context;
    $node = false;

    // If setting showsettingsincourse is enabled
    if (get_config('theme_moove', 'showsettingsincourse') == 'yes') {
		//echo "<script>alert('ok');</script>";
        // Settings belonging to activity or resources.
        if ($context->contextlevel == CONTEXT_MODULE) {
            $node = $PAGE->settingsnav->find('modulesettings', navigation_node::TYPE_SETTING);

			//print_object($node->get_children_key_list());
            //print_object(' MOOVE node modulesettings');

            //print_object(local_ulpgccore_boostnav_get_all_childrenkeys($node));
            //print_object(" MOOVE local_ulpgccore_boostnav_get_all_childrenkeys");

            /*
            $siblings = $node->get_siblings();
            foreach($siblings as $n) {
                //print_object($n->get_children_key_list());
                //print_object(' SIBLIBFS node modulesettings');
            }

            //$url = new moodle_url('/mod/assign/overrides.php', ['cmid' => $PAGE->cm->id, 'mode' => 'user']);
            $newnode = navigation_node::create(get_string('reuse', 'moove'),
                        null, navigation_node::TYPE_CATEGORY, null, 'mod_bkrestore');
            $bnode  = $node->add_node($newnode, 'www');
            if($n = $PAGE->settingsnav->find('backup', navigation_node::TYPE_SETTING)) {
                //$n->set_parent($bnode);
                $bnode->add_node(clone $n);
                $n->remove();
                print_object("moved");
            }
            if($n = $PAGE->settingsnav->find('restore', navigation_node::TYPE_SETTING)) {
                //$n->set_parent($bnode);
                $bnode->add_node(clone $n);

                $n->remove();
                print_object("moved");
            }
*/
            //print_object(local_ulpgccore_boostnav_get_all_childrenkeys($node));
            //print_object(" MOOVE local_ulpgccore_boostnav_get_all_childrenkeys");


        } else if ($context->contextlevel == CONTEXT_COURSECAT) {
            // For course category context, show category settings menu, if we're on the course category page.
            if ($PAGE->pagetype === 'course-index-category') {
                $node = $PAGE->settingsnav->find('categorysettings', navigation_node::TYPE_CONTAINER);
            }
        } else {
            $node = false;
        }
    }

    return $node;
}

/**
 * Add settings nodes to main data to render
 *
 * @param array $data The object containig main data for template rendering
 * @return array
 */
function theme_moove_navbar_settings(array $data): array {
    if(!(get_config('theme_moove', 'showsettingsincourse') == 'yes')) {
        return $data;
    }

    $data['incoursesettings'] = false;
    $data['inactivitysettings'] = false;

    if($data['showsettingsincourse'] = (get_config('theme_moove', 'showsettingsincourse') == 'yes')) {
        $node = theme_moove_get_incourse_settings();
        if(!empty($node)) {
            $data['node'] = $node;
            $data['incoursesettings'] = get_string('actionsmenucourse', 'theme_moove');
            $data['coursesettingsicon'] = trim(get_config('theme_moove', 'coursesettingsicon'));
            if(empty($data['coursesettingsicon'])) {
                $data['coursesettingsicon'] = 'fa-cog';
            }

        }
        $node = theme_moove_get_incourse_activity_settings();
        if(!empty($node)) {
            $data['activitynode'] = $node;
            $data['inactivitysettings'] = get_string('actionsmenuactivity', 'theme_moove');
            $data['activitysettingsicon'] = trim(get_config('theme_moove', 'activitysettingsicon'));
            if(empty($data['activitysettingsicon'])) {
                $data['activitysettingsicon'] = 'fa-wrench';
            }
        }
        $data['showmenuitemicons'] = (get_config('theme_moove', 'showmenuitemicons') == 'yes');
    }
    return $data;
}

/**
 * Collects union settings nodes and generates array
 * These settings copied form moove
 *
 * @return array
 */
function theme_moove_union_settings(): array {
    global $PAGE;

    $courserelatedhintshtml = theme_moove_get_course_related_hints();
    if ($courserelatedhintshtml) {
        $templatecontext['courserelatedhints'] = $courserelatedhintshtml;
    }

    $hintsetting = get_config('theme_moove', 'javascriptdisabledhint');
    if ($hintsetting == THEME_MOOVE_SETTING_SELECT_YES) {
        // Add marker to show the hint to templatecontext.
        $templatecontext['showjavascriptdisabledhint'] = true;
    }

    // static pages
    $config = get_config('theme_moove');
    // The static pages to be supported.
    $staticpages = array('imprint', 'contact', 'help', 'maintenance');

    // Iterate over the static pages.
    foreach ($staticpages as $staticpage) {
        // If the page is enabled.
        if ($config->{'enable'.$staticpage} == THEME_MOOVE_SETTING_SELECT_YES) {
            // Here the link is shown ONLY in footer popover.  // ecastro ULPGC
            $templatecontext[$staticpage.'linkpositionfooter'] = true;
            // Add the page link and page title to the templatecontext.
            $templatecontext[$staticpage.'link'] = theme_moove_get_staticpage_link($staticpage);
            $templatecontext[$staticpage.'pagetitle'] = theme_moove_get_staticpage_pagetitle($staticpage);
        }
    }

    // footer
    $templatecontext['ulpgcfooter'] = $config->ulpgcfooter;
    foreach(array(1,2,3) as $i) {
        $templatecontext['footerblock'.$i] = $config->{"footerblock$i"};
    }

    // JS section
    $backtotopbutton = get_config('theme_moove', 'backtotopbutton');
    // Add back to top AMC module if the feature is enabled.
    if ($backtotopbutton == THEME_MOOVE_SETTING_SELECT_YES) {
        $PAGE->requires->js_call_amd('theme_moove/backtotopbutton', 'init');
    }

    $scrollspy = get_config('theme_moove', 'scrollspy');
    // Add scroll-spy AMD module if the feature is enabled.
    if ($scrollspy == THEME_MOOVE_SETTING_SELECT_YES) {
        $PAGE->requires->js_call_amd('theme_moove/scrollspy', 'init');
    }

    return $templatecontext;
}

/**
 * Build the course related hints HTML code.
 * This function evaluates and composes all course related hints which may appear on a course page below the course header.
 *
 * @copyright  2022 Alexander Bias, lern.link GmbH <alexander.bias@lernlink.de>
 * @copyright  based on code from theme_boost_campus by Kathrin Osswald.
 *
 * @return string.
 */
function theme_moove_get_course_related_hints() {
    global $CFG, $COURSE, $PAGE, $USER, $OUTPUT;

    // Require user library.
    require_once($CFG->dirroot.'/user/lib.php');

    // Initialize HTML code.
    $html = '';

    // If the setting showhintcoursehidden is set and the visibility of the course is hidden and
    // a hint for the visibility will be shown.
    if (get_config('theme_moove', 'showhintcoursehidden') == THEME_MOOVE_SETTING_SELECT_YES
            && has_capability('theme/moove:viewhintinhiddencourse', \context_course::instance($COURSE->id))
            && $PAGE->has_set_url()
            && $PAGE->url->compare(new moodle_url('/course/view.php'), URL_MATCH_BASE)
            && $COURSE->visible == false) {

        // Prepare template context.
        $templatecontext = array('courseid' => $COURSE->id);

        // If the user has the capability to change the course settings, an additional link to the course settings is shown.
        if (has_capability('moodle/course:update', context_course::instance($COURSE->id))) {
            $templatecontext['showcoursesettingslink'] = true;
        } else {
            $templatecontext['showcoursesettingslink'] = false;
        }

        // Render template and add it to HTML code.
        $html .= $OUTPUT->render_from_template('theme_moove/course-hint-hidden', $templatecontext);
    }

    // If the setting showhintcourseguestaccess is set and the user is accessing the course with guest access,
    // a hint for users is shown.
    // We also check that the user did not switch the role. This is a special case for roles that can fully access the course
    // without being enrolled. A role switch would show the guest access hint additionally in that case and this is not
    // intended.
    if (get_config('theme_moove', 'showhintcourseguestaccess') == THEME_MOOVE_SETTING_SELECT_YES
            && is_guest(\context_course::instance($COURSE->id), $USER->id)
            && $PAGE->has_set_url()
            && $PAGE->url->compare(new moodle_url('/course/view.php'), URL_MATCH_BASE)
            && !is_role_switched($COURSE->id)) {

        // Require self enrolment library.
        require_once($CFG->dirroot . '/enrol/self/lib.php');

        // Prepare template context.
        $templatecontext = array('courseid' => $COURSE->id,
                'role' => role_get_name(get_guest_role()));

        // Search for an available self enrolment link in this course.
        $templatecontext['showselfenrollink'] = false;
        $instances = enrol_get_instances($COURSE->id, true);
        $plugins = enrol_get_plugins(true);
        foreach ($instances as $instance) {
            // If the enrolment plugin isn't enabled currently, skip it.
            if (!isset($plugins[$instance->enrol])) {
                continue;
            }

            // Remember the enrolment plugin.
            $plugin = $plugins[$instance->enrol];

            // If there is a self enrolment link.
            if ($plugin->show_enrolme_link($instance)) {
                $templatecontext['showselfenrollink'] = true;
                break;
            }
        }

        // Render template and add it to HTML code.
        $html .= $OUTPUT->render_from_template('theme_moove/course-hint-guestaccess', $templatecontext);
    }

    // If the setting showhintcourseselfenrol is set, a hint for users is shown that the course allows unrestricted self
    // enrolment. This hint is only shown if the course is visible, the self enrolment is visible and if the user has the
    // capability "theme/moove:viewhintcourseselfenrol".
    if (get_config('theme_moove', 'showhintcourseselfenrol') == THEME_MOOVE_SETTING_SELECT_YES
            && has_capability('theme/moove:viewhintcourseselfenrol', \context_course::instance($COURSE->id))
            && $PAGE->has_set_url()
            && $PAGE->url->compare(new moodle_url('/course/view.php'), URL_MATCH_BASE)
            && $COURSE->visible == true) {

        // Get the active enrol instances for this course.
        $enrolinstances = enrol_get_instances($COURSE->id, true);

        // Prepare to remember when self enrolment is / will be possible.
        $selfenrolmentpossiblecurrently = false;
        $selfenrolmentpossiblefuture = false;
        foreach ($enrolinstances as $instance) {
            // Check if unrestricted self enrolment is possible currently or in the future.
            $now = (new \DateTime("now", \core_date::get_server_timezone_object()))->getTimestamp();
            if ($instance->enrol == 'self' && empty($instance->password) && $instance->customint6 == 1 &&
                    (empty($instance->enrolenddate) || $instance->enrolenddate > $now)) {

                // Build enrol instance object with all necessary information for rendering the note later.
                $instanceobject = new stdClass();

                // Remember instance name.
                if (empty($instance->name)) {
                    $instanceobject->name = get_string('pluginname', 'enrol_self') .
                            " (" . get_string('defaultcoursestudent', 'core') . ")";
                } else {
                    $instanceobject->name = $instance->name;
                }

                // Remember type of unrestrictedness.
                if (empty($instance->enrolenddate) && empty($instance->enrolstartdate)) {
                    $instanceobject->unrestrictedness = 'unlimited';
                    $selfenrolmentpossiblecurrently = true;
                } else if (empty($instance->enrolstartdate) &&
                        !empty($instance->enrolenddate) && $instance->enrolenddate > $now) {
                    $instanceobject->unrestrictedness = 'until';
                    $selfenrolmentpossiblecurrently = true;
                } else if (empty($instance->enrolenddate) &&
                        !empty($instance->enrolstartdate) && $instance->enrolstartdate > $now) {
                    $instanceobject->unrestrictedness = 'from';
                    $selfenrolmentpossiblefuture = true;
                } else if (empty($instance->enrolenddate) &&
                        !empty($instance->enrolstartdate) && $instance->enrolstartdate <= $now) {
                    $instanceobject->unrestrictedness = 'since';
                    $selfenrolmentpossiblecurrently = true;
                } else if (!empty($instance->enrolstartdate) && $instance->enrolstartdate > $now &&
                        !empty($instance->enrolenddate) && $instance->enrolenddate > $now) {
                    $instanceobject->unrestrictedness = 'fromuntil';
                    $selfenrolmentpossiblefuture = true;
                } else if (!empty($instance->enrolstartdate) && $instance->enrolstartdate <= $now &&
                        !empty($instance->enrolenddate) && $instance->enrolenddate > $now) {
                    $instanceobject->unrestrictedness = 'sinceuntil';
                    $selfenrolmentpossiblecurrently = true;
                } else {
                    // This should not happen, thus continue to next instance.
                    continue;
                }

                // Remember enrol start date.
                if (!empty($instance->enrolstartdate)) {
                    $instanceobject->startdate = $instance->enrolstartdate;
                } else {
                    $instanceobject->startdate = null;
                }

                // Remember enrol end date.
                if (!empty($instance->enrolenddate)) {
                    $instanceobject->enddate = $instance->enrolenddate;
                } else {
                    $instanceobject->enddate = null;
                }

                // Remember this instance.
                $selfenrolinstances[$instance->id] = $instanceobject;
            }
        }

        // If there is at least one unrestricted enrolment instance,
        // show the hint with information about each unrestricted active self enrolment in the course.
        if (!empty($selfenrolinstances) &&
                ($selfenrolmentpossiblecurrently == true || $selfenrolmentpossiblefuture == true)) {

            // Prepare template context.
            $templatecontext = array();

            // Add the start of the hint t the template context
            // depending on the fact if enrolment is already possible currently or will be in the future.
            if ($selfenrolmentpossiblecurrently == true) {
                $templatecontext['selfenrolhintstart'] = get_string('showhintcourseselfenrolstartcurrently', 'theme_moove');
            } else if ($selfenrolmentpossiblefuture == true) {
                $templatecontext['selfenrolhintstart'] = get_string('showhintcourseselfenrolstartfuture', 'theme_moove');
            }

            // Iterate over all enrolment instances to output the details.
            foreach ($selfenrolinstances as $selfenrolinstanceid => $selfenrolinstanceobject) {
                // If the user has the capability to config self enrolments, enrich the instance name with the settings link.
                if (has_capability('enrol/self:config', \context_course::instance($COURSE->id))) {
                    $url = new moodle_url('/enrol/editinstance.php', array('courseid' => $COURSE->id,
                            'id' => $selfenrolinstanceid, 'type' => 'self'));
                    $selfenrolinstanceobject->name = html_writer::link($url, $selfenrolinstanceobject->name);
                }

                // Add the enrolment instance information to the template context depending on the instance configuration.
                if ($selfenrolinstanceobject->unrestrictedness == 'unlimited') {
                    $templatecontext['selfenrolinstances'][] = get_string('showhintcourseselfenrolunlimited', 'theme_moove',
                            array('name' => $selfenrolinstanceobject->name));
                } else if ($selfenrolinstanceobject->unrestrictedness == 'until') {
                    $templatecontext['selfenrolinstances'][] = get_string('showhintcourseselfenroluntil', 'theme_moove',
                            array('name' => $selfenrolinstanceobject->name,
                                    'until' => userdate($selfenrolinstanceobject->enddate)));
                } else if ($selfenrolinstanceobject->unrestrictedness == 'from') {
                    $templatecontext['selfenrolinstances'][] = get_string('showhintcourseselfenrolfrom', 'theme_moove',
                            array('name' => $selfenrolinstanceobject->name,
                                    'from' => userdate($selfenrolinstanceobject->startdate)));
                } else if ($selfenrolinstanceobject->unrestrictedness == 'since') {
                    $templatecontext['selfenrolinstances'][] = get_string('showhintcourseselfenrolsince', 'theme_moove',
                            array('name' => $selfenrolinstanceobject->name,
                                    'since' => userdate($selfenrolinstanceobject->startdate)));
                } else if ($selfenrolinstanceobject->unrestrictedness == 'fromuntil') {
                    $templatecontext['selfenrolinstances'][] = get_string('showhintcourseselfenrolfromuntil', 'theme_moove',
                            array('name' => $selfenrolinstanceobject->name,
                                    'until' => userdate($selfenrolinstanceobject->enddate),
                                    'from' => userdate($selfenrolinstanceobject->startdate)));
                } else if ($selfenrolinstanceobject->unrestrictedness == 'sinceuntil') {
                    $templatecontext['selfenrolinstances'][] = get_string('showhintcourseselfenrolsinceuntil', 'theme_moove',
                            array('name' => $selfenrolinstanceobject->name,
                                    'until' => userdate($selfenrolinstanceobject->enddate),
                                    'since' => userdate($selfenrolinstanceobject->startdate)));
                }
            }

            // If the user has the capability to config self enrolments, add the call for action to the template context.
            if (has_capability('enrol/self:config', \context_course::instance($COURSE->id))) {
                $templatecontext['calltoaction'] = true;
            } else {
                $templatecontext['calltoaction'] = false;
            }

            // Render template and add it to HTML code.
            $html .= $OUTPUT->render_from_template('theme_moove/course-hint-selfenrol', $templatecontext);
        }
    }

    // If the setting showswitchedroleincourse is set and the user has switched his role,
    // a hint for the role switch will be shown.
    if (get_config('theme_moove', 'showswitchedroleincourse') === THEME_MOOVE_SETTING_SELECT_YES
            && is_role_switched($COURSE->id) ) {

        // Get the role name switched to.
        $opts = \user_get_user_navigation_info($USER, $PAGE);
        $role = $opts->metadata['rolename'];

        // Get the URL to switch back (normal role).
        $url = new moodle_url('/course/switchrole.php',
                array('id' => $COURSE->id,
                        'sesskey' => sesskey(),
                        'switchrole' => 0,
                        'returnurl' => $PAGE->url->out_as_local_url(false)));

        // Prepare template context.
        $templatecontext = array('role' => $role,
                'url' => $url->out());

        // Render template and add it to HTML code.
        $html .= $OUTPUT->render_from_template('theme_moove/course-hint-switchedrole', $templatecontext);
    }

    // Return HTML code.
    return $html;
}

/**
 * Build the link to a static page.
 *
 * @param string $page The static page's identifier.
 * @return string.
 */
function theme_moove_get_staticpage_link($page) {
    // Compose the URL object.
    $url = new moodle_url('/theme/moove/pages/'.$page.'.php');

    // Return the string representation of the URL.
    return $url->out();
}

/**
 * Build the page title of a static page.
 *
 * @param string $page The static page's identifier.
 * @return string.
 */
function theme_moove_get_staticpage_pagetitle($page) {
    // Get the configured page title.
    $pagetitleconfig = format_string(get_config('theme_moove', $page.'pagetitle'), true,
    ['context' => \context_system::instance()]);

    // If there is a string configured.
    if ($pagetitleconfig) {
        // Return this setting.
        return $pagetitleconfig;

        // Otherwise.
    } else {
        // Return the default string.
        return get_string($page.'pagetitledefault', 'theme_moove');
    }
}
