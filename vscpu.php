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
			
			
			
			// Iniciar el script de la brisca
			IABriscaInstancia = new IABrisca();
			IABriscaInstancia.console2.on = true;
			
		
		
		
			var miNombre = "forest";
			
			var arrPosiciones = document.getElementById('posiciones').children;
			
			
			
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
			var arrCartas = IABriscaInstancia.IABriscaBaseInstancia.cartasTotalArray;
			for(var i in arrCartas){
				todas_las_cartas.innerHTML += '<img class="carta" style="width:'+widthCarta+'px;height:'+heightCarta+'px" id="carta_'+arrCartas[i]+'" src="img/cartas/'+arrCartas[i]+'.jpg">';
			}
			
			var mazo_cartas = document.getElementById('MM');
			mazo_cartas.innerHTML = '<img class="carta" style="position:absolute;width:'+widthCarta+'px;height:'+heightCarta+'px;top:-'+heightCarta2+'px;left:-'+widthCarta2+'px;" src="img/cartas/back2.jpg">';
			
			
			// Mover la carta a dónde
			function moverCartaA(carta, hasta){
				var cartaObj = document.getElementById('carta_'+carta);
				var hastaObj = document.getElementById(hasta);
				
				for(var i = 0; i < arrPosiciones.length; ++i){
					if(arrPosiciones[i].innerHTML.indexOf('{{'+carta+'}}') != -1){
						arrPosiciones[i].innerHTML = arrPosiciones[i].innerHTML.split('{{'+carta+'}}').join('');
					}
				}
				hastaObj.innerHTML += '{{'+carta+'}}';
				
				if(cartaObj.parentElement.id != 'todas_las_cartas_visible'){
					moverCartaDeA(carta, 'MM', hasta);
					return;
				}
				
				cartaObj.style.top = hastaObj.offsetTop -heightCarta2+"px";
				cartaObj.style.left = hastaObj.offsetLeft -widthCarta2+"px";
			}
			
			// Mover la carta a dónde
			function moverCartaDeA(carta, desde, hasta){
				var cartaObj = document.getElementById('carta_'+carta);
				var desdeObj = document.getElementById(desde);
				if(cartaObj.parentElement.id != 'todas_las_cartas_visible'){
					document.getElementById('todas_las_cartas_visible').appendChild(cartaObj);
				}
				
				cartaObj.style.top = desdeObj.offsetTop -heightCarta2+"px";
				cartaObj.style.left = desdeObj.offsetLeft -widthCarta2+"px";
				setTimeout((function(carta, hasta){
					return function(){
						moverCartaA(carta, hasta);
					}
				})(carta, hasta), 0);
			}
			
			// Retorna el número, del 1 al 3, donde poner la carta a un jugador
			function huecoLibreJugador(jugadorID){
				for(var i = 1; i <= 3; ++i){
					if(document.getElementById('P'+jugadorID+'C'+i).innerHTML === ''){
						return i;
					}
				}
			}
			
			// Retorna el número, del 1 al 4, donde poner la carta en la mesa
			function huecoLibreMesa(){
				for(var i = 1; i <= 4; ++i){
					if(document.getElementById('MC'+i).innerHTML === ''){
						return i;
					}
				}
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
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			IABriscaInstancia.IABriscaMesaInstancia.iniciarMesa();
			IABriscaInstancia.IABriscaMesaInstancia.tiempoPensandoIAms = 1500;
			IABriscaInstancia.IABriscaMesaInstancia.tiempoEntreRondas = 1000;
			
			
			//IABriscaMesaInstancia.tiempoPensandoIAms = 0;
			//IABriscaMesaInstancia.tiempoEntreRondas = 0;
			
			
			var jugador1 = new IABriscaInstancia.HumanoBriscaJugador();
			jugador1.iniciarJugador(1);
			var jugador2 = new IABriscaInstancia.IABriscaJugador();
			jugador2.iniciarJugador(2);
			var jugador3 = new IABriscaInstancia.IABriscaJugador();
			jugador3.iniciarJugador(3);
			var jugador4 = new IABriscaInstancia.IABriscaJugador();
			jugador4.iniciarJugador(4);
			
			IABriscaInstancia.IABriscaMesaInstancia.insertaJugadoresEnMesa([jugador1,jugador2,jugador3,jugador4]);

			
			// GO
			IABriscaInstancia.IABriscaMesaInstancia.comienzaPartida();

			

		</script>
	</body>
</html>