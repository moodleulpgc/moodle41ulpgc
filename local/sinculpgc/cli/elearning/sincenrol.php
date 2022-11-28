<?php
if (! defined('CLI_SCRIPT')) {
    define('CLI_SCRIPT', true);
}
require_once ('/var/www/moodle39/config.php');

if ($CFG->plataforma == 'elearning')
{
	$CFG->plataforma = 'oe';
	$cambiadoplataforma=1;
}


//$inicio = date('H:i:s');
//$inicio = time();
$enrol = enrol_get_plugin('sinculpgc');
$num = $enrol->sync_enrolments();

if ($cambiadoplataforma==1)
{
	$CFG->plataforma = 'elearning';
}

//$fin = date('H:i:s');
//$fin = time();
//$output = array ('Matrículas', date('H:i:s', $inicio), date('H:i:s', $fin), $fin-$inicio, $num);
//echo json_encode($output);
?>
