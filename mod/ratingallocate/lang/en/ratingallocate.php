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
 * English strings for ratingallocate
 *
 *
 * @package    mod_ratingallocate
 * @copyright  2014 M Schulze, T Reischmann, C Usener
 * @copyright  based on code by Stefan Koegel copyright (C) 2013 Stefan Koegel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

// <editor-fold defaultstate="collapsed" desc="General Plugin Settings">
$string['ratingallocate'] = 'Fair Allocation';
$string['ratingallocatename'] = 'Name of this Fair Allocation';
$string['ratingallocatename_help'] = 'Please choose a name for this Fair Allocation activity.';
$string['modulename'] = 'Fair Allocation';
$string['modulename_help'] =
        'The Fair Allocation module lets you define choices your participants can then rate. The participants can then be distributed automatically to the available choices according to their ratings.';
$string['modulenameplural'] = 'Fair Allocations';
$string['pluginadministration'] = 'Fair Allocation administration';
$string['pluginname'] = 'Fair Allocation';
$string['groupingname'] = 'Created from Fair Allocation "{$a}"';
$string['ratingallocate:addinstance'] = 'Add new instance of Fair Allocation';
$string['ratingallocate:view'] = 'View instances of Fair Allocation';
$string['ratingallocate:distribute_unallocated'] = 'Ability to distribute unallocated users automatically';
$string['ratingallocate:give_rating'] = 'Create or edit choice';
$string['ratingallocate:start_distribution'] = 'Start allocation of users to choices';
$string['ratingallocate:export_ratings'] = 'Ability to export the user ratings';
$string['ratingallocate:modify_choices'] = 'Ability to modify, edit or delete the set of choices of a Fair Allocation';
$string['crontask'] = 'Automated allocation for Fair Allocation';
$string['algorithmtimeout'] = 'Algorithm timeout';
$string['configalgorithmtimeout'] = 'The time in seconds after which the algorithm is assumed to be stuck.
The current run is terminated and marked as failed.';
$string['algorithmforcebackground'] = 'Force calculation as background task';
$string['configalgorithmforcebackground'] = 'Even if triggered manually by the user the distribution will always be calculated in the background.';
$string['downloaduserfields'] = 'Additional user fields for download';
$string['configdownloaduserfields'] =
        'When downloading a table with users in it, these fields may be shown in addition to the users\' first and last name.';
$string['userid'] = 'User ID';
$string['calendarstart'] = '{$a} opens';
$string['calendarstop'] = '{$a} closes';
$string['openafterclose'] = 'This activity must start before it ends.';
$string['closebeforeopen'] = 'This activity must end after it begins.';
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="Rating Form for Users">
$string['choicestatusheading'] = 'Status';
$string['timeremaining'] = 'Time remaining';
$string['publishdate_estimated'] = 'Estimated publication date';
$string['rateable_choices'] = 'Rateable Choices';
$string['rating_is_over'] = 'The rating is over.';
$string['rating_is_over_with_allocation'] = 'The rating is over. You were allocated to \'{$a}\'.';
$string['rating_is_over_no_allocation'] = 'The rating is over. You could not be allocated to any choice.';
$string['ratings_saved'] = 'Your ratings have been saved.';
$string['ratings_deleted'] = 'Your ratings have been deleted.';
$string['strategyname'] = 'Strategy is "{$a}"';
$string['too_early_to_rate'] = 'It is too early to rate.';
$string['your_allocated_choice'] = 'Your Allocation';
$string['you_are_not_allocated'] = 'You were not allocated to any choice!';
$string['your_rating'] = 'Your Rating';
$string['edit_rating'] = 'Edit Rating';
$string['delete_rating'] = 'Delete Rating';
$string['results_not_yet_published'] = 'Results have not yet been published.';
$string['no_choice_to_rate'] = 'There are no choices to rate!';
$string['too_few_choices_to_rate'] = 'There are too few choices to rate! Students have to rank at least {$a} choices!';
$string['at_least_one_rateable_choices_needed'] = 'You need at least one rateable choice.';
$string['no_rating_possible'] = 'Currently, there is no rating possible!';
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="Administrator View">
$string['allocation_manual_explain_only_raters'] = 'Select a choice to be assigned to a user.
Only users who rated at least one choice and who are not allocated yet are listed.';
$string['allocation_manual_explain_all'] = 'Select a choice to be assigned to a user.';
$string['distribution_algorithm'] = 'Distribution Algorithm';
$string['distribution_saved'] = 'Distribution saved (in {$a}s).';
$string['distributeequally'] = 'Distribute unallocated users equally';
$string['distributefill'] = 'Distribute unallocated users by filling up';
$string['distribution_description'] = 'Distribution of unallocated users';
$string['distribution_description_help'] = 'You can choose between two different algorithms to distribute currently unallocated users.<br/>
 <i>Distribute equally:</i> Users are being distributed equally across the choices regarding the maximum of each choice.<br/>
 <i>Fill up choices:</i> Every choice is being filled up with users first before filling up the next choice. Choices with
 least places left are filled up first.<br/><br/>
 Group restrictions will be respected.';
