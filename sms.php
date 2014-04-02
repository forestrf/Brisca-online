<?php

// SI se llama sin la opción sala, se termina el script
if(!isset($_GET['sala'])){
	exit;
}

require_once('definiciones.php');
require_once('funciones.php');


$database = new DB();
$database->Abrir();


// Detectar si el usuario está logueado. Si no lo está, enviarlo a login. En caso de que esté logueado, pero no se corresponda el login con la base de datos, lo mismo.
detectaLogueadoORedireccion($database, "login.php");




$ID_User = $_COOKIE['u'];

// El archivo de la base de datos se forma del siguiente modo: md5(md5(variable))
// en el get se usa: md5(variable)
// variable se forma a partir de: microtime + 4 letras/numeros al azar
$sala = md5($_GET['sala']);

// n del último mensaje recibido
if(isset($_GET['ult']) && preg_match("/^[0-9]+?$/", $_GET['ult'])){
	$n = $_GET['ult'];
}




$archivo = "chats/$sala";


$db = new SQLite3($archivo, 0666);
// $db->exec('CREATE TABLE mensajes (n INTEGER , usuario STRING, mensaje STRING)');
// $db->exec('CREATE TABLE integrantes (id_user STRING)');
// $db->exec("INSERT INTO integrantes (id_user) VALUES ('$id_user')"); // Esto por cada usuario de la sala. Solo ellos podrán escribir después

$result = $db->query('SELECT n, usuario, mensaje FROM mensajes'.(isset($n)?" WHERE n > $n":''));
var_dump($result->fetchArray());



 

?>