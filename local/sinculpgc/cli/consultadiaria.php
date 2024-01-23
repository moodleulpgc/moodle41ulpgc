<?php

define ( 'CLI_SCRIPT', true );
$dir = __DIR__;
require_once ($dir . '/../../../config.php');
$pp = $CFG->plataforma;


$link = mysqli_connect($CFG->dbhost, $CFG->dbuser, $CFG->dbpass);

mysqli_select_db($link, $CFG->dbname);

$dia = "select DATE_SUB(CURDATE(), INTERVAL 1 DAY) as fecha";


$result = mysqli_query($link, $dia);


$row = $result->fetch_assoc();

echo "Día: ";
print_r ($row['fecha']);
$dia=$row['fecha'];

$consulta= 'select count(distinct(userid)) as total from ulp_logstore_standard_log where action = "loggedin" and from_unixtime(timecreated) like "' . $row['fecha'] . '%"';

$result = mysqli_query($link, $consulta);

mysqli_data_seek ($result, 0);

$extraido= mysqli_fetch_array($result);

echo "\n- Usuarios:";

  

$result = mysqli_query($link, $consulta);


$row = $result->fetch_assoc();

print_r ($row['total']);

$resultado=$row['total'];

echo "\n";
mysqli_free_result($result);

mysqli_close($link);


$db = oci_new_connect($CFG->ulpgcdbuser,$CFG->ulpgcdbpass,"PRODUCCION.ULPGC.ES");

if ($CFG->plataforma=="tp")
{
        $valor=4;
}


if ($CFG->plataforma=="tf")
{
        $valor=5;
}

if ($CFG->plataforma=="el")
{
        $valor=6;
}


$sql1="insert into cau.TCAU302(id_indicador,fecha_valor,valor) 
    VALUES( $valor,to_date('".$dia."','YYYY-MM-DD'),$resultado)";

print_r ($sql1);



$result=oci_parse($db,$sql1);
oci_execute($result);



?>