$string['distribute_unallocated_fill_confirm'] = 'All currently unallocated users will be distributed to the choices.
 Each choice will be filled up to its maximum before assigning users to the next choice.';
$string['distribute_unallocated_equally_confirm'] = 'All currently unallocated users will be distributed to the choices.
 The choices will be filled up equally, so all of them have about the same amount of places left.';
$string['no_user_to_allocate'] = 'There is no user you could allocate';
$string['ratings_table'] = 'Ratings and Allocations';
$string['ratings_table_sum_allocations'] = 'Number of allocations / Maximum';
$string['ratings_table_sum_allocations_value'] = '{$a->sum} / {$a->max}';
$string['ratings_table_user'] = 'User';
$string['allocations_table'] = 'Allocations Overview';
$string['allocations_table_choice'] = 'Choice';
$string['allocations_table_users'] = 'Users';
$string['allocations_table_noallocation'] = 'No Allocation';
$string['start_distribution_explanation'] =
        ' An algorithm will automatically try to fairly allocate the users according to their given ratings.';
$string['distribution_table'] = 'Distribution Table';
$string['download_problem_mps_format'] = 'Download Equation (mps/txt)';
$string['export_choice_text_suffix'] = ' - Text';
$string['export_choice_alloc_suffix'] = ' - Allocation';
$string['too_early_to_distribute'] = 'Too early to distribute. Rating is not over yet.';
$string['algorithm_already_running'] =
        'Another instance of the allocation algorithm is already running. Please wait a few minutes and refresh the page.';
$string['algorithm_scheduled_for_cron'] =
        'The allocation algorithm run is scheduled for immediate execution by the cron job. Please wait a few minutes and refresh the page.';
$string['algorithm_now_scheduled_for_cron'] =
        'The allocation algorithm run has now been scheduled for execution by the cron job. Please wait a few minutes and refresh the page.';
$string['start_distribution'] = 'Run Allocation Algorithm';
$string['confirm_start_distribution'] =
        'Running the algorithm will delete all existing allocations, if any. Are you sure to continue?';
$string['delete_all_ratings'] = 'Delete all student ratings';
$string['delete_all_ratings_explanation'] = 'Deletes all ratings that students have submitted so far and all allocations
        that may have been created based on these ratings. Use with caution.';
$string['distribution_unallocated_already_running'] = 'The distribution of unallocated users is currently being processed. Please wait some time and reload the page to check if it has been finished.';
$string['distributing_unallocated_users_started'] = 'The distribution of unallocated users has been started. Please wait some time and have a look at the distribution table.';
$string['confirm_delete_all_ratings'] = 'Are you sure you want to delete all ratings students have submitted so far?';
$string['error_deleting_all_insufficient_permission'] = 'You don\'t have the permission to do that';
$string['error_deleting_all_no_rating_possible'] = 'You can\'t delete the ratings when the rating phase is already over';
$string['success_deleting_all'] = 'Deleted all ratings';
$string['unassigned_users'] = 'Unassigned Users';
$string['invalid_dates'] = 'Dates are invalid. Starting date must be before ending date.';
$string['invalid_publishdate'] = 'Publication date is invalid. Publication date must be after the end of rating.';
$string['rated'] = 'rated {$a}';
$string['no_rating_given'] = 'Unrated';
$string['export_options'] = 'Export Options';
$string['manual_allocation_saved'] = 'Your manual allocation has been saved.';
$string['manual_allocation_nothing_to_be_saved'] = 'There was nothing to be saved.';
$string['publish_allocation'] = 'Publish Allocation';
$string['distribution_published'] = 'Allocation has been published.';
$string['create_moodle_groups'] = 'Create Groups From Allocation';
$string['moodlegroups_created'] = 'The corresponding Moodle groups and groupings have been created.';
$string['saveandcontinue'] = 'Save and Continue';

