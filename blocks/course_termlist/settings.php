<?php

defined('MOODLE_INTERNAL') || die;


// Empty $settings to prevent a single settings page from being created by lib/classes/plugininfo/block.php
// because we will create several settings pages now.
$settings = null;

// Create admin settings category.
$ADMIN->add('blocksettings', new admin_category('block_course_termlist',
                get_string('pluginname', 'block_course_termlist', null, true)));

                
// Settings page: General.
$settingspage = new admin_settingpage('block_course_termlist_general',
        get_string('settingspage_general', 'block_course_termlist', null, true));            

    $settingspage->add(new admin_setting_configtext('block_course_termlist/blocktitle',
                            get_string('blocktitle', 'block_course_termlist', null, true),
                            get_string('blocktitle_desc', 'block_course_termlist', null, true),
                            get_string('blocktitledefault', 'block_course_termlist', null, true),
                            PARAM_TEXT));
        
    $settingspage->add(new admin_setting_configtext('block_course_termlist/excluded', 
                            get_string('excluded', 'block_course_termlist'),
                            get_string('configexcluded', 'block_course_termlist'), ''));

    $settingspage->add(new admin_setting_configcheckbox('block_course_termlist/useallowedcats', 
                            get_string('useallowedcats', 'block_course_termlist'),
                            get_string('configuseallowedcats', 'block_course_termlist'), 0));

    $settingspage->add(new admin_setting_configcheckbox('block_course_termlist/onlyactiveenrol', 
                            get_string('onlyactiveenrol', 'block_course_termlist'),
                            get_string('configonlyactiveenrol', 'block_course_termlist'), 0));
                       
                       
    $settingspage->add(new admin_setting_configcheckbox('block_course_termlist/showcategorieslink', 
                            get_string('showcategorieslink', 'block_course_termlist'),
                            get_string('configshowcategorieslink', 'block_course_termlist'), 0));
    $settingspage->add(new admin_setting_configcheckbox('block_course_termlist/showdepartmentslink', 
                            get_string('showdepartmentslink', 'block_course_termlist'),
                            get_string('configshowdepartmentslink', 'block_course_termlist'), 0));
    $settingspage->add(new admin_setting_configcheckbox('block_course_termlist/hideallcourseslink', 
                            get_string('hideallcourseslink', 'block_course_termlist'),
                            get_string('confighideallcourseslink', 'block_course_termlist'), 0));
    // Add settings page to the admin settings category.
    $ADMIN->add('block_course_termlist', $settingspage);
    
// Settings page: List Format.
$settingspage = new admin_settingpage('block_course_termlist_viewlist',
        get_string('settingspage_viewlist', 'block_course_termlist', null, true));      

        $settingspage->add(new admin_setting_configcheckbox('block_course_termlist/enablehidecourses',
                get_string('enablehidecourses', 'block_course_termlist', null, true),
                get_string('enablehidecourses_desc', 'block_course_termlist', null, true),
                1));
        
    $settingspage->add(new admin_setting_configcheckbox('block_course_termlist/showshortname',
            get_string('showshortname', 'block_course_termlist', null, true),
            get_string('showshortname_desc', 'block_course_termlist', null, true),
            0));

    $settingspage->add(new admin_setting_configcheckbox('block_course_termlist/showrecentactivity',
            get_string('showrecentactivity', 'block_course_termlist', null, true),
            get_string('showrecentactivity_desc', 'block_course_termlist', null, true),
            0));

            
        $settingspage->add(new admin_setting_heading('block_course_termlist/teachersheading',
                get_string('teachersheading', 'block_course_termlist', null, true),
                ''));
        
        $settingspage->add(new admin_setting_configcheckbox('block_course_termlist/showteachername',
                get_string('showteachername', 'block_course_termlist', null, true),
                get_string('showteachername_desc', 'block_course_termlist', null, true),
                0));
                
        // Possible teacher name styles.
        $teachernamestylemodes[1] = get_string('teachernamestylefullname', 'block_course_termlist', null, true);
        $teachernamestylemodes[2] = get_string('teachernamestylelastname', 'block_course_termlist', null, false); // Don't use string lazy loading here because the string will be directly used and would produce a PHP warning otherwise.
        $teachernamestylemodes[3] = get_string('teachernamestylefirstname', 'block_course_termlist', null, true);
        $teachernamestylemodes[4] = get_string('teachernamestylefullnamedisplay', 'block_course_termlist', get_config('core', 'fullnamedisplay'), true);

        $settingspage->add(new admin_setting_configselect('block_course_termlist/showteachernamestyle',
                get_string('showteachernamestyle', 'block_course_termlist', null, true),
                get_string('showteachernamestyle_desc', 'block_course_termlist', null, true),
                $teachernamestylemodes[2],
                $teachernamestylemodes));

        $settingspage->add(new admin_setting_pickroles('block_course_termlist/teacherroles',
                get_string('teacherroles', 'block_course_termlist', null, true),
                get_string('teacherroles_desc', 'block_course_termlist', null, true),
                array('editingteacher')));
                
        $settingspage->add(new admin_setting_configcheckbox('block_course_termlist/hideonphones',
                get_string('hideonphones', 'block_course_termlist', null, true),
                get_string('hideonphones_desc', 'block_course_termlist', null, true),
                0));
        
    // Add settings page to the admin settings category.
    $ADMIN->add('block_course_termlist', $settingspage);

