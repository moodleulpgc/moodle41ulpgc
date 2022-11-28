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

$string['pluginname'] = 'Librairies additionnelles pour les plugins VF (MyLearningFactory)';

$string['potcourses'] = 'Cours potentiels';
$string['nomatchingcourses'] = 'Aucun cours ne correspond';
$string['previouslyselectedcourses'] = 'Précédemment sélectionnés';
$string['courseselectorpreserveselected'] = 'Préserver la sélection';
$string['courseselectorautoselectunique'] = 'Sélectionner automatiquement un résultat unique';
$string['courseselectorsearchanywhere'] = 'Rechercher partout';
$string['configenablelocalpdf'] = 'Activer la version locale de tcpdf';
$string['configdocbaseurl'] = 'Url de base de la documentation';
$string['configdoccustomerid'] = 'ID d\'abonné à la documentation';
$string['configdoccustomerpublickey'] = 'Clef publique de documentation';
$string['configdonutrenderercolors'] = 'Couleurs du renderer de donuts';
$string['completionpercent'] = 'Achèvement : {$a}%';
$string['helponblock'] = 'Guide d\'utilisation du bloc';
$string['helponmodule'] = 'Guide d\'utilisation de l\'activité';
$string['configjqplotshadows'] = 'Ombres des graphes JQPlot';
$string['configeditorplugins'] = 'Catalogue de plugins pour documentation additionnelle';

$string['configenablelocalpdf_desc'] = 'Active la version locale de TCPDF avec des polices et fonctionnalités en plus.
Ceci nécessite de protéger la distribution standard. Plus d\'informations dans le fichier README.txt.';

$string['configpdfgeneration'] = 'Paramètres de génération PDF';

$string['configpdfenabled'] = 'Activer l\'édition PDF';
$string['configpdfenabled_desc'] = 'Active la génération en PDF des factures et autres documents de la boutique';
$string['configprintconfig'] = 'Configuration générale des documents';
$string['configprintconfig_desc'] = 'Une structure sérialisée d\'attributs de positionnement et de rendu';
$string['configdefaulttemplate'] = 'Modèle de document par défaut';
$string['configdefaulttemplate_desc'] = 'Un modèle de document par défaut pour le plugin. Certains plugins pourront utiliser des modèles spécifiques.';
$string['configdocborderimage'] = 'Image des bordures';
$string['configdocwatermarkimage'] = 'Image de Filigranne.';
$string['configdoclogoimage'] = 'Image de logo';
$string['configdocheaderimage'] = 'Image d\'en-tête';
$string['configdocinnerheaderimage'] = 'En tête intérieure';
$string['configdocfooterimage'] = 'Pied de page';
$string['configdocinnerfooterimage'] = 'Pied de page intérieur';
$string['configgooglemapsapikey'] = 'Clef d\'API Google Maps';
$string['configgooglemapsapikey_desc'] = 'Google Maps n\'est plus un service gratuit. Vous devez sourcrire un contrat de sevice de données applicatives pour utiliser les cartes Google.';
$string['missinggooglekey'] = 'Vous n\'avez pas entré de clef d\'API Google. Voir la documentation https://developers.google.com/maps/documentation/javascript/get-api-key';

$string['toomanycoursestoshow'] = 'Il y a trop de cours possibles. ({$a})';
$string['toomanycoursesmatchsearch'] = 'Trop de cours répondent au critère "{$a->search}". ({$a->count})';

$string['configdocborderimage_desc'] = 'Une image jpg ou png.';

$string['configdocwatermarkimage_desc'] = 'Une image jpg ou png. Surveillez la taille et le définition de ces images pour ne pas . Clefs de configuration associées : watermarkx, watermarky; watermarkw et watermarkh.';

$string['configdoclogoimage_desc'] = 'Une image jpg ou png. Clefs de configuration associées : logox, logoy; logow and logoh.';

$string['configdocheaderimage_desc'] = 'Une image jpg ou png. Clefs de configuration associées : headerx, headery; headerw and headerh.';

$string['configdocinnerheaderimage_desc'] = 'Une image jpg ou png. Clefs de configuration associées : headerx, headery; headerw et headerh.';

$string['configdocfooterimage_desc'] = 'Une image jpg ou png. Clefs de configuration associées : footerx, footery; footerw and footerh.';

$string['configdocinnerfooterimage_desc'] = 'Une image jpg ou png. Clefs de configuration associées : footerx, footery; footerw and footerh.';

$string['configdocbaseurl_desc'] = 'Url de base du volume de documentation source.';

$string['configdoccustomerid_desc'] = 'Identifiant d\'abonné à la documentation';

$string['configdoccustomerpublickey_desc'] = 'La clef publique  d\'encodage des tickets d\'accès à la documentation. Cette clef vous est fournie par l\'éditeur de la documentation.';

$string['configdonutrenderercolors_desc'] = 'Donner une liste de couleurs HTML séparée par des virgules.';

$string['configjqplotshadows_desc'] = 'Si activé, ajouter un ombrage sur les générateurs jqplot.';
