YUI.add('moodle-mod_offlinequiz-offlinequizquestionbank', function (Y, NAME) {

// This file is part of mod_offlinequiz for Moodle - http://moodle.org/
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
 * Add questions from question bank functionality for a popup in offlinequiz editing page.
 *
 * @package       mod
 * @subpackage    offlinequiz
 * @author        Juergen Zimmer <zimmerj7@univie.ac.at>
 * @copyright     2015 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @since         Moodle 2.8+
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

var CSS = {
    QBANKLOADING:       'div.questionbankloading',
    ADDQUESTIONLINKS:   '.menu [data-action="questionbank"]',
    ADDTOQUIZCONTAINER: 'td.addtoofflinequizaction',
    PREVIEWCONTAINER:   'td.previewaction',
    SEARCHOPTIONS:      '#advancedsearch'
};

var PARAMS = {
    PAGE: 'addonpage',
    HEADER: 'header'
};

var POPUP = function() {
    POPUP.superclass.constructor.apply(this, arguments);
};

Y.extend(POPUP, Y.Base, {
    loadingDiv: '',
    dialogue: null,
    addonpage: 0,
    searchRegionInitialised: false,
    tags: '',

    create_dialogue: function() {
        // Create a dialogue on the page and hide it.
        var config = {
            headerContent : '',
            bodyContent : Y.one(CSS.QBANKLOADING),
            draggable : true,
            modal : true,
            centered: true,
            width: null,
            visible: false,
            postmethod: 'form',
            footerContent: null,
            extraClasses: ['mod_offlinequiz_qbank_dialogue']
        };
        this.dialogue = new M.core.dialogue(config);
        this.dialogue.bodyNode.delegate('click', this.link_clicked, 'a[href]', this);
        this.dialogue.hide();

        this.loadingDiv = this.dialogue.bodyNode.getHTML();

        Y.later(100, this, function() {this.load_content(window.location.search);});
    },

    initializer : function() {
        if (!Y.one(CSS.QBANKLOADING)) {
            return;
        }
        this.create_dialogue();
        Y.one('body').delegate('click', this.display_dialogue, CSS.ADDQUESTIONLINKS, this);
    },

    display_dialogue : function (e) {
        e.preventDefault();
        this.dialogue.set('headerContent', e.currentTarget.getData(PARAMS.HEADER));

        this.addonpage = e.currentTarget.getData(PARAMS.PAGE);
        var controlsDiv = this.dialogue.bodyNode.one('.modulespecificbuttonscontainer');
        if (controlsDiv) {
            var hidden = controlsDiv.one('input[name=addonpage]');
            if (!hidden) {
                hidden = controlsDiv.appendChild('<input type="hidden" name="addonpage">');
            }
            hidden.set('value', this.addonpage);
        }

        this.initialiseSearchRegion();
        this.dialogue.show();
    },

    load_content : function(queryString) {
        this.dialogue.bodyNode.append(this.loadingDiv);

        Y.io(M.cfg.wwwroot + '/mod/offlinequiz/questionbank.ajax.php' + queryString, {
            method: 'GET',
            on: {
                success: this.load_done,
                failure: this.load_failed
            },
            context: this
        });

    },

    load_done: function(transactionid, response) {
        var result = JSON.parse(response.responseText);
        if (!result.status || result.status !== 'OK') {
            // Because IIS is useless, Moodle can't send proper HTTP response
            // codes, so we have to detect failures manually.
            this.load_failed(transactionid, response);
            return;
        }


        this.dialogue.bodyNode.setHTML(result.contents);
        if(Y.one('#qbheadercheckbox')) {
             Y.one('#qbheadercheckbox').on('click', function(e) {
                if(e._currentTarget.checked === true) {
                    Y.all('.questionbankformforpopup .select-multiple-checkbox').set('checked', 'true');
                }
                else {
                    Y.all('.questionbankformforpopup .select-multiple-checkbox').set('checked', '');
                }
            });
        }

        Y.use('moodle-qbank_editquestion-chooser', function() {M.question.init_chooser({});});
        this.dialogue.bodyNode.one('form').delegate('change', this.options_changed, '.searchoptions', this);
        if (this.dialogue.visible) {
            Y.later(0, this.dialogue, this.dialogue.centerDialogue);
        }
        require(
                [
                        'jquery',
                    'core/form-autocomplete'
                ],
                function(
                    $,
                    AutoComplete
                ) {
                    var root = $('[class="tag-condition-container"]');
                    var selectElement = root.find('[data-region="tag-select"]');
                    var loadingContainer = root.find('[data-region="overlay-icon-container"]');
                    var placeholderText = M.str.offlinequiz["filterbytags"];
                    var noSelectionText = M.str.offlinequiz["notagselected"];

                    AutoComplete.enhance(
                        selectElement, // Element to enhance.
                        false, // Don't allow support for creating new tags.
                        false, // Don't allow AMD module to handle loading new tags.
                        placeholderText, // Placeholder text.
                        false, // Make search case insensitive.
                        true, // Show suggestions for tags.
                        noSelectionText // Text when no tags are selected.
                    ).always(function() {
                        // Hide the loading icon once the autocomplete has initialised.
                        loadingContainer.addClass('hidden');
                    });

        });
        Y.on(M.core.event.FILTER_CONTENT_UPDATED,this.options_changed, this);
        this.searchRegionInitialised = false;
        if (this.dialogue.get('visible')) {
            this.initialiseSearchRegion();
        }

        this.dialogue.fire('widget:contentUpdate');
        // TODO MDL-47602 really, the base class should listen for the even fired
        // on the previous line, and fix things like makeResponsive.
        // However, it does not. So the next two lines are a hack to fix up
        // display issues (e.g. overall scrollbars on the page). Once the base class
        // is fixed, this comment and the following four lines should be deleted.
        if (this.dialogue.get('visible')) {
            this.dialogue.hide();
            this.dialogue.show();
        }
    },

    load_failed: function() {
    },

    link_clicked: function(e) {
        // Add question to offlinequiz. mofify the URL, then let it work as normal.
        if (e.currentTarget.ancestor(CSS.ADDTOQUIZCONTAINER)) {
            e.currentTarget.set('href', e.currentTarget.get('href') + '&addonpage=' + this.addonpage);
            return;
        }

        // Question preview. Needs to open in a pop-up.
        if (e.currentTarget.ancestor(CSS.PREVIEWCONTAINER)) {
            openpopup(e, {
                url: e.currentTarget.get('href'),
                name: 'questionpreview',
                options: 'height=600,width=800,top=0,'*
                + 'left=0,menubar=0,location=0,scrollbars,'
                + 'resizable,toolbar,status,directories=0,'
                + 'fullscreen=0,dependent'
            });
            return;
        }

        // Click on expand/collaspse search-options. Has its own handler.
        // We should not interfere.
        if (e.currentTarget.ancestor(CSS.SEARCHOPTIONS)) {
            return;
        }

        // Anything else means reload the pop-up contents.
        e.preventDefault();
        this.load_content(e.currentTarget.get('search'));
    },

    options_changed: function(e) {
        if(e && e.currentTarget && e.currentTarget.get) {
            e.preventDefault();
            this.load_content('?' + Y.IO.stringify(e.currentTarget.get('form')));
        } else {
            var classes = e.nodes._nodes[0].className;
            if(classes.includes("form-autocomplete-selection") && classes.includes("form-autocomplete-multiple")) {
                var newtags = this.get_tags(e.nodes._nodes[0].children);
                if(newtags !== this.tags) {
                    this.tags = newtags;
                    var displayoptions = Y.IO.stringify(Y.one('#displayoptions'));
                    this.load_content('?' + displayoptions );
                    window.onbeforeunload = null;
                }
            }
        }
    },

    get_tags: function(nodes) {
        var result = '';
        for(node in nodes) {
            result += nodes[node].textContent;
        }
        return result;
    },

    initialiseSearchRegion: function() {
        if (this.searchRegionInitialised === true) {
            return;
        }
        if (!Y.one(CSS.SEARCHOPTIONS)) {
            return;
        }

        M.util.init_collapsible_region(Y, "advancedsearch", "question_bank_advanced_search",
                M.util.get_string('clicktohideshow', 'moodle'));
        this.searchRegionInitialised = true;
    }
});

M.mod_offlinequiz = M.mod_offlinequiz || {};
M.mod_offlinequiz.offlinequizquestionbank = M.mod_offlinequiz.offlinequizquestionbank || {};
M.mod_offlinequiz.offlinequizquestionbank.init = function() {
    return new POPUP();
};

}, '@VERSION@', {
    "requires": [
        "base",
        "event",
        "node",
        "io",
        "io-form",
        "yui-later",
        "moodle-question-qbankmanager",
        "moodle-qbank_editquestion-chooser",
        "moodle-question-searchform",
        "moodle-core-notification"
    ]
});
