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
 * Reset course warnings for a profield field.
 *
 * @category   profilefield
 * @copyright
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from 'core/ajax';
import Notification from 'core/notification';

const Selectors = {
    regions: {
        reset: '[data-region="profilefield_callsummons/reset"]',
    },
};

/**
 * Attach the necessary event handlers to the action links
 */
export const registerEventListeners = () => {
    document.addEventListener('click', e => {
        const resetAction = e.target.closest(Selectors.regions.reset);

        if (resetAction) {
            e.preventDefault();

            Promise.resolve(resetAction)
                .then(resetWarnings)
                .catch(Notification.exception);
        }
    });
};

/**
 * Reset the course warnings for the profile field.
 *
 * @param {HTMLElement} clickedItem The action element that the user chose.
 * @returns {Promise}
 */
const resetWarnings = clickedItem => {
    if (clickedItem.dataset.profilefieldid) {
        return Ajax.call([{
            methodname: 'profilefield_callsummons_reset_warnings',
            args: {
                profilefieldid: clickedItem.dataset.profilefieldid,
            }
        }])[0];
    }

    return Promise.resolve();
};
