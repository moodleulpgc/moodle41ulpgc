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
 * @package local_vflibs
 * @author valery.fremaux@gmail.com
 * @category local
 */

// Privacy
$string['privacy:metadata'] = 'The Local VFLibs plugin does not store any personal data about any user.';

$string['pluginname'] = 'Extra common libraries for VF plugins';

$string['potcourses'] = 'Potential courses';
$string['nomatchingcourses'] = 'No courses matching';
$string['previouslyselectedcourses'] = 'Previously selected';
$string['courseselectorpreserveselected'] = 'Preserve selection';
$string['courseselectorautoselectunique'] = 'Auto select unique';
$string['courseselectorsearchanywhere'] = 'Search anywhere';
$string['configenablelocalpdf'] = 'Enable local pdf';
$string['configdocbaseurl'] = 'Documentation base url';
$string['configdoccustomerid'] = 'Documentation customer id';
$string['configdoccustomerpublickey'] = 'Documentation public key';
$string['configeditorplugins'] = 'Editor plugins for extra documentation';
$string['configgooglemapsapikey'] = 'Google Maps SKU API Key.';
$string['configgooglemapsapikey_desc'] = 'Google maps are now only available on pay per use mode. You must subscribe to Google API services to use maps.';
$string['completionpercent'] = 'Completion: {$a}%';
$string['helponblock'] = 'Help on the block';
$string['helponmodule'] = 'Help on the course module';
$string['missinggooglekey'] = 'Your Google Maps API key is empty. You may subscribe for a key at Google. See documentation https://developers.google.com/maps/documentation/javascript/get-api-key';

$string['configenablelocalpdf_desc'] = 'Enables the use of the locally modified tcpdf library. This needs protecting
the original lib/tcpdf lib from definition collision. Read the REDME.txt file for more information.';

$string['configdocbaseurl_desc'] = 'Base url for the remote documentation source';
$string['configdoccustomerid_desc'] = 'Customer key for document access authentication';
$string['configdoccustomerpublickey_desc'] = 'A PEM public key for encrypting the documentation access token. It is given by the documentation provider.';

$string['configeditorplugins_desc'] = '';

$string['configdonutrenderercolors'] = 'JQPlot simple donut colors';
$string['configdonutrenderercolors_desc'] = 'Give an HTML color codes list, coma separated.';
$string['configjqplotshadows'] = 'JQPlot graph shadows';
$string['configjqplotshadows_desc'] = 'If set, will put shadows on shapes and plit generators.';

$string['configpdfgeneration'] = 'Pdf generation settings';

$string['configpdfenabled'] = 'Pdf generator enabled';
$string['configpdfenabled_desc'] = 'enables pdf generation for bills and other shop documents';
$string['configprintconfig'] = 'Pdf general configuration';
$string['configprintconfig_desc'] = 'A serialized set of printing attributes';
$string['configdefaulttemplate'] = 'Default content template';
$string['configdefaulttemplate_desc'] = 'an html sequence as defalt document for the current plugin.';
$string['configdocborderimage'] = 'An image for document border';
$string['configdocborderimage_desc'] = 'A jpg or png image. Transparency is NOT supported in tcpdf. Try compress it as much as possible.';
$string['configdocwatermarkimage'] = 'A background image for watermarking the document.';
$string['configdocwatermarkimage_desc'] = 'A jpg or png image. Transparency is NOT supported in tcpdf. Note that big images may severely lower the pdf generator performance. Associated printconfigs : watermarkx, watermarky; watermarkw and watermarkh.';
$string['configdoclogoimage'] = 'A logo image';
$string['configdoclogoimage_desc'] = 'A jpg or png image. Transparency is NOT supported in tcpdf. Associated printconfigs : logox, logoy; logow and logoh.';
$string['configdocheaderimage'] = 'Header';
$string['configdocheaderimage_desc'] = 'A jpg or png image. Transparency is NOT supported in tcpdf. Associated printconfigs : headerx, headery; headerw and headerh.';
$string['configdocinnerheaderimage'] = 'Inner Header';
$string['configdocinnerheaderimage_desc'] = 'A jpg or png image. Transparency is NOT supported in tcpdf. Associated printconfigs : headerx, headery; headerw and headerh.';
$string['configdocfooterimage'] = 'Footer';
$string['configdocfooterimage_desc'] = 'A jpg or png image. Transparency is NOT supported in tcpdf. Associated printconfigs : footerx, footery; footerw and footerh.';
$string['configdocinnerfooterimage'] = 'Inner Footer';
$string['configdocinnerfooterimage_desc'] = 'A jpg or png image. Transparency is NOT supported in tcpdf. Associated printconfigs : footerx, footery; footerw and footerh.';

$string['toomanycoursestoshow'] = 'Too many courses to show. ({$a})';
$string['toomanycoursesmatchsearch'] = 'Too many courses match the search "{$a->search}". ({$a->count})';