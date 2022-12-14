/**
 * Block "course overview (campus)" - JS code for hiding courses
 *
 * @package    block_course_termlist
 * @copyright  Enrique Castro <@ULPGC> based on block_course_termlist by Alexander Bias
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery'], function($) {
    "use strict";

    /**
     * Function to hide a course from the course list.
     */
    function hideCourse(e) {
        var hiddenCount;
        // Prevent the event from refreshing the page.
        if (e !== undefined) {
            e.preventDefault();
        }

        // When hidden course managing is active.
        if (e.data.manage === 1) {
            // Change the icon.
            $('#ctl-hidecourseicon-' + e.data.course).addClass('ctl-hidden');
            $('#ctl-showcourseicon-' + e.data.course).removeClass('ctl-hidden');
        }
        // When hidden course managing is not active.
        if (e.data.manage === 0) {
            // Change the icon.
            $('#ctl-hidecourseicon-' + e.data.course).addClass('ctl-hidden');
            $('#ctl-showcourseicon-' + e.data.course).removeClass('ctl-hidden');

            // Use a nice slide animation to make clear where the course is going.
            var courseId = e.data.course;
            $('.ctl-hidecourse-' + e.data.course).slideUp(function() {
                $('.ctl-hidecourse-' + courseId).addClass('ctl-hidden');
                hiddenCount = parseInt($('#ctl-hiddencoursescount').html(), 10);
                $('#ctl-hiddencoursescount').html(hiddenCount + 1);
                $('#ctl-hiddencoursesmanagement-bottom .row').removeClass('ctl-hidden');
            });
        }

        // Store the course status (Uses AJAX to save to the database).
        M.util.set_user_preference('block_course_termlist-hidecourse-' + e.data.course, 1);
    }

    /**
     * Function to show a course in the course list.
     */
    function showCourse(e) {
        // Prevent the event from refreshing the page.
        if (e !== undefined) {
            e.preventDefault();
        }

        // When hidden course managing is active.
        if (e.data.manage === 1) {
            // Change the icon.
            $('#ctl-showcourseicon-' + e.data.course).addClass('ctl-hidden');
            $('#ctl-hidecourseicon-' + e.data.course).removeClass('ctl-hidden');
        }

        // Store the course status (Uses AJAX to save to the database).
        M.util.set_user_preference('block_course_termlist-hidecourse-' + e.data.course, 0);
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
        initHideCourse: function(params) {
            var i;
            var courses = params.courses.split(" ");
            for (i = 0; i < courses.length; i++) {
                // Add change listener to hide courses widgets.
                $('#ctl-hidecourseicon-' + courses[i]).on('click', {course: courses[i], manage: params.manage}, hideCourse);
                // Add change listener to show courses widgets.
                $('#ctl-showcourseicon-' + courses[i]).on('click', {course: courses[i], manage: params.manage}, showCourse);
                // Add change listener to show / hide courses widgets for local_boostcoc.
                // Do this only when hidden courses management isn't active. This way, the notshowncourses will not be remembered on
                // the server until the user finishes hidden courses management. While working in hidden courses management in one
                // browser tab, the nav drawer in a second browser tab would still show the old status. But we accept this because
                // otherwise we would have to implement a second localBoostCOCRemember detection algorithm for hidden courses
                // management.
                if (params.local_boostcoc == true && params.manage == false) {
                    $('#ctl-hidecourseicon-' + courses[i]).on('click', localBoostCOCRememberNotShownCourses).on('click',
                            localBoostCOCRememberActiveFilters);
                }
            }
        }
    };
});
