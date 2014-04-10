/*
IABriscaBaseInstancia = new IABriscaBase();
IABriscaMesaInstancia = new IABriscaMesa();


var jugador1 = new IABriscaJugador();
jugador1.iniciarJugador("a");
var jugador2 = new IABriscaJugador();
jugador2.iniciarJugador("b");
var jugador3 = new IABriscaJugador();
jugador3.iniciarJugador("c");
var jugador4 = new IABriscaJugador();
jugador4.iniciarJugador("d");

IABriscaMesaInstancia.iniciarMesa();

IABriscaMesaInstancia.insertaJugadoresEnMesa([jugador1,jugador2,jugador3,jugador4]);





contadorrr = 30;


IABriscaMesaInstancia.iniciaRonda();
IABriscaMesaInstancia.informaEstado();
IABriscaMesaInstancia.lanzaRonda(again);


function again(){
	if(IABriscaMesaInstancia.faltanRondasPorJugar()){
		if(contadorrr == 0)
			awgawawghw();
		--contadorrr;
		
		
		
		IABriscaMesaInstancia.iniciaRonda();
		IABriscaMesaInstancia.informaEstado();
		IABriscaMesaInstancia.lanzaRonda(again);
	}
}
*/




	






// La clase IABriscaBase contiene las funciones y variables necesarias para realizar una decisión de tirar una carta.
// Una instancia de la clase es suficiente para todos los cpu, ya que por cada jugada se indican todas las variables de entorno
// Para manejar distintos jugadores de forma simultanea es buena idea usar un wrapper para cada jugador (que recordará por ejemplo qué cartas han sido usadas ya y el recuento de puntos del jugador), y además usar una "IA" que se encargue de repartir las cartas y llevar el control del mazo, recuento de puntos, etc.
function IABriscaBase(){
	// O = oro
	// E = espadas
	// B = bastos
	// C = copas
	
	// Para cada jugada es necesario especificar el palo que manda y el palo de la mesa

	// Array con todas las posibles cartas
	this.cartasTotalArray = [
		"O1","O3","O12","O11","O10","O9","O8","O7","O6","O5","O4","O2",
		"E1","E3","E12","E11","E10","E9","E8","E7","E6","E5","E4","E2",
		"B1","B3","B12","B11","B10","B9","B8","B7","B6","B5","B4","B2",
		"C1","C3","C12","C11","C10","C9","C8","C7","C6","C5","C4","C2"
	];
	
	//Ordenador por valor de numero de carta
	this.cartasTotalArrayNumero = [1,3,12,11,10,9,8,7,6,5,4,2];
	
	// array con la correspondencia de los puntos con las cartas.
	this.puntosCartas = {
		"1":11, "3":10, "12":4, "11":3, "10":2, "9":0, "8":0, "7":0, "6":0, "5":0, "4":0, "2":0
	};
	
	// array con la correspondencia de los puntos con las cartas.
	this.palosCartas = ["O","E","B","C"];
	
	// En el siguiente array se guardar�n las cartas gastadas para cada jugada.
	this.cartasUsadas = {
		"O":[],
		"E":[],
		"B":[],
		"C":[]
	};
	
	
	
	
	
	
	/*
	cartas sigue la siguiente estructura:
	
	cartas = [
		"Xnn",
		"Xn",
		etc
	]
	
	X = O,E,B o C
	n,nn = numeros
	*/

	
	// Retorna el palo de una carta
	this.paloCarta = function(carta){
		return carta[0];
	};
	
	// Retorna el n�mero carta
	this.numeroCarta = function(carta){
		return carta.substr(1);
	};
	
	// Retorna el total de puntos en la carta dada a partir del array puntosCartas
	this.totalPuntosEnCarta = function(carta){
		// var palo = cartas[i][0]
		var n = parseInt(this.numeroCarta(carta));
		return this.puntosCartas[n];
	};
	
	// Retorna el total de puntos en el grupo de cartas dado a partir del array puntosCartas
	this.totalPuntosEnCartas = function(cartas){
		if(cartas.length === 0 || cartas == undefined){
			return 0;
		}
		var suma = 0;
		for(var i in cartas){
			suma += this.totalPuntosEnCarta(cartas[i]);
		}
		return suma;
	};
	
	
	
	// Retorna true o false en caso de que 
	this.paloPresenteEnCartas = function(palo, cartas){
		if(cartas.length == 0 || cartas == undefined){
			return false;
		}
		if(typeof palo == "string"){
			for(var i in cartas){
				var paloT = this.paloCarta(cartas[i]);
				if(palo == paloT){
					return true;
				}
			}
		}
		else{
			for(var i in cartas){
				var paloT = this.paloCarta(cartas[i]);
				for(var j in palo){
					if(palo[j] == paloT){
						return true;
					}
				}
			}
		}
		return false;
	};
	
	
	
	
	
	
	
	// Valor del palo: 0 = sin valor, 1 = palo de la mesa, 2 = palo que manda
	// Valor m�nimo es el valor en puntos m�nimo de la carta
	// Retorna la carta del array cartas que cumple con los requisitos fijados, o false si ninguna carta cumple
	this.cartaMenorValor = function(cartas, paloQueMandaSiempre, paloQueMandaEnMesa){
		
		if(cartas.length == 1){
			return cartas[0];
		}
		
		// Ordenadas de mayor a menor por valor
		var cartasOrdenadas = this.ordenCartasPorValor(cartas, paloQueMandaSiempre, paloQueMandaEnMesa);
		
		return cartasOrdenadas[cartasOrdenadas.length-1];
	};
	
	
	
	// Retorna las cartas odenadas de mayor a menor valor dado paloQueMandaSiempre y paloQueMandaEnMesa
	this.ordenCartasPorValor = function(cartas, paloQueMandaSiempre, paloQueMandaEnMesa){
		// Clonar un objeto
		// http://stackoverflow.com/questions/122102/most-efficient-way-to-clone-an-object
		// Aqu� se guardar�n los palos y cartas ordenados
		var ordenPalosCartas2 = [];
		
		// Este bucle ordena this.cartasTotal de forma que this.cartasTotal[0] = paloQueMandaSiempre y 1 = paloQueMandaEnMesa. 2 y 3 son "aleatorios"
		var W=[];
		if(paloQueMandaSiempre != undefined && paloQueMandaSiempre !== ''){
			W.push(paloQueMandaSiempre);
		}
		if(paloQueMandaEnMesa != undefined && paloQueMandaEnMesa !== '' && paloQueMandaSiempre !== paloQueMandaEnMesa){
			W.push(paloQueMandaEnMesa);
		}
		var M=this.palosCartas.slice(0);
		for(var w2 in W){
			M.splice(M.indexOf(W[w2]), 1);
		}
		// console2.log(ordenPalosCartas);
		
		// Se borran las cartas no listadas en la variable "cartas"
		//for(var i in ordenPalosCartas){
		for(var palo in W){
			palo = W[palo];
			// console2.log("mirando palo "+palo);
			for(var carta in this.cartasTotalArrayNumero){
				// console2.log(ordenPalosCartas[i][palo]);
				for(var carta2 in cartas){
					if(palo === this.paloCarta(cartas[carta2]) && this.cartasTotalArrayNumero[carta] == this.numeroCarta(cartas[carta2])){
						// console2.log(palo+"=="+palo2);
						// console2.log(ordenPalosCartas[i][palo][carta]+"=="+this.numeroCarta(cartas[carta2]));
						// console2.log("---------");
						ordenPalosCartas2.push(cartas[carta2]);
					}
				}
			}
		}
		

		// console2.log("mirando palo "+palo);
		for(var carta in this.cartasTotalArrayNumero){
			// console2.log(ordenPalosCartas[i][palo]);
			for(var carta2 in cartas){
				var palo2 = this.paloCarta(cartas[carta2]);
				if((M.indexOf(palo2)>=0) && this.cartasTotalArrayNumero[carta] == this.numeroCarta(cartas[carta2])){
					// console2.log(palo+"=="+palo2);
					// console2.log(ordenPalosCartas[i][palo][carta]+"=="+this.numeroCarta(cartas[carta2]));
					// console2.log("---------");
					ordenPalosCartas2.push(cartas[carta2]);
				}
			}
		}
		
		
		// console2.log(ordenPalosCartas);
		// console2.log(ordenPalosCartas2);
		return ordenPalosCartas2;
	};
	
	
	
	
	
	
	
	// Pasos a seguir, tal y como se ve en el diagrama mediante if else.
	this.calculaJugada = function(cartasEnMesa, cartasEnMano, paloQueMandaSiempre, paloQueMandaEnMesa, ultimoEnTirar, cartasJugadas){
		
		
		// Al final se retornar� una carta, la que se debe tirar
		var thisT = this;
		
		function totalPuntosCartasEnManoMayorQue0(){
			return hayPuntosEnMesa();
			if(thisT.totalPuntosEnCartas(cartasEnMano) > 0){
				return cartaManoDelPaloManda();
			}
			else{
				return hayPuntosEnMesa();
			}
		};
		
		function cartaManoDelPaloManda(){
			if(thisT.paloPresenteEnCartas(paloQueMandaSiempre, cartasEnMano)){
				return hayPuntosEnMesa();
			}
			else{
				return FIN_tiraCartaMenorValor();
			}
		}
		
		function hayPuntosEnMesa(){
			if(thisT.totalPuntosEnCartas(cartasEnMesa) > 0){
				return soyUltimoEnTirar();
			}
			else{
				return cartaConPuntosGanadoraNoPaloMandaSiempre();
			}
		}
		
		function cartaConPuntosGanadoraNoPaloMandaSiempre(){
			var cartasEnManoConPuntos = [];
			for(var i in cartasEnMano){
				if(IABriscaBaseInstancia.totalPuntosEnCarta(cartasEnMano[i]) > 0 && thisT.paloCarta(cartasEnMano[i]) !== paloQueMandaSiempre){
					cartasEnManoConPuntos.push(cartasEnMano[i]);
				}
			}
			if(cartasEnManoConPuntos.length === 0){
				return cartaMenorValorManoConPuntos();
			}
			else{
				var mayorCartaMesa = thisT.ordenCartasPorValor(cartasEnMesa, paloQueMandaSiempre, paloQueMandaEnMesa)[0];
				//Esta variable tiene la carta más alta de la mesa y las cartas de la mano ordenadas por valor
				var cartasEnManoMayorMesa = thisT.ordenCartasPorValor(cartasEnManoConPuntos.concat(mayorCartaMesa), paloQueMandaSiempre, paloQueMandaEnMesa);
				var siguenteSeTira = false;
				var indiceCartaATirar = cartasEnManoMayorMesa.indexOf(mayorCartaMesa)-1;
				if(indiceCartaATirar == -1){
					return cartaMenorValorManoConPuntos();
				}
				else{
					return FIN_tiraCartaConPuntosGanadora(cartasEnManoMayorMesa[indiceCartaATirar]);
				}
			}
		}
		
		function puedoGanarLaMesa(){
			if(cartasEnMesa.length > 0){
				var mayorCartaMesa = thisT.ordenCartasPorValor(cartasEnMesa, paloQueMandaSiempre, paloQueMandaEnMesa)[0];
				var mayorCartaMano = thisT.ordenCartasPorValor(cartasEnMano, paloQueMandaSiempre, paloQueMandaEnMesa)[0];
				var mayorCarta = thisT.ordenCartasPorValor([mayorCartaMesa, mayorCartaMano], paloQueMandaSiempre, paloQueMandaEnMesa)[0];
				if(mayorCarta == mayorCartaMano){
					return totalPuntosCartasEnManoMayorQue0();
				}
				else{
					return FIN_tiraCartaMenorValor();
				}
			}
			else{
				return FIN_tiraCartaMenorValor();
			}
		}

		function soyUltimoEnTirar(){
			var tengoUnoOTresGanadorPaloNoManda = false;
			
			for(var i in cartasEnMano){
				var nCartaManoT = thisT.numeroCarta(cartasEnMano[i]);
				if(thisT.paloCarta(cartasEnMano[i]) == paloQueMandaEnMesa && (nCartaManoT == 1 || nCartaManoT == 3)){
					// Mirar si este 1 o 3 es mayor que lo que hay en la mesa
					var puedaGanar = true;
					for(var j in cartasEnMesa){
						if(
							thisT.paloCarta(cartasEnMesa[j]) == paloQueMandaSiempre ||
							(thisT.numeroCarta(cartasEnMesa[j]) == 1 && thisT.paloCarta(cartasEnMesa[j]) == paloQueMandaEnMesa)
						){
							puedaGanar = false;
							break; // Salimos del for
						}
					}
					if(puedaGanar){
						tengoUnoOTresGanadorPaloNoManda = true;
						break; // Salimos del for
					}
				}
			}
			
			
			
			
			if(ultimoEnTirar){
				if(tengoUnoOTresGanadorPaloNoManda){
					return FIN_tiraCartaUnoTresNoPaloManda();
				}
				else{
					return FIN_tiraCartaGanadoraMenorValor();
				}
			}
			else{
				if(tengoUnoOTresGanadorPaloNoManda){
					return cartaSinTirarPaloMandaSinContarEnMiMano();
				}
				else{
					return FIN_tiraCartaGanadoraMenorValor();
				}
			}
		}
		
		function cartaSinTirarPaloMandaSinContarEnMiMano(){
			var cartasDondeMirar = cartasJugadas.slice(0).concat(cartasEnMano);
			for(var i in thisT.cartasTotalArrayNumero){
				if(cartasDondeMirar.indexOf(paloQueMandaSiempre+thisT.cartasTotalArrayNumero[i]) === -1){
					// Todavía queda en juego (no en mi mano) alguna carta del palo que manda
					return masDeXPuntosEnLaMesa(8);
				}
			}
			return FIN_tiraCartaUnoTresNoPaloManda();
		}
		
		function masDeXPuntosEnLaMesa(cuantosPuntos){
			if(thisT.totalPuntosEnCartas(cartasEnMano) >= cuantosPuntos){
				return FIN_tiraCartaGanadoraMenorValor();
			}
			else{
				return FIN_tiraCartaMenorValor();
			}
		}
		
		function cartaMenorValorManoConPuntos(){
			if(thisT.totalPuntosEnCartas([thisT.cartaMenorValor(cartasEnMano)]) > 0){
				return soyUltimoEnTirar();
			}
			else{
				return FIN_tiraCartaMenorValor();
			}
		}
		
		
		// Pasos finales de las decisiones, el lanzamiento de la carta.
		
		function FIN_tiraCartaMenorValor(){
			//En esta tirada, todas las cartas menos las del palo que manda siempre son igual de importantes
			return thisT.cartaMenorValor(cartasEnMano, paloQueMandaSiempre, "");
		}
		
		function FIN_tiraCartaUnoTresNoPaloManda(){
			for(var i in cartasEnMano){
				var valorCartaT = thisT.numeroCarta(cartasEnMano[i]);
				if(
					thisT.paloCarta(cartasEnMano[i]) != paloQueMandaSiempre &&
					thisT.paloCarta(cartasEnMano[i]) == paloQueMandaEnMesa &&
					(
						valorCartaT == 1 || valorCartaT == 3
					)
				){
					return cartasEnMano[i];
				}
			}
			// En caso de que falle lo de arriba. Nunca se sabe.
			return FIN_tiraCartaMenorValor();
		}
		
		function FIN_tiraCartaGanadoraMenorValor(){
			if(cartasEnMesa.length > 0){
				var mayorCartaMesa = thisT.ordenCartasPorValor(cartasEnMesa, paloQueMandaSiempre, paloQueMandaEnMesa)[0];
				//Esta variable tiene la carta más alta de la mesa y las cartas de la mano ordenadas por valor
				var cartasEnManoMayorMesa = thisT.ordenCartasPorValor(cartasEnMano.slice(0).concat(mayorCartaMesa), paloQueMandaSiempre, paloQueMandaEnMesa);
				var siguenteSeTira = false;
				var indiceCartaATirar = cartasEnManoMayorMesa.indexOf(mayorCartaMesa)-1;
				if(indiceCartaATirar == -1){
					return FIN_tiraCartaMenorValor();
				}
				else{
					return cartasEnManoMayorMesa[indiceCartaATirar];
				}
			}
			else{
				return FIN_tiraCartaMenorValor();
			}
		}
		
		// Llamamos a la función ya con la carta que debe tirar
		function FIN_tiraCartaConPuntosGanadora(carta){
			return carta;
		}
		
		
		
		// Iniciar secuencia de preguntas para tirar la carta. se retornará la carta.
		if(cartasEnMano.length!==1){
			return puedoGanarLaMesa();
		}
		else{
			return cartasEnMano[0];
		}
	};

}

