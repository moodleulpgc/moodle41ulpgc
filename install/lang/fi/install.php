<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Automatically generated strings for Moodle installer
 *
 * Do not edit this file manually! It contains just a subset of strings
 * needed during the very first steps of installation. This file was
 * generated automatically by export-installer.php (which is part of AMOS
 * {@link https://moodledev.io/general/projects/api/amos}) using the
 * list of strings defined in /install/stringnames.txt.
 *
 * @package   installer
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['admindirname'] = 'Ylläpitohakemisto';
$string['availablelangs'] = 'Saatavilla olevat kielipaketit';
$string['chooselanguagehead'] = 'Valitse kieli';
$string['chooselanguagesub'] = 'Valitse kieli asennusohjelmaa varten. Tätä kieltä käytetään sivuston oletuskielenä, jonka voi vaihtaa tarpeen mukaan. Myöhemmin voit myös lisätä käyttöön muitakin kieliä.';
$string['clialreadyconfigured'] = 'Konfiguraatiotiedosto config.php on jo olemassa. Käytä admin/cli/install_database.php -tiedostoa, jos haluat asentaa Moodlen tälle sivustolle.';
$string['clialreadyinstalled'] = 'Konfiguraatiotiedosto config.php on jo olemassa. Käytä admin/cli/upgrade.php-tiedostoa, jos haluat päivittää Moodlen tälle sivustolle.';
$string['cliinstallheader'] = 'Moodlen {$a} komentoriviasennusohjelma';
$string['clitablesexist'] = 'Tietokantataulut on jo luotu, CLI-asennusta ei voida jatkaa.';
$string['databasehost'] = 'Tietokannan isäntä';
$string['databasename'] = 'Tietokannan nimi';
$string['databasetypehead'] = 'Valitse tietokannan ajuri';
$string['dataroot'] = 'Datahakemisto';
$string['datarootpermission'] = 'Datahakemistojen oikeudet';
$string['dbprefix'] = 'Taulukon etumerkki';
$string['dirroot'] = 'Moodle-hakemisto';
$string['environmenthead'] = 'Ympäristön tarkistus';
$string['environmentsub2'] = 'Jokaisessa Moodle-julkaisussa on joitakin vähimmäisvaatimuksia PHP-versiolta sekä joitakin pakollisia PHP-lisäosia.
Ennen jokaista asennusta ja päivitystä suoritetaan täysi ympäristön tarkistus. Ole hyvä ja ota yhteyttä palvelimen ylläpitoon jos et tiedä kuinka asentaa uutta versiota tai PHP-lisäosia.';
$string['errorsinenvironment'] = 'Ympäristön tarkastus epäonnistui!';
$string['installation'] = 'Asennus';
$string['langdownloaderror'] = 'Valitettavasti kieltä "{$a}" ei voitu ladata. Asennus jatkuu englanniksi.';
$string['memorylimithelp'] = '<p>PHP muistiraja palvelimellesi on tällä hetkellä asetettu {$a}:han.</p>

<p>Tämä saattaa aiheuttaa Moodlelle muistiongelmia myöhemmin, varsinkin jos sinulla on paljon mahdollisia moduuleita ja/tai paljon käyttäjiä.</p>

<p>Suosittelemme, että valitset asetuksiksi PHP:n korkeimmalla mahdollisella raja-arvolla, esimerkiksi 40M.
On olemassa monia tapoja joilla voit yrittää tehdä tämän:</p>
<ol>
<li>Jos pystyt, uudelleenkäännä PHP <i>--enable-memory-limit</i>. :llä.
Tämä sallii Moodlen asettaa muistirajan itse.</li>
<li>Jos sinulla on pääsy php.ini tiedostoosi, voit muuttaa <b>memory_limit</b> asetuksen siellä johonkin kuten 40M. Jos sinulla ei ole pääsyoikeutta, voit kenties pyytää ylläpitäjää tekemään tämän puolestasi.</li>
<li>Joillain PHP palvelimilla voit luoda a .htaccess tiedoston Moodle hakemistossa, sisältäen tämän rivin:
<p><blockquote>php_value memory_limit 40M</blockquote></p>
<p>Kuitenkin, joillain palvelimilla tämä estää  <b>kaikkia</b> PHP sivuja toimimasta (näet virheet, kun katsot sivuja), joten sinun täytyy poistaa .htaccess tiedosto.</p></li>
</ol>';
$string['paths'] = 'Polut';
$string['pathserrcreatedataroot'] = 'Asennusohjelma ei voi luoda datahakemistoa ({$a->dataroot}).';
$string['pathshead'] = 'Varmista polut';
$string['pathsrodataroot'] = 'Dataroot-hakemisto ei ole kirjoitettavissa.';
$string['pathsroparentdataroot'] = 'Ylähakemisto ({$a->parent}) ei ole kirjoitettavissa. Asennusohjelma ei voi luoda datahakemistoa ({$a->dataroot}).';
$string['pathssubadmindir'] = 'Jotkut sivustot käyttävät /admin URL-osoitetta hallintapaneelille tai vastaavalle. Tämä on valitettavasti ristiriidassa Moodlen normaalin admin-sivun sijainnin kanssa.
Voit korjata tämän nimeämällä asennuksesi admin-hakemiston uudelleen, antamalla uuden nimen tähän. Esimerkiksi: <em>moodleadmin</em>. Tämä korjaa admin-linkit Moodlessa.';
$string['pathssubdataroot'] = '<p>Hakemisto, johon Moodle tallentaa kaiken käyttäjien lataaman sisällön.</p>
<p>Tämän hakemiston tulee olla verkkopalvelimen käyttäjän luettavissa ja kirjoitettavissa (yleensä "www-data", "nobody" tai "apache").</p>
<p>Se ei saa olla suoraan käytettävissä verkon kautta.</p>
<p>Jos hakemistoa ei tällä hetkellä ole, asennusprosessi yrittää luoda sen.</p>';
$string['pathssubdirroot'] = '<p>Koko hakemistopolku Moodle-asennuskoodin sisältävään hakemistoon.</p>';
$string['pathssubwwwroot'] = '<p>Moodlen täydellinen verkko-osoite, eli osoite, jonka käyttäjät kirjoittavat selaimensa osoitepalkkiin päästäkseen Moodleen.</p>
<p>Moodleen ei ole mahdollista päästä käyttäen useita osoitteita. Jos sivustollesi pääsee useiden osoitteiden kautta, valitse helpoin ja määritä pysyvä uudelleenohjaus muille osoitteille.</p>
<p>Jos sivustosi on käytettävissä sekä Internetistä että sisäisestä verkosta (intranetistä), käytä tässä julkista osoitetta.</p>
<p>Jos nykyinen osoite ei ole oikea, vaihda verkko-osoite selaimesi osoitepalkissa ja aloita asennus uudelleen.</p>';
$string['pathsunsecuredataroot'] = 'Dataroot-sijainti on turvallinen';
$string['pathswrongadmindir'] = 'Admin-hakemistoa ei ole';
$string['phpextension'] = '{$a} PHP-lisäosa';
$string['phpversion'] = 'PHP versio';
$string['phpversionhelp'] = '<p>Moodle vaatii PHP-version vähintään 5.6.5 tai 7.1 (7.0.x:ssä on joitain rajoituksia).</p>
<p>Käytät tällä hetkellä versiota {$a}.</p>
<p>Sinun on päivitettävä PHP tai siirryttävä palvelimelle, jossa on uudempi PHP-versio.</p>';
$string['welcomep10'] = '{$a->installername} ({$a->installerversion})';
$string['welcomep20'] = 'Näet tämän sivun koska olet onnistuneesti asentanut ja käynnistänyt <strong>{$a->packname} {$a->packversion}</strong> paketin tietokoneellasi.
Onnittelut!';
$string['welcomep30'] = 'Tämä julkaisu <strong>{$a->installername}</strong> sisältää sovellukset ympäristön luomiseen, jossa <strong>Moodle</strong> toimii:';
$string['welcomep40'] = 'Tämä paketti sisältää myös <strong>Moodlen {$a->moodlerelease} ({$a->moodleversion})</strong>.';
$string['welcomep50'] = 'Kaikkia tämän paketin sovelluksia hallitsevat niihin liittyvät lisenssit. Koko <strong>{$a->installername}</strong> paketti on <a href="http://www.opensource.org/docs/definition_plain.html">avointa lähdekoodia</a> ja sitä jaellaan <a href="http://www.gnu.org/copyleft/gpl.html">GPL</a> lisenssin alla.';
$string['welcomep60'] = 'Seuraavat sivut opastavat sinua helposti seurattavien vaiheiden läpi <strong>Moodlen</strong> konfiguroinnissa koneellesi. Voit hyväksyä oletusasetukset tai vaihtoehtoisesti muuttaa niitä tarvitsemallasi tavalla.';
$string['welcomep70'] = 'Napsauta "Seuraava"-painiketta jatkaaksesi moodlen asennusta';
$string['wwwroot'] = 'Web-osoite';
