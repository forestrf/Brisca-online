<?php

class IABrisca{
	// Funciones extraidas de IABrisca.js a php

	// O = oro
	// E = espadas
	// B = bastos
	// C = copas

	// Array con todas las posibles cartas
	public static $cartasTotalArray = array(
		'O1','O3','O12','O11','O10','O9','O8','O7','O6','O5','O4','O2',
		'E1','E3','E12','E11','E10','E9','E8','E7','E6','E5','E4','E2',
		'B1','B3','B12','B11','B10','B9','B8','B7','B6','B5','B4','B2',
		'C1','C3','C12','C11','C10','C9','C8','C7','C6','C5','C4','C2'
	);
	

	// Ordenador por valor de numero de carta
	public static $cartasTotalArrayNumero = array(1,3,12,11,10,9,8,7,6,5,4,2);
	

	// array con la correspondencia de los puntos con las cartas.
	public static $puntosCartas = array(
		'1'=>11,'3'=>10,'12'=>4,'11'=>3,'10'=>2,
		'9'=>0,'8'=>0,'7'=>0,'6'=>0,'5'=>0,'4'=>0,'2'=>0
	);
	

	// array con la correspondencia de los puntos con las cartas.
	public static $palosCartas = array('O','E','B','C');

	// Retorna el palo de una carta
	public static function paloCarta($carta){
		return substr($carta, 0, 1);
	}

	// Retorna el número carta
	public static function numeroCarta($carta){
		return intval(substr($carta, 1));
	}
		
	// Retorna el total de puntos en la carta dada a partir del array puntosCartas
	public static function totalPuntosEnCarta($carta){
		// var palo = cartas[i][0]
		$n = self::numeroCarta($carta);
		return self::$puntosCartas[$n];
	}

	// Retorna el total de puntos en el grupo de cartas dado a partir del array puntosCartas
	public static function totalPuntosEnCartas($cartas = array()){
		if(($i_t = count($cartas)) === 0){
			return 0;
		}
		$suma = 0;
		for($i=0; $i<$i_t; ++$i){
			$suma += self::totalPuntosEnCarta($cartas[$i]);
		}
		return $suma;
	}
	
	// Retorna true o false en caso de que 
	public static function paloPresenteEnCartas($palo, $cartas = array()){
		if(($i_t = count($cartas)) === 0){
			return false;
		}
		if(!is_array($palo)){
			for($i=0; $i<$i_t; ++$i){
				$paloT = self::paloCarta($cartas[$i]);
				if($palo === $paloT){
					return true;
				}
			}
		}
		else{
			for($i=0; $i<$i_t; ++$i){
				$paloT = self::paloCarta($cartas[$i]);
				foreach($palo as $j){
					if($j === $paloT){
						return true;
					}
				}
			}
		}
		return false;
	}
	
	// Valor del palo: 0 = sin valor, 1 = palo de la mesa, 2 = palo que manda
	// Valor mínimo es el valor en puntos m�nimo de la carta
	// Retorna la carta del array cartas que cumple con los requisitos fijados, o false si ninguna carta cumple
	public static function cartaMenorValor($cartas = array(), $paloQueMandaSiempre = '', $paloQueMandaEnMesa = ''){
		
		if(count($cartas) === 1){
			return $cartas[0];
		}
		
		// Ordenadas de mayor a menor por valor
		$cartasOrdenadas = self::ordenCartasPorValor($cartas, $paloQueMandaSiempre, $paloQueMandaEnMesa);
		
		return $cartasOrdenadas[count($cartasOrdenadas)-1];
	}
		
	// Retorna las cartas odenadas de mayor a menor valor dado paloQueMandaSiempre y paloQueMandaEnMesa
	public static function ordenCartasPorValor($cartas = array(), $paloQueMandaSiempre = '', $paloQueMandaEnMesa = ''){
		// Clonar un objeto
		// http://stackoverflow.com/questions/122102/most-efficient-way-to-clone-an-object
		// Aquí se guardarán los palos y cartas ordenados
		$ordenPalosCartas2 = array();
		
		// Este bucle ordena this.cartasTotal de forma que this.cartasTotal[0] = paloQueMandaSiempre y 1 = paloQueMandaEnMesa. 2 y 3 son "aleatorios"
		$W=array();
		if($paloQueMandaSiempre !== ''){
			$W[] = $paloQueMandaSiempre;
		}
		if($paloQueMandaEnMesa !== '' && $paloQueMandaSiempre !== $paloQueMandaEnMesa){
			$W[] = $paloQueMandaEnMesa;
		}
		$M=self::$palosCartas; //Por defecto se hace una copia del array
		foreach($W as $w2){
			array_splice($M, array_search($w2, $M), 1);
		}
		
		// Se borran las cartas no listadas en la variable "cartas"
		//for(var i in ordenPalosCartas){
		foreach($W as $palo){
			// T.console2.log("mirando palo "+palo);
			foreach(self::$cartasTotalArrayNumero as $cartaN){
				// T.console2.log(ordenPalosCartas[i][palo]);
				foreach($cartas as $carta2){
					if($palo === self::paloCarta($carta2) && $cartaN == self::numeroCarta($carta2)){
						// T.console2.log(palo+"=="+palo2);
						// T.console2.log("---------");
						$ordenPalosCartas2[] = $carta2;
					}
				}
			}
		}
		

		// T.console2.log("mirando palo "+palo);
		foreach(self::$cartasTotalArrayNumero as $cartaN){
			// T.console2.log(ordenPalosCartas[i][palo]);
			foreach($cartas as $carta2){
				$palo2 = self::paloCarta($carta2);
				if(array_search($palo2, $M) !== false && $cartaN == self::numeroCarta($carta2)){
					// T.console2.log(palo+"=="+palo2);
					// T.console2.log("---------");
					$ordenPalosCartas2[] = $carta2;
				}
			}
		}
		
		
		// T.console2.log(ordenPalosCartas);
		// T.console2.log(ordenPalosCartas2);
		return $ordenPalosCartas2;
	}
}