// La clase IABriscaJugador contiene la memoria de un jugador: Historial de cartas jugadas y cartas en la mano.
// Además, se encarga de mirar en la mesa las cartas que hay. El repartidor le reparte las cartas (no las pide)
function IABriscaJugador(){
	
	// el jugador tiene un ID. Será un número para poder identificar al jugador después
	this.jugadorID = 0;
	
	// Cada jugador mantiene un recuento de las cartas usadas. Esto podría realizarse únicamente en la mesa
	// Pero de esta forma puede cambiarse la IA del jugador más fácilmente, pudiendo poner olvido aleatorio de cartas jugadas para aumentar la jugabilidad
	this.cartasJugadas = [];
	
	this.cartasGanadas = [];
	
	this.cartasEnMano = [];
	
	// 1 = 100% azar, 0 = 0% azar. en caso de azar, se tirará una carta distinta a la elegida por la IA.
	this.azar = 0;
	
	
	
	// Se inicia al jugador dándole un id y si queremos, azar
	this.iniciarJugador = function(jugadorID, azar){
		this.jugadorID = jugadorID;
		if(azar != undefined){
			this.azar = azar;
		}
	};
	
	
	
	this.lanzaCarta = function(callback){
		var cartasEnMesa = IABriscaMesaInstancia.cartasEnMesaF();
		var paloQueMandaSiempre = IABriscaMesaInstancia.paloQueMandaSiempre;
		var paloQueMandaEnMesa = IABriscaMesaInstancia.paloQueMandaEnMesa;
		var ultimoEnTirar = IABriscaMesaInstancia.jugadoresPorTirar == 1;
		
		var cartaATirar = IABriscaBaseInstancia.calculaJugada(cartasEnMesa, this.cartasEnMano, paloQueMandaSiempre, paloQueMandaEnMesa, ultimoEnTirar, this.cartasJugadas);
		
		console2.log('IABriscaBaseInstancia.calculaJugada('+JSON.stringify(cartasEnMesa)+', '+JSON.stringify(this.cartasEnMano)+', "'+paloQueMandaSiempre+'", "'+paloQueMandaEnMesa+'", '+(ultimoEnTirar?'true':'false')+', '+JSON.stringify(this.cartasJugadas)+') = '+cartaATirar);
		
		if(this.azar !== 0 && Math.random() < this.azar && this.cartasEnMano.length > 1){
			var cartaATirarT = [];
			for(var i in this.cartasEnMano){
				if(this.cartasEnMano[i] != cartaATirar){
					cartaATirarT = cartaATirarT.concat(this.cartasEnMano[i]);
				}
			}
			if(cartaATirarT.length > 1){
				cartaATirar = cartaATirarT[Math.floor(Math.random()*cartaATirarT.length)];
			}
			else{
				cartaATirar = cartaATirarT[0];
			}
			
			this.cartasEnMano.splice(this.cartasEnMano.indexOf(cartaATirar), 1);
			
			return cartaATirar;
		}
		
		this.cartasEnMano.splice(this.cartasEnMano.indexOf(cartaATirar), 1);
			
		callback(this, cartaATirar);
	};
	
	this.robaCarta = function(carta){
		this.cartasEnMano = this.cartasEnMano.concat(carta);
	};
	
	this.ganaMesa = function(cartas){
		this.cartasGanadas = this.cartasGanadas.concat(cartas);
	};
	
	this.cartasJugadasMesa = function(cartas){
		this.cartasJugadas = this.cartasJugadas.concat(cartas);
	};
	
	
	
}

