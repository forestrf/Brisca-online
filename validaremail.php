<?php

require_once('definiciones.php');
require_once('funciones.php');

$database = new DB();
$database->Abrir();

if(isset($_GET['n']) && isset($_GET['p'])){
	if($database->CompruebaEmailValidacion($_GET['n'], $_GET['p'])){
		$database->ValidaEmailPorID($_GET['n']);
		// Usuario marcado como email válido.
		// Redireccionar a login.php
		header("Location: ".PATH."login.php", true, 302);
		exit;
	}
}
// Redireccionar a fallo
header("Location: ".PATH."emailvalidacionincorrecto.html", true, 302);

?>