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
 * Implementaton of the librarysource_sudocument plugin.
 *
 * @package    librarysource
 * @subpackage sudocument
 * @copyright  2019 Enrique  Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/library/source/sourcebase.php');


/**
 * A wrapper for a searchabe repository class to manage Library files
 *
 * @copyright  2019 Enrique Castro @ ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class librarysource_sudocument extends library_source_base {

    /** @var stdclass site config. */
    protected $config;

    /**
     * Set the use of repository plugin, override to change.
     */
    public function use_repository() {
        //// TODO  
        // Check API working, send error if not
        
        $this->config = get_config('librarysource_sudocument');
    
        return false;
    }

    /**
     * Gets the source repository, either file or directory.
     */
    public function set_repository() {
        $this->repository = null;
    }

    /*
    
    https://apinodo.ulpgc.es/api/
    items?property[0][property]=438&
    property[0][type]=eq&property[0][text]=43683&
    property[1][property]=438&property[1][type]=in&property[1][text]=4036
    
    
    https://apinodo.ulpgc.es/api/media/29380
    
    */

    /**
     * Gets the source repository, either file or directory.
     */
    private function curl_connection_call($apifun, $request) {
    
        if (! function_exists( 'curl_init' )) {
            throw new Exception( 'PHP cURL requiered' );
        }  
        
        $this->config = get_config('librarysource_sudocument');
        
        $url = $this->config->apiurl; 
        if(substr($url, -1) != '/') {
            $url .=  '/';
        }
        if(substr($apifun, 0, 1) == '/') {
            $apifun = substr($apifun, 1);
        }
        
        $header[] = "Content-Type: multipart/form-data";    

        $get = $url.$apifun; 
        if($request) { 
            $get .= '?'.$request;
        }
        
        //print_object("call REST  ".$get);
        //print_object("call REST  ".$url.$apifun);
        //print_object($request);
        //print_object(" ------------- request  ---------------  ");
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $get);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        //curl_setopt($ch, CURLOPT_POST, true);
        //curl_setopt( $ch, CURLOPT_POSTFIELDS, $request );
       

       
       
        $errors = array();
        $rawresponse = curl_exec( $ch );
        if ($errno = curl_errno($ch)) {
            $errors[] = curl_error($ch);
        }
        if ($rawresponse === false) {
            $error = 'request failed: ' . s( curl_error( $ch ) );
            curl_close( $ch );
            $this->curl_call_print($apifun, $error);
            $errors[] = $error;
        } 
        curl_close($ch);
        
        if($errors) {
            return $errors;
        } 
        if($rawresponse) {
        
        
            $res = json_decode( $rawresponse );
            /*
            print_object($res);
            
            $result = $this->parse_response($res);
            
            print_object($result[0]);
            
*/
            
            //$this->curl_call_print($apifun, $res);
            return $res;
        }
    }
    
    private function parse_response($response) {
    
        $result = array();
        foreach($response as $item => $res) {
            $res = (array)$res;
            $objects = array();
            foreach(array_keys($res) as $key) {
                $parts = explode(':', $key);
                if(count($parts) == 2) {
                    $objects[$parts[0]] = $parts[0];
                }
             }
             
             foreach($objects as $okey) {
                $object = new stdClass();
                $name = ($okey === 'o') ? 'item' : $okey;
                foreach($res as $key => $val) {
                    
                    $parts = explode(':', $key);
                    if(count($parts) == 2 && $parts[0] == $okey) {
                        $object->{$parts[1]} = $val;
                        unset($res[$key]);
                        //print_object(" moved $key");
                    }                
                }
                $res[$name] = $object;
            }
            $response[$item] = $res;
        }
        return $response;
    }
    
    
    private function extract_response_data($response) {    
    
        $result = array();
        foreach($response as $item => $res) {
            $data = new stdClass();
            
            if(property_exists($res, 'o:id')) {
                $data->id = $res->{'o:id'};
            }

            if(property_exists($res, 'o:media')) {
                $data->media = $res->{'o:media'};
            }

            if(property_exists($res, 'o:modified')) {
                $data->timemodified = strtotime($res->{'o:modified'}->{'@value'});
            }

            if(property_exists($res, 'dcterms:title')) {
                $data->title = $res->{'dcterms:title'}[0]->{'@value'};
            }

            if(property_exists($res, 'dcterms:date')) {
                $data->date = $res->{'dcterms:date'}[0]->{'@value'};
            }

            if(property_exists($res, 'dcterms:identifier')) {
                foreach($res->{'dcterms:identifier'} as $index => $identifier) {
                    if($identifier->{'type'} == 'uri') {
                        $data->identifier = $res->{'dcterms:identifier'}[$index]->{'@id'};
                        break;
                    }
                }
            }

            $result[] = $data;
        }
    
        return $result;
    }
    
    
    private function curl_call_print($fun, $res) {
        echo "<h4>Funtion $fun response</h4>\n";
        echo "<pre>";
        if (is_string($res)) {
            echo s($res);
        } else {
            $opt = JSON_PRETTY_PRINT | JSON_UNESCAPED_LINE_TERMINATORS | JSON_UNESCAPED_SLASHES;
            echo s(json_encode($res, $opt));
        }
        echo '</pre>';
    }

    
    /**
     * Get a string suitable for API GEt call 
     * @param $queryterms array of query term arrays
     */
    private function get_api_encoded_query(array $queryterms) {    
    
        $terms = array();
        $i = 0;
        foreach($queryterms as $query) {
            $property = array();
            foreach($query as $key => $value) {
                $property[] = "property[$i][$key]=$value";
            }
            $terms[] = implode('&',$property);  
            $i++;
        }
        return implode('&', $terms);  
    
    /*    
    https://apinodo.ulpgc.es/api/items?
    property[0][property]=438&property[0][type]=eq&property[0][text]=43683&
    property[1][property]=438&property[1][type]=in&property[1][text]=4036
      */
    }



    /**
     * Searches remote repository for matching pattern and locates the remote entry.
     * @param $search record from the database.
     */
    public function get_remote_resource($search) {
        $apifun = 'items';
        $request = array();

        $sep = $this->config->separator;

        //print_object("$search");
        //print_object($this->reponame);
        //print_object($this->pathname);
        //print_object(" ----- search ...");

        if(strpos($search, $this->config->handleurl) !== false) {
            $request[] = array('property' => 10,
                                'type' => 'eq',
                                'text' => $search);
        } else {
            $parts = explode($sep, $search);
            if(count($parts) > 1) {
                if(isset($parts[0]) && $parts[0])  {
                    $request[] = array('property' => 438,
                                        'type' => 'in',
                                        'text' => $parts[0]);
                }

                if(isset($parts[1]) && $parts[1])  {
                    $request[] = array('property' => 438,
                                        'type' => 'eq',
                                        'text' => $parts[1]);
                }

                if(isset($parts[2]) && $parts[2])  {
                    $request[] = array('property' => 7,
                                        'type' => 'eq',
                                        'text' => $parts[2]);
                }
            } else {
                if($parts[0]) {
                    $request[] = array('property' => 438,
                                        'type' => 'eq',
                                        'text' => $parts[0]);
                }
            }
        }

        //print_object(($request));

        $request = $this->get_api_encoded_query($request);
        if($this->pathname) {
            $request = 'item_set_id='.$this->pathname.'&'.$request;
        }
        //print_object($request);

        $data = $this->curl_connection_call($apifun, $request);

        //print_object($data);

        $data = $this->extract_response_data($data);
        //print_object($data);
        // if there are several matches just get the latest
        $resource = reset($data);
        if(count($data) > 1) {
            foreach($data as $res) {
                if($res->timemodified > $resource->timemodified) {
                    $resource = $res;
                }
            }
        }
        $this->remote = $resource;

        return $this->remote;
/*
SUdocument@ resource
stdClass Object
(
    [id] => 226759
    [media] => Array
        (
            [0] => stdClass Object
                (
                    [@id] => https://apinodo.ulpgc.es/api/media/226760
                    [o:id] => 226760
                )

            [1] => stdClass Object
                (
                    [@id] => https://apinodo.ulpgc.es/api/media/226761
                    [o:id] => 226761
                )

            [2] => stdClass Object
                (
                    [@id] => https://apinodo.ulpgc.es/api/media/226762
                    [o:id] => 226762
                )

        )

    [timemodified] => 1707308834
    [title] => Apuntes de turismo y desarrollo sostenible (prueba)
    [date] => 2012
    [identifier] => https://hdl.handle.net/11730/sudoc/1541
)
SUdocument@ resource
*/
    }




    /**
     * Get a file by searching repository for matching pattern .
     * @param $search record from the database.
     */
    public function search_files($search) {

        if(empty($this->remote->id)) {
            $resource = $this->get_remote_resource($search);
        }

        $files = array();
        if($resource->media) {
            foreach($resource->media as $idx => $mediaobj) {
                $mediaid = $mediaobj->{'o:id'};
                if($media = $this->curl_connection_call('media/'.$mediaid, '')) {
                    //print_object($media);
                    $file = new stdClass();
                    $file->id = $media->{'o:id'};
                    $file->filename = $media->{'o:source'};
                    $file->source = $media->{'o:filename'};
                    $timemodified = strtotime($media->{'o:modified'}->{'@value'});
                    $file->timemodified = max($resource->timemodified, $timemodified);
                    $file->handle = $resource->identifier;
                    $file->title = $resource->title;
                    $files[] = $file;
                    $this->remote->media[$idx] = $file;
                }
            }
        }
        //print_object("SUdocument@ resource");
        //print_object($this->remote);
        //print_object("SUdocument@ resource");
        return $files;
    }
    
    /**
     * Get a list of files within a folder .
     * @param string $pathname folder to list.
     */
    public function list_files($pathname) {
        $files = array();
        return $files;
    }
    
    /**
     * Override this function to get the source.
     */
    public function moodle_file_from_source($externalfile, $file_record, $fs) {

        if(!isset($externalfile->source) || !isset($externalfile->source)) {
            //this is a directory or something else, skip
            return false;
        }

        $file_record['filename'] = $externalfile->filename;
        
        $externalfile->fullurl = $this->config->linkurl.'?id='.$externalfile->source;
        $headers[] = "Content-Type: application/pdf";    
        //redirect($externalfile->url);
        //$fs->get_file_instance(stdClass $filerecord)  
        //$reference = $this->repository->get_file_reference($source);
        /*
        $options = array('headers' => $headers);
        if(!$file = $fs->get_file($file_record['contextid'], $file_record['component'], 
                                    $file_record['filearea'], $file_record['itemid'], 
                                    $file_record['filepath'], $file_record['filename'])) {
            //$file = $fs->create_file_from_url($file_record, $externalfile->fullurl, $options, true);
        } else {
            // if file exists, check date for update if required
            if($externalfile->timemodified > $file->get_timemodified()) {
                //internal is oudated, substitute
                $file->delete();
                //$file = $fs->create_file_from_url($file_record, $externalfile->fullurl, $options, true);
            }
        }
        */

        return $externalfile;
    }
}
