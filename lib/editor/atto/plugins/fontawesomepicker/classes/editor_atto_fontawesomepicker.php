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
 * @package    atto_fontawesomepicker
 * @copyright  2020 DNE - Ministere de l'Education Nationale 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace atto_fontawesomepicker;

/**
 * Class editor_atto_fontawesomepicker.
 *
 * This class contain functions to create a new configuration text field.
 *
 * @package    atto_fontawesomepicker
 * @copyright  2020 DNE - Ministere de l'Education Nationale 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class editor_atto_fontawesomepicker extends \admin_setting_configtext {
    private $rows;
    private $cols;
    private $pathfonts;

    /**
     * @param string $name
     * @param string $visiblename
     * @param string $description
     * @param mixed $defaultsetting string or array
     * @param mixed $paramtype
     * @param string $cols The number of columns to make the editor
     * @param string $rows The number of rows to make the editor
     * @param array $pathfonts The path list to locate the svg files
     */
    public function __construct($name, $visiblename, $description, $defaultsetting, $paramtype=PARAM_RAW, $cols='60', $rows='8', $pathfonts) {
        $this->rows         = $rows;
        $this->cols         = $cols;
        $this->pathfonts    = $pathfonts;

        parent::__construct($name, $visiblename, $description, $defaultsetting, $paramtype);
    }

    /**
     * Returns an XHTML string for the editor.
     *
     * @param string $data
     * @param string $query
     * @return string XHTML string for the editor
     */
    public function output_html($data, $query = '') {
        global $OUTPUT;

        $default = $this->get_defaultsetting();
        $defaultinfo = $default;
        if (!is_null($default) and $default !== '') {
            $defaultinfo = "\n".$default;
        }

        $pathfonts = $this->build_data_fonts_from_config($this->pathfonts);

        $error = null;
        $faclasses = [];
        if (is_array($pathfonts)) {
            foreach ($pathfonts as $type => $path) {
                if (file_exists($path)) {
                    $versionfontawesome = "4";
                    $cssfontawesome = file_get_contents($path);
                    if (strpos($cssfontawesome, 'glyph-name="file_alt"') === false) {
                        $versionfontawesome = "5+";
                    }

                    $firstpartpreg = null;
                    preg_match_all('|glyph-name="(.*)".*unicode="(.*)"|sU', trim($cssfontawesome), $firstpartpreg, PREG_SET_ORDER);

                    foreach ($firstpartpreg as $item) {
                        if (isset($item[1]) && $item[2] && substr($item[1], 0, 1) !== '_' ) {
                            if (strpos($item[1], 'uniF') !== false
                                || strpos($item[1], '.') !== false
                                || strpos($item[1], 'lessequal') !== false
                                || $this->start_with($item[1],"_")
                                || $this->start_with($item[1],"f0")
                                || $this->start_with($item[1],"f1")
                                || $this->start_with($item[1],"f2")){
                                continue;
                            }
                            if ($versionfontawesome == "4") {
                                $item[1] = str_replace("_", "-", $item[1]);
                                if (array_key_exists($item[1], $this->map_correct_icon_name())) {
                                    $item[1] = $this->map_correct_icon_name()[$item[1]];
                                }
                            }

                            $faclasses[] = [
                                "type" => $type,
                                "name" => $item[1],
                                "unicode" => htmlentities($item[2])
                            ];
                        }
                    }
                } else {
                    $error = get_string("error1","atto_fontawesomepicker");
                }
            }
        }

        $name = array_column($faclasses, 'name');
        array_multisort($name, SORT_ASC, $faclasses);

        $context = (object) [
            'cols' => $this->cols,
            'rows' => $this->rows,
            'id' => $this->get_id(),
            'name' => $this->get_full_name(),
            'value' => $data,
            'forceltr' => $this->get_force_ltr(),
            'error' => $error,
            'icons' => $faclasses
        ];
        $element = $OUTPUT->render_from_template('atto_fontawesomepicker/setting_configfontawesome', $context);

        return format_admin_setting($this, $this->visiblename, $element, $this->description, true, '', $defaultinfo, $query);
    }

    /**
     * Returns a data array for the FontAwesome plugin config.
     *
     * @param string $value
     * @return array|false 
     */    
    public function build_data_fonts_from_config(string $value) {
        $elements = explode('||', $value);

        $pathfonts = [];
        for ($i = 0; $i < count($elements); $i++) {
            if (strpos($elements[$i], ':') === false) {
                continue;
            }

            $icopath = explode(':', $elements[$i]);
            $pathfonts[trim($icopath[0])] = $icopath[1];
        }

        if (count($pathfonts) == 0) {
            return false;
        }

        return $pathfonts;
    }

    /**
     * List of FontAwesome icons in conflict between the version 4 and the version 5.
     *
     * @return array
     */
    private function map_correct_icon_name() {
        return [
            'arrow-circle-alt-left' => 'arrow-circle-o-left',
            'ban-circle' => 'ban',
            'beaker' => 'flask',
            'bell' => 'bell-o',
            'bell-alt' => 'bell',
            'bitbucket-sign' => 'bitbucket-square',
            'bookmark-empty' => 'bookmark-o',
            'calendar-empty' => 'calendar-o',
            'check-empty' => 'check-square-o',
            'check-minus' => 'check-square',
            'check-sign' => 'calendar-o',
            'chevron-sign-down' => 'chevron-circle-down',
            'chevron-sign-left' => 'chevron-circle-left',
            'chevron-sign-right' => 'chevron-circle-right',
            'chevron-sign-up' => 'chevron-circle-up',
            'circle-arrow-down' => 'arrow-circle-down',
            'circle-arrow-left' => 'arrow-circle-left',
            'circle-arrow-right' => 'arrow-circle-right',
            'circle-arrow-up' => 'arrow-circle-up',
            'circle-blank' => 'circle-o',
            'collapse-top' => 'caret-square-o-up',
            'collapse' => 'caret-square-o-down',
            'collapse-alt' => 'minus-square-o',
            'comment-alt' => 'comment-o',
            'comments-alt' => 'comments-o',
            'dot-circle-alt' => 'dot-circle-o',
            'double-angle-down' => 'angle-double-down',
            'double-angle-left' => 'angle-double-left',
            'double-angle-right' => 'angle-double-right',
            'double-angle-up' => 'angle-double-up',
            'download' => 'arrow-circle-o-down',
            'download-alt' => 'download',
            'dribble' => 'dribbble',
            'edit-sign' => 'pencil-square',
            'ellipsis-horizontal' => 'ellipsis-h',
            'ellipsis-vertical' => 'ellipsis-v',
            'envelope' => 'envelope-o',
            'envelope-alt' => 'envelope',
            'exclamation-sign' => 'exclamation-circle',
            'expand-alt' => 'expand',
            'eye-close' => 'eye-slash',
            'eye-open' => 'eye',
            'facebook-sign' => 'facebook-square',
            'facetime-video' => 'video-camera',
            'file-alt' => 'file-o',
            'file-text-alt' => 'file-text-o',
            'flag-alt' => 'flag-o',
            'folder-close' => 'folder',
            'folder-close-alt' => 'folder-o',
            'folder-open-alt' => 'folder-open-o',
            'food' => 'cutlery',
            'frown' => 'frown-o',
            'fullscreen' => 'arrows-alt',
            'github-sign' => 'github-square',
            'google-plus-sign' => 'google-plus-square',
            'h-sign' => 'h-square',
            'hand-down' => 'hand-o-down',
            'hand-left' => 'hand-o-left',
            'hand-right' => 'hand-o-right',
            'hand-up' => 'hand-o-up',
            'hdd' => 'hdd-o',
            'heart-empty' => 'heart-o',
            'hospital' => 'hospital-o',
            'indent-left' => 'outdent',
            'indent-right' => 'indent',
            'info-sign' => 'info-circle',
            'keyboard' => 'keyboard-o',
            'lemon' => 'lemon-o',
            'light-bulb' => 'lightbulb-o',
            'linkedin-sign' => 'linkedin-square',
            'meh' => 'meh-o',
            'microphone-off' => 'microphone-slash',
            'minus-sign' => 'minus-circle',
            'minus-sign-alt' => 'minus-square',
            'move' => 'arrows',
            'off' => 'power-off',
            'ok' => 'check',
            'ok-circle' => 'check-circle-o',
            'ok-sign' => 'check-circle',
            'ol' => 'list-ol',
            'paper-clip' => 'paperclip',
            'phone-sign' => 'phone-square',
            'picture' => 'picture-o',
            'pinterest-sign' => 'pinterest-square',
            'play-sign' => 'play-circle',
            'plus-sign' => 'plus-circle',
            'pushpin' => 'thumb-tack',
            'question-sign' => 'question-circle',
            'remove-circle' => 'times-circle-o',
            'remove-sign' => 'times-circle',
            'resize-full' => 'expand',
            'resize-horizontal' => 'arrows-h',
            'resize-small' => 'compress',
            'resize-vertical' => 'arrows-v ',
            'screenshot' => 'crosshairs',
            'share-sign' => 'share-square',
            'sign-blank' => 'square',
            'signin' => 'sign-in',
            'signout' => 'sign-out',
            'smile' => 'smile-o',
            'sort-by-alphabet' => 'sort-alpha-asc',
            'sort-by-attributes' => 'sort-amount-asc',
            'sort-by-attributes-alt' => 'sort-amount-desc',
            'sort-by-order' => 'sort-numeric-asc',
            'sort-by-order-alt' => 'sort-numeric-desc',
            'star-empty' => 'star-o',
            'stackexchange' => 'stack-overflow',
            'sun' => 'sun-o',
            'thumbs-down-alt' => 'thumbs-o-down',
            'thumbs-up-alt' => 'thumbs-o-up',
            'time' => 'clock-o',
            'tumblr-sign' => 'tumblr-square',
            'twitter-sign' => 'twitter-square',
            'ul' => 'list-ul',
            'upload-alt' => 'upload',
            'warning-sign' => 'exclamation-triangle',
            'xing-sign' => 'xing-square ',
            'youtube-sign' => 'youtube-square',
            'zoom-in' => 'search-plus',
            'zoom-out' => 'search-minus',
        ];
    }

    /**
     * Try to find the needle in a string.
     *
     * @param $haystack
     * @param $needle
     * @return bool
     */
    private function start_with($haystack, $needle) {
        $length = strlen($needle);
        return substr($haystack, 0, $length ) === $needle;
    }
}
