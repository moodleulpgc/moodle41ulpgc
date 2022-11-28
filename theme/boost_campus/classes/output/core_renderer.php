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
 * Renderers to align Moodle's HTML with that expected by Bootstrap
 *
 * @package   theme_boost_campus
 * @copyright 2017 Kathrin Osswald, Ulm University kathrin.osswald@uni-ulm.de
 *            copyright based on code from theme_boost by Bas Brands
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_boost_campus\output;

use coding_exception;
use core\plugininfo\enrol;
use html_writer;
use tabobject;
use tabtree;
use custom_menu_item;
use custom_menu;
use block_contents;
use navigation_node;
use action_link;
use stdClass;
use moodle_url;
use preferences_groups;
use action_menu;
use help_icon;
use single_button;
use single_select;
use paging_bar;
use url_select;
use context_course;
use pix_icon;
use context_system;

/**
 * Extending the core_renderer interface.
 *
 * @copyright 2017 Kathrin Osswald, Ulm University kathrin.osswald@uni-ulm.de
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package theme_boost_campus
 * @category output
 */
class core_renderer extends \core_renderer {

    /**
     * Override to add additional class for the random login image to the body.
     *
     * Returns HTML attributes to use within the body tag. This includes an ID and classes.
     *
     * KIZ MODIFICATION: This renderer function is copied and modified from /lib/outputrenderers.php
     *
     * @since Moodle 2.5.1 2.6
     * @param string|array $additionalclasses Any additional classes to give the body tag,
     * @return string
     */
    public function body_attributes($additionalclasses = array()) {
        global $CFG;
        require_once($CFG->dirroot . '/theme/boost_campus/locallib.php');

        if (!is_array($additionalclasses)) {
            $additionalclasses = explode(' ', $additionalclasses);
        }

        // MODIFICATION START.
        // Only add classes for the login page.
        if ($this->page->bodyid == 'page-login-index') {
            $additionalclasses[] = 'loginbackgroundimage';
            // Generating a random class for displaying a random image for the login page.
            $additionalclasses[] = theme_boost_campus_get_random_loginbackgroundimage_class();
        }
        // MODIFICATION END.

        return ' id="'. $this->body_id().'" class="'.$this->body_css_classes($additionalclasses).'"';
    }

    /**
     * Override to be able to use uploaded images from admin_setting as well.
     *
     * Returns the moodle_url for the favicon.
     *
     * KIZ MODIFICATION: This renderer function is copied and modified from /lib/outputrenderers.php
     *
     * @since Moodle 2.5.1 2.6
     * @return moodle_url The moodle_url for the favicon
     * @throws \moodle_exception
     */
    public function favicon() {
        // MODIFICATION START.
        $logo = null;
        if (!during_initial_install()) {
            $logo = get_config('theme_boost_campus', 'favicon');
        }
        if (empty($logo)) {
            //return $this->image_url('favicon', 'theme');
            return 'https://www.ulpgc.es/sites/all/themes/ulpgc/favicon.ico'; // ecastro ULPGC
        }

        // Use $CFG->themerev to prevent browser caching when the file changes.
        return moodle_url::make_pluginfile_url(context_system::instance()->id, 'theme_boost_campus', 'favicon', '',
                theme_get_revision(), $logo);
        // MODIFICATION END.
        // @codingStandardsIgnoreStart
        /* ORIGINAL START.
        return $this->image_url('favicon', 'theme');
        ORIGINAL END. */
        // @codingStandardsIgnoreEnd
    }


    /*
     * This renders the navbar.
     * Uses bootstrap compatible html.
     */
    public function navbar() {
    
        $items = $this->page->navbar->get_items();
        
        $ulpgcshorten = get_config('local_ulpgccore','shortennavbar');
        foreach($items as $item) {
            if($item->type == 20) {
                $item->text = html_writer::span($item->text, 'course');
            } elseif(($item->type == 10 || $item->type == 11 || $item->type == 60) &&        
                ($ulpgcshorten)) { // ecastro ULPGC, to make breadcrumb easier
                $item->text = local_ulpgccore_shorten_titles($item->text);
            }
        }    
    
        return $this->render_from_template('theme_boost_campus/breadcrumb', $this->page->navbar);
    }

