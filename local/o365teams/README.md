# Office 365 Extended Teams Integration #

This is an extension to official local_o365 to allow management of Teams channels 
and other improvements in o365 connection with moodle

## Features
 * usersmatch task to pre-match users by email. This allows a better user synchronization. 
   Normally o365 only add to Teams to matched users in moodle.
 * Add a site label and shortname to Teams names in o365   
 * Add private channels for moodle groups in a course that match an group idnumber pattern.
 * Add o365 usergroups (i.e. plain user lists, for mail) for each moodle group


## Installing via uploaded ZIP file ##

1. Log in to your Moodle site as an admin and go to _Site administration >
   Plugins > Install plugins_.
2. Upload the ZIP file with the plugin code. You should only be prompted to add
   extra details if your plugin type is not automatically detected.
3. Check the plugin validation report and finish the installation.

## Installing manually ##

The plugin can be also installed by putting the contents of this directory to

    {your/moodle/dirroot}/local/o365teams

Afterwards, log in to your Moodle site as an admin and go to _Site administration >
Notifications_ to complete the installation.

Alternatively, you can run

    $ php admin/cli/upgrade.php

to complete the installation from the command line.

## License ##

2022 Enrique Castro @ULPGC

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <https://www.gnu.org/licenses/>.
