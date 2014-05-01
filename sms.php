<?php

// SI se llama sin la opción sala, se termina el script
if(!isset($_POST['sala'])){
	exit;
}

require_once('definiciones.php');
require_once('funciones.php');
require_once('smsfunciones.php');


$database = new DB();


// Detectar si el usuario está logueado. Si no lo está, enviarlo a login. En caso de que está logueado, pero no se corresponda el login con la base de datos, lo mismo.
$datos_usuario = detectaLogueadoORedireccion($database, "login.php");



$sala = intval($_POST['sala']);

$dbsqlite = abredbsqlitesala($sala);



// Dependiendo de qué queremos hacer, leeremos de la db o escribiremos en ella.

if(isset($_POST['msg'])){
	echo procesasms($_POST['msg'], 'msg', $dbsqlite, $datos_usuario);
	$dbsqlite->close();
	unset($dbsqlite);
}
elseif(isset($_POST['jugada'])){ // POOOOOOOOOOOOOOOOR HAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEER
	// Comprobar si una jugada es válida. Si lo es, agregarla. Si es el último en tirar, decidir ganador e insertar en chat la jugada de llevarse alguien las cartas
	// Después, insertar las jugadas de respartir de cartas
	// escribir
	echo procesasms(array($datos_usuario['ID']=>$_POST['jugada']), 'jugada', $dbsqlite, $datos_usuario);
	
	procesasms(array('lanza'=>id_desde_hueco_sala($prox_jugador_a_tirar)), 'orden', $dbsqlite);
	
	$dbsqlite->close();
	unset($dbsqlite);
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
		
		$result = $dbsqlite->query("SELECT n, usuario, mensaje FROM mensajes WHERE n > {$n};");
		$resultado = $result->fetchArray(SQLITE3_ASSOC);
		if($resultado){
			$resultados = Array();
			$resultados[] = $resultado;
			while($resultado = $result->fetchArray(SQLITE3_ASSOC)){
				$resultados[] = $resultado;
			}
			echo json_encode($resultados);
			
			$dbsqlite->close();
			unset($dbsqlite);
			
			exit;
		}
		
		// 0.2 segundos
		usleep(200000);
	}
	
	echo "[]";
	
}






?>