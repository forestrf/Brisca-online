<?php
// Comprobar si la sala está llena. De no estarlo, meter el usuario en la sala (consulta db).

// Estrategia: div contenedores con chat y un div contenedor, uno con la mesa de juego vacía y otro con listado de usuarios en la sala.
// Mediante ajax a vsplayerlobbyconsultas preguntar cuando está listo el juego. Cuando esté listo, cambiar al div de la mesa de juego
// mediante sms se enviarán json con texto o jugadas. sms debe ser modificado para validar las jugadas.

// No debería llamarse nunca a esta url sin sala desde algún menú, pero quien sabe
if(!isset($_GET['sala'])){
	header('Location: /');
	exit;
}

require_once('definiciones.php');
require_once('funciones.php');
require_once('php/IABrisca.class.php');
require_once('smsfunciones.php');

$database = new DB();


// Detectar si el usuario está logueado. Si no lo está, enviarlo a login. En caso de que está logueado, pero no se corresponda el login con la base de datos, lo mismo.
if(!$usuario = detectaLogueadoORedireccion($database)){
	exit;
}



if($usuario['sala'] !== '-1' && $usuario['sala'] !== $_GET['sala']){
	header('Location: /salaabierta.php?sala='.$usuario['sala']); // POOOOOOOOOR HACEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEER
	exit;
}

// El usuario no está en ninguna sala

$sala = intval($_GET['sala']);


$salaInfo = $database->salaInfo($sala);
//print_r($salaInfo);


$hueco_sala = '';

// El usuario ya está en la sala?
for($i = 1; $i<=4; ++$i){
	if($usuario['ID'] === $salaInfo[$i]){
		$hueco_sala = $i;
		break;
	}
}

if($hueco_sala === ''){
	//El usuario no está en la sala
	if($salaInfo['p_total'] < $salaInfo['jugadores_max']){
		// EL usuario cabe en la sala y no está en otra sala. Meterle
		for($i = 1; $i<=4; ++$i){
			if($salaInfo[$i] === '-1'){
				$hueco_sala = $i;
				break;
			}
		}
		
		// Debería ser true siempre
		if(isset($hueco_sala)){
			$database->salaMeterUsuario($usuario['ID'], $sala, $hueco_sala);
			
			$dbsqlite = abredbsqlitesala($sala);
			++$salaInfo['p_total'];
			
			meter_usuario($dbsqlite, $usuario, $hueco_sala, $salaInfo);
			
			$salaInfo[$hueco_sala] = $usuario['ID'];
			
			if($salaInfo['p_total'] == $salaInfo['jugadores_max']){
				//Ya están todos los jugadores, lanzar partida
				procesasms('Iniciando partida', 'aviso', $dbsqlite);
				procesasms('START', 'orden', $dbsqlite);
				
				//Elejir quien será el que empieza.
				$comienza = rand(1,$salaInfo['jugadores_max']);
				
				//Repartir cartas
				$cartas = IABrisca::$cartasTotalArray;
				
				foreach($cartas as $c){
					$dbsqlite->query("INSERT INTO cartas (carta, posicion) VALUES('{$c}', 'mazo');");
				}
				
				$tot = $salaInfo['jugadores_max'];
				
				
				$carta_siempre_manda = extraer_carta_azar($cartas);
				$dbsqlite->query("UPDATE cartas SET posicion = 'carta_siempre_manda', palo_manda_siempre = 1 WHERE carta = '{$carta_siempre_manda}';");
				procesasms(array('palo_manda_siempre'=>$carta_siempre_manda), 'orden', $dbsqlite);
				
				// Por cada jugador
				for($i=$comienza; $i<$tot+$comienza; ++$i){
					//Por cada carta
					for($j=0; $j<3; ++$j){
						//Sacar una carta al azar de la baraja
						$carta = extraer_carta_azar($cartas);
						$n = ClampCircular($i, 1, $tot);
						repartir_carta($dbsqlite, id_desde_hueco_sala($n), $carta);
					}
					$dbsqlite->query("INSERT INTO usuarios (ID, lanza, hueco_sala) VALUES (".id_desde_hueco_sala($n).", 0, ".$n.");");
				}
				
				usuario_lanza($dbsqlite, id_desde_hueco_sala($comienza), true);
			}
			/*else{
				// Faltan jugadores
			}*/
			$dbsqlite->close();
			unset($dbsqlite);
		}
	}
}

function id_desde_hueco_sala($i){
	global $salaInfo;
	return $salaInfo[$i];
}


?>