// Settings page: Category filters.
$settingspage = new admin_settingpage('block_course_termlist_categoryfilter',
        get_string('settingspage_categoryfilter', 'block_course_termlist', null, true));      

        // Parent category filter: Activation.
        $settingspage->add(new admin_setting_heading('block_course_termlist/categorycoursefiltersettingheading',
                get_string('categorycoursefiltersettingheading', 'block_course_termlist', null, true),
                ''));

        $settingspage->add(new admin_setting_configcheckbox('block_course_termlist/categorycoursefilter',
                get_string('categorycoursefilter', 'block_course_termlist', null, true),
                get_string('categorycoursefilter_desc', 'block_course_termlist', null, true),
                0));

        $settingspage->add(new admin_setting_configtext('block_course_termlist/categorycoursefilterdisplayname',
                get_string('categorycoursefilterdisplayname', 'block_course_termlist', null, true),
                get_string('categorycoursefilterdisplayname_desc', 'block_course_termlist', null, true),
                get_string('category', 'block_course_termlist', null, true),
                PARAM_TEXT));
        
        // Top level category filter: Activation.
        $settingspage->add(new admin_setting_heading('block_course_termlist/toplevelcategorycoursefiltersettingheading',
                get_string('toplevelcategorycoursefiltersettingheading', 'block_course_termlist', null, true),
                ''));

        $settingspage->add(new admin_setting_configcheckbox('block_course_termlist/toplevelcategorycoursefilter',
                get_string('toplevelcategorycoursefilter', 'block_course_termlist', null, true),
                get_string('toplevelcategorycoursefilter_desc', 'block_course_termlist', null, true),
                0));

        $settingspage->add(new admin_setting_configtext('block_course_termlist/toplevelcategorycoursefilterdisplayname',
                get_string('toplevelcategorycoursefilterdisplayname', 'block_course_termlist', null, true),
                get_string('toplevelcategorycoursefilterdisplayname_desc', 'block_course_termlist', null, true),
                get_string('toplevelcategory', 'block_course_termlist', null, true),
                PARAM_TEXT));
        
        
    // Add settings page to the admin settings category.
    $ADMIN->add('block_course_termlist', $settingspage);
    