$string['last_algorithm_run_date'] = 'Last algorithm run at';
$string['last_algorithm_run_date_none'] = '-';
$string['last_algorithm_run_status'] = 'Status of last run';
$string['last_algorithm_run_status_-1'] = 'Failed';
$string['last_algorithm_run_status_0'] = 'Not started';
$string['last_algorithm_run_status_1'] = 'Running';
$string['last_algorithm_run_status_2'] = 'Successful';

$string['modify_allocation_group'] = 'Modify Allocation';
$string['modify_allocation_group_desc_too_early'] =
        'The rating phase has not yet started. You can start the allocation process after the rating phase has ended.';
$string['modify_allocation_group_desc_rating_in_progress'] =
        'The rating phase is currently running. You can start the allocation process after the rating phase has ended.';
$string['modify_allocation_group_desc_ready'] =
        'The rating phase has ended. You can now run the algorithm for an automatic allocation.';
$string['modify_allocation_group_desc_ready_alloc_started'] = 'The rating phase has ended. Some allocations have already been created.
Rerunning the algorithm will delete all current allocations.
You can now modify the allocations manually or proceed to publishing the allocations.';
$string['modify_allocation_group_desc_published'] = 'The allocations have been published.
You should only alter them with care.
If you do so, please inform the students about the changes manually!';
$string['publish_allocation_group'] = 'Publish Allocation';
$string['publish_allocation_group_desc_too_early'] =
        'The rating phase has not started yet. Please wait till the rating phase has ended and then start to create allocations, first.';
$string['publish_allocation_group_desc_rating_in_progress'] =
        'The rating phase is in progress. Please wait till the rating phase has ended and then start to create allocations, first.';
$string['publish_allocation_group_desc_ready'] = 'There are no allocations yet. Please see the modify allocation section.';
$string['publish_allocation_group_desc_ready_alloc_started'] = 'The allocations can now be published.
After publishing the allocations they can no longer be altered.
Please have a look at the current allocations by following the link in the reports section.
You can choose to create groups within your course for all allocations.
If the same groups have already been created by this plugin, they will be purged before refilling them.
This can be done before and after publishing the allocations.';
$string['publish_allocation_group_desc_published'] = 'The allocations are already published.
You can choose to create groups within your course for all allocations.
If the same groups have already been created by this plugin, they will be purged before refilling them.';
$string['reports_group'] = 'Reports';

$string['manual_allocation'] = 'Manual Allocation';
$string['manual_allocation_form'] = 'Manual Allocation Form';
$string['filter_hide_users_without_rating'] = 'Hide users without rating';
$string['filter_show_alloc_necessary'] = 'Hide users with allocation';
$string['update_filter'] = 'Update Filter';

