<?php

$idnumber = $_GET['codigo'];

$params = explode('_', $idnumber);
$nCodAsignatura = $params[5];
$codTitulacion = $params[0];
$codPlan = $params[1];
$codEspecialidad = $params[2];

header('Location: https://www2.ulpgc.es/index.php?pagina=plan_estudio&ver=pantalla&numPantalla=99&nCodAsignatura='.$nCodAsignatura.'&codTitulacion='.$codTitulacion.'&codPlan='.$codPlan.'&codEspecialidad='.$codEspecialidad);
?>
