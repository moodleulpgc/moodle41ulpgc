<?php
if (! defined('CLI_SCRIPT')) {
    define('CLI_SCRIPT', true);
}
require_once (__DIR__ . '/../../../../config.php');

if ($CFG->plataforma == 'elearning')
{
	$CFG->plataforma = 'so';
	$cambiadoplataforma=1;
}
//$inicio = date('H:i:s');
//$inicio = time();
$casulpgcauth = get_auth_plugin('casulpgc');
$num = $casulpgcauth->sync_users();
//$fin = date('H:i:s');
//$fin = time();
//$output = array ('Usuarios', date('H:i:s', $inicio), date('H:i:s', $fin), $fin-$inicio, $num);
//echo json_encode($output);


$CFG->plataforma = 'oe';
//$inicio = date('H:i:s');
//$inicio = time();
$casulpgcauth = get_auth_plugin('casulpgc');
$num = $casulpgcauth->sync_users();
//$fin = date('H:i:s');
//$fin = time();
//$output = array ('Usuarios', date('H:i:s', $inicio), date('H:i:s', $fin), $fin-$inicio, $num);
//echo json_encode($output);







if ($cambiadoplataforma==1)
{
	$CFG->plataforma = 'elearning';
}

?>