$string['show_table'] = 'Show Ratings and Allocations';
$string['show_allocation_table'] = 'Show Allocations Overview';
$string['allocation_statistics'] = 'Allocation Statistics';
$string['show_allocation_statistics'] = 'Show Allocation Statistics';
$string['allocation_statistics_description'] = 'This statistic gives an impression of the overall satisfaction of the allocation.
It is counting the allocations according to the rating the user has given to the respective choice.
<ul>
<li>{$a->rated} out of {$a->usersinchoice} user(s) have placed their vote.</li>
<li>{$a->users} out of {$a->total} user(s) got a choice they rated with "{$a->rating}".</li>
<li>{$a->unassigned} user(s) could not be allocated to a choice yet.</li>
</ul>';
$string['allocation_table_description'] = 'This statistic gives an overview over all allocations of this instance.</br>
All users, which rated and were not allocated, are listed under \'No Allocation\'';
$string['allocation_statistics_description_no_alloc'] = 'This statistic gives an impression of the overall satisfaction of the allocation.
It is counting the allocations according to the rating the user has given to the respective choice.
<ul>
<li>Currently {$a->notrated} user(s) have not yet given a rating.</li>
<li>{$a->rated} user(s) already placed a vote.</li>
<li>There are no allocations yet.</li>
</ul>';

$string['rating_raw'] = '{$a}';
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="Form to edit choices (administrator)">
$string['delete_choice'] = 'Delete choice';
$string['deleteconfirm'] = 'Do you really want to delete the choice "{$a}"?';
$string['choice_deleted_notification'] = 'Choice "{$a}" was deleted.';
$string['choice_deleted_notification_error'] = 'Choice requested for deletion could not be found.';
$string['modify_choices_group'] = 'Choices';
$string['modify_choices'] = 'Edit Choices';
$string['modify_choices_explanation'] = 'Shows the list of all choices. Here, the choices can be hidden, altered and deleted.';
$string['modify_choices_group_desc_too_early'] = 'Here, the choices can be specified, which should be available to the students.';
$string['modify_choices_group_desc_rating_in_progress'] =
        'The rating is in progress, you should not change the set of available choices in this step.';
$string['modify_choices_group_desc_ready'] =
        'The rating phase is over, you can now modify the amount of students of each choice or deactivate some choices to variate the outcome of the distribution.';
$string['modify_choices_group_desc_ready_alloc_started'] =
        'The rating phase is over, you can now modify the amount of students of each choice or deactivate some choices to variate the outcome of the distribution.';
$string['modify_choices_group_desc_published'] =
        'The allocations have been published, it is no longer recommended to alter the choices.';
$string['err_positivnumber'] = 'You must supply a positive number here.';
$string['saveandnext'] = 'Save and add next';
$string['choice_added_notification'] = 'Choice saved.';

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="Form to upload choices via CSV (administrator)">
$string['upload_choices'] = 'Upload choices via CSV';
$string['upload_choices_required_fields'] = 'Required Fields';
$string['upload_choices_fields_desc'] = 'CSV files uploaded through this form are expected to be UTF-8 encoded, and include the following choice fields:<br/><pre>{$a}</pre>
The file is required to contain a header line. Multiple groups should be separated by a semicolon. The "active" column should contain 1 or 0 to make the choice enabled or disabled.<br/>
Example file content:
<pre>
title,explanation,maxsize,active,groups
First Choice,This is the description of the first choice,15,1,Group A;Group B;Group C
Second Choice,This is the description of the second choice,12,0,Group A;Group D
</pre>';
$string['csvempty'] = 'CSV file is empty.';
$string['csvupload'] = 'Upload CSV';
$string['csvupload_further_problems'] = '{$a} further problems found but not displayed.';
$string['csvupload_explanation'] = 'Bulk upload choices via a CSV file';
$string['csvupload_missing_fields'] = 'Columns missing from CSV import: {$a}';
$string['csvupload_missing_groups'] = 'Line {$a->row}: group(s) not available in course: [{$a->invalidgroups}]';
$string['csvupload_live_problems'] = 'Problems found in CSV import: {$a}';
$string['csvupload_live_success'] = 'CSV import successful. {$a->importcount} choices imported.';
$string['csvupload_test_problems'] = 'Problems found in CSV import test: {$a}';
$string['csvupload_test_success'] = 'CSV import test successful. {$a->importcount} choices can be imported.';
$string['csvupload_test_upload'] = 'Test upload';
$string['csvupload_test_upload_help'] = 'When checked: test the uploaded CSV file for data problems, but do not commit to the database.';
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="Form to edit the instance(administrator)">
$string['choice_active'] = 'Choice is active';
$string['choice_active_help'] = 'Only active choices are displayed to the user. Inactive choices are not displayed.';
$string['choice_explanation'] = 'Description (optional)';
$string['choice_maxsize'] = 'Max. number of participants';
$string['choice_maxsize_display'] = 'Maximum number of students';
$string['choice_title'] = 'Title';
$string['choice_title_help'] = 'Title of the choice. *Attention* all active choices will be displayed while ordered by title.';
$string['choice_usegroups'] = 'Restrict visibility by groups';
$string['choice_usegroups_help'] = '* If selected, this choice will only be visible to the members of the specified groups.
* Disabling the restriction means that this choice will be available to anyone.
* Enabling the restriction without specifying a single group means that this choice will be *not* available for anyone.';
$string['choice_groupselect'] = 'Groups';
$string['edit_choice'] = 'Edit choice';
$string['rating_endtime'] = 'Rating ends at';
$string['rating_begintime'] = 'Rating begins at';
$string['newchoicetitle'] = 'New choice {$a}';
$string['deletechoice'] = 'Delete choice';
$string['publishdate'] = 'Estimated publication date';
$string['runalgorithmbycron'] = 'Automatic allocation after rating period';
$string['runalgorithmbycron_help'] =
        'Automatically runs the allocation algorithm after the rating period ended. However, the results have to be published manually.';