// Settings page: t¡Term filter.
$settingspage = new admin_settingpage('block_course_termlist_termfilter',
        get_string('settingspage_termfilter', 'block_course_termlist', null, true));      

        // Term filter: Activation.
        $settingspage->add(new admin_setting_heading('block_course_termlist/termcoursefiltersettingheading',
                get_string('termcoursefiltersettingheading', 'block_course_termlist', null, true),
                ''));

        $settingspage->add(new admin_setting_configcheckbox('block_course_termlist/termcoursefilter',
                get_string('termcoursefilter', 'block_course_termlist', null, true),
                get_string('termcoursefilter_desc', 'block_course_termlist', null, true),
                0));

        $settingspage->add(new admin_setting_configtext('block_course_termlist/termcoursefilterdisplayname',
                get_string('termcoursefilterdisplayname', 'block_course_termlist', null, true),
                get_string('termcoursefilterdisplayname_desc', 'block_course_termlist', null, true),
                get_string('term', 'block_course_termlist', null, true),
                PARAM_TEXT));
        
        // Term filter: Term names.
        $settingspage->add(new admin_setting_heading('block_course_termlist/termnamesettingheading',
                get_string('termnamesettingheading', 'block_course_termlist', null, true),
                ''));

        $settingspage->add(new admin_setting_configtext('block_course_termlist/term1name',
                get_string('term1name', 'block_course_termlist', null, true),
                get_string('term1name_desc', 'block_course_termlist', null, true),
                get_string('term1', 'block_course_termlist'),
                PARAM_CLEANHTML));

        $settingspage->add(new admin_setting_configtext('block_course_termlist/term2name',
                get_string('term2name', 'block_course_termlist', null, true),
                get_string('term2name_desc', 'block_course_termlist', null, true),
                get_string('term2', 'block_course_termlist'),
                PARAM_TEXT));

        $settingspage->add(new admin_setting_configtext('block_course_termlist/term3name',
                get_string('term3name', 'block_course_termlist', null, true),
                get_string('term3name_desc', 'block_course_termlist', null, true),
                get_string('term3', 'block_course_termlist'),
                PARAM_TEXT));

        $settingspage->add(new admin_setting_configtext('block_course_termlist/term4name',
                get_string('term4name', 'block_course_termlist', null, true),
                get_string('term4name_desc', 'block_course_termlist', null, true),
                get_string('term4', 'block_course_termlist'),
                PARAM_TEXT));
        
        // Term filter: Timeless courses.
        $settingspage->add(new admin_setting_heading('block_course_termlist/timelesssettingheading',
                get_string('timelesssettingheading', 'block_course_termlist', null, true),
                ''));

        $settingspage->add(new admin_setting_configcheckbox('block_course_termlist/timelessenabled',
                get_string('timelessenabled', 'block_course_termlist', null, true),
                get_string('timelessenabled_desc', 'block_course_termlist', null, true),
                1));

        $settingspage->add(new admin_setting_configtext('block_course_termlist/timelessname',
                get_string('timelessname', 'block_course_termlist', null, true),
                get_string('timelessname_desc', 'block_course_termlist', null, true),
                get_string('timelessenabled', 'block_course_termlist', null, true),
                PARAM_TEXT));

                
        $settingspage->add(new admin_setting_configtext('block_course_termlist/timelessterms',
                get_string('timelessterms', 'block_course_termlist', null, true),
                get_string('timelessterms_desc', 'block_course_termlist', null, true),
                get_string('timelessenabled', 'block_course_termlist', null, true),
                PARAM_TEXT));
        
    // Add settings page to the admin settings category.
    $ADMIN->add('block_course_termlist', $settingspage);

    
            /*
if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_configtext('block_course_termlist/excluded', 
                        get_string('excluded', 'block_course_termlist'),
                        get_string('configexcluded', 'block_course_termlist'), ''));

    $settings->add(new admin_setting_configcheckbox('block_course_termlist/useallowedcats', 
                        get_string('useallowedcats', 'block_course_termlist'),
                        get_string('configuseallowedcats', 'block_course_termlist'), 0));

    $settings->add(new admin_setting_configcheckbox('block_course_termlist/onlyactiveenrol', 
                        get_string('onlyactiveenrol', 'block_course_termlist'),
                        get_string('configonlyactiveenrol', 'block_course_termlist'), 0));
                       
                       
    $settings->add(new admin_setting_configcheckbox('block_course_termlist/showcategorieslink', 
                        get_string('showcategorieslink', 'block_course_termlist'),
                        get_string('configshowcategorieslink', 'block_course_termlist'), 0));
    $settings->add(new admin_setting_configcheckbox('block_course_termlist/showdepartmentslink', 
                        get_string('showdepartmentslink', 'block_course_termlist'),
                        get_string('configshowdepartmentslink', 'block_course_termlist'), 0));
    $settings->add(new admin_setting_configcheckbox('block_course_termlist/hideallcourseslink', 
                        get_string('hideallcourseslink', 'block_course_termlist'),
                        get_string('confighideallcourseslink', 'block_course_termlist'), 0));
}


*/
