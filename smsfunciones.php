<?php

require_once 'php/IABrisca.class.php';

function abredbsqlitesala($sala){
	$archivo = "chats/$sala.sqlite";

	if(!file_exists($archivo)){
		$db = new SQLite3($archivo, 0666);
		$db->exec('CREATE TABLE mensajes (n INTEGER PRIMARY KEY AUTOINCREMENT, usuario STRING, mensaje STRING);');
		// primero estará en 1 o 0. A partir de quien es primero se calcula a quien le toca tirar
		// lanza estará en 0, 1 o 2. si está en 1, es el único que puede lanzar. Si está en 2 es que ya ha lanzado. Una vez lanzado si hay jugadores entre el y el primero, se moverá el 1 en lanza al siguiente jugador
		// hueco_sala no es necesaria ya que la info está en la db, pero para no hacer consultas de más a la db, prefiero ponerlo aquí.
		$db->exec('CREATE TABLE usuarios (ID INTEGER PRIMARY KEY, primero INTEGER, lanza INTEGER, hueco_sala INTEGER);');
		$db->exec('CREATE TABLE cartas (carta STRING, posicion STRING, propietario STRING, palo_manda_siempre INTEGER, palo_manda_mesa INTEGER);');
		$result = $db->exec('PRAGMA journal_mode=WAL;');
	}
	else{
		$db = new SQLite3($archivo);
	}

	// De esta forma evitamos que de error por tabla bloqueada. Damos 5 segundos de tiempo para que la tabla deje de estar bloqueada.
	$db -> busyTimeout(5000);

	return $db;
}

