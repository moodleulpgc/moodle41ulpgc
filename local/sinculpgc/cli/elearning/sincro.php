<?php

define ( 'CLI_SCRIPT', true );
$dir = __DIR__;
require_once ($dir . '/../../../../config.php');
$pp = $CFG->plataforma;
$nombre_fichero="semaforo.tmp";
$maestro=0;
$retardo=rand(1,20);
sleep($retardo);

if (file_exists($nombre_fichero)) {
    	exit();
} else {
	system("touch $nombre_fichero");		
	$maestro=1;
}




// Sincronización de cohortes
include $dir . '/sinccohortes.php';
echo "Sincro de COHORTES finalizada\n";

// Sincronización de CATEGORIAS
include $dir . '/sinccategorias.php';
echo "Sincro de CATEGORIAS finalizada\n";

// Sincronización de CURSOS
include $dir . '/sinccursos.php';
echo "Sincro de CURSOS finalizada\n";

// Sincronización de USUARIOS
include $dir . '/sincusuarios.php';
echo "Sincro de USUARIOS finalizada\n";

// Sincronización de ASIGNACIONES de cohortes
include $dir . '/sincagruparcohortes.php';
echo "Sincro de ASIGNACIONES de COHORTES finalizada\n";

// Sincronización de ENROLAMIENTOS
include $dir . '/sincenrol.php';
echo "Sincro de ENROLAMIENTOS finalizada\n";

print $maestro;

if ($maestro == 1)
{
	system("rm -f $nombre_fichero");
	print "entramos en el if";
}




?>
