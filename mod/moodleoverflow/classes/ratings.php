<?php
// This file is part of a plugin for Moodle - http://moodle.org/
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
 * The moodleoverflow ratings manager.
 *
 * @package   mod_moodleoverflow
 * @copyright 2017 Kennet Winter <k_wint10@uni-muenster.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_moodleoverflow;
use moodle_exception;

/**
 * Static methods for managing the ratings of posts.
 *
 * @package   mod_moodleoverflow
 * @copyright 2017 Kennet Winter <k_wint10@uni-muenster.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ratings {

    /**
     * Add a rating.
     * This is the basic function to add or edit ratings.
     *
     * @param object $moodleoverflow
     * @param int    $postid
     * @param int    $rating
     * @param object $cm
     * @param null   $userid
     *
     * @return bool|int
     */
    public static function moodleoverflow_add_rating($moodleoverflow, $postid, $rating, $cm, $userid = null) {
        global $DB, $USER, $SESSION;

        // Has a user been submitted?
        if (!isset($userid)) {
            $userid = $USER->id;
        }

        // Is the submitted rating valid?
        $possibleratings = array(RATING_NEUTRAL, RATING_DOWNVOTE, RATING_UPVOTE, RATING_SOLVED,
            RATING_HELPFUL, RATING_REMOVE_DOWNVOTE, RATING_REMOVE_UPVOTE,
            RATING_REMOVE_SOLVED, RATING_REMOVE_HELPFUL);
        if (!in_array($rating, $possibleratings)) {
            throw new moodle_exception('invalidratingid', 'moodleoverflow');
        }

        // Get the related discussion.
        if (!$post = $DB->get_record('moodleoverflow_posts', array('id' => $postid))) {
            throw new moodle_exception('invalidparentpostid', 'moodleoverflow');
        }

        // Check if the post belongs to a discussion.
        if (!$discussion = $DB->get_record('moodleoverflow_discussions', array('id' => $post->discussion))) {
            throw new moodle_exception('notpartofdiscussion', 'moodleoverflow');
        }

        // Get the related course.
        if (!$course = $DB->get_record('course', array('id' => $moodleoverflow->course))) {
            throw new moodle_exception('invalidcourseid');
        }

        // Are multiple marks allowed?
        $markssetting = $DB->get_record('moodleoverflow', array('id' => $moodleoverflow->id), 'allowmultiplemarks');
        $multiplemarks = (bool) $markssetting->allowmultiplemarks;

        // Retrieve the contexts.
        $modulecontext = \context_module::instance($cm->id);
        $coursecontext = \context_course::instance($course->id);

        // Redirect the user if capabilities are missing.
        $canrate = self::moodleoverflow_user_can_rate($post, $modulecontext, $userid);
        if (!$canrate) {

            // Catch unenrolled users.
            if (!isguestuser() && !is_enrolled($coursecontext)) {
                $SESSION->wantsurl = qualified_me();
                $SESSION->enrolcancel = get_local_referer(false);
                redirect(new \moodle_url('/enrol/index.php', array(
                    'id' => $course->id,
                    'returnurl' => '/mod/moodleoverflow/view.php?m' . $moodleoverflow->id
                )), get_string('youneedtoenrol'));
            }

            // Notify the user, that he can not post a new discussion.
            throw new moodle_exception('noratemoodleoverflow', 'moodleoverflow');
        }

        // Make sure post author != current user, unless they have permission.
        if (($post->userid == $userid) && !
            (($rating == RATING_SOLVED || $rating == RATING_REMOVE_SOLVED) &&
                has_capability('mod/moodleoverflow:marksolved', $modulecontext))
        ) {
            throw new moodle_exception('rateownpost', 'moodleoverflow');
        }

        // Check if we are removing a mark.
        if (in_array($rating / 10, $possibleratings)) {

            if (!get_config('moodleoverflow', 'allowratingchange')) {
                throw new moodle_exception('noratingchangeallowed', 'moodleoverflow');

                return false;
            }

            // Delete the rating.
            self::moodleoverflow_remove_rating($postid, $rating / 10, $userid, $modulecontext);

            return true;
        }

        // Check for an older rating in this discussion.
        $oldrating = self::moodleoverflow_check_old_rating($postid, $userid);

        // Mark a post as solution or as helpful.
        if ($rating == RATING_SOLVED || $rating == RATING_HELPFUL) {

            // Check if the current user is the startuser.
            if ($rating == RATING_HELPFUL && $userid != $discussion->userid) {
                throw new moodle_exception('notstartuser', 'moodleoverflow');
            }

            // Check if the current user is a teacher.
            if ($rating == RATING_SOLVED && !has_capability('mod/moodleoverflow:marksolved', $modulecontext)) {
                throw new moodle_exception('notteacher', 'moodleoverflow');
            }

            // Check if multiple marks are not enabled.
            if (!$multiplemarks) {

                // Get other ratings in the discussion.
                $sql = "SELECT *
                        FROM {moodleoverflow_ratings}
                        WHERE discussionid = ? AND rating = ?";
                $otherrating = $DB->get_record_sql($sql, [ $discussion->id, $rating ]);

                // If there is an old rating, update it. Else create a new rating record.
                if ($otherrating) {
                    return self::moodleoverflow_update_rating_record($post->id, $rating, $userid, $otherrating->id, $modulecontext);

                } else {
                    $mid = $moodleoverflow->id;

                    return self::moodleoverflow_add_rating_record($mid, $discussion->id, $post->id,
                                                                  $rating, $userid, $modulecontext);
                }

            } else {
                // If multiplemarks are allowed, only create a new rating.
                $mid = $moodleoverflow->id;
                return self::moodleoverflow_add_rating_record($mid, $discussion->id, $post->id, $rating, $userid, $modulecontext);
            }
        }

        // Update an rating record.
        if ($oldrating['normal']) {

            if (!get_config('moodleoverflow', 'allowratingchange')) {
                throw new moodle_exception('noratingchangeallowed', 'moodleoverflow');

                return false;
            }

            // Check if the rating can still be changed.
            if (!self::moodleoverflow_can_be_changed($postid, $oldrating['normal']->rating, $userid)) {
                return false;
            }

            // Update the rating record.
            return self::moodleoverflow_update_rating_record($post->id, $rating, $userid, $oldrating['normal']->id, $modulecontext);
        }

        // Create a new rating record.
        $mid = $moodleoverflow->id;
        $did = $post->discussion;

        return self::moodleoverflow_add_rating_record($mid, $did, $postid, $rating, $userid, $modulecontext);
    }

    /**
     * Get the reputation of a user.
     * Whether within a course or an instance is decided by the settings.
     *
     * @param int  $moodleoverflowid
     * @param null $userid
     * @param bool $forcesinglerating If true you only get the reputation for the given $moodleoverflowid,
     * even if coursewidereputation = true
     *
     * @return int
     */
    public static function moodleoverflow_get_reputation($moodleoverflowid, $userid = null, $forcesinglerating = false) {
        global $DB, $USER;

        // Get the user id.
        if (!isset($userid)) {
            $userid = $USER->id;
        }

        // Check the moodleoverflow instance.
        if(is_object($moodleoverflowid)) { // ecastro ULPGC
            $moodleoverflow = clone $moodleoverflowid;
            $moodleoverflowid = $moodleoverflow->id;
        } elseif (!$moodleoverflow = $DB->get_record('moodleoverflow', array('id' => $moodleoverflowid))) {
            throw new moodle_exception('invalidmoodleoverflowid', 'moodleoverflow');
        }
        
        
        // Check whether the reputation can be summed over the whole course.
        if ($moodleoverflow->coursewidereputation && !$forcesinglerating) {
            return self::moodleoverflow_get_reputation_course($moodleoverflow->course, $userid);
        }

        // Else return the reputation within this instance.
        return self::moodleoverflow_get_reputation_instance($moodleoverflow, $userid); // ecastro ULPGC

    }

    /**
     * Sort the answers of a discussion by their marks and votes.
     *
     * @param object $posts all the posts from a discussion.
     */
    public static function moodleoverflow_sort_answers_by_ratings($posts) {
        // Create a copy that only has the answer posts and save the parent post.
        $answerposts = $posts;
        $parentpost = array_shift($answerposts);

        // Create an empty array for the sorted posts and add the parent post.
        $sortedposts = array();
        $sortedposts[0] = $parentpost;

        // Check if solved posts are preferred over helpful posts.
        $solutionspreferred = false;
        if ($posts[array_key_first($posts)]->ratingpreference == 1) {
            $solutionspreferred = true;
        }

        // Sort the answer posts by ratings.
        // Build groups of different types of answers (Solved and helpful, only solved/helpful, other).
        // markedsolved == 1 means the post is marked as solved.
        // markedhelpful == 1 means the post is marked as helpful.
        // If a group is complete, sort the group.
        $index = 1;
        $startsolvedandhelpful = 1;
        $startsolved = 1;
        $starthelpful = 1;
        $startother = 1;
        // Solved and helpful posts are first.
        foreach ($answerposts as $post) {
            if ($post->markedsolution > 0 && $post->markedhelpful > 0) {
                $sortedposts[$index] = $post;
                $index++;
            }
        }
        // Update the indices and sort the group by votes.
        if ($index > $startsolvedandhelpful) {
            $startsolved = $index;
            $starthelpful = $index;
            $startother = $index;
            self::moodleoverflow_quicksort_post_by_votes($sortedposts, $startsolvedandhelpful, $index - 1);
        }

        // Check if solutions are preferred.
        if ($solutionspreferred) {

            // Build the group of only solved posts.
            foreach ($answerposts as $post) {
                if ($post->markedsolution > 0 && $post->markedhelpful == 0) {
                    $sortedposts[$index] = $post;
                    $index++;
                }
            }
            // Update the indices and sort the group by votes.
            if ($index > $startsolved) {
                $starthelpful = $index;
                $startother = $index;
                self::moodleoverflow_quicksort_post_by_votes($sortedposts, $startsolved, $index - 1);
            }

            // Build the group of only helpful posts.
            foreach ($answerposts as $post) {
                if ($post->markedsolution == 0 && $post->markedhelpful > 0) {
                    $sortedposts[$index] = $post;
                    $index++;
                }
            }
            // Update the indices and sort the group by votes.
            if ($index > $starthelpful) {
                $startother = $index;
                self::moodleoverflow_quicksort_post_by_votes($sortedposts, $starthelpful, $index - 1);
            }
        } else {

            // Build the group of only helpful posts.
            foreach ($answerposts as $post) {
                if ($post->markedsolution == 0 && $post->markedhelpful > 0) {
                    $sortedposts[$index] = $post;
                    $index++;
                }
            }
            // Update the indices and sort the group by votes.
            if ($index > $starthelpful) {
                $startsolved = $index;
                $startother = $index;
                self::moodleoverflow_quicksort_post_by_votes($sortedposts, $starthelpful, $index - 1);
            }

            // Build the group of only solved posts.
            foreach ($answerposts as $post) {
                if ($post->markedsolution > 0 && $post->markedhelpful == 0) {
                    $sortedposts[$index] = $post;
                    $index++;
                }
            }
            // Update the indices and sort the group by votes.
            if ($index > $startsolved) {
                $startother = $index;
                self::moodleoverflow_quicksort_post_by_votes($sortedposts, $startsolved, $index - 1);
            }
        }

        // Now build the group of posts without ratings like helpful/solved.
        foreach ($answerposts as $post) {
            if ($post->markedsolution == 0 && $post->markedhelpful == 0) {
                $sortedposts[$index] = $post;
                $index++;
            }
        }
        // Update the indices and sort the group by votes.
        if ($index > $startother) {
            self::moodleoverflow_quicksort_post_by_votes($sortedposts, $startother, $index - 1);
        }

        // Rearrange the indices and return the sorted posts.

        $neworder = array();
        foreach ($sortedposts as $post) {
            $neworder[$post->id] = $post;
        }

        // Return now the sorted posts.
        return $neworder;
    }

    /**
     * Did the current user rated the post?
     *
     * @param int  $postid
     * @param null $userid
     *
     * @return mixed
     */
    public static function moodleoverflow_user_rated($postid, $userid = null) {
        global $DB, $USER;

        // Is a user submitted?
        if (!$userid) {
            $userid = $USER->id;
        }

        // Get the rating.
        $sql = "SELECT firstrated, rating
                  FROM {moodleoverflow_ratings}
                 WHERE userid = ? AND postid = ? AND (rating = 1 OR rating = 2)";

        return ($DB->get_record_sql($sql, [ $userid, $postid ]));
    }

    /**
     * Get the rating of a single post.
     *
     * @param int $postid
     *
     * @return array
     */
    public static function moodleoverflow_get_rating($postid) {
        global $DB;

        // Retrieve the full post.
        if (!$post = $DB->get_record('moodleoverflow_posts', array('id' => $postid))) {
            throw new moodle_exception('postnotexist', 'moodleoverflow');
        }

        // Get the rating for this single post.
        return self::moodleoverflow_get_ratings_by_discussion($post->discussion, $postid);
    }

    /**
     * Get the ratings of all posts in a discussion.
     *
     * @param int  $discussionid
     * @param null $postid
     *
     * @return array
     */
    public static function moodleoverflow_get_ratings_by_discussion($discussionid, $postid = null) {
        global $DB;

        // Get the amount of votes.
        $sql = "SELECT id as postid,
                       (SELECT COUNT(rating) FROM {moodleoverflow_ratings} WHERE postid=p.id AND rating = 1) AS downvotes,
	                   (SELECT COUNT(rating) FROM {moodleoverflow_ratings} WHERE postid=p.id AND rating = 2) AS upvotes,
                       (SELECT COUNT(rating) FROM {moodleoverflow_ratings} WHERE postid=p.id AND rating = 3) AS issolved,
                       (SELECT COUNT(rating) FROM {moodleoverflow_ratings} WHERE postid=p.id AND rating = 4) AS ishelpful
                  FROM {moodleoverflow_posts} p
                 WHERE p.discussion = ?
              GROUP BY p.id";
        $votes = $DB->get_records_sql($sql, [ $discussionid ]);

        // A single post is requested.
        if ($postid) {

            // Check if the post is part of the discussion.
            if (array_key_exists($postid, $votes)) {
                return $votes[$postid];
            }

            // The requested post is not part of the discussion.
            throw new moodle_exception('postnotpartofdiscussion', 'moodleoverflow');
        }

        // Return the array.
        return $votes;
    }

    /**
     * Check if a discussion is marked as solved or helpful.
     *
     * @param int  $discussionid
     * @param bool $teacher
     *
     * @return bool|mixed
     */
    public static function moodleoverflow_discussion_is_solved($discussionid, $teacher = false) {
        global $DB;

        // Is the teachers solved-status requested?
        if ($teacher) {

            // Check if a teacher marked a solution as solved.
            if ($DB->record_exists('moodleoverflow_ratings', array('discussionid' => $discussionid, 'rating' => RATING_SOLVED))) {

                // Return the rating record.
                return self::moodleoverflow_discussion_best_rating($discussionid, RATING_SOLVED); // ecastro ULPGC
                // Return the rating record.
                //return $DB->get_records('moodleoverflow_ratings', array('discussionid' => $discussionid, 'rating' => 3));
            }

            // The teacher has not marked the discussion as solved.
            return false;
        }

        // Check if the topic starter marked a solution as helpful.
        if ($DB->record_exists('moodleoverflow_ratings', array('discussionid' => $discussionid, 'rating' => RATING_HELPFUL))) {

            // Return the rating record.
            return self::moodleoverflow_discussion_best_rating($discussionid, RATING_HELPFUL); // ecastro ULPGC
            // Return the rating record.
            //return $DB->get_records('moodleoverflow_ratings', array('discussionid' => $discussionid, 'rating' => 4));
        }

        // The topic starter has not marked a solution as helpful.
        return false;
    }

    /**
     * Get the reputation of a user within a single instance.
     *
     * @param int  $moodleoverflowid
     * @param null $userid
     *
     * @return int
     */
    public static function moodleoverflow_get_reputation_instance($moodleoverflowid, $userid = null) {
        global $DB, $USER;

        // Get the user id.
        if (!isset($userid)) {
            $userid = $USER->id;
        }

        // Check the moodleoverflow instance.
        if(is_object($moodleoverflowid)) {
            $moodleoverflow = clone $moodleoverflowid;
            $moodleoverflowid = $moodleoverflow->id;
        } elseif (!$moodleoverflow = $DB->get_record('moodleoverflow', array('id' => $moodleoverflowid))) {
            throw new moodle_exception('invalidmoodleoverflowid', 'moodleoverflow');
        }

        // Initiate a variable.
        $reputation = 0;

        if ($moodleoverflow->anonymous != anonymous::EVERYTHING_ANONYMOUS) {

            // Get all posts of this user in this module.
            // Do not count votes for own posts.
            $sql = "SELECT r.id, r.postid as post, r.rating
                  FROM {moodleoverflow_posts} p
                  JOIN {moodleoverflow_ratings} r ON p.id = r.postid
                 WHERE p.userid = ? AND NOT r.userid = ? AND r.moodleoverflowid = ? ";

            if ($moodleoverflow->anonymous == anonymous::QUESTION_ANONYMOUS) {
                $sql .= " AND p.parent <> 0 ";
            }

            $sql .= "ORDER BY r.postid ASC";

            $params = array($userid, $userid, $moodleoverflowid);
            $records = $DB->get_records_sql($sql, $params);

            // Check if there are results.
            $records = (isset($records)) ? $records : array();

            // Iterate through all ratings.
            foreach ($records as $record) {

                if($moodleoverflow->repcountvotes) { // ecastro ULPGC only count ifinstance allowed
                    // The rating is a downvote.
                    if ($record->rating == RATING_DOWNVOTE) {
                        $reputation += get_config('moodleoverflow', 'votescaledownvote');
                        continue;
                    }

                    // The rating is an upvote.
                    if ($record->rating == RATING_UPVOTE) {
                        $reputation += get_config('moodleoverflow', 'votescaleupvote');
                        continue;
                    }
                }

                // The post has been marked as helpful by the question owner.
                if ($record->rating == RATING_HELPFUL) {
                    $reputation += get_config('moodleoverflow', 'votescalehelpful');
                    continue;
                }

                // The post has been marked as solved by a teacher.
                if ($record->rating == RATING_SOLVED) {
                    $reputation += get_config('moodleoverflow', 'votescalesolved');
                    continue;
                }

                // Another rating should not exist.
                continue;
            }
        }

        if($moodleoverflow->repcountownvotes) { // ecastro ULPGC to avoid student voting conditionally by instance
            // Get votes this user made.
            // Votes for own posts are not counting.
            $sql = "SELECT COUNT(id) as amount
                    FROM {moodleoverflow_ratings}
                    WHERE userid = ? AND moodleoverflowid = ? AND (rating = 1 OR rating = 2)";
            $params = array($userid, $moodleoverflowid);
            $votes = $DB->get_record_sql($sql, $params);

            // Add reputation for the votes.
            //$reputation += get_config('moodleoverflow', 'votescalevote') * $votes->amount;
            $reputation += get_config('moodleoverflow', 'votescalevote') * $moodleoverflow->votefactorvote/100 * $votes->amount;
        }

        // ecastro ULPGC
        // Get posts this user made.
        if($moodleoverflow->repcountposts) {
            $sql = "SELECT COUNT(p.id) as amount
                    FROM {moodleoverflow_posts} p
                    JOIN {moodleoverflow_discussions} d ON d.id = p.discussion
                    WHERE p.userid = ? AND d.moodleoverflow = ? AND d.course = ? AND (p.parent = d.firstpost OR p.parent = 0) ";
            $params = array($userid, $moodleoverflowid, $moodleoverflow->course);
            $posts = $DB->get_record_sql($sql, $params);

            // Add reputation for the posts made.
            $reputation += get_config('moodleoverflow', 'votescalevote') * $moodleoverflow->votefactorpost/100 * $posts->amount;

            // Get comments this user made.
            $sql = "SELECT COUNT(p.id) as amount
                    FROM {moodleoverflow_posts} p
                    JOIN {moodleoverflow_discussions} d ON d.id = p.discussion
                    WHERE p.userid = ? AND d.moodleoverflow = ? AND d.course = ? AND p.parent != d.firstpost AND p.parent != 0";
            $params = array($userid, $moodleoverflowid, $moodleoverflow->course);
            $posts = $DB->get_record_sql($sql, $params);

            // Add reputation for the posts made.
            $reputation += get_config('moodleoverflow', 'votescalevote') * $moodleoverflow->votefactorcomment/100 * $posts->amount;
        }
        // ecastro ULPGC
        
        // Can the reputation of a user be negative?
        if (!$moodleoverflow->allownegativereputation && $reputation <= 0) {
            $reputation = 0;
        }
        
        // Return the rating of the user.
        return $reputation;
    }

    /**
     * Get the ratings of all posts in a discussion.
     *
     * @param int  $discussionid
     * @param null $userid
     * @author ecastro ULPGC
     *
     * @return array
     */
    public static function moodleoverflow_get_answers_by_discussion($discussionid, $userid = null) {
        global $DB, $USER;

        // Get the user id.
        if (!isset($userid)) {
            $userid = $USER->id;
        }

        $sql = "SELECT COUNT(p.id)
                    FROM {moodleoverflow_posts} p
                    JOIN {moodleoverflow_discussions} d ON p.discussion = d.id AND p.parent = d.firstpost
                    WHERE  p.discussion = :discussion AND p.userid = :userid ";

        return $DB->count_records_sql($sql, ['discussion' => $discussionid,  'userid' => $userid]);
    }

    /**
     * Get the post & rating counts for a user in a moodleoverflow instance
     *
     * @param int  $moodleoverflow
     * @param null $userid
     * @author ecastro ULPGC
     *
     * @return array
     */
    public static function moodleoverflow_get_singleuser_stats($moodleoverflow, $userid = null) {
        global $DB, $USER;

        // Get the user id.
        if (!isset($userid)) {
            $userid = $USER->id;
        }

        $sql = "SELECT p.userid, COUNT(p.id) AS posts,
                        (SELECT COUNT(p.id)
                           FROM {moodleoverflow_posts} p2
                           JOIN {moodleoverflow_discussions} d2 ON p2.discussion = d2.id AND p2.parent = d.firstpost
                          WHERE  d2.moodleoverflow = :moid2 AND p2.userid = :userid2
                       GROUP BY p2.userid
                        ) AS answers,
                        (SELECT COUNT(r.id)
                           FROM {moodleoverflow_ratings} r
                          WHERE  r.moodleoverflowid = :moid3 AND r.userid = :userid3 AND (r.rating > 1 AND r.rating < 10)
                       GROUP BY r.userid
                        ) AS votes
                    FROM {moodleoverflow_posts} p
                    JOIN {moodleoverflow_discussions} d ON p.discussion = d.id
                   WHERE  d.moodleoverflow = :moid1 AND p.userid = :userid1
               GROUP BY p.userid ";
        $params =  ['moid1' => $moodleoverflow->id,
                    'moid2' => $moodleoverflow->id,
                    'moid3' => $moodleoverflow->id,
                    'userid1' => $userid, 'userid2' => $userid, 'userid3' => $userid];
        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Get the reputation of a user within a course.
     *
     * @param int  $courseid
     * @param null $userid
     *
     * @return int
     */
    public static function moodleoverflow_get_reputation_course($courseid, $userid = null) {
        global $USER, $DB;

        // Get the userid.
        if (!isset($userid)) {
            $userid = $USER->id;
        }

        // Initiate a variable.
        $reputation = 0;

        // Check if the course exists.
        if (!$course = $DB->get_record('course', array('id' => $courseid))) {
            throw new moodle_exception('invalidcourseid');
        }

        // Get all moodleoverflow instances in this course.
        $sql = "SELECT id
                  FROM {moodleoverflow}
                 WHERE course = ?
                   AND coursewidereputation = 1";
        $params = array($course->id);
        $instances = $DB->get_records_sql($sql, $params);

        // Check if there are instances in this course.
        $instances = (isset($instances)) ? $instances : array();

        // Sum the reputation of each individual instance.
        foreach ($instances as $instance) {
            $reputation += self::moodleoverflow_get_reputation_instance($instance->id, $userid);
        }

        // The result does not need to be corrected.
        return $reputation;
    }

    /**
     * Check for all old rating records from a user for a specific post.
     *
     * @param int  $postid
     * @param int  $userid
     * @param null $oldrating
     *
     * @return array|mixed
     */
    private static function moodleoverflow_check_old_rating($postid, $userid, $oldrating = null) {
        global $DB;

        // Initiate the array.
        $rating = array();

        // Get the normal rating.
        $sql = "SELECT *
                FROM {moodleoverflow_ratings}
                WHERE userid = ? AND postid = ? AND (rating = 1 OR rating = 2)";
        $rating['normal'] = $DB->get_record_sql($sql, [ $userid, $postid ]);

        // Return the rating if it is requested.
        if ($oldrating == RATING_DOWNVOTE || $oldrating == RATING_UPVOTE) {
            return $rating['normal'];
        }

        // Get the solved rating.
        $sql = "SELECT *
                FROM {moodleoverflow_ratings}
                WHERE userid = ? AND postid = ? AND rating = 3";
        $rating['solved'] = $DB->get_record_sql($sql, [ $userid, $postid ]);

        // Return the rating if it is requested.
        if ($oldrating == RATING_SOLVED) {
            return $rating['solved'];
        }

        // Get the helpful rating.
        $sql = "SELECT *
                FROM {moodleoverflow_ratings}
                WHERE userid = ? AND postid = ? AND rating = 4";
        $rating['helpful'] = $DB->get_record_sql($sql, [ $userid, $postid ]);

        // Return the rating if it is requested.
        if ($oldrating == RATING_HELPFUL) {
            return $rating['helpful'];
        }

        // Return all ratings.
        return $rating;
    }

    /**
     * Check if the rating can be changed.
     *
     * @param int $postid
     * @param int $rating
     * @param int $userid
     *
     * @return bool
     */
    private static function moodleoverflow_can_be_changed($postid, $rating, $userid) {
        global $CFG;

        // Check if the old read record exists.
        $old = self::moodleoverflow_check_old_rating($postid, $userid, $rating);
        if (!$old) {
            return false;
        }

        return true;
    }

    /**
     * Removes a rating record.
     *
     * @param int             $postid
     * @param int             $rating
     * @param int             $userid
     * @param \context_module $modulecontext
     *
     * @return bool
     */
    private static function moodleoverflow_remove_rating($postid, $rating, $userid, $modulecontext) {
        global $DB;

        // Check if the post can be removed.
        if (!self::moodleoverflow_can_be_changed($postid, $rating, $userid)) {
            return false;
        }

        // Get the old rating record.
        $oldrecord = self::moodleoverflow_check_old_rating($postid, $userid, $rating);

        // Trigger an event.
        $params = array(
            'objectid' => $oldrecord->id,
            'context' => $modulecontext,
        );
        $event = \mod_moodleoverflow\event\rating_deleted::create($params);
        $event->add_record_snapshot('moodleoverflow_ratings', $oldrecord);
        $event->trigger();

        // Remove the rating record.
        return $DB->delete_records('moodleoverflow_ratings', array('id' => $oldrecord->id));
    }

    /**
     * Add a new rating record.
     *
     * @param int             $moodleoverflowid
     * @param int             $discussionid
     * @param int             $postid
     * @param int             $rating
     * @param int             $userid
     * @param \context_module $mod
     *
     * @return bool|int
     */
    private static function moodleoverflow_add_rating_record($moodleoverflowid, $discussionid, $postid, $rating, $userid, $mod) {
        global $DB;

        // Create the rating record.
        $record = new \stdClass();
        $record->userid = $userid;
        $record->postid = $postid;
        $record->discussionid = $discussionid;
        $record->moodleoverflowid = $moodleoverflowid;
        $record->rating = $rating;
        $record->firstrated = time();
        $record->lastchanged = time();

        // Add the record to the database.
        $recordid = $DB->insert_record('moodleoverflow_ratings', $record);

        // Trigger an event.
        $params = array(
            'objectid' => $recordid,
            'context' => $mod,
        );
        $event = \mod_moodleoverflow\event\rating_created::create($params);
        $event->trigger();

        // Add the record to the database.
        return $recordid;
    }

    /**
     * Update an existing rating record.
     *
     * @param int             $postid
     * @param int             $rating
     * @param int             $userid
     * @param int             $ratingid
     * @param \context_module $modulecontext
     *
     * @return bool
     */
    private static function moodleoverflow_update_rating_record($postid, $rating, $userid, $ratingid, $modulecontext) {
        global $DB;

        // Update the record.
        $sql = "UPDATE {moodleoverflow_ratings}
                   SET postid = ?, userid = ?, rating=?, lastchanged = ?
                 WHERE id = ?";

        // Trigger an event.
        $params = array(
            'objectid' => $ratingid,
            'context' => $modulecontext,
        );
        $event = \mod_moodleoverflow\event\rating_updated::create($params);
        $event->trigger();

        return $DB->execute($sql, array($postid, $userid, $rating, time(), $ratingid));
    }

    /**
     * Check if a user can rate the post.
     *
     * @param object $post
     * @param \context_module   $modulecontext
     * @param null|int $userid
     *
     * @return bool
     */
    public static function moodleoverflow_user_can_rate($post, $modulecontext, $userid = null) {
        global $USER;
        if (!$userid) {
            // Guests and non-logged-in users can not rate.
            if (isguestuser() || !isloggedin()) {
                return false;
            }
            $userid = $USER->id;
        }

        // Check the capability.
        return capabilities::has(capabilities::RATE_POST, $modulecontext, $userid)
            && $post->reviewed == 1;
    }

    /**
     * Check if a discussion is marked as solved or helpful.
     *
     * @param int  $discussionid
     * @param bool $teacher
     *
     * @return bool|mixed
     */
    public static function moodleoverflow_discussion_is_locked($moodleoverflow, $discussionid, $solved = null, $helpful = null) : bool {
        global $DB, $USER;    

        // not lock if not used locking or pass deadline date
        if(empty($moodleoverflow->lockdiscussions) || ($moodleoverflow->lockuntildate && time() > $moodleoverflow->lockuntildate)) {
            return false;
        }

        if(empty($solved)) {
            $solved = self::moodleoverflow_discussion_is_solved($discussionid, true);
        }
        if(empty($helpful)) {
            $helpful = self::moodleoverflow_discussion_is_solved($discussionid, false);
        }
        $reputation = self::moodleoverflow_get_reputation($moodleoverflow->id, $USER->id, true);

        // Only apply locking for users that have not achieved the maximun reputation
        if($moodleoverflow->gradescalefactor >= $reputation) {
            if((($moodleoverflow->lockdiscussions == RATING_SOLVED) && !empty($solved)) ||
                (($moodleoverflow->lockdiscussions == RATING_HELPFUL) && !empty($helpful)) ||
                (($moodleoverflow->lockdiscussions > RATING_HELPFUL) && !empty($helpful) && !empty($solved))) {
                    return true;
            }
        }

        return false;
    }
    

    /**
     * Check if a discussion is marked as solved or helpful.
     *
     * @param int  $discussionid
     * @param int $rating the rating value to check (3, 4)
     *
     * @return bool|stdclass
     */
    public static function moodleoverflow_discussion_best_rating(int $discussionid, int $rating = RATING_SOLVED) {
        global $DB;

        $sql = "SELECT r.*, (SELECT SUM(rt.rating)   
                                FROM {moodleoverflow_ratings} rt 
                                WHERE rt.discussionid = r.discussionid AND rt.postid = r.postid
                                GROUP BY rt.postid
                                ) as total
                FROM {moodleoverflow_ratings} r 
                WHERE r.discussionid = :dissid AND r.rating = :rating 
                ORDER BY total DESC, r.firstrated ASC ";
        $params = ['dissid' => $discussionid, 'rating' => $rating];        
        if($recs = $DB->get_records_sql($sql, $params, 0, 1)) {
            return(reset($recs));
        }        
        return false;
    }
    
    /**
     * Sorts answerposts of a discussion with quicksort algorithm
     * @param array $posts  the posts that are being sorted
     * @param int   $low    the index from where the sorting begins
     * @param int   $high   the index until the array is being sorted
     */
    private static function moodleoverflow_quicksort_post_by_votes(array &$posts, $low, $high) {
        if ($low >= $high) {
            return;
        }
        $left = $low;
        $right = $high;
        $pivot = $posts[intval(($low + $high) / 2)]->votesdifference;
        do {
            while ($posts[$left]->votesdifference > $pivot) {
                $left++;
            }
            while ($posts[$right]->votesdifference < $pivot) {
                $right--;
            }
            if ($left <= $right) {
                $temp = $posts[$right];
                $posts[$right] = $posts[$left];
                $posts[$left] = $temp;
                $right--;
                $left++;
            }
        } while ($left <= $right);
        if ($low < $right) {
            self::moodleoverflow_quicksort_post_by_votes($posts, $low, $right);
        }
        if ($high > $left ) {
            self::moodleoverflow_quicksort_post_by_votes($posts, $left, $high);
        }
    }

}