//La ejecución de esta función no debe detenerse en caso de que el usuario corte la conexión. OJOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOO
function procesasms($entrada, $tipo, $dbsqlite, $datos_usuario=null){
	switch($tipo){
		case 'jugada':
			// Comprobar si una jugada es válida. Si lo es, agregarla. Si es el último en tirar, decidir ganador e insertar en chat la jugada de llevarse alguien las cartas
			// Después, insertar las jugadas de respartir de cartas
			// escribir
			
			// $orden = array('ID'=>$datos_usuario['ID'],'jugada'=>$_POST['jugada']);
			
			// ¿Tiene el usuario la carta que quiere tirar?
			$carta = $entrada;
			$result = $dbsqlite->query("SELECT posicion FROM cartas WHERE carta = '{$carta}' AND posicion = '{$datos_usuario['ID']}';");
			$results = array_from_sqliteResponse($result);
			if(count($results) === 0){
				// En caso de ser false, está intentando hacer algo que no toca.
				return '{"resultado":false}';
			}
			
			// El usuario tiene la carta. ¿Es el turno de tirar del usuario?
			$result = $dbsqlite->query("SELECT hueco_sala, primero FROM usuarios WHERE ID = '{$datos_usuario['ID']}' AND lanza = 1;");
			$results = array_from_sqliteResponse($result);
			if(count($results) === 0){
				// En caso de ser false, está intentando lanzar cuando no es su turno. Ignorar
				return '{"resultado":false}';
			}
			
			// Guardar el hueco de la sala del usuario actual
			$results = $results[0];
			$hueco_sala = $results['hueco_sala'];
			$primero = $results['primero'];
			
			//El usuario puede tirar la carta que indica y es su turno de tirar. Realizar jugada
			$dbsqlite->query("UPDATE usuarios SET lanza = 2 WHERE ID = '{$datos_usuario['ID']}';");
			$dbsqlite->query("UPDATE cartas SET posicion = 'mesa', propietario = '{$datos_usuario['ID']}'".($primero==1?', palo_manda_mesa = 1':'')." WHERE carta = '{$carta}';");
			
			// Poner mensaje de lanzamiento
			procesasms(array($datos_usuario['ID']=>$carta), 'orden', $dbsqlite);
			
			//Comprobar si falta alguien por tirar
			$result = $dbsqlite->query("SELECT ID, primero, lanza, hueco_sala FROM usuarios WHERE lanza = 0;");
			$results = array_from_sqliteResponse($result);
			
			if(count($results) === 0){
				//Todos los jugadores ya han lanzado. Decidir ganador. Después, comprobar si quedan cartas en el mazo
				$dbsqlite->query("UPDATE usuarios SET lanza = 0, primero = 0;");
				
				$result = $dbsqlite->query("SELECT carta, propietario FROM cartas WHERE posicion = 'mesa' AND propietario != '';");
				$results = array_from_sqliteResponse($result);
				$cartas_jugadas = array();
				$cartas_jugadas_propietario = array();
				foreach($results as $elem){
					$cartas_jugadas[] = $elem['carta'];
					$cartas_jugadas_propietario[$elem['carta']] = $elem['propietario'];
				}
				
				$result = $dbsqlite->query("SELECT carta, palo_manda_siempre, palo_manda_mesa FROM cartas WHERE palo_manda_siempre = 1 OR palo_manda_mesa = 1;");
				$results = array_from_sqliteResponse($result);
				$carta_manda_siempre = $results[0]['palo_manda_siempre'] == 1 ? $results[0]['carta'] : $results[1]['carta'];
				$palo_manda_siempre = IABrisca::paloCarta($carta_manda_siempre);
				$carta_manda_mesa = $results[0]['palo_manda_mesa'] == 1 ? $results[0]['carta'] : $results[1]['carta'];
				$palo_manda_mesa = IABrisca::paloCarta($carta_manda_mesa);
				
				$cartas_ordenadas = IABrisca::ordenCartasPorValor($cartas_jugadas, $palo_manda_siempre, $palo_manda_mesa);
				
				$ganador_id = $cartas_jugadas_propietario[$cartas_ordenadas[0]];
				
				// Permitir terminar la animación de las cartas moviéndose.
				sleep(1);
				
				ganar_cartas($dbsqlite, $ganador_id, $cartas_jugadas);
				
				// En caso de continuar la partida, repartir cartas. De lo contrario, terminar partida.
				$result = $dbsqlite->query("SELECT count(*) as recuento FROM cartas WHERE posicion LIKE '%ganadas';");
				$results = array_from_sqliteResponse($result);
				
				if($results[0]['recuento'] == count(IABrisca::$cartasTotalArray)){
					// Partida terminada.
					$usuarios = $dbsqlite->query("SELECT ID FROM usuarios;");
					$usuarios = array_from_sqliteResponse($usuarios);
					
					$puntos_jugadores = array();
					foreach($usuarios as $user){
						$cartas_arr = $dbsqlite->query("SELECT carta FROM cartas WHERE propietario = {$user['ID']};");
						$cartas_arr = array_from_sqliteResponse($cartas_arr);
						$cartas = array();
						foreach($cartas_arr as $carta){
							$cartas[] = $carta['carta'];
						}
						$puntos = IABrisca::totalPuntosEnCartas($cartas);
						$puntos_jugadores[$user['ID']] = $puntos;
					}
					
					$empate = false;

					$ganador_partida_id = 0;
					$ganador_partida_puntos = 0;
					foreach($puntos_jugadores as $id=>$puntos){
						if($puntos > $ganador_partida_puntos){
							$empate = false;
							$ganador_partida_id = $id;
							$ganador_partida_puntos = $puntos;
						}
						elseif($puntos == $ganador_partida_puntos){
							$empate = true;
						}
					}
					
					$database = new DB();
					
					foreach($puntos_jugadores as $id=>$puntos){
						if($empate){
							if($puntos != $ganador_partida_puntos){
								guardar_derrota($database, $id);
							}
						}
						else{
							if($puntos != $ganador_partida_puntos){
								guardar_derrota($database, $id);
							}
							else{
								guardar_victoria($database, $id);
							}
						}
						guardar_puntuacion_max($database, $id, $puntos);
					}
					
					// En caso de empate enviar la id de un ganador inexistente (-1). De lo contrario enviar la id del ganador.
					if($empate){
						procesasms(array('termina'=>array('ganador'=>'-1')), 'orden', $dbsqlite);
					}
					else{
						procesasms(array('termina'=>array('ganador'=>$ganador_partida_id)), 'orden', $dbsqlite);
					}
				}
				else{
					//La partida puede no estar terminada y no quedar cartas en el mazo
					$result = $dbsqlite->query("SELECT carta FROM cartas WHERE posicion = 'mazo';");
					$results = array_from_sqliteResponse($result);
					$cartas_mazo = array();
					foreach($results as $c){
						$cartas_mazo[] = $c['carta'];
					}
					
					if(count($cartas_mazo) > 0){
						// Se pueden repartir cartas
						
						// Cartas en mazo para poder repartir (incluir carta palo_siempre_manda AL FINAL)
						$usuarios = $dbsqlite->query("SELECT ID FROM usuarios;");
						$usuarios = array_from_sqliteResponse($usuarios);
						
						$i_t=count($usuarios);
						
						$p_ganador = array_search($ganador_id, $usuarios);
						for($i=0; $i<$i_t; ++$i){
							if($usuarios[$i]['ID'] === $ganador_id){
								$p_ganador = $i;
								break;
							}
						}
						
						for($i=$p_ganador; $i<$i_t +$p_ganador; ++$i){
							if(count($cartas_mazo) > 0){
								$carta = extraer_carta_azar($cartas_mazo);
							}
							else{
								$carta = $carta_manda_siempre;
							}
							// Pausar un segundo por cada carta que se reparte. Un segundo después de que el ganador se lleva las cartas se reparte la primera carta
							sleep(1);
							repartir_carta($dbsqlite, $usuarios[ClampCircular($i,0,$i_t-1)]['ID'], $carta);
						}
						
					}/*
					else{
						// No quedan cartas por repartir
						
					}
					*/
					
					usuario_lanza($dbsqlite, $ganador_id, true);
				}
				
				
				
				
			}
			else{
				// en $results tenemos un array con los resultados.
				$i_t = count($results);
				if($i_t === 1){
					// Solo queda uno por tirar. Ponerlo como próximo a tirar
					$ID_prox_usuario = $results[0]['ID'];
				}
				else{
					// Quedan varios por tirar. Es necesario elegir quien tirará.
					// Se cogerá las posiciones en la mesa y se elegirá la mayor más cercana a la posición del actual jugador.
					// De no haber ninguna superior, se cojerá la posición 1 al ser la primera inferior y ser imposible que haya tirado.
					// Se parte de un número que no puede ser 
					$hueco_sala_prox = 100;
					for($i=0; $i<$i_t; ++$i){
						if($results[$i]['hueco_sala'] > $hueco_sala && $results[$i]['hueco_sala'] < $hueco_sala_prox){
							$hueco_sala_prox = $results[$i]['hueco_sala'];
						}
					}
					
					// De no tener un hueco_sala_prox, el próximo a tirar será el que esté en el 1
					if($hueco_sala_prox === 100){
						$hueco_sala_prox = 1;
					}
					
					// Hacer lanzar al próximo usuario
					$result = $dbsqlite->query("SELECT ID FROM usuarios WHERE hueco_sala = '{$hueco_sala_prox}';");
					$ID_prox_usuario = $result->fetchArray(SQLITE3_ASSOC);
					$ID_prox_usuario = $ID_prox_usuario['ID'];
				}
				
				usuario_lanza($dbsqlite, $ID_prox_usuario);
			}
			
			return '{"resultado":true}';
		break;
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

function array_from_sqliteResponse(&$result){
	$results = array();
	while($r = $result->fetchArray(SQLITE3_ASSOC)){
		$results[] = $r;
	}
	return $results;
}

function repartir_carta(&$dbsqlite, $ID, $carta){
	$dbsqlite->query("UPDATE cartas SET posicion = '{$ID}', propietario = '{$ID}' WHERE carta = '{$carta}';");
	procesasms(array('reparte'=>array($ID=>$carta)), 'orden', $dbsqlite);
}

function ganar_cartas(&$dbsqlite, $ID, $cartas){
	$carta_where = " carta = '{$cartas[0]}'";
	for($i = 1; $i<$i_t=count($cartas); ++$i){
		$carta_where .= " OR carta = '{$cartas[$i]}' ";
	}
	$dbsqlite->query("UPDATE cartas SET posicion = '{$ID}_ganadas', propietario = {$ID}, palo_manda_mesa = 0 WHERE {$carta_where};");
	procesasms(array('gana'=>array($ID=>$cartas)), 'orden', $dbsqlite);
}

function extraer_carta_azar(&$cartas){
	$c = array_splice($cartas,rand(0, count($cartas)-1),1);
	return $c[0];
}

function usuario_lanza(&$dbsqlite, $ID, $primero=false){
	/*if($primero){
		$dbsqlite->query("UPDATE usuarios SET primero = 0");
	}*/
	$primero = $primero?', primero = 1':'';
	$dbsqlite->query("UPDATE usuarios SET lanza = 1{$primero} WHERE ID = {$ID};");
	procesasms(array('lanza'=>$ID), 'orden', $dbsqlite);
}

function meter_usuario(&$dbsqlite, &$usuario, $hueco_sala, &$salaInfo){
	procesasms($usuario['NICK'].' se ha unido a la partida', 'aviso', $dbsqlite);
	procesasms(array('p'=>$hueco_sala,'ID'=>$usuario['ID'],'NICK'=>$usuario['NICK']), 'config', $dbsqlite);
	
	$r = $salaInfo['jugadores_max'] - $salaInfo['p_total'];
	//Todavía faltan jugadores
	procesasms('Falta'.($r>1||$r==0?'n':'').' '.$r.' jugador'.($r>1||$r==0?'es':''), 'aviso', $dbsqlite);
}

function quitar_usuario(&$dbsqlite, &$usuario, &$salaInfo){
	procesasms($usuario['NICK'].' ha salido de la partida', 'aviso', $dbsqlite);
	procesasms(array('p'=>'-1','ID'=>$usuario['ID']), 'config', $dbsqlite);
	
	$r = $salaInfo['jugadores_max'] - $salaInfo['p_total'];
	//Todavía faltan jugadores
	procesasms('Falta'.($r>1||$r==0?'n':'').' '.$r.' jugador'.($r>1||$r==0?'es':''), 'aviso', $dbsqlite);
}

function guardar_victoria(&$database, $ID){
	$database->guardar_victoria('online', $ID);
}

function guardar_derrota(&$database, $ID){
	$database->guardar_derrota('online', $ID);
}

function guardar_puntuacion_max(&$database, $ID, $puntos){
	$database->guardar_puntuacion_max('online', $ID, $puntos);
}



?>