    /**
     * Wrapper for header elements.
     *
     * KIZ MODIFICATION: This renderer function is copied and modified from /lib/outputrenderers.php
     *
     * @return string HTML to display the main header.
     */
    public function full_header() {
        // MODIFICATION START.
        global $USER, $COURSE;
        // MODIFICATION END.

        if ($this->page->include_region_main_settings_in_header_actions() &&
                !$this->page->blocks->is_block_present('settings')) {
            // Only include the region main settings if the page has requested it and it doesn't already have
            // the settings block on it. The region main settings are included in the settings block and
            // duplicating the content causes behat failures.
            $this->page->add_header_action(html_writer::div(
                    $this->region_main_settings_menu(),
                    'd-print-none',
                    ['id' => 'region-main-settings-menu']
            ));
        }

        $header = new stdClass();
        // MODIFICATION START.
        // Show the context header settings menu on all pages except for the profile page as we replace
        // it with an edit button there and if we are not on the content bank view page (contentbank/view.php)
        // as this page only adds header actions.
        if ($this->page->pagelayout != 'mypublic' && $this->page->bodyid != 'page-contentbank') {
            $header->settingsmenu = $this->context_header_settings_menu();
        }
        // MODIFICATION END.
        // @codingStandardsIgnoreStart
        /* ORIGINAL START
        $header->settingsmenu = $this->context_header_settings_menu();
        ORIGINAL END. */
        // @codingStandardsIgnoreEnd
        //$header->contextheader = $this->context_header();
        $header->contextheader = $this->context_course_header(); //  ecastro ULPGC

        $header->hasnavbar = empty($this->page->layout_options['nonavbar']);
        //$header->hasnavbar = empty($PAGE->layout_options['nonavbar']); // ecastro ULPGC        
        $header->navbar = $this->navbar();
        // MODIFICATION START.
        // Show the page heading button on all pages except for the profile page.
        // There we replace it with an edit profile button.
        //if ($PAGE->pagelayout != 'mypublic') { // ecastro ULPGC
        if ($this->page->pagelayout != 'mypublic') {
            $header->pageheadingbutton = $this->page_heading_button();
        } else {
            // Get the id of the user for whom the profile page is shown.
            $userid = optional_param('id', $USER->id, PARAM_INT);
            // Check if the shown and the operating user are identical.
            $currentuser = $USER->id == $userid;
            if (($currentuser || is_siteadmin($USER) || !is_siteadmin($userid)) &&
                has_capability('moodle/user:update', \context_system::instance())) {
                $url = new moodle_url('/user/editadvanced.php', array('id'       => $userid, 'course' => $COURSE->id,
                                                                      'returnto' => 'profile'));
                $header->pageheadingbutton = $this->single_button($url, get_string('editmyprofile', 'core'));
            } else if ((has_capability('moodle/user:editprofile', \context_user::instance($userid)) &&
                    !is_siteadmin($USER)) || ($currentuser &&
                    has_capability('moodle/user:editownprofile', \context_system::instance()))) {
                $url = new moodle_url('/user/edit.php', array('id'       => $userid, 'course' => $COURSE->id,
                                                              'returnto' => 'profile'));
                $header->pageheadingbutton = $this->single_button($url, get_string('editmyprofile', 'core'));
            }
        }
        // MODIFICATION END.
        // @codingStandardsIgnoreStart
        /* ORIGINAL START
        $header->pageheadingbutton = $this->page_heading_button();
        ORIGINAL END. */
        // @codingStandardsIgnoreEnd
        $header->courseheader = $this->course_header();
        $header->headeractions = $this->page->get_header_actions();
        return $this->render_from_template('core/full_header', $header);
        
    }

