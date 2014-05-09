<?php
// Comprobar si la sala está llena. De no estarlo, meter el usuario en la sala (consulta db).

// Estrategia: div contenedores con chat y un div contenedor, uno con la mesa de juego vacía y otro con listado de usuarios en la sala.
// Mediante ajax a vsplayerlobbyconsultas preguntar cuando está listo el juego. Cuando esté listo, cambiar al div de la mesa de juego
// mediante sms se enviarán json con texto o jugadas. sms debe ser modificado para validar las jugadas.

// No debería llamarse nunca a esta url sin sala desde algún menú, pero quien sabe
if(!isset($_GET['sala'])){
	header('Location: /', true, 302);
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
	header('Location: /salaabierta.php?sala='.$usuario['sala'], true, 302);
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
					// En caso de ser por parejas, decidir aquí las parejas.
					// Las parejas serán asignadas 1-3 y 2-4
					if($salaInfo['parejas'] === '1'){
						$pareja = $n%2===0?2:1;
						$dbsqlite->query("INSERT INTO usuarios (ID, lanza, hueco_sala, pareja) VALUES (".id_desde_hueco_sala($n).", 0, ".$n.", {$pareja});");
						procesasms(array('pareja'=>array('ID'=>id_desde_hueco_sala($n), 'pareja'=>$pareja)), 'orden', $dbsqlite);
					}
					else{
						$dbsqlite->query("INSERT INTO usuarios (ID, lanza, hueco_sala) VALUES (".id_desde_hueco_sala($n).", 0, ".$n.");");
					}
				}
				
				$database->salaMarcarIniciada($sala);
				$dbsqlite->query("UPDATE estados SET valor = '1' WHERE clave = 'iniciado';");
				$dbsqlite->query("INSERT INTO estados (clave, valor) VALUES ('parejas', '{$salaInfo['parejas']}');");
				
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
		<title>Jugar contra otro(s) jugadores</title>
		<link type="text/css" rel="stylesheet" href="css/reset.css">
		<link type="text/css" rel="stylesheet" href="css/mesajuego.css">
		<link type="text/css" rel="stylesheet" href="css/chat.css">
		<link type="text/css" rel="stylesheet" href="css/vsplayer.css">
		<script type="text/javascript" src="js/IABrisca.js"></script>
		<script type="text/javascript" src="js/funciones.js"></script>
	</head>
	<body>
		<div id="contenedor">
			<form method="POST" action="vsplayerlobbyconsultas.php" class="boton_abandonar_sala" id="abandonar_sala">
				<input type="submit" value="abandonar sala">
				<input type="hidden" name="accion" value="abandonarsala">
			</form>
			<div id="chat">
				<div id="historial">
					<div class="globo">
						Te has unido a la sala "<?php echo html_entity_decode($salaInfo['nombre']);?>"
					</div>
					<div class="clear"></div>
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
		
		
		
		
		
		
		<script type="text/javascript">
		
			var hueco_sala = <?php echo $hueco_sala?>;
			var miId = <?php echo $usuario['ID']?>;
			var miNombre = '<?php echo $usuario['NICK']?>';
			
			var parejas = [];
			
			var cantidadJugadores = <?php echo $salaInfo['jugadores_max']?>;
			
			// "ID":"hueco_sala"
			var jugadores_por_id = {<?php $t='';
				for($i=1; $i<=$salaInfo['jugadores_max']; ++$i){
					if($salaInfo[$i] != -1){
						$t.=',"'.$salaInfo[$i].'":asigna_hueco_correcto('.$i.')';
					}
				}
				echo substr($t, 1);
			?>};
			
			// "ID":"NICK"
			var nick_desde_id = {<?php $t='';
				for($i=1; $i<=$salaInfo['jugadores_max']; ++$i){
					if($salaInfo[$i] != -1){
						$t.=',"'.$salaInfo[$i].'":"'.$database->nickDesdeId($salaInfo[$i]).'"';
					}
				}
				echo substr($t, 1);
			?>};
			
			function asigna_hueco_correcto(i){
				return ClampCircular(i-hueco_sala+1,1,cantidadJugadores)
			}
			
			function id_desde_hueco(hueco){
				for(var i in jugadores_por_id){
					if(jugadores_por_id[i] == hueco){
						return i;
					}
				}
			}
		
		
		
			// Iniciar el script de la brisca
			function iniciarPartidaBrisca_VSHUMAN(){
				document.getElementById("mensaje_esperando").style.display = "none";
			
				IABriscaInstancia = new IABrisca();
				
				IABriscaInstancia.moverCarta = moverCarta;
				IABriscaInstancia.seteaPuntos = function(id, puntos){
					var id2 = cantidadJugadores==2&&id==3?2:id;
					seteaPuntos(id, puntos, nick_desde_id[id_desde_hueco(id2)]);
				};
				IABriscaInstancia.pideCartaHumano = pideCartaHumano;
				
				//IABriscaInstancia.console2.on = true;
				
				
				instanciaCartas(true);
			}
			
			
			function iniciarPartidaBrisca_VSHUMAN_2(palo_manda_siempre){
				var cantidadJugadores = <?php echo $salaInfo['jugadores_max']?>;
				IABriscaInstancia.IABriscaMesaInstancia.iniciarMesa({'palo_manda_siempre':palo_manda_siempre});
				IABriscaInstancia.IABriscaMesaInstancia.lanzaRonda = function(){};
				
				
				//IABriscaInstancia.IABriscaMesaInstancia.tiempoEntreRondas = 0;
				//IABriscaInstancia.IABriscaMesaInstancia.tiempoRepartiendoCarta = 0;
				
				jugadores = [];
				var j_array = [];
				
				for(var j = 1; j <= 4; ++j){
					document.getElementById("P"+j+"N").style.display = "none";
				}
				
				switch(cantidadJugadores){
					case 2:
						j_array = [1,3];
					break;
					case 3:
						j_array = [1,2,3];
					break;
					case 4:
						j_array = [1,2,3,4];
					break;
				}
				
				for(var i = 0; i < j_array.length; ++i){
					jugadores[i] = new IABriscaInstancia.HumanoBriscaJugador();
					jugadores[i].iniciarJugador(j_array[i]);
					seteaPuntos(j_array[i], 0, nick_desde_id[id_desde_hueco(j_array[i])]);
					document.getElementById("P"+j_array[i]+"N").style.display = "inherit";
				}
				
				
				// Inserta jugadores y comienza
				IABriscaInstancia.IABriscaMesaInstancia.agregaJugadoresAMesa(jugadores);
				
				recuentoCartas = IABriscaInstancia.IABriscaBaseInstancia.cartasTotalArray.length;
			}
			
			
			
			
			
			
			
			
			
			
			
			
			
			///////////////////////////////////////////////////
			//
			// FUNCIONES
			//
			///////////////////////////////////////////////////
			
			
			
			
			
			//<input type="text" name="entradatxt" id="entradatxt" placeholder="Escribe un mensaje"><input type="submit" value="enviar" id="enviar">
			enviar = document.getElementById('enviar');
			entradatxt = document.getElementById('entradatxt');
			historial = document.getElementById('historial');
			
			enviar.onclick = enviaMensajeChat;
			entradatxt.onkeydown = function(e){
				var code = e.which; // recommended to use e.which, it's normalized across browsers
				if(code==13){
					e.preventDefault();
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
				if(historial.scrollTop + historial.offsetHeight>= historial.scrollHeight){
					var hacerScroll = true;
				}
				// Agregar nuevo mensaje al chat html
				historial.innerHTML += '<div class="globo '+(mio?'mio':'')+' '+(de!==null?'aviso':'')+'">'+
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
			
			cartasFalsas_i = 0;
			function procesaMensaje(usuario, json){
				json = JSON.parse(json);
				if(typeof json["msg"] !== "undefined"){
					insertarChatComentario(usuario, json["msg"], usuario===miNombre, usuario===miNombre);
				}
				else if(typeof json["aviso"] !== "undefined"){
					insertarChatComentario(null, json["aviso"], false);
				}
				else if(typeof json["orden"] !== "undefined"){
					//console.log(json["orden"]);
					switch(json["orden"]){
						case 'START':
							console.log('gogogo');
							iniciarPartidaBrisca_VSHUMAN();
						break;
						case 'detener':
							console.log('detener partida');
							borrarPideCartaHumano();
						break;
						default:
							var orden = json["orden"];
							if(typeof orden['reparte'] !== "undefined"){
								for(var i in orden['reparte']){
									if(orden['reparte'][i] === 'x'){
										console.log('Repartir a '+nick_desde_id[i]+' carta desconocida');
										IABriscaInstancia.IABriscaMesaInstancia.peticionJugadorRobar(jugadores[jugadores_por_id[i]-1], 'F'+cartasFalsas_i);
										++cartasFalsas_i;
									}
									else{
										console.log('Repartir a '+nick_desde_id[i]+' la carta '+orden['reparte'][i]);
										IABriscaInstancia.IABriscaMesaInstancia.peticionJugadorRobar(jugadores[jugadores_por_id[i]-1], orden['reparte'][i]);
									}
									--recuentoCartas;
									if(recuentoCartas===0){
										// Se han repartido todas las cartas. Eliminar cartas F que no estén en uso
										for(var j = 0; j < IABriscaInstancia.IABriscaBaseInstancia.cartasTotalArray.length; ++j){
											var carta = 'F'+j;
											var cartaF = document.getElementById('carta_'+carta);
											var cartaEnPersona = false;
											for(var k in jugadores){
												if(ArrayIndexOf(jugadores[k].cartasEnMano, carta) !== -1){
													cartaEnPersona = true;
													break;
												}
											}
											if(!cartaEnPersona){
												// La carta no está en ningún usuario, borrar
												cartaF.remove();
											}
										}
										for(var j in IABriscaInstancia.IABriscaBaseInstancia.cartasTotalArray){
											var carta = IABriscaInstancia.IABriscaBaseInstancia.cartasTotalArray[j];
											var cartaF = document.getElementById('carta_'+carta);
											var cartaEnPersona = false;
											for(var k in jugadores){
												if(ArrayIndexOf(jugadores[k].cartasEnMano, carta) !== -1){
													cartaEnPersona = true;
													break;
												}
											}
											
											if(!cartaEnPersona){
												// La carta no está en ningún usuario, borrar
												cartaF.style.top = "-100%";
												cartaF.className += ' sintransition';
												cartaF.offsetHeight;
												cartaF.className = carta1Obj.className.split(" sintransition").join("");
											}
										}
									}
								}
							}
							else if(typeof orden['palo_manda_siempre'] !== "undefined"){
								console.log('Palo manda siempre = '+orden['palo_manda_siempre']);
								iniciarPartidaBrisca_VSHUMAN_2(orden['palo_manda_siempre']);
							}
							else if(typeof orden['lanza'] !== "undefined"){
								console.log('Petición de lanzar carta al jugador '+nick_desde_id[orden['lanza']]);
								if(miId == orden['lanza']){
									jugadores[jugadores_por_id[orden['lanza']]-1].lanzaCarta(jugadorLanzaCarta);
								}
								else{
									borrarPideCartaHumano();
								}
							}
							else if(typeof orden['gana'] !== "undefined"){
								for(var id_ganador in orden['gana']){
									console.log('Petición de ganar cartas al jugador '+nick_desde_id[id_ganador]);
									var cartasMesaTemp = IABriscaInstancia.IABriscaMesaInstancia.cartasEnMesa.slice(0);
									IABriscaInstancia.IABriscaMesaInstancia.peticionJugadorGanarMesa(jugadores[jugadores_por_id[id_ganador]-1]);
									borrarPideCartaHumano();
									if(typeof parejas["p"+miId] != "undefined"){
										console.log('Victoria de pareja');
										var jug = jugadores[ClampCircular(jugadores_por_id[id_ganador] +2, 1, 4)-1];
										jug.ganaMesa(cartasMesaTemp);
										IABriscaInstancia.seteaPuntos(jug.jugadorID, IABriscaInstancia.IABriscaBaseInstancia.totalPuntosEnCartas(jug.cartasGanadas));
									}
								}
							}
							else if(typeof orden['termina'] !== "undefined"){
								var ganador = orden['termina']['ganador'];
								if(typeof parejas["p"+miId] != "undefined"){
									if(parejas["p"+miId] == ganador){
										ganador = miId;
									}
									else{
										ganador = -1;
									}
								}
								else{
									console.log('Partida terminada. id del ganador: '+ganador);
								}
								if(ganador == miId){
									fin_mensaje(1, IABriscaInstancia.IABriscaBaseInstancia.totalPuntosEnCartas(jugadores[0].cartasGanadas));
								}
								else if(ganador == -1){
									fin_mensaje(0, IABriscaInstancia.IABriscaBaseInstancia.totalPuntosEnCartas(jugadores[0].cartasGanadas));
								}
								else{
									fin_mensaje(-1, IABriscaInstancia.IABriscaBaseInstancia.totalPuntosEnCartas(jugadores[0].cartasGanadas));
								}
							}
							else if(typeof orden['pareja'] !== "undefined"){
								parejas["p"+orden['pareja']['ID']] = orden['pareja']['pareja'];
							}
							else{
								for(var i in orden){
									console.log("El jugador "+nick_desde_id[i]+" tira la carta "+orden[i]);
									
									var jugador = jugadores[jugadores_por_id[i]-1];
									
									if(i != miId){
										if(ArrayIndexOf(jugador.cartasEnMano, orden[i]) === -1){
											var cartaFalsaAzar = Math.max(0,Math.min(Math.floor(Math.random()*jugador.cartasEnMano.length),jugador.cartasEnMano.length-1));
											while(jugador.cartasEnMano[cartaFalsaAzar].indexOf("F") === -1){
												cartaFalsaAzar = Math.max(0,Math.min(Math.floor(Math.random()*jugador.cartasEnMano.length),jugador.cartasEnMano.length-1));
											}
											cartaFalsaAzar_carta = jugador.cartasEnMano[cartaFalsaAzar];
											
											sustituyeCarta(cartaFalsaAzar_carta, orden[i]);
											
											jugador.cartasEnMano.splice(cartaFalsaAzar, 1);
										}
									}
									
									IABriscaInstancia.IABriscaMesaInstancia.peticionJugadorLanzarRecibiendoCarta(
										jugador,
										orden[i]
									);
								}
							}
						break;
						//console.log(orden);
					}
				}
				else if(typeof json["config"] !== "undefined"){
					var config = json["config"];
					if(config["p"] != -1){
						jugadores_por_id[config["ID"]] = asigna_hueco_correcto(config["p"]);
						nick_desde_id[config["ID"]] = config["NICK"];
						console.log('El jugador con ID '+config["ID"]+' ocupará la posición '+config["p"]+' rectificada a '+jugadores_por_id[config["ID"]]);
					}
					else{
						delete jugadores_por_id[config["ID"]];
						delete nick_desde_id[config["ID"]];
						console.log('El jugador con ID '+config["ID"]+' se ha quitado de la lista de jugadores.');
					}
				}
			}
			
			
			// callback para jugador humano
			function jugadorLanzaCarta(jugador, carta){
				var params = "sala="+sala+"&jugada=" + carta;
				enviarMensaje(params);
			}
			
			
			function resetBrisca(){
				document.getElementById('abandonar_sala').submit();
			}
			
			
			
			// loop recibir msg
			ult = 0;
			loop();

		</script>
	</body>
</html>
