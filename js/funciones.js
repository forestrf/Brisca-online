var zIndexTemp = 1;
// Mover la carta a dónde
function moverCartaA(carta, hasta){
	var cartaObj = document.getElementById('carta_'+carta);
	var hastaObj = document.getElementById(hasta);
	
	for(var i = 0; i < arrPosiciones.length; ++i){
		if(arrPosiciones[i].cartas === carta){
			arrPosiciones[i].cartas = '';
		}
	}
	hastaObj.cartas = carta;
	
	/*if(cartaObj.parentElement.id != 'todas_las_cartas_visible'){
		moverCartaDeA(carta, 'MM', hasta);
		return;
	}*/
	
	cartaObj.className = "carta "+hasta;
	cartaObj.style.marginTop = (-heightCarta2 +((Math.random()*2) -1) *maximoAzarDesfaseWidth) +"px";
	cartaObj.style.marginLeft = (-widthCarta2 +((Math.random()*2) -1) *maximoAzarDesfaseHeight) +"px";
	if(hasta.indexOf("P2") !== -1 || hasta.indexOf("P4") !== -1){
		cartaObj.style.webkitTransform =
		cartaObj.style.mozTransform =
		cartaObj.style.msTransform =
		cartaObj.style.oTransform =
		cartaObj.style.transform = 'rotate('+(90 +((Math.random()*2) -1) *maximoAzarDesgaseGrados)+'deg)';
	}
	else{
		cartaObj.style.webkitTransform =
		cartaObj.style.mozTransform =
		cartaObj.style.msTransform =
		cartaObj.style.oTransform =
		cartaObj.style.transform = 'rotate('+(((Math.random()*2) -1) *maximoAzarDesgaseGrados)+'deg)';
	}
	cartaObj.style.zIndex = ++zIndexTemp;
}

// Mover la carta a dónde
function moverCartaDeA(carta, desde, hasta){
	var cartaObj = document.getElementById('carta_'+carta);
	var desdeObj = document.getElementById(desde);
	/*if(cartaObj.parentElement.id != 'todas_las_cartas_visible'){
		document.getElementById('todas_las_cartas_visible').appendChild(cartaObj);
	}*/
	
	cartaObj.className = "carta "+desde;
	cartaObj.style.marginTop = -heightCarta2+"px";
	cartaObj.style.marginLeft = -widthCarta2+"px";
	setTimeout((function(carta, hasta){
		return function(){
			moverCartaA(carta, hasta);
		}
	})(carta, hasta), 0);
}

// Deshabilitar transiciones css, intercambiar cartas y rehabilitar transiciones
// http://stackoverflow.com/questions/11131875/what-is-the-cleanest-way-to-disable-css-transition-effects-temporarily
function sustituyeCarta(carta1, carta2){
	carta1Obj = document.getElementById('carta_'+carta1);
	carta2Obj = document.getElementById('carta_'+carta2);
	carta1Obj.className += ' sintransition';
	carta2Obj.className += ' sintransition';
	
	var cssTemp = carta1Obj.className;
	carta1Obj.className = carta2Obj.className;
	carta2Obj.className = cssTemp;
	
	cssTemp = carta1Obj.style.cssText;
	carta1Obj.style.cssText = carta2Obj.style.cssText;
	carta2Obj.style.cssText = cssTemp;
	
	carta1Obj.offsetHeight;
	carta1Obj.className = carta1Obj.className.split(" sintransition").join("");
	carta2Obj.className = carta2Obj.className.split(" sintransition").join("");
	
	//Cambiar también en la mesa para que mueveCartaA
	var posCarta1 = null;
	var posCarta2 = null;
	for(var i = 0; i < arrPosiciones.length; ++i){
		if(arrPosiciones[i].cartas === carta1){
			posCarta1 = i;
		}
		else if(arrPosiciones[i].cartas === carta2){
			posCarta2 = i;
		}
	}
	if(posCarta1 !== null){
		arrPosiciones[posCarta1].cartas = '';
		arrPosiciones[posCarta1].cartas = carta2;
	}
	if(posCarta2 !== null){
		arrPosiciones[posCarta2].cartas = '';
		arrPosiciones[posCarta2].cartas = carta1;
	}
}



// Retorna el número, del 1 al 3, donde poner la carta a un jugador
function huecoLibreJugador(jugadorID){
	for(var i = 1; i <= 3; ++i){
		if(document.getElementById('P'+jugadorID+'C'+i).cartas === ''){
			return i;
		}
	}
}

// Retorna el número, del 1 al 4, donde poner la carta en la mesa
function huecoLibreMesa(){
	for(var i = 1; i <= 4; ++i){
		if(document.getElementById('MC'+i).cartas === ''){
			return i;
		}
	}
}

function moverCarta(carta, donde){
	if(donde.indexOf("P1")!==-1 || donde.indexOf("MC")!==-1 || donde.indexOf("C0")!==-1){
		document.getElementById('carta_'+carta).src = "/img/cartas/"+carta+".jpg";
	}
	else{
		document.getElementById('carta_'+carta).src = "/img/cartas/back2.jpg";
	}
	if(donde.indexOf("P")!==-1 || donde.indexOf("MC")!==-1 || donde.indexOf("C0")!==-1){
		if(donde.indexOf("MC")!==-1 && donde.indexOf("0",2)===-1){
			var huecoLibre = huecoLibreMesa();
			moverCartaA(carta, donde+huecoLibre);
		}
		else if(donde.indexOf("P")!==-1 && donde.indexOf("0",2)===-1){
			var huecoLibre = huecoLibreJugador(donde.substr(1, donde.length -1));
			moverCartaA(carta, donde+'C'+huecoLibre);
		}
		else{
			moverCartaA(carta, donde);
		}
	}
	else{
		moverCartaA(carta, donde);
	}
}

