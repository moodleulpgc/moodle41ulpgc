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
 * Implementaton of the videolibsource_office365 plugin.
 *
 * @package    videolibsource
 * @subpackage office365
 * @copyright  2021 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/videolib/source/sourcebase.php');


/**
 * A rule representing the time limit. It does not actually restrict access, but we use this
 * class to encapsulate some of the relevant code.
 *
 * @copyright  2009 Tim Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class videolibsource_office365 extends videolib_source_base {

    /** @var string the repository name. */
    protected $reponame;

    /** @var class the repository instance. */
    protected $repository;
    
    /** @var bool the playlist flag. */
    protected $playlist;
    
    /**
     * Create an instance of this source for a particular videolib.
     * @param $videolib record from the database.
     */
    public function __construct($videolib, $parameters = null) {
        $this->reponame = $videolib->reponame;
        $this->playlist = $videolib->playlist;
        $this->repository = null;
        parent::__construct($videolib, $parameters);
    }
    

    /**
     * Gets the source repository, either file or directory.
     */
    public function set_repository() {
        global $DB;
        $this->repository = null;

        $this->onlyvisible = get_config('videolibsource_'.$this->source, 'allowhidden') ? false : true; 
        
        $instances = repository::get_instances(array('onlyvisible' => $this->onlyvisible, 
                                                        'type' => 'office365',
                                                        'currentcontext' => $this-context));
        foreach($instances as $repository) {
            if($repository->get_name() == $this->reponame) {
                $this->repository = $repository;
                return true;
            }
        }

        return false;
    }      
    
    
    public function instance_video_setup($context = null) {
        $this->context = $context;
        $this->setup = $this->set_repository();

    }
    
    public function get_displayed_content() {    
        global $CFG; 
        
        if(!$this->setup) {
            return '';
        }
        
        //$files = $this->repository->search('V-');
        //print_object($files);
        $found = $this->repository->search($this->searchpattern);
    
    
        $file_record = array(
            'contextid' => $this->context->id, 'component' => 'mod_videolib', 'filearea' => 'content',
            'sortorder' => 0, 'itemid' => $this->instanceid, 'filepath' => '/',
        );

        // get file storage
        $fs = get_file_storage();    
        $content = [];
        $videotag = '<video controls="true" width="600" rel="0" showinfo="0" controlsList="nodownload" class="vjs-tech videoitem">';
        $videotag .= '<source src="@link@" />  ';
        $videotag .= '</video><p><hr /></p>';
    
        if(count($found['list'])) {
            if($this->playlist) {
                $files = $found['list'];
            } else {
                $files = [$found['list'][0]];
            }
            $info = new stdClass();
            $info->num = ''; 
            $info->name = '';
            foreach($files as $i => $file) {
                $localfile = $this->moodle_file_from_source($file, $file_record, $fs);
                $path = '/'.$this->context->id.'/mod_videolib/content/'.$this->instanceid.'/'.$localfile->get_filepath().$localfile->get_filename();
                $fileurl = file_encode_url($CFG->wwwroot.'/pluginfile.php', $path, false, true);                
                $fname = basename($file['source'], '.mp4');  
                $info->num = $i + 1; 
                $info->name = str_replace('_', ' ', trim(strrchr(basename($file['source'], '.mp4'), '-'), ' -,.'));
                $title = \html_writer::tag('h4', get_string('playlistitem', 'videolib', $info), ['class' => 'videotitle']);
                $content[] = $title.html_writer::div(str_replace('@link@', $fileurl, $videotag),  
                                              'mediaplugin mediaplugin_videojs d-block',
                                              ['oncontextmenu' => 'return false;']);
            }
        } 
    
        return \html_writer::alist($content, ['class'  => 'playlist']);
    }    

}