<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
		<title>Jugar contra la máquina</title>
		<link type="text/css" rel="stylesheet" href="css/reset.css">
		<link type="text/css" rel="stylesheet" href="css/mesajuego.css">
		<link type="text/css" rel="stylesheet" href="css/chat.css">
		<link type="text/css" rel="stylesheet" href="css/vsplayer.css">
		<script type="text/javascript" src="js/IABrisca.js"></script>
		<script type="text/javascript" src="js/funciones.js"></script>
	</head>
	<body>
		<div id="contenedor">
			<div id="chat">
			
			
			<form method="POST" action="vsplayerlobbyconsultas.php">
				<input type="submit" value="abandonar sala">
				<input type="hidden" name="accion" value="abandonarsala">
			</form>
			
			
				<div id="historial">
					<div class="globo">
						Te has unido a la sala "<?php echo html_entity_decode($salaInfo['nombre']);?>"
					</div>
				</div>
				<input type="text" name="entradatxt" id="entradatxt" placeholder="Escribe un mensaje"><input type="button" value="enviar" id="enviar">
			</div>
			<div id="mesa">
				<div id="todas_las_cartas">
					<!--llenar este div con todas las cartas desde js-->
				</div>
				
				<!--Posiciones. Para poder ver donde están-->
				<div id="posiciones">
					<!--cartas en mano-->
					<div id="P1C1"></div>
					<div id="P1C2"></div>
					<div id="P1C3"></div>
					<div id="P2C1"></div>
					<div id="P2C2"></div>
					<div id="P2C3"></div>
					<div id="P3C1"></div>
					<div id="P3C2"></div>
					<div id="P3C3"></div>
					<div id="P4C1"></div>
					<div id="P4C2"></div>
					<div id="P4C3"></div>
					
					<!--cartas ganadas-->
					<div id="P1C0"></div>
					<div id="P2C0"></div>
					<div id="P3C0"></div>
					<div id="P4C0"></div>
					
					<!--cartas palo que manda-->
					<div id="MC0"></div>
					
					<!--cartas en mesa-->
					<div id="MC1"></div>
					<div id="MC2"></div>
					<div id="MC3"></div>
					<div id="MC4"></div>
					
					<!--carta mazo-->
					<div id="MM"></div>
					
					<!--Marcador de puntos-->
					<div id="P1N" style="<?php echo in_array($salaInfo['jugadores_max'], array(2,3,4))?'display:block':''?>"></div>
					<div id="P2N" style="<?php echo in_array($salaInfo['jugadores_max'], array(3,4))?'display:block':''?>"></div>
					<div id="P3N" style="<?php echo in_array($salaInfo['jugadores_max'], array(2,3,4))?'display:block':''?>"></div>
					<div id="P4N" style="<?php echo in_array($salaInfo['jugadores_max'], array(4))?'display:block':''?>"></div>
				</div>
				
				<div id="mensaje_fin" class="off"></div>
				
				<div id="mensaje_esperando">
					Esperando a que se unan más jugadores
				</div>
			</div>
		</div>
		
		soy <?php echo $hueco_sala?>
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		<script type="text/javascript">
		
			var hueco_sala = <?php echo $hueco_sala?>;
			var miId = <?php echo $usuario['ID']?>;
			var miNombre = '<?php echo $usuario['NICK']?>';
			
			var cantidadJugadores = <?php echo $salaInfo['jugadores_max']?>;
			
			// "ID":"hueco_sala"
			var jugadores_por_id = {<?php
				echo '"'.$salaInfo[1].'":asigna_hueco_correcto(1)';
				for($i=2; $i<=$salaInfo['jugadores_max']; ++$i){
					echo ',"'.$salaInfo[$i].'":asigna_hueco_correcto('.$i.')';
				}
			?>};
			
			function asigna_hueco_correcto(i){
				return ClampCircular(i-hueco_sala+1,1,cantidadJugadores)
			}
		
		
		
			// Iniciar el script de la brisca
			function iniciarPartidaBrisca_VSHUMAN(){
				
				
				
				document.getElementById("mensaje_esperando").style.display = "none";
			
				IABriscaInstancia = new IABrisca();
				
				IABriscaInstancia.moverCarta = moverCarta;
				IABriscaInstancia.seteaPuntos = seteaPuntos;
				IABriscaInstancia.pideCartaHumano = pideCartaHumano;
				IABriscaInstancia.fin = fin;
				
				IABriscaInstancia.console2.on = true;
			
		
		
		
				instanciaCartas();
				
			}
			
			
			function iniciarPartidaBrisca_VSHUMAN_2(palo_manda_siempre){
				var cantidadJugadores = <?php echo $salaInfo['jugadores_max']?>;
				IABriscaInstancia.IABriscaMesaInstancia.iniciarMesa({'palo_manda_siempre':palo_manda_siempre});
				
				
				//IABriscaInstancia.IABriscaMesaInstancia.tiempoEntreRondas = 0;
				//IABriscaInstancia.IABriscaMesaInstancia.tiempoRepartiendoCarta = 0;
				
				jugadores = [];
				
				switch(cantidadJugadores){
					case 2:
						jugadores[0] = new IABriscaInstancia.HumanoBriscaJugador();
						jugadores[0].iniciarJugador(1);
						jugadores[0].callback = jugadorLanzaCarta;
						jugadores[1] = new IABriscaInstancia.CallbackBriscaJugador();
						jugadores[1].iniciarJugador(3);
						seteaPuntos('P1', 0);
						seteaPuntos('P3', 0);
						document.getElementById("P1N").style.display = "inherit";
						document.getElementById("P2N").style.display = "none";
						document.getElementById("P3N").style.display = "inherit";
						document.getElementById("P4N").style.display = "none";
					break;
					case 3:
						jugadores[0] = new IABriscaInstancia.HumanoBriscaJugador();
						jugadores[0].iniciarJugador(1);
						jugadores[0].callback = jugadorLanzaCarta;
						jugadores[1] = new IABriscaInstancia.CallbackBriscaJugador();
						jugadores[1].iniciarJugador(2);
						jugadores[2] = new IABriscaInstancia.CallbackBriscaJugador();
						jugadores[2].iniciarJugador(3);
						seteaPuntos('P1', 0);
						seteaPuntos('P2', 0);
						seteaPuntos('P3', 0);
						document.getElementById("P1N").style.display = "inherit";
						document.getElementById("P2N").style.display = "inherit";
						document.getElementById("P3N").style.display = "inherit";
						document.getElementById("P4N").style.display = "none";
					break;
					case 4:
						jugadores[0] = new IABriscaInstancia.HumanoBriscaJugador();
						jugadores[0].iniciarJugador(1);
						jugadores[0].callback = jugadorLanzaCarta;
						jugadores[1] = new IABriscaInstancia.CallbackBriscaJugador();
						jugadores[1].iniciarJugador(2);
						jugadores[2] = new IABriscaInstancia.CallbackBriscaJugador();
						jugadores[2].iniciarJugador(3);
						jugadores[3] = new IABriscaInstancia.CallbackBriscaJugador();
						jugadores[3].iniciarJugador(4);
						seteaPuntos('P1', 0);
						seteaPuntos('P2', 0);
						seteaPuntos('P3', 0);
						seteaPuntos('P4', 0);
						document.getElementById("P1N").style.display = "inherit";
						document.getElementById("P2N").style.display = "inherit";
						document.getElementById("P3N").style.display = "inherit";
						document.getElementById("P4N").style.display = "inherit";
					break;
					default:
						return;
					break;
				}
				
				
				
				// Inserta jugadores y comienza
				IABriscaInstancia.IABriscaMesaInstancia.agregaJugadoresAMesa(jugadores);
			}
			
			
			
			
			
			
			
			
			
			
			
			
			
			///////////////////////////////////////////////////
			//
			// FUNCIONES
			//
			///////////////////////////////////////////////////
			
			
			
			
			

			
			
			function fin(resultados){
				//console.log(resultados);
				mensaje_fin = document.getElementById("mensaje_fin");
				mensaje_fin.className = "";
				
				var mensaje = "HAS GANADO";
				
				var misPuntos = resultados["1"];
				for(var i in resultados){
					if(i != "1"){
						if(resultados[i] > misPuntos){
							mensaje = "HAS PERDIDO";
							continue;
						}
						else if(resultados[i] ==  misPuntos){
							mensaje = "NO HAY GANADOR";
						}
					}
				}
				
				mensaje_fin.innerHTML = mensaje + "<br><br>Tu puntuación es " + misPuntos + " puntos<br><br><br><br><div class='boton' onclick='resetBrisca()'>Volver a jugar</div><br>"+
				"<a class='boton' href='/'>Ir al home</a><br>";
			}
			
			
			
			
			
			
			
			
			
			
			
			
			//<input type="text" name="entradatxt" id="entradatxt" placeholder="Escribe un mensaje"><input type="submit" value="enviar" id="enviar">
			enviar = document.getElementById('enviar');
			entradatxt = document.getElementById('entradatxt');
			historial = document.getElementById('historial');
			
			enviar.onclick = enviaMensajeChat;
			entradatxt.onkeydown = function(e){
				var code = e.which; // recommended to use e.which, it's normalized across browsers
				if(code==13)e.preventDefault();
				if(code==13||code==188||code==186){
					enviaMensajeChat();
				}
			};
			
			
			sala = <?php echo $salaInfo['ID']?>;
			
			function enviarMensaje(params){
				var r = new XMLHttpRequest();
				var url = "sms.php";
				r.open("POST", url, true);
				r.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
				r.send(params);
				//enviar mensaje por ajax. El mensaje se enviará y la conexión se quedará abierta. Cuando la conexión se cierre reabrir en un loop para recibir del chat
			}
		
			function enviaMensajeChat(){
				if(entradatxt.value.split(" ").join("") != ""){
					// insertarChatComentario(miNombre, entradatxt.value, true, true);
					var params = "sala="+sala+"&msg=" + entradatxt.value;
					enviarMensaje(params);
				}
				entradatxt.value = "";
				//enviar mensaje por ajax. El mensaje se enviará y la conexión se quedará abierta. Cuando la conexión se cierre reabrir en un loop para recibir del chat
			}
		
			
				
				
			function insertarChatComentario(de, que, mio, forzarScroll){
				var hacerScroll = false;
				// Este if detecta si tenemos el scroll abajo del todo
				if(historial.scrollTop + historial.innerHeight >= historial.scrollHeight){
					var hacerScroll = true;
				}
				// Agregar nuevo mensaje al chat html
				historial.innerHTML += '<div class="globo '+(mio?'mio':'')+'">'+
					(de!==null?('<span class="nombre">'+de+'</span>'):'')+que+
					'</div><div class="clear"></div>';
				if(hacerScroll || forzarScroll){
					historial.scrollTop = 10000000;
				}
			}
			
			
			
			
			loopRequest = null;
			loop = function(){
				loopRequest = new XMLHttpRequest();
				var url = "sms.php";
				var params = "sala=<?php echo $salaInfo['ID']?>&ult=" + ult;
				loopRequest.open("POST", url, true);
				loopRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
				loopRequest.onreadystatechange = function(){
					if(loopRequest.readyState == 4){
						if(loopRequest.status == 200){
							var resp = JSON.parse(loopRequest.responseText);
							if(resp.length>0){
								for(var i in resp){
									procesaMensaje(resp[i].usuario, resp[i].mensaje);
								}
								ult = resp[resp.length-1].n;
							}
						}
						// Medio segundo antes de reconectar al chat tras recibir una respuesta
						setTimeout(loop, 500);
					}
				};
				loopRequest.send(params);
			};
			
			function procesaMensaje(usuario, json){
				json = JSON.parse(json);
				if(typeof json["msg"] !== "undefined"){
					insertarChatComentario(usuario, json["msg"], usuario===miNombre);
				}
				else if(typeof json["aviso"] !== "undefined"){
					insertarChatComentario(null, json["aviso"], false);
				}
				else if(typeof json["orden"] !== "undefined"){
					console.log(json["orden"]);
					if(json["orden"] === 'START'){
						console.log('gogogo');
						iniciarPartidaBrisca_VSHUMAN();
					}
					else{
						var orden = json["orden"];
						if(typeof orden['reparte'] !== "undefined"){
							for(var i in orden['reparte']){
								console.log('Repartir a '+i+' la '+orden['reparte'][i]);
								IABriscaInstancia.IABriscaMesaInstancia.peticionJugadorRobar(jugadores[jugadores_por_id[i]-1], orden['reparte'][i]);
							}
						}
						else if(typeof orden['palo_manda_siempre'] !== "undefined"){
							console.log('Palo manda siempre = '+orden['palo_manda_siempre']);
							iniciarPartidaBrisca_VSHUMAN_2(orden['palo_manda_siempre']);
						}
						else if(typeof orden['lanza'] !== "undefined"){
							console.log('Petición de lanzar carta al jugador '+orden['lanza']);
							jugadores[jugadores_por_id[orden['lanza']]-1].lanzaCarta(jugadorLanzaCarta);
							if(miId != orden['lanza']){
								borrarPideCartaHumano();
							}
						}
						else if(typeof orden['gana'] !== "undefined"){
							for(var id_ganador in orden['gana']){
								console.log('Petición de ganar cartas al jugador '+id_ganador);
								IABriscaInstancia.IABriscaMesaInstancia.peticionJugadorGanarMesa(jugadores[jugadores_por_id[id_ganador]-1]);
								borrarPideCartaHumano();
							}
						}
						else{
							for(var i in orden){
								var jugador = jugadores_por_id[i]-1;
								
								IABriscaInstancia.IABriscaMesaInstancia.peticionJugadorLanzarRecibiendoCarta(
									jugadores[jugador],
									orden[i]
								);
							}
						}
						//console.log(orden);
					}
				}
				else if(typeof json["config"] !== "undefined"){
					var config = json["config"];
					jugadores_por_id[config["ID"]] = asigna_hueco_correcto(config["p"]);
					console.log('El jugador con ID '+config["ID"]+' ocupará la posición '+config["p"]+' rectificada a '+jugadores_por_id[config["ID"]]);
				}
			}
			
			
			// callback para jugador humano
			function jugadorLanzaCarta(jugador, carta){
				var params = "sala="+sala+"&jugada=" + carta;
				enviarMensaje(params);
			}
			
			
			
			// loop recibir msg
			
			ult = 0;
			
			loop();
			
			
			
			
			
			

		</script>
	</body>
</html>