    /**
     * Override to display course settings on every course site for permanent access
     *
     * This is an optional menu that can be added to a layout by a theme. It contains the
     * menu for the course administration, only on the course main page.
     *
     * MODIFICATION: This renderer function is copied and modified from /lib/outputrenderers.php.
     *
     * @return string
     */
    public function context_header_settings_menu() {
        $context = $this->page->context;
        $menu = new action_menu();

        $items = $this->page->navbar->get_items();
        $currentnode = end($items);

        $showcoursemenu = false;
        $showfrontpagemenu = false;
        $showusermenu = false;

        // We are on the course home page.
        // MODIFICATION START.
        // REASON: With the original code, the course settings icon will only appear on the course main page.
        // Therefore the access to the course settings and related functions is not possible on other
        // course pages as there is no omnipresent block anymore. We want these to be accessible
        // on each course page.
        if (($context->contextlevel == CONTEXT_COURSE || $context->contextlevel == CONTEXT_MODULE) && !empty($currentnode)) {
            $showcoursemenu = true;
        }
        // MODIFICATION END.
        // @codingStandardsIgnoreStart
        /* ORIGINAL START.
        if (($context->contextlevel == CONTEXT_COURSE) &&
                !empty($currentnode) &&
                ($currentnode->type == navigation_node::TYPE_COURSE || $currentnode->type == navigation_node::TYPE_SECTION)) {
            $showcoursemenu = true;
        }
        ORIGINAL END. */
        // @codingStandardsIgnoreEnd

        $courseformat = course_get_format($this->page->course);
        // This is a single activity course format, always show the course menu on the activity main page.
        if ($context->contextlevel == CONTEXT_MODULE &&
                !$courseformat->has_view_page()) {

            $this->page->navigation->initialise();
            $activenode = $this->page->navigation->find_active_node();
            // If the settings menu has been forced then show the menu.
            if ($this->page->is_settings_menu_forced()) {
                $showcoursemenu = true;
            } else if (!empty($activenode) && ($activenode->type == navigation_node::TYPE_ACTIVITY ||
                        $activenode->type == navigation_node::TYPE_RESOURCE)) {

                // We only want to show the menu on the first page of the activity. This means
                // the breadcrumb has no additional nodes.
                if ($currentnode && ($currentnode->key == $activenode->key && $currentnode->type == $activenode->type)) {
                    $showcoursemenu = true;
                }
            }
        }

        // This is the site front page.
        if ($context->contextlevel == CONTEXT_COURSE &&
                !empty($currentnode) &&
                $currentnode->key === 'home') {
            $showfrontpagemenu = true;
        }

        // This is the user profile page.
        if ($context->contextlevel == CONTEXT_USER &&
                !empty($currentnode) &&
                ($currentnode->key === 'myprofile')) {
            $showusermenu = true;
        }

        if ($showfrontpagemenu) {
            $settingsnode = $this->page->settingsnav->find('frontpage', navigation_node::TYPE_SETTING);
            if ($settingsnode) {
                // Build an action menu based on the visible nodes from this navigation tree.
                $skipped = $this->build_action_menu_from_navigation($menu, $settingsnode, false, true);

                // We only add a list to the full settings menu if we didn't include every node in the short menu.
                if ($skipped) {
                    $text = get_string('morenavigationlinks');
                    $url = new moodle_url('/course/admin.php', array('courseid' => $this->page->course->id));
                    $link = new action_link($url, $text, null, null, new pix_icon('t/edit', $text));
                    $menu->add_secondary_action($link);
                }
            }
        } else if ($showcoursemenu) {
            $settingsnode = $this->page->settingsnav->find('courseadmin', navigation_node::TYPE_COURSE);
            if ($settingsnode) {
                // Build an action menu based on the visible nodes from this navigation tree.
                $skipped = $this->build_action_menu_from_navigation($menu, $settingsnode, false, true);

                // We only add a list to the full settings menu if we didn't include every node in the short menu.
                if ($skipped) {
                    $text = get_string('morenavigationlinks');
                    $url = new moodle_url('/course/admin.php', array('courseid' => $this->page->course->id));
                    $link = new action_link($url, $text, null, null, new pix_icon('t/edit', $text));
                    $menu->add_secondary_action($link);
                }
            }
        } else if ($showusermenu) {
            // Get the course admin node from the settings navigation.
            $settingsnode = $this->page->settingsnav->find('useraccount', navigation_node::TYPE_CONTAINER);
            if ($settingsnode) {
                // Build an action menu based on the visible nodes from this navigation tree.
                $this->build_action_menu_from_navigation($menu, $settingsnode);
            }
        }

        return $this->render($menu);
    }

    /**
     * Override to use theme_boost_campus login template
     * Renders the login form.
     *
     * MODIFICATION: This renderer function is copied and modified from lib/outputrenderers.php
     *
     * @param \core_auth\output\login $form The renderable.
     * @return string
     */
    public function render_login(\core_auth\output\login $form) {
        global $CFG, $SITE;

        $context = $form->export_for_template($this);

        // Override because rendering is not supported in template yet.
        if ($CFG->rememberusername == 0) {
            $context->cookieshelpiconformatted = $this->help_icon('cookiesenabledonlysession');
        } else {
            $context->cookieshelpiconformatted = $this->help_icon('cookiesenabled');
        }
        $context->errorformatted = $this->error_text($context->error);
        $url = $this->get_logo_url();
        if ($url) {
            $url = $url->out(false);
        }
        $context->logourl = $url;
        $context->sitename = format_string($SITE->fullname, true,
                ['context' => context_course::instance(SITEID), "escape" => false]);
        // MODIFICATION START.
        // Only if setting "loginform" is checked, then call own login.mustache.
        if (get_config('theme_boost_campus', 'loginform') == 'yes') {
            return $this->render_from_template('theme_boost_campus/loginform', $context);
        } else {
            return $this->render_from_template('core/loginform', $context);
        }
        // MODIFICATION END.
        // @codingStandardsIgnoreStart
        /* ORIGINAL START.
        return $this->render_from_template('core/loginform', $context);
        ORIGINAL END. */
        // @codingStandardsIgnoreEnd
    }