function HumanoBriscaJugador(){
	
	var thisT = this;
	
	// el jugador tiene un ID. Será un número para poder identificar al jugador después
	this.jugadorID = 0;
	
	// Cada jugador mantiene un recuento de las cartas usadas. Esto podría realizarse únicamente en la mesa
	// Pero de esta forma puede cambiarse la IA del jugador más fácilmente, pudiendo poner olvido aleatorio de cartas jugadas para aumentar la jugabilidad
	
	this.cartasGanadas = [];
	
	this.cartasEnMano = [];
	
	
	
	
	// Se inicia al jugador dándole un id y si queremos, azar
	this.iniciarJugador = function(jugadorID){
		thisT.jugadorID = jugadorID;
	};
	
	
	// Para el jugador, esta función guarda el callback y genera los onclick en las cartas del jugador, los caules procovan la jugada.
	this.lanzaCarta = function(callback){
		for(var i in thisT.cartasEnMano){
			var id = 'jugador_'+thisT.jugadorID+'_carta_'+(parseInt(i)+1);
			document.getElementById(id).onclick = (function(carta, callback, lanzaCartaOnClick){
				return function(){
					for(var i in thisT.cartasEnMano){
						document.getElementById('jugador_'+thisT.jugadorID+'_carta_'+(parseInt(i)+1)).onclick = undefined;
					}
					lanzaCartaOnClick(carta, callback);
				};
			})(thisT.cartasEnMano[i], callback, thisT.lanzaCartaOnClick);
		}
		
		console2.log('Preparado lanzamiento manual.');
	};
	
	this.lanzaCartaOnClick = function(cartaATirar, callback){
		console2.log('Tirada manual: '+cartaATirar);
		
		thisT.cartasEnMano.splice(thisT.cartasEnMano.indexOf(cartaATirar), 1);
			
		callback(thisT, cartaATirar);
	};
	
	this.robaCarta = function(carta){
		thisT.cartasEnMano = thisT.cartasEnMano.concat(carta);
	};
	
	this.ganaMesa = function(cartas){
		thisT.cartasGanadas = thisT.cartasGanadas.concat(cartas);
	};
	
	this.cartasJugadasMesa = function(cartas){};
}

