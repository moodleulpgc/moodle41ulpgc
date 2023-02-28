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
 * Display warning notifications.
 *
 * @category   profilefield
 * @copyright
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

export const init = userProfiles => {
    const courses = document.querySelectorAll('.block_course_termlist .my-course-name');
    courses.forEach(course => {
        const courseUrl = course.getAttribute("href");
        const courseId = courseUrl.substring(courseUrl.indexOf("=") + 1);
        Object.entries(userProfiles).forEach(entry => {
            const coursesProfile = entry[1];
            const title = coursesProfile.title;
            const coursesProfileField = Object.keys(coursesProfile);
            if (coursesProfileField.includes(courseId)
                    && ((coursesProfile.iconalways == '1') || ((!coursesProfile[courseId])))) {
                const icon = document.createElement('i');
                icon.className = 'fa ' + coursesProfile.icon;
                icon.classList.add(coursesProfile.extraclass);
                icon.setAttribute("title", title);
                course.appendChild(icon);
            }
        });
    });
};