$string['select_strategy'] = 'Rating strategy';
$string['select_strategy_help'] = 'Choose a rating strategy:

* **Accept-Deny** The user can decide for each choice to accept or deny it.
* **Accept-Neutral-Deny** The user can decide for each choice to accept or deny or to be neutral about it.
* **Likert Scale** The user can rate each choice with a number from a defined range. The range of numbers can be defined individually (beginning with 0). A high number corresponds to a high preference.
* **Give Points** The user can rate the choices by assigning a number of points. The maximum number of points can be defined individually. A high number of points corresponds to a high preference.
* **Rank Choices** The user has to rank the available choices. How many choices need to be rated can be defined individually.
* **Tick Accept**  The user can state for each choice whether it is acceptable for him/her.

This option can\'t be changed if a student has already submitted their preference.
';
$string['strategy_not_specified'] = 'You have to select a strategy.';
$string['strategyspecificoptions'] = 'Strategy specific options';
$string['strategy_altered_after_preferences'] = 'Strategy cannot be changed after preferences where submitted';

$string['err_required'] = 'You need to provide a value for this field.';
$string['err_minimum'] = 'The minimum value for this field is {$a}.';
$string['err_maximum'] = 'The maximum value for this field is {$a}.';
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="Form to edit choices">
$string['show_choices_header'] = 'List of all choices';
$string['newchoice'] = 'Add new choice';
$string['choice_table_title'] = 'Title';
$string['choice_table_explanation'] = 'Description';
$string['choice_table_maxsize'] = 'Max. Size';
$string['choice_table_active'] = 'Active';
$string['choice_table_usegroups'] = 'Groups';
$string['choice_table_tools'] = 'Edit';
// </editor-fold>

$string['is_published'] = 'Published';

$string['strategy_settings_label'] = 'Designation for "{$a}"';

$string['strategy_settings_default'] = 'Default value for rating form';
$string['strategy_settings_default_help'] = 'The rating form, the users are provided with, will contain a set of radio buttons for each available choice.
This value defines the default value the radio buttons are initialized with.';

/* Specific to Strategy01, YesNo */
$string['strategy_yesno_name'] = 'Accept-Deny';
$string['strategy_yesno_setting_crossout'] = 'Maximum number of choices the user can rate with "Deny"';
$string['strategy_yesno_max_no'] = 'You may only assign "Deny" to {$a} choice(s).';
$string['strategy_yesno_maximum_crossout'] = 'You may only assign "Deny" to at most {$a} choice(s).';
$string['strategy_yesno_rating_crossout'] = 'Deny';
$string['strategy_yesno_rating_choose'] = 'Accept';

