<?php

require_once (__DIR__ . '/../../../config.php');
require_once ('../locallib.php');

if ((isset($_GET["dni1"])) && (isset($_GET["dni2"])) && (isset($_GET["token"])))
{       	
   $fecha=date('d-m-Y h:i:s');
   $registro="Intentado el cambio con dni original ". $_GET["dni1"] . " al dni nuevo ". $_GET["dni2"] . "\n";
   $salida= `echo "$fecha" >> /var/www/moodledata/registro_cambios_dni.txt`;
   $salida= `echo "$registro" >> /var/www/moodledata/registro_cambios_dni.txt`;
   if ($_GET["token"] == "fecf52be83053f435d26e89ea2973750")
   {	   
	$existing_user = $DB->get_record('user', array(
             'username' => $_GET["dni1"]
	));

	if ($existing_user) {
		$existing_user->username= $_GET["dni2"];
		$existing_user->idnumber= $_GET["dni2"];
	    if ($DB->update_record('user', $existing_user)) {
		print "Cambio realizado";
       	    } else {
		print "Cambio no realizado -> Error en update";
	    }	
	}
    } else {
        print "Cambio no realizado -> Token incorrecto";
    }
} else {
	print "Cambio no realizado -> Error en parámetros";
}

?>

