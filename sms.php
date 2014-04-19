<?php

// SI se llama sin la opción sala, se termina el script
if(!isset($_POST['sala'])){
	exit;
}

require_once('definiciones.php');
require_once('funciones.php');


$database = new DB();


// Detectar si el usuario está logueado. Si no lo está, enviarlo a login. En caso de que está logueado, pero no se corresponda el login con la base de datos, lo mismo.
$datos_usuario = detectaLogueadoORedireccion($database, "login.php");



$ID_User = $_COOKIE['u'];

// El archivo de la base de datos se forma del siguiente modo: md5(md5(variable))
// en el get se usa: md5(variable)
// variable se forma a partir de: microtime + 4 letras/numeros al azar
$sala = md5($_POST['sala']);



$archivo = "chats/$sala";



if(!file_exists($archivo)){
	$db = new SQLite3($archivo, 0666);
	$db->exec('CREATE TABLE mensajes (n INTEGER PRIMARY KEY AUTOINCREMENT, usuario STRING, mensaje STRING);');
	$result = $db->exec('PRAGMA journal_mode=WAL;');
}
else{
	$db = new SQLite3($archivo);
}

// De esta forma evitamos que de error por tabla bloqueada. Damos 5 segundos de tiempo para que la tabla deje de estar bloqueada.
$db -> busyTimeout(5000);

// $db->exec('CREATE TABLE mensajes (n INTEGER , usuario STRING, mensaje STRING)');
// $db->exec('CREATE TABLE integrantes (id_user STRING)');
// $db->exec("INSERT INTO integrantes (id_user) VALUES ('$id_user')"); // Esto por cada usuario de la sala. Solo ellos podrán escribir después


// Dependiendo de qué queremos hacer, leeremos de la db o escribiremos en ella.
if(isset($_POST['msg'])){
	// escribir
	$msg = mysql_escape_mimic($_POST['msg']);
	
	$nick = $datos_usuario['NICK'];
	
	$result = $db->query("INSERT INTO mensajes (usuario, mensaje) VALUES ('{$nick}','{$msg}');");
	if($result){
		echo '{"resultado":true}';
	}
	else{
		echo '{"resultado":false}';
	}
	$db->close();
	unset($db);
}
elseif(isset($_POST['ult']) && preg_match("/^[0-9]+?$/", $_POST['ult'])){
	// leer
	// Se mantendrá la conexión abierta y 
	
	// n del último mensaje recibido
	$n = $_POST['ult'];
	
	// Tiempo máximo de ejecución actual = 30 segundos.
	// tiempo por vuelta aprox = 0.2 segundos.
	// Vueltas en 30 segundos = 30 / 0.2 = 150
	// Para evitar ajustarnos, mejor 20 segundos. 20 / 0.2 = 100
	$vueltas = 100;
	
	for($i = 0; $i < $vueltas; ++$i){
		
		$result = $db->query("SELECT n, usuario, mensaje FROM mensajes WHERE n > {$n};");
		$resultado = $result->fetchArray(SQLITE3_ASSOC);
		if($resultado){
			$resultados = Array();
			$resultados[] = $resultado;
			while($resultado = $result->fetchArray(SQLITE3_ASSOC)){
				$resultados[] = $resultado;
			}
			echo json_encode($resultados);
			
			$db->close();
			unset($db);
			
			exit;
		}
		
		// 0.2 segundos
		usleep(200000);
	}
	
	echo "[]";
	
}






?>