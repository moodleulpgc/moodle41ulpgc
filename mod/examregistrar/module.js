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
 * Javascript helper function for examregistrar module
 *
 * @package    mod
 * @subpackage examregistrar
 * @copyright  2014 Enrique Castro modified  2009 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

M.mod_examregistrar = {};

M.mod_examregistrar.init_tree = function(Y, id, expand_all) {
    Y.use('yui2-treeview', function(Y) {
        var tree = new Y.YUI2.widget.TreeView(id);

        tree.subscribe("clickEvent", function(node, event) {
            // we want normal clicking which redirects to url
            return false;
        });

        if (expand_all) {
            tree.expandAll();
        } else {
            // Else just expand the top node.
            tree.getRoot().children[0].expand();
        }

        tree.render();
    });
}

M.mod_examregistrar.init_manage_table = function(Y) {
    Y.use('node', function(Y) {
        var selectall = Y.one('th.c0 input');
        if (selectall) {
            selectall.on('change', function(e) {
                if (e.currentTarget.get('checked')) {
                    checkboxes = Y.all('td.c0 input[type="checkbox"]');
                    checkboxes.each(function(node) {
                        rowelement = node.get('parentNode').get('parentNode');
                        node.set('checked', true);
                        rowelement.removeClass('unselectedrow');
                        rowelement.addClass('selectedrow');
                    });
                } else {
                    checkboxes = Y.all('td.c0 input[type="checkbox"]');
                    checkboxes.each(function(node) {
                        rowelement = node.get('parentNode').get('parentNode');
                        node.set('checked', false);
                        rowelement.removeClass('selectedrow');
                        rowelement.addClass('unselectedrow');
                    });
                }
            });
        }
    });
};