/* Specific to Strategy02, YesMayBeNo */
$string['strategy_yesmaybeno_name'] = 'Accept-Neutral-Deny';
$string['strategy_yesmaybeno_setting_maxno'] = 'Maximum number of choices the user can rate with "Deny"';
$string['strategy_yesmaybeno_max_no'] = 'You may only assign "Deny" to {$a} choice(s).';
$string['strategy_yesmaybeno_max_count_no'] = 'You may only assign "Deny" to at most {$a} choice(s).';
$string['strategy_yesmaybeno_rating_no'] = 'Deny';
$string['strategy_yesmaybeno_rating_maybe'] = 'Neutral';
$string['strategy_yesmaybeno_rating_yes'] = 'Accept';

// Specific to Strategy03, Likert
$string['strategy_lickert_name'] = 'Likert Scale';
$string['strategy_lickert_setting_maxno'] = 'Maximum number of choices the user can rate with 0';
$string['strategy_lickert_max_no'] = 'You may only assign 0 points to at most {$a} choice(s).';
$string['strategy_lickert_setting_maxlickert'] = 'Highest number on the likert scale (3, 5 or 7 are common values)';
$string['strategy_lickert_rating_biggestwish'] = '{$a} - Highly appreciated';
$string['strategy_lickert_rating_exclude'] = '{$a} - Exclude';

// Specific to Strategy04, Points
$string['strategy_points_name'] = 'Give Points';
$string['strategy_points_setting_maxzero'] = 'Maximum number of choices to which the user can give 0 points';
$string['strategy_points_explain_distribute_points'] =
        'Give points to each choice, you have a total of {$a} points to distribute. Prioritize the best choice by giving the most points.';
$string['strategy_points_explain_max_zero'] = 'You may only assign 0 points to at most {$a} choice(s).';
$string['strategy_points_incorrect_totalpoints'] = 'Incorrect total number of points. The sum of all points has to be {$a}.';
$string['strategy_points_setting_totalpoints'] = 'Total number of points the user can assign';
$string['strategy_points_max_count_zero'] = 'You may give 0 points to at most {$a} choice(s).';
$string['strategy_points_illegal_entry'] = 'The points that you assign to a choice must be between 0 and {$a}.';

// Specific to Strategy05, Order
$string['strategy_order_name'] = 'Rank Choices';
$string['strategy_order_no_choice'] = '{$a}. Choice';
$string['strategy_order_use_only_once'] = 'Choices cannot be selected twice and must be unique.';
$string['strategy_order_explain_choices'] =
        'Select one choice in each select-box. The first choice receives the highest priority, and so on.';
$string['strategy_order_setting_countoptions'] =
        'Minimum number of fields the user has to vote on (smaller than or equal to the number of choices!)';
$string['strategy_order_header_description'] = 'Available Choices';
$string['strategy_order_choice_none'] = 'Please select a choice';

// Specific to Strategy06, tickyes
$string['strategy_tickyes_name'] = 'Tick Accept';
$string['strategy_tickyes_accept'] = 'Accept';
$string['strategy_tickyes_not_accept'] = '-';
$string['strategy_tickyes_setting_mintickyes'] = 'Minimum number of choices to accept';
$string['strategy_tickyes_error_mintickyes'] = 'You have to tick at least {$a} boxes.';
$string['strategy_tickyes_explain_mintickyes'] = 'You have to tick a minimum of {$a} boxes.';

// As message provider, for the notification after allocation
$string['messageprovider:notifyalloc'] = 'Notification of option allocation';
$string['allocation_notification_message_subject'] = 'Allocation published for {$a}';
$string['allocation_notification_message'] =
        'Concerning the "{$a->ratingallocate}", you have been assigned to the choice "{$a->choice} ({$a->explanation})".';
$string['no_allocation_notification_message'] = 'Concerning the "{$a->ratingallocate}", you could not be assigned to any choice.';
$string['messageprovider:allocation'] = 'Notification about published allocation';

// Logging
$string['log_rating_saved'] = 'User rating saved';
$string['log_rating_saved_description'] =
        'The user with id "{$a->userid}" saved his rating for the Fair Allocation with id "{$a->ratingallocateid}".';

$string['log_rating_deleted'] = 'User rating deleted';
$string['log_rating_deleted_description'] =
        'The user with id "{$a->userid}" deleted his rating for the Fair Allocation with id "{$a->ratingallocateid}".';

