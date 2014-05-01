<?php

function abredbsqlitesala($sala){
	$archivo = "chats/$sala";

	if(!file_exists($archivo)){
		$db = new SQLite3($archivo, 0666);
		$db->exec('CREATE TABLE mensajes (n INTEGER PRIMARY KEY AUTOINCREMENT, usuario STRING, mensaje STRING);');
		$db->exec('CREATE TABLE usuarios (ID INTEGER PRIMARY KEY, cartas_en_mano STRING, cartas_ganadas STRING);');
		$db->exec('CREATE TABLE cartas (cartas_en_mazo STRING, carta_siempre_manda STRING);');
		$result = $db->exec('PRAGMA journal_mode=WAL;');
	}
	else{
		$db = new SQLite3($archivo);
	}

	// De esta forma evitamos que de error por tabla bloqueada. Damos 5 segundos de tiempo para que la tabla deje de estar bloqueada.
	$db -> busyTimeout(5000);

	return $db;
}

function procesasms($entrada, $tipo, $dbsqlite, $datos_usuario=null){
	switch($tipo){
		case 'msg':
			// escribir
			$msg = mysql_escape_mimic($entrada);
			
			$nick = $datos_usuario['NICK'];
			
			$msg = '{"msg":'.json_encode($msg).'}';
			
			$result = $dbsqlite->query("INSERT INTO mensajes (usuario, mensaje) VALUES ('{$nick}','{$msg}');");
			
			if($result){
				return '{"resultado":true}';
			}
			else{
				return '{"resultado":false}';
			}
		break;
		case 'jugada':
			// POOOOOOOOOOOOOOOOR HAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEER
			// Comprobar si una jugada es válida. Si lo es, agregarla. Si es el último en tirar, decidir ganador e insertar en chat la jugada de llevarse alguien las cartas
			// Después, insertar las jugadas de respartir de cartas
			// escribir
			$orden = mysql_escape_mimic($entrada);
			
			$nick = $datos_usuario['NICK'];
			
			$cartas_en_mano = $dbsqlite->query("SELECT cartas_en_mano FROM usuarios WHERE ID = {$datos_usuario['ID']};");
			
			procesasms($orden, 'orden', $dbsqlite);
			
			$result = true;
			
			if($result){
				return '{"resultado":true}';
			}
			else{
				return '{"resultado":false}';
			}
		break;
		case 'aviso':
		case 'orden':
		case 'config':
			//Como msg, pero sin usuario
			$msg = mysql_escape_mimic($entrada);
			
			$msg = json_encode(array($tipo=>$msg));
			
			$result = $dbsqlite->query("INSERT INTO mensajes (mensaje) VALUES ('{$msg}');");
			
			if($result){
				return true;
			}
			else{
				return false;
			}
		break;
	}
}

?>