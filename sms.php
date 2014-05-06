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
$usuario = detectaLogueadoORedireccion($database, "login.php");



$sala = intval($_POST['sala']);

$dbsqlite = abredbsqlitesala($sala);



// Dependiendo de qué queremos hacer, leeremos de la db o escribiremos en ella.

if(isset($_POST['msg'])){
	echo procesasms($_POST['msg'], 'msg', $dbsqlite, $usuario);
	$dbsqlite->close();
	unset($dbsqlite);
}
elseif(isset($_POST['jugada'])){
	echo procesasms($_POST['jugada'], 'jugada', $dbsqlite, $usuario);
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
		
		$result = $dbsqlite->query("SELECT n, usuario, mensaje, privacidad FROM mensajes WHERE n > {$n};");
		$resultados = Array();
		while($resultado = $result->fetchArray(SQLITE3_ASSOC)){
			//print_r($resultado);
			$privacidad = $resultado['privacidad'];
			if($privacidad == ''){
				$resultados[] = array(
					'n'=>$resultado['n'],
					'usuario'=>$resultado['usuario'],
					'mensaje'=>$resultado['mensaje']
				);
			}
			else{
				$privacidad = json_decode($privacidad, true);
				if(
					(isset($privacidad['permitir']) && in_array($usuario['ID'], $privacidad['permitir'])) ||
					(isset($privacidad['denegar']) && !in_array($usuario['ID'], $privacidad['denegar']))
				){
					$resultados[] = array(
						'n'=>$resultado['n'],
						'usuario'=>$resultado['usuario'],
						'mensaje'=>$resultado['mensaje']
					);
				}
				else{
					$n = $resultado['n'];
				}
			}
			
		}
		
		if(count($resultados) > 0){
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