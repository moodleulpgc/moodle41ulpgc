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

/**
 * @package    atto_fontawesomepicker
 * @copyright  2020 DNE - Ministere de l'Education Nationale
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Atto text editor fontawesomepicker plugin.
 *
 * @namespace M.atto_fontawesomepicker
 * @class button
 * @extends M.editor_atto.EditorPlugin
 */
Y.namespace('M.atto_fontawesomepicker').Button = Y.Base.create('button', Y.M.editor_atto.EditorPlugin, [], {
    initializer: function () {
        this.embeddingmode = this.get('embeddingmode');
        var icons = this.get('icons');
        var items = [];
        var cpt = 0;
        Y.Array.each(icons, function (icon) {
            if(cpt < 20 ){
                items.push({
                    text: '<i class="' + icon + ' fa-2x" aria-hidden="true"></i>',
                    callbackArgs: icon
                });
                cpt++;
            }
        });

        for (var i = 0; i < items.length % 3 ; i++) {
            items.push({
                text: '',
                callbackArgs: null
            });
        }

        this.addToolbarMenu({
            icon: 'ed/font-awesome-brands',
            iconComponent: 'atto_fontawesomepicker',
            overlayWidth: '4',
            globalItemConfig: {
                callback: this._addfontawesomeicon
            },
            items: items
        });
    },

    /**
     * Add Icon
     *
     * @method _changeStyle
     * @param {EventFacade} e
     * @param {string} color The new background color
     * @private
     */
    _addfontawesomeicon: function (e, icon) {
        if(icon){
            
            if (this.embeddingmode == 0) {
                document.execCommand('insertHTML', false, '<span class="' + icon + ' fa-2x" aria-hidden="true"></span>');
            } else {
                document.execCommand('insertText', false, "[" + icon.replace('fa ', '') + " fa-pull-left fa-2x]");
            }

            // Mark as updated
            this.markUpdated();
        }
    }

}, {
    ATTRS: {
        icons: {
            value: {}
        },
        embeddingmode: {
            value: {}
        }
    }
});