var borrarPideCartaHumano = function(){
	console.log('borrarPideCartaHumano');
	for(var z in cartasTempParaHumano){
		document.getElementById('carta_'+cartasTempParaHumano[z]).style.cursor = 'default';
		document.getElementById('carta_'+cartasTempParaHumano[z]).onclick = function(){};
	}
}

var cartasTempParaHumano;
function pideCartaHumano(id, posiblesCartas, callback){
	cartasTempParaHumano = posiblesCartas;
	for(var i in posiblesCartas){
		var id = 'carta_'+posiblesCartas[i];
		document.getElementById(id).style.cursor = 'pointer';
		document.getElementById(id).onclick = (function(i, callback, thisT){
			return function(){
				for(var z in posiblesCartas){
					document.getElementById('carta_'+posiblesCartas[z]).style.cursor = 'default';
					document.getElementById('carta_'+posiblesCartas[z]).onclick = function(){};
				}
				callback(posiblesCartas[i]);
			};
		})(i, callback);
	}
}

function seteaPuntos(id, puntos, nick){
	if(typeof nick === 'undefined'){
		document.getElementById('P'+id+'N').innerHTML = "Puntos: "+puntos;
	}
	else{
		document.getElementById('P'+id+'N').innerHTML = '<div class="nombre_puntos">'+nick+"</div>Puntos: "+puntos;
	}
}

function ClampCircular(numero, minimo, maximo){
	while(numero < minimo){
		numero += maximo -minimo +1;
	}
	while(numero > maximo){
		numero -= maximo -minimo +1;
	}
	return numero;
};


// http://andylangton.co.uk/blog/development/get-viewport-size-width-and-height-javascript
function viewport(){
	var e = window
	, a = 'inner';
	if ( !( 'innerWidth' in window ) )
	{
	a = 'client';
	e = document.documentElement || document.body;
	}
	return { width : e[ a+'Width' ] , height : e[ a+'Height' ] }
}


function instanciaCartas(falsas){
	arrPosiciones = document.getElementById('posiciones').children;
	for(var i=0; i < arrPosiciones.length; ++i){
		arrPosiciones[i].cartas = '';
	}
	
	
	
	
	// Indicar el tamaño de las cartas mediante javascript editando el style
	// Tamaño de las cartas = 201 x 279
	// ratio = 201 / 279 = 0.72
	// ancho = alto * 0.72
	// alto = ancho / 0.72
	aspectRatio = 0.72;
	
	windowInnerWH = viewport();
	
	maximo = Math.min(windowInnerWH.height,windowInnerWH.width);
	if(maximo == windowInnerWH.height){
		widthCarta = maximo*0.17;
	}
	else{
		widthCarta = maximo*0.17*aspectRatio;
	}
	heightCarta = widthCarta/aspectRatio;
	widthCarta2 = widthCarta/2;
	heightCarta2 = heightCarta/2;
	maximoAzarDesfaseWidth = widthCarta * 0.02;
	maximoAzarDesfaseHeight = heightCarta * 0.02;
	maximoAzarDesgaseGrados = 2;
	
	todas_las_cartas = document.getElementById('todas_las_cartas');
	todas_las_cartas.innerHTML = "";
	mazo_cartas = document.getElementById('MM');
	arrCartas = IABriscaInstancia.IABriscaBaseInstancia.cartasTotalArray;
	for(var i in arrCartas){
		todas_las_cartas.innerHTML += '<img class="carta MM" style="position:absolute;width:'+widthCarta+'px;height:'+heightCarta+'px;margin-top:'+(-heightCarta2)+'px;margin-left:'+(-widthCarta2)+'px;" id="carta_'+arrCartas[i]+'" src="img/cartas/back2.jpg">';
	}
	if(typeof falsas !== 'undefined' && falsas === true){
		for(var i=0; i<arrCartas.length; ++i){
			todas_las_cartas.innerHTML += '<img class="carta MM" style="position:absolute;width:'+widthCarta+'px;height:'+heightCarta+'px;margin-top:'+(-heightCarta2)+'px;margin-left:'+(-widthCarta2)+'px;" id="carta_F'+i+'" src="img/cartas/back2.jpg">';
		}
	}
}


function fin_mensaje(resultado, misPuntos){

	mensaje = {"-1":"HAS PERDIDO","0":"NO HAY GANADOR","1":"HAS GANADO"}[resultado];

	//console.log(resultados);
	mensaje_fin = document.getElementById("mensaje_fin");
	mensaje_fin.className = "";
	
	mensaje_fin.innerHTML = mensaje + "<br><br>Tu puntuación es " + misPuntos + " puntos<br><br><br><br><div class='boton' onclick='resetBrisca()'>Volver a jugar</div><br>";
}

function ArrayIndexOf(arr, elt /*, from*/){
	var len = arr.length >>> 0;
	
	var from = Number(arguments[1]) || 0;
	from = (from < 0) ? Math.ceil(from) : Math.floor(from);
	if(from < 0){
		from += len;
	}
	
	for(; from < len; from++){
		if (from in arr && arr[from] === elt){
			return from;
		}
	}
	return -1;
};
