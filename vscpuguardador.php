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
	isset($_POST['puntuacion']) && isset($_POST['victoria']) && 
	$_POST['puntuacion'] >= 0 && $_POST['puntuacion'] <= 120 &&
	($_POST['victoria'] == 'si' || $_POST['victoria'] == 'no' || $_POST['victoria'] == '')
){
	if($_POST['victoria'] == 'si'){
		$database->guardar_victoria('cpu', $usuario['ID']);
	}
	elseif($_POST['victoria'] == 'no'){
		$database->guardar_derrota('cpu', $usuario['ID']);
	}
	$database->guardar_puntuacion_max('cpu', $usuario['ID'], $_POST['puntuacion']);
}
?>
