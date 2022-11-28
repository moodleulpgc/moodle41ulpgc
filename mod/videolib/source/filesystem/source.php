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
 * Implementaton of the videolibsource_filesystem plugin.
 *
 * @package    videolibsource
 * @subpackage filesystem
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
class videolibsource_filesystem extends videolib_source_base {

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
        
        $instances = repository::get_instances(array('onlyvisible'=>$this->onlyvisible, 
                                                        'type'=>'filesystem',
                                                        'currentcontext' => $this->context));
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

    

    /**
     * Check if repository allow file upload from moodle to remote. Override to change.
     */
    public function allow_uploads() {
        return true;
    }

    /**
     * Check if repository allow remote file deletion from moodle. Override to change.
     */
    public function allow_deletes() {
        return true;
    }

    /**
     * Get a list of all subdirectories, recursively, within the given path
     * @param string $dir path to scan
     */
    public function get_subdirs($dir) {
        $subDir = array();
        $directories = array_filter(glob($dir, GLOB_MARK|GLOB_ONLYDIR), 'is_dir');
        $subDir = array_merge($subDir, $directories);
        foreach ($directories as $directory) $subDir = array_merge($subDir, $this->get_subdirs($directory.'*'));
        return $subDir;
    }

    
    /**
     * Get a list of all subdirectories, recursively, within the given path
     * @param string $dir path to scan
     */
    public function get_numbered_filename($path, $fname, $extension) {
        $i = 1;
        while(file_exists($path.$fname."($i).".$extension)) {
            $i++;
        }
    
        return $path.$fname."($i).".$extension;
    }
    
    
    /**
     * Get a list of files within a folder .
     * @param array $draftfiles array of files as returned from get_area_files
     * @param string $insertpath root path where to insert files in local filesystem
     * @param string $updatepode how to behave if file already exists
     */
    public function save_uploaded_files($draftfiles, $insertpath, $updatemode) {
    
        $rootpath = rtrim($insertpath, '/');
        
        $count = 0;
        
        foreach($draftfiles as $file) {
            if($file->is_directory()) {
                $file->delete();
                continue;
            }
            $filepath = $file->get_filepath();
            check_dir_exists($rootpath.$filepath);
            $filename = $file->get_filename();
            $fname = pathinfo($filename, PATHINFO_FILENAME);
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            $path = $rootpath.$filepath.$filename;
            $copyfile = true;
            if(file_exists($path)) {
                if($updatemode == LIBRARY_FILEUPDATE_REOLD) {
                    //rename old file to new filename and add new
                    $newname = $this->get_numbered_filename($rootpath.$filepath, $fname, $extension);
                    rename($path, $newname);

                } elseif($updatemode == LIBRARY_FILEUPDATE_RENEW) {
                    //rename new file to new filename, keep existing
                    $path = $this->get_numbered_filename($rootpath.$filepath, $fname, $extension);

                } elseif ($updatemode == LIBRARY_FILEUPDATE_NO) {
                    $copyfile = false;
                }
            }
            
            if($copyfile) {
                $file->copy_content_to($path);
                $count++;
            }
            // remove draftfile
            $file->delete();
        }
        return $count;
    }

    /**
     * Deletes files by reference
     * @param array $draftfiles array of files as returned from get_area_files
     */
    public function delete_selected_files($draftfiles) {
        $count = 0;
            foreach($draftfiles as $file) {
                if($file->is_directory()) {
                    $file->delete();
                    continue;
                }
                $path = $this->repository->get_rootpath().ltrim($file->get_reference(), '/');
                if(file_exists($path)) {
                    unlink($path);
                    $count++;
                }
                $file->delete();
            }
        return $count;
    }
    
    /**
     * Gets the absolute path of a library folder
     */
    public function get_absolute_path() {
        $path = rtrim($this->repository->get_rootpath().$this->pathname, '/'); 
        return $path.'/';
    }
    
    /**
     * Returns a list of all subdirectories in the repository
     */
    public function get_folders() {
        $root = rtrim($this->repository->get_rootpath(), '/');
        $folders = array();
        foreach($this->get_subdirs($this->repository->get_rootpath()) as $dir) {
            $folders[$dir] = str_replace($root, '' , $dir);
        }
        ksort($folders);
        return $folders;
    }
    
    public function rglob($pattern='*', $path='', $flags = 0) {
        $paths=glob($path.'*', GLOB_MARK|GLOB_ONLYDIR|GLOB_NOSORT);
        $files=glob($path.$pattern, $flags);
        foreach ($paths as $path) {
        $files=array_merge($files,rglob($pattern, $path, $flags));
        }
        return $files;
    }      
    
    
}
