<?php
// This file is part of Ranking block for Moodle - http://moodle.org/
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
 * Theme Moove block settings file
 *
 * @package    theme_moove
 * @copyright  2017 Willian Mano http://conecti.me
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// This line protects the file from being accessed by a URL directly.
defined('MOODLE_INTERNAL') || die();

// This is used for performance, we don't need to know about these settings on every page in Moodle, only when
// we are looking at the admin settings pages.
if ($ADMIN->fulltree) {

    require_once($CFG->dirroot . '/theme/moove/lib.php');
    // Due to MDL-58376, we will use binary select settings instead of checkbox settings throughout this theme.
    $yesnooption = array(THEME_MOOVE_SETTING_SELECT_YES => get_string('yes'),
                    THEME_MOOVE_SETTING_SELECT_NO => get_string('no'));


    // Boost provides a nice setting page which splits settings onto separate tabs. We want to use it here.
    $settings = new theme_boost_admin_settingspage_tabs('themesettingmoove', get_string('configtitle', 'theme_moove'));

    /*
    * ----------------------
    * General settings tab
    * ----------------------
    */
    $page = new admin_settingpage('theme_moove_general', get_string('generalsettings', 'theme_moove'));

    // Logo file setting.
    $name = 'theme_moove/logo';
    $title = get_string('logo', 'theme_moove');
    $description = get_string('logodesc', 'theme_moove');
    $opts = array('accepted_types' => array('.png', '.jpg', '.gif', '.webp', '.tiff', '.svg'), 'maxfiles' => 1);
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'logo', 0, $opts);
    $page->add($setting);

    // Favicon setting.
    $name = 'theme_moove/favicon';
    $title = get_string('favicon', 'theme_moove');
    $description = get_string('favicondesc', 'theme_moove');
    $opts = array('accepted_types' => array('.ico'), 'maxfiles' => 1);
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'favicon', 0, $opts);
    $page->add($setting);

    // Preset.
    $name = 'theme_moove/preset';
    $title = get_string('preset', 'theme_moove');
    $description = get_string('preset_desc', 'theme_moove');
    $default = 'default.scss';

    $context = context_system::instance();
    $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, 'theme_moove', 'preset', 0, 'itemid, filepath, filename', false);

    $choices = [];
    foreach ($files as $file) {
        $choices[$file->get_filename()] = $file->get_filename();
    }
    // These are the built in presets.
    $choices['default.scss'] = 'default.scss';
    $choices['plain.scss'] = 'plain.scss';

    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Preset files setting.
    $name = 'theme_moove/presetfiles';
    $title = get_string('presetfiles', 'theme_moove');
    $description = get_string('presetfiles_desc', 'theme_moove');

    $setting = new admin_setting_configstoredfile($name, $title, $description, 'preset', 0,
        array('maxfiles' => 10, 'accepted_types' => array('.scss')));
    $page->add($setting);

    // Login page background image.
    $name = 'theme_moove/loginbgimg';
    $title = get_string('loginbgimg', 'theme_moove');
    $description = get_string('loginbgimg_desc', 'theme_moove');
    $opts = array('accepted_types' => array('.png', '.jpg', '.svg'));
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'loginbgimg', 0, $opts);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Variable $brand-color.
    // We use an empty default value because the default colour should come from the preset.
    $name = 'theme_moove/brandcolor';
    $title = get_string('brandcolor', 'theme_moove');
    $description = get_string('brandcolor_desc', 'theme_moove');
    $setting = new admin_setting_configcolourpicker($name, $title, $description, '#0f47ad');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Variable $navbar-header-color.
    // We use an empty default value because the default colour should come from the preset.
    $name = 'theme_moove/secondarymenucolor';
    $title = get_string('secondarymenucolor', 'theme_moove');
    $description = get_string('secondarymenucolor_desc', 'theme_moove');
    $setting = new admin_setting_configcolourpicker($name, $title, $description, '#0f47ad');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $fontsarr = [
        'Roboto' => 'Roboto',
        'Poppins' => 'Poppins',
        'Montserrat' => 'Montserrat',
        'Open Sans' => 'Open Sans',
        'Lato' => 'Lato',
        'Raleway' => 'Raleway',
        'Inter' => 'Inter',
        'Nunito' => 'Nunito',
        'Encode Sans' => 'Encode Sans',
        'Work Sans' => 'Work Sans',
        'Oxygen' => 'Oxygen',
        'Manrope' => 'Manrope',
        'Sora' => 'Sora',
        'Epilogue' => 'Epilogue'
    ];

    $name = 'theme_moove/fontsite';
    $title = get_string('fontsite', 'theme_moove');
    $description = get_string('fontsite_desc', 'theme_moove');
    $setting = new admin_setting_configselect($name, $title, $description, 'Roboto', $fontsarr);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $name = 'theme_moove/enablecourseindex';
    $title = get_string('enablecourseindex', 'theme_moove');
    $description = get_string('enablecourseindex_desc', 'theme_moove');
    $default = 1;
    $choices = array(0 => get_string('no'), 1 => get_string('yes'));
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $page->add($setting);

    // Must add the page after definiting all the settings!
    $settings->add($page);

    /*
    * ----------------------
    * Advanced settings tab
    * ----------------------
    */
    $page = new admin_settingpage('theme_moove_advanced', get_string('advancedsettings', 'theme_moove'));

    // Raw SCSS to include before the content.
    $setting = new admin_setting_scsscode('theme_moove/scsspre',
        get_string('rawscsspre', 'theme_moove'), get_string('rawscsspre_desc', 'theme_moove'), '', PARAM_RAW);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Raw SCSS to include after the content.
    $setting = new admin_setting_scsscode('theme_moove/scss', get_string('rawscss', 'theme_moove'),
        get_string('rawscss_desc', 'theme_moove'), '', PARAM_RAW);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Google analytics block.
    $name = 'theme_moove/googleanalytics';
    $title = get_string('googleanalytics', 'theme_moove');
    $description = get_string('googleanalyticsdesc', 'theme_moove');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $settings->add($page);

    /*
    * -----------------------
    * Frontpage settings tab
    * -----------------------
    */
    $page = new admin_settingpage('theme_moove_frontpage', get_string('frontpagesettings', 'theme_moove'));

    // Disable teachers from cards.
    $name = 'theme_moove/disableteacherspic';
    $title = get_string('disableteacherspic', 'theme_moove');
    $description = get_string('disableteacherspicdesc', 'theme_moove');
    $default = 1;
    $choices = array(0 => get_string('no'), 1 => get_string('yes'));
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $page->add($setting);

    // Slideshow.
    $name = 'theme_moove/slidercount';
    $title = get_string('slidercount', 'theme_moove');
    $description = get_string('slidercountdesc', 'theme_moove');
    $default = 0;
    $options = array();
    for ($i = 0; $i < 13; $i++) {
        $options[$i] = $i;
    }
    $setting = new admin_setting_configselect($name, $title, $description, $default, $options);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // If we don't have an slide yet, default to the preset.
    $slidercount = get_config('theme_moove', 'slidercount');

    if (!$slidercount) {
        $slidercount = $default;
    }

    if ($slidercount) {
        for ($sliderindex = 1; $sliderindex <= $slidercount; $sliderindex++) {
            $fileid = 'sliderimage' . $sliderindex;
            $name = 'theme_moove/sliderimage' . $sliderindex;
            $title = get_string('sliderimage', 'theme_moove');
            $description = get_string('sliderimagedesc', 'theme_moove');
            $opts = array('accepted_types' => array('.png', '.jpg', '.gif', '.webp', '.tiff', '.svg'), 'maxfiles' => 1);
            $setting = new admin_setting_configstoredfile($name, $title, $description, $fileid, 0, $opts);
            $page->add($setting);

            $name = 'theme_moove/slidertitle' . $sliderindex;
            $title = get_string('slidertitle', 'theme_moove');
            $description = get_string('slidertitledesc', 'theme_moove');
            $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_TEXT);
            $page->add($setting);

            $name = 'theme_moove/slidercap' . $sliderindex;
            $title = get_string('slidercaption', 'theme_moove');
            $description = get_string('slidercaptiondesc', 'theme_moove');
            $default = '';
            $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
            $page->add($setting);
        }
    }

    $setting = new admin_setting_heading('slidercountseparator', '', '<hr>');
    $page->add($setting);

    $name = 'theme_moove/displaymarketingbox';
    $title = get_string('displaymarketingboxes', 'theme_moove');
    $description = get_string('displaymarketingboxesdesc', 'theme_moove');
    $default = 1;
    $choices = array(0 => get_string('no'), 1 => get_string('yes'));
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $page->add($setting);

    $displaymarketingbox = get_config('theme_moove', 'displaymarketingbox');

    if ($displaymarketingbox) {
        // Marketingheading.
        $name = 'theme_moove/marketingheading';
        $title = get_string('marketingsectionheading', 'theme_moove');
        $default = 'Awesome App Features';
        $setting = new admin_setting_configtext($name, $title, '', $default);
        $page->add($setting);

        // Marketingcontent.
        $name = 'theme_moove/marketingcontent';
        $title = get_string('marketingsectioncontent', 'theme_moove');
        $default = 'Moove is a Moodle template based on Boost with modern and creative design.';
        $setting = new admin_setting_confightmleditor($name, $title, '', $default);
        $page->add($setting);

        for ($i = 1; $i < 5; $i++) {
            $filearea = "marketing{$i}icon";
            $name = "theme_moove/$filearea";
            $title = get_string('marketingicon', 'theme_moove', $i . '');
            $opts = array('accepted_types' => array('.png', '.jpg', '.gif', '.webp', '.tiff', '.svg'));
            $setting = new admin_setting_configstoredfile($name, $title, '', $filearea, 0, $opts);
            $page->add($setting);

            $name = "theme_moove/marketing{$i}heading";
            $title = get_string('marketingheading', 'theme_moove', $i . '');
            $default = 'Lorem';
            $setting = new admin_setting_configtext($name, $title, '', $default);
            $page->add($setting);

            $name = "theme_moove/marketing{$i}content";
            $title = get_string('marketingcontent', 'theme_moove', $i . '');
            $default = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod.';
            $setting = new admin_setting_confightmleditor($name, $title, '', $default);
            $page->add($setting);
        }

        $setting = new admin_setting_heading('displaymarketingboxseparator', '', '<hr>');
        $page->add($setting);
    }

    // Enable or disable Numbers sections settings.
    $name = 'theme_moove/numbersfrontpage';
    $title = get_string('numbersfrontpage', 'theme_moove');
    $description = get_string('numbersfrontpagedesc', 'theme_moove');
    $default = 1;
    $choices = array(0 => get_string('no'), 1 => get_string('yes'));
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $page->add($setting);

    $numbersfrontpage = get_config('theme_moove', 'numbersfrontpage');

    if ($numbersfrontpage) {
        $name = 'theme_moove/numbersfrontpagecontent';
        $title = get_string('numbersfrontpagecontent', 'theme_moove');
        $description = get_string('numbersfrontpagecontentdesc', 'theme_moove');
        $default = get_string('numbersfrontpagecontentdefault', 'theme_moove');
        $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
        $page->add($setting);
    }

    // Enable FAQ.
    $name = 'theme_moove/faqcount';
    $title = get_string('faqcount', 'theme_moove');
    $description = get_string('faqcountdesc', 'theme_moove');
    $default = 0;
    $options = array();
    for ($i = 0; $i < 11; $i++) {
        $options[$i] = $i;
    }
    $setting = new admin_setting_configselect($name, $title, $description, $default, $options);
    $page->add($setting);

    $faqcount = get_config('theme_moove', 'faqcount');

    if ($faqcount > 0) {
        for ($i = 1; $i <= $faqcount; $i++) {
            $name = "theme_moove/faqquestion{$i}";
            $title = get_string('faqquestion', 'theme_moove', $i . '');
            $setting = new admin_setting_configtext($name, $title, '', '');
            $page->add($setting);

            $name = "theme_moove/faqanswer{$i}";
            $title = get_string('faqanswer', 'theme_moove', $i . '');
            $setting = new admin_setting_confightmleditor($name, $title, '', '');
            $page->add($setting);
        }

        $setting = new admin_setting_heading('faqseparator', '', '<hr>');
        $page->add($setting);
    }

    $settings->add($page);

    /*
    * --------------------
    * Footer settings tab
    * --------------------
    */
    $page = new admin_settingpage('theme_moove_footer', get_string('footersettings', 'theme_moove'));

    // Setting to display the course settings page as a panel within the course.
    $name = 'theme_moove/ulpgcfooter';
    $title = get_string('showulpgcfooter', 'theme_moove');
    $description = get_string('showulpgcfooter_desc', 'theme_moove');
    $setting = new admin_setting_configcheckbox($name, $title, $description,
                                                    THEME_MOOVE_SETTING_SELECT_NO,
                                                    THEME_MOOVE_SETTING_SELECT_YES,
                                                    THEME_MOOVE_SETTING_SELECT_NO); // Overriding default values

    $page->add($setting);

    foreach(array(1,2,3) as $i) {
        $page->add(new admin_setting_confightmleditor('theme_moove/footerblock'.$i,
                            get_string('footerblock'.$i, 'theme_moove'),
                            get_string('footerblock_desc', 'theme_moove'),
                            null,
                            PARAM_RAW));
    }

    // moove standard elements
    $name = 'theme_moove/footerheading';
    $title = get_string('footerheading', 'theme_moove');
    $description = get_string('footernotused_desc', 'theme_moove');
    $setting = new admin_setting_heading($name, $title, $description);
    $page->add($setting);

    // Website.
    $name = 'theme_moove/website';
    $title = get_string('website', 'theme_moove');
    $description = get_string('websitedesc', 'theme_moove');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $page->add($setting);

    // Mobile.
    $name = 'theme_moove/mobile';
    $title = get_string('mobile', 'theme_moove');
    $description = get_string('mobiledesc', 'theme_moove');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $page->add($setting);

    // Mail.
    $name = 'theme_moove/mail';
    $title = get_string('mail', 'theme_moove');
    $description = get_string('maildesc', 'theme_moove');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $page->add($setting);

    // Facebook url setting.
    $name = 'theme_moove/facebook';
    $title = get_string('facebook', 'theme_moove');
    $description = get_string('facebookdesc', 'theme_moove');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $page->add($setting);

    // Twitter url setting.
    $name = 'theme_moove/twitter';
    $title = get_string('twitter', 'theme_moove');
    $description = get_string('twitterdesc', 'theme_moove');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $page->add($setting);

    // Linkdin url setting.
    $name = 'theme_moove/linkedin';
    $title = get_string('linkedin', 'theme_moove');
    $description = get_string('linkedindesc', 'theme_moove');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $page->add($setting);

    // Youtube url setting.
    $name = 'theme_moove/youtube';
    $title = get_string('youtube', 'theme_moove');
    $description = get_string('youtubedesc', 'theme_moove');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $page->add($setting);

    // Instagram url setting.
    $name = 'theme_moove/instagram';
    $title = get_string('instagram', 'theme_moove');
    $description = get_string('instagramdesc', 'theme_moove');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $page->add($setting);

    // Whatsapp url setting.
    $name = 'theme_moove/whatsapp';
    $title = get_string('whatsapp', 'theme_moove');
    $description = get_string('whatsappdesc', 'theme_moove');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $page->add($setting);

    // Telegram url setting.
    $name = 'theme_moove/telegram';
    $title = get_string('telegram', 'theme_moove');
    $description = get_string('telegramdesc', 'theme_moove');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $page->add($setting);

    $settings->add($page);

    /*
    * --------------------
    * ULPGC 1 settings tab
    * --------------------
    */
    $page = new admin_settingpage('theme_moove_ulpgc1', get_string('ulpgcsitesettings', 'theme_moove'));


        // Create primary navigation heading.
        $name = 'theme_moove/primarynavigationheading';
        $title = get_string('primarynavigationheading', 'theme_moove', null, true);
        $setting = new admin_setting_heading($name, $title, null);
        $page->add($setting);

        // Prepare hide nodes options.
        $hidenodesoptions = array(
                THEME_MOOVE_SETTING_HIDENODESPRIMARYNAVIGATION_HOME => get_string('home'),
                THEME_MOOVE_SETTING_HIDENODESPRIMARYNAVIGATION_MYHOME => get_string('myhome'),
                THEME_MOOVE_SETTING_HIDENODESPRIMARYNAVIGATION_MYCOURSES => get_string('mycourses'),
                THEME_MOOVE_SETTING_HIDENODESPRIMARYNAVIGATION_SITEADMIN => get_string('administrationsite')
        );

        // Setting: Hide nodes in primary navigation.
        $name = 'theme_moove/hidenodesprimarynavigation';
        $title = get_string('hidenodesprimarynavigationsetting', 'theme_moove', null, true);
        $description = get_string('hidenodesprimarynavigationsetting_desc', 'theme_moove', null, true);
        $setting = new admin_setting_configmulticheckbox($name, $title, $description, array(), $hidenodesoptions);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);

        // Create navigation heading.
        $name = 'theme_moove/navigationheading';
        $title = get_string('navigationheading', 'theme_moove', null, true);
        $setting = new admin_setting_heading($name, $title, null);
        $page->add($setting);

        // Setting: back to top button.
        $name = 'theme_moove/backtotopbutton';
        $title = get_string('backtotopbuttonsetting', 'theme_moove', null, true);
        $description = get_string('backtotopbuttonsetting_desc', 'theme_moove', null, true);
        $setting = new admin_setting_configselect($name, $title, $description, THEME_MOOVE_SETTING_SELECT_NO, $yesnooption);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);

        // Setting: scroll-spy.
        $name = 'theme_moove/scrollspy';
        $title = get_string('scrollspysetting', 'theme_moove', null, true);
        $description = get_string('scrollspysetting_desc', 'theme_moove', null, true);
        $setting = new admin_setting_configselect($name, $title, $description, THEME_MOOVE_SETTING_SELECT_NO, $yesnooption);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);

        // Setting: Activity navigation.
        $name = 'theme_moove/activitynavigation';
        $title = get_string('activitynavigationsetting', 'theme_moove', null, true);
        $description = get_string('activitynavigationsetting_desc', 'theme_moove', null, true);
        $setting = new admin_setting_configselect($name, $title, $description, THEME_MOOVE_SETTING_SELECT_NO, $yesnooption);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);



    // Settings title for grouping course settings related aspects together. We don't need a description here.
    $name = 'theme_moove/sitesettingsheading';
    $title = get_string('sitesettingsheading', 'theme_moove', null, true);
    $setting = new admin_setting_heading($name, $title, null);
    $page->add($setting);


    // icons for primary navbar
    $name = 'theme_moove/iconhome';
    $title = get_string('iconhome', 'theme_moove');
    $description = get_string('iconhome_desc', 'theme_moove');
    $setting = new admin_setting_configtext($name, $title, $description, 'fa-home');
    $page->add($setting);

    $name = 'theme_moove/iconmyhome';
    $title = get_string('iconmyhome', 'theme_moove');
    $description = get_string('iconmyhome_desc', 'theme_moove');
    $setting = new admin_setting_configtext($name, $title, $description, 'fa-dashboard');
    $page->add($setting);

    $name = 'theme_moove/iconmycourses';
    $title = get_string('iconmycourses', 'theme_moove');
    $description = get_string('iconmycourses_desc', 'theme_moove');
    $setting = new admin_setting_configtext($name, $title, $description, 'fa-cubes');
    $page->add($setting);

    $name = 'theme_moove/iconsiteadminnode';
    $title = get_string('iconsiteadminnode', 'theme_moove');
    $description = get_string('iconsiteadminnode_desc', 'theme_moove');
    $setting = new admin_setting_configtext($name, $title, $description, 'fa-cogs');
    $page->add($setting);

    // Settings title for grouping course settings related aspects together. We don't need a description here.
    $name = 'theme_moove/blocksheading';
    $title = get_string('blocksheading', 'theme_moove');
    $setting = new admin_setting_heading($name, $title, null);
    $page->add($setting);

    // Setting for displaying a standard Font Awesome icon in front of the block title.
    $name = 'theme_moove/blockicon';
    $title = get_string('blockiconsetting', 'theme_moove');
    $description = get_string('blockiconsetting_desc', 'theme_moove');
    $setting = new admin_setting_configcheckbox($name, $title, $description,
                                                    THEME_MOOVE_SETTING_SELECT_NO,
                                                    THEME_MOOVE_SETTING_SELECT_YES,
                                                    THEME_MOOVE_SETTING_SELECT_NO);
    // Overriding default values
        // yes = 1 and no = 0 because of the use of empty() in theme_moove_get_pre_scss() (lib.php). Default 0 value would
        // not write the variable to scss that could cause the scss to crash if used in that file. See MDL-58376.
        $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Setting for the width of the block column on the Dashboard.
    $name = 'theme_moove/blockwidthdashboard';
    $title = get_string('blockwidthdashboardsetting', 'theme_moove');
    $description = get_string('blockwidthdashboardsetting_desc', 'theme_moove');
    $setting = new admin_setting_configtext_with_maxlength($name, $title, $description, 360, PARAM_INT, null, 3);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);


    // Create JavaScript heading.
    $name = 'theme_moove/javascriptheading';
    $title = get_string('javascriptheading', 'theme_moove', null, true);
    $setting = new admin_setting_heading($name, $title, null);
    $page->add($setting);

    // Setting: JavaScript disabled hint.
    $name = 'theme_moove/javascriptdisabledhint';
    $title = get_string('javascriptdisabledhint', 'theme_moove', null, true);
    $description = get_string('javascriptdisabledhint_desc', 'theme_moove', null, true);
    $setting = new admin_setting_configselect($name, $title, $description, THEME_MOOVE_SETTING_SELECT_NO, $yesnooption);
    $page->add($setting);


    $settings->add($page);

    /*
    * --------------------
    * ULPGC 2 settings tab
    * --------------------
    */
    $page = new admin_settingpage('theme_moove_ulpgc2', get_string('ulpgccoursesettings', 'theme_moove'));

    // Settings title for grouping course settings related aspects together. We don't need a description here.
    $name = 'theme_moove/coursesettingsheading';
    $title = get_string('coursesettingsheading', 'theme_moove', null, true);
    $setting = new admin_setting_heading($name, $title, null);
    $page->add($setting);



    // Setting to display the course settings page as a panel within the course.
    $name = 'theme_moove/showsettingsincourse';
    $title = get_string('showsettingsincourse', 'theme_moove', null, true);
    $description = get_string('showsettingsincourse_desc', 'theme_moove', null, true);
    $setting = new admin_setting_configcheckbox($name, $title, $description,
                                                    THEME_MOOVE_SETTING_SELECT_NO,
                                                    THEME_MOOVE_SETTING_SELECT_YES,
                                                    THEME_MOOVE_SETTING_SELECT_NO);
    // Overriding default values
    // yes = 1 and no = 0 because of the use of empty() in theme_moove_get_pre_scss() (lib.php).
    // Default 0 value would not write the variable to scss that could cause the scss to crash if used in that file.
    // See MDL-58376.
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Setting to display the course settings page as a panel within the course.
    $name = 'theme_moove/showmenuitemicons';
    $title = get_string('showmenuitemicons', 'theme_moove', null, true);
    $description = get_string('showmenuitemicons_desc', 'theme_moove', null, true);
    $setting = new admin_setting_configcheckbox($name, $title, $description,
                                                    THEME_MOOVE_SETTING_SELECT_NO,
                                                    THEME_MOOVE_SETTING_SELECT_YES,
                                                    THEME_MOOVE_SETTING_SELECT_NO);
    // Overriding default values
    $page->add($setting);

    $name = 'theme_moove/coursesettingsicon';
    $title = get_string('coursesettingsicon', 'theme_moove');
    $description = get_string('coursesettingsicon_desc', 'theme_moove');
    $setting = new admin_setting_configtext($name, $title, $description, 'fa-cog');
    $page->add($setting);

    $name = 'theme_moove/activitysettingsicon';
    $title = get_string('activitysettingsicon', 'theme_moove');
    $description = get_string('activitysettingsicon_desc', 'theme_moove');
    $setting = new admin_setting_configtext($name, $title, $description, 'fa-wrench');
    $page->add($setting);



    // Setting to display the switch role to link as a separate tab within the in-course settings panel.
    $name = 'theme_moove/switchtoroleposition';
    $title = get_string('switchtorolepositionsetting', 'theme_moove', null, true);
    $description = get_string('switchtorolepositionsetting_desc', 'theme_moove', null, true);
    $switchtorolesetting = [
     // Don't use string lazy loading (= false) because the string will be directly used and would produce a PHP warning otherwise.
    'no' => get_string('switchtorolesettingjustmenu', 'theme_moove', null, false),
    'yes' => get_string('switchtorolesettingjustcourse', 'theme_moove', null, true),
    'both' => get_string('switchtorolesettingboth', 'theme_moove', null, true)
    ];
    $setting = new admin_setting_configselect($name, $title, $description, $switchtorolesetting['no'],
        $switchtorolesetting);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);
    $settings->hide_if('theme_moove/switchtoroleposition',
            'theme_moove/showsettingsincourse', 'notchecked');

    // Create course related hints heading.
    $name = 'theme_moove/courserelatedhintsheading';
    $title = get_string('courserelatedhintsheading', 'theme_moove', null, true);
    $setting = new admin_setting_heading($name, $title, null);
    $page->add($setting);


    // Setting: Show hint for switched role.
    $name = 'theme_moove/showswitchedroleincourse';
    $title = get_string('showswitchedroleincoursesetting', 'theme_moove', null, true);
    $description = get_string('showswitchedroleincoursesetting_desc', 'theme_moove', null, true);
    $setting = new admin_setting_configselect($name, $title, $description, THEME_MOOVE_SETTING_SELECT_NO, $yesnooption);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Setting: Show hint in hidden courses.
    $name = 'theme_moove/showhintcoursehidden';
    $title = get_string('showhintcoursehiddensetting', 'theme_moove', null, true);
    $description = get_string('showhintcoursehiddensetting_desc', 'theme_moove', null, true);
    $setting = new admin_setting_configselect($name, $title, $description, THEME_MOOVE_SETTING_SELECT_NO, $yesnooption);
    $page->add($setting);

    // Setting: Show hint guest for access.
    $name = 'theme_moove/showhintcourseguestaccess';
    $title = get_string('showhintcoursguestaccesssetting', 'theme_moove', null, true);
    $description = get_string('showhintcourseguestaccesssetting_desc', 'theme_moove', null, true);
    $setting = new admin_setting_configselect($name, $title, $description, THEME_MOOVE_SETTING_SELECT_NO, $yesnooption);
    $page->add($setting);

    // Setting: Show hint for self enrolment without enrolment key.
    $name = 'theme_moove/showhintcourseselfenrol';
    $title = get_string('showhintcourseselfenrolsetting', 'theme_moove', null, true);
    $description = get_string('showhintcourseselfenrolsetting_desc', 'theme_moove', null, true);
    $setting = new admin_setting_configselect($name, $title, $description, THEME_MOOVE_SETTING_SELECT_NO, $yesnooption);
    $page->add($setting);

    // Telegram url setting.
    $name = 'theme_moove/telegram2';
    $title = get_string('telegram', 'theme_moove');
    $description = get_string('telegramdesc', 'theme_moove');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $page->add($setting);

    $settings->add($page);

    /*
    * --------------------
    * ULPGC 3 settings tab
    * --------------------
    */
    $page = new admin_settingpage('theme_moove_ulpgc3', get_string('ulpgcothersettings', 'theme_moove'));

    // The static pages to be supported.
    $staticpages = array('imprint', 'contact', 'help', 'maintenance');

    // Iterate over the pages.
    foreach ($staticpages as $staticpage) {

        // Create page heading.
        $name = 'theme_moove/'.$staticpage.'heading';
        $title = get_string($staticpage.'heading', 'theme_moove', null, true);
        $setting = new admin_setting_heading($name, $title, null);
        $page->add($setting);

        // Setting: Enable page.
        $name = 'theme_moove/enable'.$staticpage;
        $title = get_string('enable'.$staticpage.'setting', 'theme_moove', null, true);
        $description = '';
        $setting = new admin_setting_configselect($name, $title, $description, THEME_MOOVE_SETTING_SELECT_NO,
                $yesnooption);
        $page->add($setting);

        // Setting: Page title.
        $name = 'theme_moove/'.$staticpage.'pagetitle';
        $title = get_string($staticpage.'pagetitlesetting', 'theme_moove', null, true);
        $description = get_string($staticpage.'pagetitlesetting_desc', 'theme_moove', null, true);
        $default = get_string($staticpage.'pagetitledefault', 'theme_moove', null, true);
        $setting = new admin_setting_configtext($name, $title, $description, $default);
        $page->add($setting);
        $page->hide_if('theme_moove/'.$staticpage.'pagetitle', 'theme_moove/enable'.$staticpage, 'neq',
                THEME_MOOVE_SETTING_SELECT_YES);

        // Setting: Page content.
        $name = 'theme_moove/'.$staticpage.'content';
        $title = get_string($staticpage.'contentsetting', 'theme_moove', null, true);
        $description = get_string($staticpage.'contentsetting_desc', 'theme_moove', null, true);
        $setting = new admin_setting_confightmleditor($name, $title, $description, '');
        $page->add($setting);
        $page->hide_if('theme_moove/'.$staticpage.'content', 'theme_moove/enable'.$staticpage, 'neq',
                THEME_MOOVE_SETTING_SELECT_YES);

        // postion is forced to footer popover, NOT footnote. //ecastro ULPGC
    }



    // Telegram url setting.
    $name = 'theme_moove/telegram3';
    $title = get_string('telegram', 'theme_moove');
    $description = get_string('telegramdesc', 'theme_moove');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $page->add($setting);

    $settings->add($page);

}