$string['log_rating_viewed'] = 'User rating viewed';
$string['log_rating_viewed_description'] =
        'The user with id "{$a->userid}" viewed his rating for the Fair Allocation with id "{$a->ratingallocateid}".';

$string['log_allocation_published'] = 'Allocation published';
$string['log_allocation_published_description'] =
        'The user with id "{$a->userid}" published the allocation for the Fair Allocation with id "{$a->ratingallocateid}".';

$string['log_distribution_triggered'] = 'Distribution triggered';
$string['log_distribution_triggered_description'] =
        'The user with id "{$a->userid}" triggered the distribution for the Fair Allocation with id "{$a->ratingallocateid}". The algorithm needed {$a->time_needed}sec.';

$string['log_manual_allocation_saved'] = 'Manual allocation saved';
$string['log_manual_allocation_saved_description'] =
        'The user with id "{$a->userid}" saved a manual allocation for the Fair Allocation with id "{$a->ratingallocateid}".';

$string['log_ratingallocate_viewed'] = 'Ratingallocate viewed';
$string['log_ratingallocate_viewed_description'] =
        'The user with id "{$a->userid}" viewed the Fair Allocation with id "{$a->ratingallocateid}".';

$string['log_allocation_table_viewed'] = 'Allocation table viewed';
$string['log_allocation_table_viewed_description'] =
        'The user with id "{$a->userid}" viewed the allocation table for the Fair Allocation with id "{$a->ratingallocateid}".';

$string['log_ratings_and_allocation_table_viewed'] = 'Ratings and allocation table viewed';
$string['log_ratings_and_allocation_table_viewed_description'] =
        'The user with id "{$a->userid}" viewed the ratings and allocation table for the Fair Allocation with id "{$a->ratingallocateid}".';

$string['log_allocation_statistics_viewed'] = 'Allocation statistics viewed';
$string['log_allocation_statistics_viewed_description'] =
        'The user with id "{$a->userid}" viewed the allocation statistics for the Fair Allocation with id "{$a->ratingallocateid}".';

$string['log_index_viewed'] = 'User viewed all instances of Fair Allocation';
$string['log_index_viewed_description'] = 'The user with id "{$a->userid}" viewed all instances of Fair Allocation in this course.';

$string['log_all_ratings_deleted'] = 'All ratings of a Fair Allocation instance were deleted';
$string['log_all_ratings_deleted_description'] = 'The user with id "{$a->userid}" has deleted all ratings for the Fair Allocation with id "{$a->ratingallocateid}".';

$string['no_id_or_m_error'] = 'You must specify a course_module ID or an instance ID';

// Language strings for Privacy API
$string['privacy:metadata:ratingallocate_ratings'] = 'Information about the user\'s ratings for given choices.';
$string['privacy:metadata:ratingallocate_ratings:choiceid'] = 'The ID of the choice the user has rated';
$string['privacy:metadata:ratingallocate_ratings:userid'] = 'The ID of the user rating this choice';
$string['privacy:metadata:ratingallocate_ratings:rating'] = 'The rating a user has given to a choice.';

$string['privacy:metadata:ratingallocate_allocations'] =
        'Information about the user\'s allocated choices for an activity instance.';
$string['privacy:metadata:ratingallocate_allocations:userid'] = 'The ID of the user who was allocated to a choice';
$string['privacy:metadata:ratingallocate_allocations:ratingallocateid'] =
        'The ID of the activity instance this allocation belongs to';
$string['privacy:metadata:ratingallocate_allocations:choiceid'] = 'The ID of the choice the user was allocated to';

$string['privacy:metadata:preference:flextable_filter'] = 'Stores the filters that are applied to the allocations table.';
$string['privacy:metadata:preference:flextable_manual_filter'] =
        'Stores the filters that are applied to the manual allocations table.';

$string['filtertabledesc'] = 'Describes the filters that are applied to the allocation table.';
$string['filtermanualtabledesc'] = 'Describes the filters that are applied to the table of the manual allocation form.';
