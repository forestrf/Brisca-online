<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
		<title>Jugar contra la máquina</title>
		<link type="text/css" rel="stylesheet" href="css/reset.css">
		<link type="text/css" rel="stylesheet" href="css/mesajuego.css">
		<link type="text/css" rel="stylesheet" href="css/chat.css">
		<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
		<script type="text/javascript" src="js/IABrisca.js"></script>
	</head>
	<body>
		<div id="contenedor">
			<div id="chat">
				<div id="historial">
					<div class="globo">
						<span class="nombre">Alguien</span>
						Comentario en el que pone algo
					</div>
					<div class="globo">
						<span class="nombre">Alguien</span>
						Comentario en el que pone algo Comentario en el que pone algo Comentario en el que pone algo Comentario en el que pone algo Comentario en el que pone algo Comentario en el que pone algo
					</div><div class="clear"></div>
					<div class="globo">
						<span class="nombre">Alguien</span>
						Comentario en el que pone algo Comentario en el que pone algo
					</div><div class="clear"></div>
					<div class="globo mio">
						<span class="nombre">Alguien</span>
						Comentario en el que pone algo Comentario en el que pone algo
					</div><div class="clear"></div>
					<div class="globo">
						<span class="nombre">Alguien</span>
						Comentario en el que pone algo
					</div><div class="clear"></div>
				</div>
				<input type="text" name="entradatxt" id="entradatxt" placeholder="Escribe un mensaje"><input type="button" value="enviar" id="enviar">
			</div>
			<div id="mesa">
				<div id="jugador">
					<div class="cartas"><img class="carta" src="" style="display:none" id="jugador_1_carta_1"><img class="carta" src="" style="display:none" id="jugador_1_carta_2"><img class="carta" src="" style="display:none" id="jugador_1_carta_3"></div>
					<div class="puntuacion"></div>
					<div class="nombre"></div>
				</div>
				<div id="jugador2">
					<div class="cartas"><img class="carta" src="" style="display:none" id="jugador_2_carta_1"><img class="carta" src="" style="display:none" id="jugador_2_carta_2"><img class="carta" src="" style="display:none" id="jugador_2_carta_3"></div>
					<div class="puntuacion"></div>
					<div class="nombre"></div>
				</div>
				<div id="jugador3">
					<div class="cartas"><img class="carta" src="" style="display:none" id="jugador_3_carta_1"><img class="carta" src="" style="display:none" id="jugador_3_carta_2"><img class="carta" src="" style="display:none" id="jugador_3_carta_3"></div>
					<div class="puntuacion"></div>
					<div class="nombre"></div>
				</div>
				<div id="jugador4">
					<div class="cartas"><img class="carta" src="" style="display:none" id="jugador_4_carta_1"><img class="carta" src="" style="display:none" id="jugador_4_carta_2"><img class="carta" src="" style="display:none" id="jugador_4_carta_3"></div>
					<div class="puntuacion"></div>
					<div class="nombre"></div>
				</div>
				
				<div id="mazo">
					<div id="palomando">
						<img class="carta" src="" style="display:none" id="carta_palo_manda_siempre">
					</div>
					<img id="carta_mazo" class="carta" src="img/cartas/back2.jpg">
				</div>
				
				<div id="cartas_mesa"><img class="carta" src="" style="display:none" id="mesa_carta_1"><img class="carta" src="" style="display:none" id="mesa_carta_2"><img class="carta" src="" style="display:none" id="mesa_carta_3"><img class="carta" src="" style="display:none" id="mesa_carta_4"></div>
			</div>
		</div>
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		<script type="text/javascript">
		
			var miNombre = "forest";
			
			
			
			
			
			
			//<input type="text" name="entradatxt" id="entradatxt" placeholder="Escribe un mensaje"><input type="submit" value="enviar" id="enviar">
			
			$('#enviar').click(enviaMensajeChat);
			$('#entradatxt').keydown(function(e){
				var code = e.which; // recommended to use e.which, it's normalized across browsers
				if(code==13)e.preventDefault();
				if(code==13||code==188||code==186){
					enviaMensajeChat();
				}
			});
			
			
			
		
			function enviaMensajeChat(){
				if($('#entradatxt').val().split(" ").join("") != ""){
					insertarChatComentario(miNombre, $('#entradatxt').val(), true, true);
				}
				$('#entradatxt').val("");
				//enviar mensaje por ajax. El mensaje se enviará y la conexión se quedará abierta. Cuando la conexión se cierre reabrir en un loop para recibir del chat
				
			}
		
			function insertarChatComentario(de, que, mio, forzarScroll){
				var hacerScroll = false;
				// Este if detecta si tenemos el scroll abajo del todo
				if($('#historial').scrollTop() + $('#historial').innerHeight() >= $('#historial')[0].scrollHeight){
					var hacerScroll = true;
				}
				// Agregar nuevo mensaje al chat html
				$('#historial').append('<div class="globo '+(mio?'mio':'')+'">'+
					'<span class="nombre">'+de+'</span>'+que+
					'</div><div class="clear"></div>');
				if(hacerScroll || forzarScroll){
					$('#historial').scrollTop(10000000);
				}
			}
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			IABriscaBaseInstancia = new IABriscaBase();
			IABriscaMesaInstancia = new IABriscaMesa();
			IABriscaMesaInstancia.iniciarMesa();
			
			
			
			
			var jugador1 = new IABriscaJugador();
			jugador1.iniciarJugador(1);
			var jugador2 = new IABriscaJugador();
			jugador2.iniciarJugador(2);
			var jugador3 = new IABriscaJugador();
			jugador3.iniciarJugador(3);
			var jugador4 = new IABriscaJugador();
			jugador4.iniciarJugador(4);
			
			IABriscaMesaInstancia.insertaJugadoresEnMesa([jugador1,jugador2,jugador3,jugador4]);

			
			// GO
			IABriscaMesaInstancia.comienzaPartida();

			

		</script>
	</body>
</html>