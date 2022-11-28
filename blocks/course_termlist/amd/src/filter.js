/**
 * Block "course overview (campus)" - JS code for filtering courses
 *
 * @package    block_course_termlist
 * @copyright  Enrique Castro <@ULPGC> based on block_course_termlist by Alexander Bias
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery'], function($) {
    "use strict";

    /**
     * Function to filter the shown courses by term.
     */
    function filterTerm(e) {
        // Prevent the event from refreshing the page.
        if (e !== undefined) {
            e.preventDefault();
        }

        var value = $('#ctl-filterterm').val();
        if (value === "all") {
            $('.termdiv').removeClass('ctl-hidden');
        } else {
            $('.termdiv').addClass('ctl-hidden');
            $('.ctl-term-' + value).removeClass('ctl-hidden');
            $('.ctl-term-').removeClass('ctl-hidden');
            $('.ctl-term-0').removeClass('ctl-hidden');
        }

        // Store the users selection (Uses AJAX to save to the database).
        M.util.set_user_preference('block_course_termlist-selectedterm', value);
    }

    /**
     * Function to filter the shown courses by term teacher.
     */
    function filterTeacher(e) {
        // Prevent the event from refreshing the page.
        if (e !== undefined) {
            e.preventDefault();
        }

        var value = $("#ctl-filterteacher").val();
        if (value === "all") {
            $('.teacherdiv').removeClass('ctl-hidden');
        } else {
            $('.teacherdiv').addClass('ctl-hidden');
            $('.ctl-teacher-' + value).removeClass('ctl-hidden');
        }

        // Store the users selection (Uses AJAX to save to the database).
        M.util.set_user_preference('block_course_termlist-selectedteacher', value);
    }

    /**
     * Function to filter the shown courses by parent category.
     */
    function filterCategory(e) {
        // Prevent the event from refreshing the page.
        if (e !== undefined) {
            e.preventDefault();
        }

        var value = $("#ctl-filtercategory").val();
        if (value === "all") {
            $('.categorydiv').removeClass('ctl-hidden');
        } else {
            $('.categorydiv').addClass('ctl-hidden');
            $('.ctl-category-' + value).removeClass('ctl-hidden');
        }

        // Store the users selection (Uses AJAX to save to the database).
        M.util.set_user_preference('block_course_termlist-selectedcategory', value);
    }

    /**
     * Function to filter the shown courses by top level category.
     */
    function filterTopLevelCategory(e) {
        // Prevent the event from refreshing the page.
        if (e !== undefined) {
            e.preventDefault();
        }

        var value = $("#ctl-filtertoplevelcategory").val();
        if (value === "all") {
            $('.toplevelcategorydiv').removeClass('ctl-hidden');
        } else {
            $('.toplevelcategorydiv').addClass('ctl-hidden');
            $('.ctl-toplevelcategory-' + value).removeClass('ctl-hidden');
        }

        // Store the users selection (Uses AJAX to save to the database).
        M.util.set_user_preference('block_course_termlist-selectedtoplevelcategory', value);
    }

    /**
     * Function to apply all filters again (used when the user has pushed the back button).
     */
    function applyAllFilters(initialSettings) {
        /* eslint-disable max-depth */
        var setting, value, $element, elementValue;
        for (setting in initialSettings) {
            if (initialSettings.hasOwnProperty(setting)) {
                value = initialSettings[setting];
                $element = $('#ctl-filter' + setting);
                if ($element.length) {
                    elementValue = $element.val();
                    if (elementValue !== value) {
                        switch (setting) {
                            case 'term':
                                filterTerm();
                                break;
                            case 'teacher':
                                filterTeacher();
                                break;
                            case 'category':
                                filterCategory();
                                break;
                            case 'toplevelcategory':
                                filterTopLevelCategory();
                                break;
                        }
                    }
                }
            }
        }
        /* eslint-enable max-depth */
    }

    /**
     * Function to remember the not shown courses for local_boostcoc.
     */
    function localBoostCOCRememberNotShownCourses() {
        // Get all course nodes which are not shown (= invisible = their height is 0) and store their IDs in an array.
        var notshowncourses = new Array();
        $('.ctl-course').each(function(index, element) {
            if ($(element).height() == 0) {
                notshowncourses.push(element.id.slice(11)); // This will remove "ctl-course-" from the id's string.
            }
        });

        // Convert not shown courses array to JSON.
        var jsonstring = JSON.stringify(notshowncourses);

        // Store the current status of not shown courses (Uses AJAX to save to the database).
        M.util.set_user_preference('local_boostctl-notshowncourses', jsonstring);
    }

    /**
     * Function to remember the active filters for local_boostcoc.
     */
    function localBoostCOCRememberActiveFilters() {
        // Get all active filters (value != all) and the fact that hidden courses are present and store them in an array.
        var activefilters = new Array();
        $('#ctl-filterterm, #ctl-filtercategory, #ctl-filtertoplevelcategory, #ctl-filterteacher').each(function(index, element) {
            if ($(element).val() !== "all") {
                activefilters.push(element.id.slice(4)); // This will remove "ctl-" from the id's string.
            }
        });
        var hiddenCount = parseInt($('#ctl-hiddencoursescount').html(), 10);
        if (hiddenCount > 0) {
            activefilters.push('hidecourses');
        }

        // Convert not shown courses array to JSON.
        var jsonstring = JSON.stringify(activefilters);

        // Store the current status of active filters (Uses AJAX to save to the database).
        M.util.set_user_preference('local_boostctl-activefilters', jsonstring);
    }

    return {
        initFilter: function(params) {
            // Add change listener to filter widgets.
            $('#ctl-filterterm').on('change', filterTerm);
            $('#ctl-filterteacher').on('change', filterTeacher);
            $('#ctl-filtercategory').on('change', filterCategory);
            $('#ctl-filtertoplevelcategory').on('change', filterTopLevelCategory);

            // Add change listener to filter widgets for local_boostcoc.
            if (params.local_boostcoc == true) {
                $('#ctl-filterterm, #ctl-filterteacher, #ctl-filtercategory, #ctl-filtertoplevelcategory').on('change',
                        localBoostCOCRememberNotShownCourses).on('change', localBoostCOCRememberActiveFilters);
            }

            // Make sure any initial filter settings are applied (may be needed if the user
            // has used the browser 'back' button).
            applyAllFilters(params.initialsettings);
        }
    };
});