// La clase IABriscaMesa se encarga de 
function IABriscaMesa(){
	
	var thisT = this;
	
	// Cartas que quedan en la mesa. Al inicio, se quita de aquí una carta al azar y se usa de paloQueMandaSiempre
	this.mazoCartas = [];
	
	// Monto de cartas en la ronda actual que se llevará el que gane.
	this.cartasEnMesa = [];
	
	// retorna mazoCartas en un nuevo array
	this.cartasEnMesaF = function(){
		return thisT.cartasEnMesa.slice(0); // De esta forma se retorna un nuevo array clonado, por lo que podemos editarlo sin problemas
	};
	
	// Se setea al inicio, pero puede cambiar ya que los jugadores pueden robar la carta (TENGO QUE MIRAR ME LAS NORMAS)
	this.paloQueMandaSiempre = '';
	this.cartaPaloQueMandaSiempre = '';
	
	// Se setea al inicio de cada ronda con la primera jugada
	this.paloQueMandaEnMesa = '';
	
	
	this.jugadoresArray = [];
	
	this.quienATiradoQueCarta = {};
	this.quienATiradoQueCarta.length = 0;
	
	this.jugadoresPorTirar = 0;
	
	this.quienLanzaPrimero = 0;
	this.quienGanoUltimaPartida = -1;
	
	
	// Tiempos, en milisegundos
	this.tiempoPensandoIAms = 500;
	this.tiempoEntreRondas = 1000;
	
	
	// Esta función debe llamarse cuando se inicia la partida. Después debe llamarse a insertaJugadoresEnMesa
	this.iniciarMesa = function(){
		// Sacar la carta para paloQueMandaSiempre;
		thisT.mazoCartas = IABriscaBaseInstancia.cartasTotalArray.slice(0); // Copia del array ya que después tocaremos la variable esta.
		
		// Se setea el paloQueSiempreManda y se quita del mazo
		thisT.cartaPaloQueMandaSiempre = thisT.mazoCartas.splice(Math.floor(Math.random()*thisT.mazoCartas.length),1)[0];
		thisT.paloQueMandaSiempre = IABriscaBaseInstancia.paloCarta(thisT.cartaPaloQueMandaSiempre);
		console2.log('palo que manda siempre: '+thisT.paloQueMandaSiempre);
		seteaImagen('carta_palo_manda_siempre', thisT.cartaPaloQueMandaSiempre);
	};
	
	// inserta a los jugadores en la mesa
	this.insertaJugadoresEnMesa = function(jugadores){
		thisT.jugadoresArray = jugadores;
		
		// Repartirles las cartas
		for(var i in jugadores){
			for(var n = 0; n < 3; n++){
				thisT.peticionJugadorRobar(jugadores[i]);
			}
		}
		
		thisT.quienLanzaPrimero = Math.floor(Math.random() * thisT.jugadoresArray.length);
	};
	
	this.faltanRondasPorJugar = function(){
		return thisT.quienATiradoQueCarta.length < IABriscaBaseInstancia.cartasTotalArray.length;
	};
	
	
	
	
	
	// Iniciar la partida y es llamada por cada ronda para evaluar si debe o no hacerse y operar según la situación.
	this.comienzaPartida = function(){
		if(thisT.faltanRondasPorJugar()){
			IABriscaMesaInstancia.ResetRonda();
			//IABriscaMesaInstancia.informaEstado();
			IABriscaMesaInstancia.lanzaRonda();
		}
		else{
			console2.log('sacabo');
		}
	};
	
	// Iniciar la primera ronda o terminar alguna abierta y pasar a la siguiente. reset de contadores para la ronda
	// Por cada ronda se resetea paloQueMandaEnMesa, las cartas en la mesa, quien las ha tidado y el nº de jugadores que faltan por tirar
	
	this.ResetRonda = function(){
		console2.log("ResetRonda ronda");
		thisT.jugadoresPorTirar = thisT.jugadoresArray.length;
		thisT.paloQueMandaEnMesa = '';
		thisT.cartasEnMesa = [];
		//this.quienATiradoQueCarta = {};
		cargaImagenesCartasMesa(thisT.cartasEnMesa);
		if(thisT.quienGanoUltimaPartida !== -1){
			thisT.quienLanzaPrimero = thisT.quienGanoUltimaPartida;
		}
	};
	
	
	
	
	// Lanza la ronda. Llama a la funcion queLlamarAlTerminar una vez terminada la ronda actual
	this.lanzaRonda = function(){
		console2.log("RONDA");
		
		if(thisT.jugadoresPorTirar !== 0){
			setTimeout(function(){
				var jugadorATirarN = ClampCircular(thisT.quienLanzaPrimero + thisT.jugadoresArray.length - thisT.jugadoresPorTirar, 0, thisT.jugadoresArray.length -1);
				thisT.peticionJugadorLanzar(thisT.jugadoresArray[jugadorATirarN]);
			}, 0);
		}
		else if(thisT.jugadoresPorTirar === 0){
			// Elejir ganador, dar las cartas (quitándolas de la mesa).
			var cartaGanadora = IABriscaBaseInstancia.ordenCartasPorValor(thisT.cartasEnMesa, thisT.paloQueMandaSiempre, thisT.paloQueMandaEnMesa)[0];
			console2.log('cartaGanadora: '+cartaGanadora);
			thisT.peticionJugadorGanarMesa(thisT.quienATiradoQueCarta[cartaGanadora]);
			thisT.quienGanoUltimaPartida = thisT.jugadoresArray.indexOf(thisT.quienATiradoQueCarta[cartaGanadora]);
			cargaImagenesCartasMesa(thisT.cartasEnMesa);
			
			for(var i in thisT.jugadoresArray){
				thisT.peticionJugadorRobar(thisT.jugadoresArray[i]);
			}
			
			setTimeout(thisT.comienzaPartida, thisT.tiempoEntreRondas);
		}
	};
	
	
	
	
	// Se le pasa un jugador y la mesa hace que tire. Después, usa la carta que ha tirado y guarda quién la ha tirado
	this.peticionJugadorLanzar = function(jugador){
		var cartaTirada = jugador.lanzaCarta(thisT.peticionJugadorLanzarRecibiendoCarta);
	};
	
	this.peticionJugadorLanzarRecibiendoCarta = function(jugador, cartaTirada){
		thisT.quienATiradoQueCarta[cartaTirada] = jugador;
		++thisT.quienATiradoQueCarta.length;
		thisT.cartasEnMesa = thisT.cartasEnMesa.concat(cartaTirada);
		--thisT.jugadoresPorTirar;
		if(thisT.paloQueMandaEnMesa == ""){
			thisT.paloQueMandaEnMesa = IABriscaBaseInstancia.paloCarta(cartaTirada);
		}
		cargaImagenesCartasJugador(jugador);
		cargaImagenesCartasMesa(thisT.cartasEnMesa);
		setTimeout(thisT.lanzaRonda, thisT.tiempoPensandoIAms);
	};
	
	
	
	this.quedanCartasPorRobar = true;
	this.peticionJugadorRobar = function(jugador){
		if(thisT.quedanCartasPorRobar){
			if(thisT.mazoCartas.length > 0){
				var cartaARobar = thisT.mazoCartas.splice(
					Math.floor(Math.random()*thisT.mazoCartas.length),1)[0];
			}
			else{
				var cartaARobar = this.cartaPaloQueMandaSiempre;
				thisT.cartaPaloQueMandaSiempre = '';
				thisT.quedanCartasPorRobar = false;
				seteaImagen('carta_palo_manda_siempre', 'blanco');
			}
			jugador.robaCarta(cartaARobar);
			cargaImagenesCartasJugador(jugador);
			if(thisT.mazoCartas.length === 0){
				seteaImagen('carta_mazo', 'blanco');
			}
		}
	};
	
	this.peticionJugadorGanarMesa = function(jugador){
		jugador.ganaMesa(thisT.cartasEnMesa);
		for(var i in thisT.jugadoresArray){
			thisT.jugadoresArray[i].cartasJugadasMesa(thisT.cartasEnMesa);
		}
		seteaPuntos('puntos_jugador_'+(thisT.jugadoresArray.indexOf(jugador)+1), IABriscaBaseInstancia.totalPuntosEnCartas(jugador.cartasGanadas));
		thisT.cartasEnMesa = [];
	};
}

