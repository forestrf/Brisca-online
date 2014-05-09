<?php
require_once 'definiciones.php';
require_once 'funciones.php';
require_once 'smsfunciones.php';

$database = new DB();


// Detectar si el usuario está logueado. Si no lo está, enviarlo a login. En caso de que está logueado, pero no se corresponda el login con la base de datos, lo mismo.
if(!$usuario = detectaLogueadoORedireccion($database)){
exit;
}



if(
  isset($_POST['puntuacion']) && !isset($_POST['victoria']) && 
  $_POST['puntuacion'] >= 0 && $_POST['puntuacion'] <= 120 &&
  ($_POST['victoria'] == 'si' || $_POST['victoria'] == 'no')
){
  if($_POST['victoria'] == 'si'){
    guardar_victoria($database, $usuario['ID']);
  }
  else{
    guardar_derrota($database, $usuario['ID']);
  }
  guardar_puntuacion_max($database, $usuario['ID'], $_POST['puntuacion']);
}



function guardar_victoria(&$database, $ID){
$database->guardar_victoria('cpu', $ID);
}

function guardar_derrota(&$database, $ID){
$database->guardar_derrota('cpu', $ID);
}

function guardar_puntuacion_max(&$database, $ID, $puntos){
$database->guardar_puntuacion_max('cpu', $ID, $puntos);
}
?>