    /*
     * New language_menu function ensures the lang menu is
     * always shown, in new position
     */
    public function language_menu() { // ecastro ULPGC
        global $CFG;

        $menu = new custom_menu('');
        
        $haslangmenu = $this->lang_menu() != '';
        if (!$haslangmenu) {
            return '';
        }

        $langs = get_string_manager()->get_list_of_translations();
        if ($haslangmenu) {
            $strlang = get_string('language');
            $currentlang = current_language();
            if (!isset($langs[$currentlang])) {
                $currentlang = $strlang;
            }
            
            $this->language = $menu->add($currentlang, new moodle_url('#'), $strlang, 10000);
            
            foreach ($langs as $langtype => $langname) {
                $this->language->add($langname, new moodle_url($this->page->url, array('lang' => $langtype)), $langname);
            }
        }

        $content = '';
        foreach ($menu->get_children() as $item) {
            $context = $item->export_for_template($this);
            if($context->url == '#') {
                $context->text = '<i class="fa fa-language fa-lg"> </i><span class="langdesc"> '.$context->text.' </span>';
            }
            
            $content .= $this->render_from_template('theme_boost_campus/menu_item', $context);
        }

        return $content;
    }

    
    /*
     * Substituting the custom_menu function to guarantee only top menu
     * always shown, even if no menu items are configured in the global
     * theme settings page.
     */
    public function custom_site_menu($custommenuitems = '') { // ecastro ULPGC
        global $CFG, $SITE;

        if (empty($custommenuitems) && !empty($CFG->custommenuitems)) {
            $custommenuitems = $CFG->custommenuitems;
        }
        $menu = new custom_menu($custommenuitems, current_language());
        
        $children = '';
        
        $children =  $menu->has_children() ?  $menu->get_children() : '';
        $item =  $children ? reset($children) : '';
        
        if (!$children || !$item || !$item->has_children()) {
            return $SITE->shortname;
        }
        
        $content = '';
        
        $item->set_text($SITE->shortname);
        $context = $item->export_for_template($this);
        $content .= $this->render_from_template('core/custom_menu_item', $context);
        
        return $content;
    }
   
   

    public function context_course_header($headerinfo = null, $headinglevel = 1) {
        global $CFG;
        if(($this->page->context->contextlevel == CONTEXT_COURSE) || 
            ($this->page->context->contextlevel == CONTEXT_MODULE)) {
            if($this->page->course->id > 1) {
                if(!isset($headerinfo)) {
                    $headerinfo = array();
                }
                $headerinfo['heading'] = $this->page->course->shortname.' - '.$this->page->course->fullname;
            }
        }
        
        return parent::context_header($headerinfo, $headinglevel);
    }
  
    
    public function region_main_settings_menu() {

        $this->page->navigation->initialise();
        $node = $this->page->navigation->find_active_node();
        
        if ($this->page->context->contextlevel == CONTEXT_COURSE) {
            if(empty($node) || $node->type == navigation_node::TYPE_CONTAINER) {
                return false;
            }
        } elseif($this->page->context->contextlevel == CONTEXT_MODULE) {
            if (!empty($node) && ($node->type == navigation_node::TYPE_ACTIVITY ||
                                    $node->type == navigation_node::TYPE_RESOURCE)) {
                $this->page->force_settings_menu(true);
            }
        }

        return parent::region_main_settings_menu();
   }
   
   public function logout() {
    global $CFG;
        return $CFG->wwwroot.'/login/logout.php?sesskey='.sesskey();
   }

    /**
     * Implementation of user image rendering.
     *
     * MODIFICATION: This renderer function is copied and modified from lib/outputrenderers.php
     *
     * @param help_icon $helpicon A help icon instance
     * @return string HTML fragment
     */
    protected function render_help_icon(help_icon $helpicon) {
        $context = $helpicon->export_for_template($this);
        // MODIFICATION START.
        // ID needed for modal dialog.
        $context->linkid = $helpicon->component.'-'.$helpicon->identifier;
        // Fill body variable needed for modal mustache with text value.
        $context->body = $context->text;
        if (get_config('theme_boost_campus', 'helptextmodal') == 'yes') {
            return $this->render_from_template('theme_boost_campus/help_icon', $context);
        } else {
            return $this->render_from_template('core/help_icon', $context);
        }
        // MODIFICATION END.
        // @codingStandardsIgnoreStart
        /* ORIGINAL START.
        $context = $helpicon->export_for_template($this);
        return $this->render_from_template('core/help_icon', $context);
        ORIGINAL END. */
        // @codingStandardsIgnoreEnd
    }
}