function cargaImagenesCartasJugador(jugador){
	for(var i=0; i<3; i++){
		if(jugador.cartasEnMano[i]){
			seteaImagen('jugador_'+jugador.jugadorID+'_carta_'+(i+1), jugador.cartasEnMano[i]);
		}
		else{
			seteaImagen('jugador_'+jugador.jugadorID+'_carta_'+(i+1), 'blanco');
		}
	}
}

function cargaImagenesCartasMesa(cartasMesa){
	for(var i=0; i<4; i++){
		if(cartasMesa[i]){
			seteaImagen('mesa_carta_'+(i+1), cartasMesa[i]);
		}
		else{
			seteaImagen('mesa_carta_'+(i+1), 'blanco');
		}
	}
}

function seteaImagen(id, carta){
	if(carta === 'blanco'){
		document.getElementById(id).src = "";
		document.getElementById(id).style.display = "none";
	}
	else{
		document.getElementById(id).src = "img/cartas/"+carta+".jpg";
		document.getElementById(id).style.display = "";
	}
}

function seteaPuntos(id, puntos){
	document.getElementById(id).innerHTML = puntos;
}

function ClampCircular(numero, minimo, maximo){
	while(numero < minimo){
		numero += maximo +1;
	}
	while(numero > maximo){
		numero -= maximo +1;
	}
	return numero;
}

var console2 = {
	on:false,
	log:function(what){
		if(this.on){
			console.log(what);
		}
	}
};






IABriscaBaseInstancia = new IABriscaBase();
IABriscaMesaInstancia = new IABriscaMesa();