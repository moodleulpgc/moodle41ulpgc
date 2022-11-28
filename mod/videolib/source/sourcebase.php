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
 * Base class for videolib source plugins.
 *
 * @package   mod_videolib
 * @copyright 2019 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * Base class for videolib source plugins.
 *
 * Doesn't do anything on it's own -- it needs to be extended.
 * This class displays videolib sources.  Because it is called from
 * within /mod/videolib/source.php you can assume that the page header
 * and footer are taken care of.
 *
 * This file can refer to itself as source.php to pass variables
 * to itself - all these will also be globally available.  You must
 * pass "id=$cm->id" or q=$videolib->id", and "mode=sourcename".
 *
 * @copyright 2019 Enrique Castro @ ULPGC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class videolib_source_base {
    /** @var string the source name. */
    protected $source;
    /** @var int the search type. */
    protected $searchtype;
    /** @var string item search pattern. */
    protected $searchpattern;
    /** @var int the display mode. */
    protected $display;
    /** @var array the display options. */
    protected $displayoptions;
    /** @var bool the setup flag. */
    protected $setup = false;    

    /**
     * Create an instance of this source for a particular videolib.
     * @param $videolib record from the database.
     */
    public function __construct($videolib, $parameters = null) {
        $this->instanceid = $videolib->id;
        $this->source = $videolib->source;
        $this->searchtype = $videolib->searchtype;
        $this->searchpattern = $videolib->searchpattern;
        if($videolib->searchtype &&  $parameters) {
            $this->searchpattern = str_replace(array_keys($parameters),array_values($parameters), $videolib->searchpattern);
        }
        $this->display = $videolib->display;
        $this->displayoptions = empty($videolib->displayoptions) ? array() : unserialize($videolib->displayoptions);
    }

    /**
     * Localize searchpattern with module instance data.
     * @param $parameters associative array with param names and values
     */
    public function get_processed_searchpattern($parameters) {
        if($this->searchtype &&  $parameters) {
            $this->searchpattern = str_replace(array_keys($parameters),array_values($parameters), $this->searchpattern);
        }
        
        return $this->searchpattern;
    }
    

    public function instance_video_setup($context = null) {    
        $this->context = $context;
        $this->setup = false;
    }
    
    public function get_displayed_content() {    
        return '';
    }
    
    /**
     * Check if repository allow file upload from moodle to remote. Override to change.
     */
    public function allow_uploads() {
        return false;
    }

    /**
     * Check if repository allow remote file deletion from moodle. Override to change.
     */
    public function allow_deletes() {
        return false;
    }    
    
    
    /**
     * Override this function to displays the source.
     * @param $cm the course-module for this videolib.
     * @param $course the coures we are in.
     * @param $videolib this videolib.
     */
    public function show() {
        if($this->setup) {
            if(!$content = $this->get_displayed_content()) {
                $content = get_string('emptymessage', 'videolib', $this->searchpattern);
            }
        } else {
            $content = get_string('defaultmessage', 'videolib');
        }
   
        return html_writer::div($content, ' videolibvideo');
    }    

    /**
     * Get an existing moodle file by reference .
     * @param $source record from the database.
     * @param $array file_record 
     */
    public function get_moodle_file($reference, $file_record = null) {
        $packedref = $reference;
        if(!base64_decode($reference, true)) {
            $packedref = file_storage::pack_reference($file_record); 
        } 

        return $this->repository->get_moodle_file($packedref);
    }
    
    /**
     * Override this function to get the source.
     */
    public function moodle_file_from_source($externalfile, $file_record, $fs) {
    
        if(!isset($externalfile['source'])) {
            //this is a directory or something else, skip
            return false;
        }
    
        $source = $externalfile['source'];
        $filename = basename($source);
        $file_record['filename'] = $filename;
    
        $reference = $this->repository->get_file_reference($source);
        
        if(!$file = $this->get_moodle_file($reference, $file_record)) {
            $file = $fs->create_file_from_reference($file_record, $this->repository->id, $reference);
        } else {
            $this->repository->sync_reference($file);
        }
    
        return $file;
    }
    
    
}
