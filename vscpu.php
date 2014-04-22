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
				<div id="todas_las_cartas">
					<!--llenar este div con todas las cartas desde js-->
				</div>
				<div id="todas_las_cartas_visible">
					<!--Mover aquí las cartas para que sean visibles-->
				</div>
				
				<!--Posiciones. Para poder ver donde están-->
				<div id="posiciones">
					<div id="P1C1">Player 1 Carta 1</div>
					<div id="P1C2">Player 1 Carta 2</div>
					<div id="P1C3">Player 1 Carta 3</div>
					<div id="P2C1">Player 2 Carta 1</div>
					<div id="P2C2">Player 2 Carta 2</div>
					<div id="P2C3">Player 2 Carta 3</div>
					<div id="P3C1">Player 3 Carta 1</div>
					<div id="P3C2">Player 3 Carta 2</div>
					<div id="P3C3">Player 3 Carta 3</div>
					<div id="P4C1">Player 4 Carta 1</div>
					<div id="P4C2">Player 4 Carta 2</div>
					<div id="P4C3">Player 4 Carta 3</div>
					
					<div id="MC0">Palo Manda Siempre</div>
					<div id="MC1">Mesa Carta 1</div>
					<div id="MC2">Mesa Carta 2</div>
					<div id="MC3">Mesa Carta 3</div>
					<div id="MC4">Mesa Carta 4</div>
					
					<div id="MM">Mesa Mazo</div>
				</div>
				
				
			</div>
		</div>
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		<script type="text/javascript">
		
			
		
			/*window.onresize=function(){
				var height = window.innerHeight;
				var mesa = document.getElementById('mesa');
				var chat = document.getElementById('chat');
				mesa.style.height = height+"px";
				mesa.style.width = height+"px";
				chat.style.width = (window.innerWidth - height)+"px";
			};*/
		
		
		
			var miNombre = "forest";
			
			
			
			// Indicar el tamaño de las cartas mediante javascript editando el style
			// Tamaño de las cartas = 201 x 279
			// ratio = 201 / 279 = 0.72
			// ancho = alto * 0.72
			// alto = ancho / 0.72
			var aspectRatio = 0.72;
			
			var maximo = Math.min(window.innerHeight,window.innerWidth);
			var widthCarta = maximo*0.1;
			var heightCarta = widthCarta/aspectRatio;
			var widthCarta2 = widthCarta/2;
			var heightCarta2 = heightCarta/2;
			
			var todas_las_cartas = document.getElementById('todas_las_cartas');
			var arrCartas = IABriscaBaseInstancia.cartasTotalArray;
			for(var i in arrCartas){
				todas_las_cartas.innerHTML += '<img class="carta" style="width:'+widthCarta+'px;height:'+heightCarta+'px" id="carta_'+arrCartas[i]+'" src="img/cartas/'+arrCartas[i]+'.jpg">';
			}
			
			var mazo_cartas = document.getElementById('MM');
			mazo_cartas.innerHTML = '<img class="carta" style="width:'+widthCarta+'px;height:'+heightCarta+'px" src="img/cartas/back2.jpg">';
			
			
			// Mover la carta a dónde
			function moverCartaA(carta, hasta){
				var cartaObj = document.getElementById('carta_'+carta);
				var hastaObj = document.getElementById(hasta);
				if(cartaObj.parentElement.id != 'todas_las_cartas_visible'){document.getElementById('todas_las_cartas_visible').appendChild(cartaObj);}
				
				cartaObj.style.top = hastaObj.offsetTop -heightCarta2+"px";
				cartaObj.style.left = hastaObj.offsetLeft -widthCarta2+"px";
			}
			
			// Mover la carta a dónde
			function moverCartaDeA(carta, desde, hasta){
				var cartaObj = document.getElementById('carta_'+carta);
				var desdeObj = document.getElementById(desde);
				if(cartaObj.parentElement.id != 'todas_las_cartas_visible'){document.getElementById('todas_las_cartas_visible').appendChild(cartaObj);}
				
				cartaObj.style.top = desdeObj.offsetTop -heightCarta2+"px";
				cartaObj.style.left = desdeObj.offsetLeft -widthCarta2+"px";
				setTimeout(function(){
					moverCartaA(carta, hasta);
				},0);
			}
			
			
			
			
			
			
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
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			console2.on = true;
			
			IABriscaMesaInstancia.iniciarMesa();
			IABriscaMesaInstancia.tiempoPensandoIAms = 1500;
			IABriscaMesaInstancia.tiempoEntreRondas = 1000;
			
			
			//IABriscaMesaInstancia.tiempoPensandoIAms = 0;
			//IABriscaMesaInstancia.tiempoEntreRondas = 0;
			
			
			var jugador1 = new HumanoBriscaJugador();
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