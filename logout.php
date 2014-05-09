<?php
require_once('definiciones.php');
require_once('funciones.php');

$database = new DB();

if(!$usuario = detectaLogueadoORedireccion($database)){
	// Redireccionar al usuario a index
	header('Location: /index.php', true, 302);
}


setcookie('u', '', 0);
setcookie('p', '', 0);

$database->SetUsuarioDeslogueadoPorID($usuario['ID']);

// Redireccionar al usuario a index
header('Location: /index.php', true, 302);
?>