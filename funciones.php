<?php

// En caso de mudar de base de datos sería necesario modificar la clase siguiente. Las funciones para la aplicación deben permanecer definidas y con los mismos parámetros
// Pero puede variar su contenido para adaptarse a la nueva base de datos
class DB {
	
	// Datos de login por defecto. En caso de necesitar cambiar el login, cambiar aquí
	private $host = "localhost";
	private $user = "root";
	private $pass = "";
	private $bd = "briscaonline";

	private $mysqli;
	
	private $conexionAbierta = false;
	
	var $LAST_MYSQL_ID = '';
	
	function Abrir($host=null, $user=null, $pass=null, $bd=null){
		if($host !== null)
			$this->host = $host;
		if($user !== null)
			$this->user = $user;
		if($pass !== null)
			$this->pass = $pass;
		if($bd !== null)
			$this->bd = $bd;
			
		// Conexión persistente:
		// http://www.php.net/manual/en/mysqli.persistconns.php
		// To open a persistent connection you must prepend p: to the hostname when connecting. 
		$this->mysqli = new mysqli('p:'.$this->host, $this->user, $this->pass, $this->bd);
		if ($this->mysqli->connect_errno) {
			echo "Fallo al contectar a MySQL: (" . $this->mysqli->connect_errno . ") " . $this->mysqli->connect_error;
			return false;
		}
		$this->mysqli->set_charset("utf8");
		return true;
	}
	
	// Realizar una consulta sql. Retorna false en caso de error, además de imprimir el error en pantalla
	// Solo aquí se realiza una consulta directamente. De esta forma se puede abrir conexión en caso de ser necesaria o usar una respuesta cacheada
	private function consulta($query, $cacheable = false){
		if($cacheable && MEMCACHE){
			$cacheado = $this->consultaCache($query);
			if($cacheado !== false){
				return $cacheado;
			}
		}
		
		if($this->conexionAbierta === false){
			$this->Abrir();
			$this->conexionAbierta = true;
		}
		
		try{
			$resultado = $this->mysqli->query($query, MYSQLI_USE_RESULT);
			if(strpos($query, 'INSERT')!==false){
				$this->LAST_MYSQL_ID = $this->mysqli->insert_id;
			}
			if($resultado === false){
				throw new Exception('Error: '.$this->mysqli->error);
				return false;
			}
			elseif($resultado === true){
				return true;
			}
		}
		catch(Exception $e){
			return false;
		}
		
		if($cacheable && MEMCACHE){
			$arrayCacheado = $resultado->fetch_array(MYSQLI_ASSOC);
			$this->cacheaResultado($query, $arrayCacheado);
			return $arrayCacheado;
		}
		$resultadoArray = array();
		while($rt = $resultado->fetch_array(MYSQLI_ASSOC)){$resultadoArray[] = $rt;};
		return $resultadoArray;
	}
	
	// FUNCIONES QUE SE USAN EN LA APLICACIÓN
	
	// Consultar si existe un nick en la base de datos
	function existeNick($nick){
		$nick = mysql_escape_mimic($nick);
		return count($this->consulta("SELECT * FROM usuarios WHERE NICK = '{$nick}'", true)) > 0;
	}
	
	// Insertar un usuario en la base de datos
	function insertaUsuario($nombre, $apellidos, $email, $nick, $password, $requiereValidacion=true){
		$nombre = mysql_escape_mimic($nombre);
		$apellidos = mysql_escape_mimic($apellidos);
		$email = mysql_escape_mimic($email);
		$nick = mysql_escape_mimic($nick);
		$password = mysql_escape_mimic($password);
		$hoy = date('Y-m-d H:i:s');
		return $this->consulta("INSERT INTO usuarios (NOMBRE, APELLIDO, NICK, PASSWORD, EMAIL, FECHA_REGISTRO, FECHA_ULT_LOGIN, IP_ULT_LOGIN) VALUES ".
												"('$nombre', '$apellidos', '$nick', '$password', '$email', '$hoy', '$hoy', '{$_SERVER["REMOTE_ADDR"]}')") === true;
	}
	
	// Indicar la password temporal que se usará para la confirmación por correo electrónico
	function setEmailPasswordValidacion($ID, $passwordT){
		$ID = mysql_escape_mimic($ID);
		$passwordT = mysql_escape_mimic($passwordT);
		$this->consulta("UPDATE usuarios SET user_validado = '{$passwordT}' WHERE ID = '{$ID}'");
	}
	
	// Retorna true si el email ahora está validado
	function CompruebaEmailValidacion($ID, $passwordT){
		$ID = mysql_escape_mimic($ID);
		$passwordT = mysql_escape_mimic($passwordT);
		return count($this->consulta("SELECT * FROM usuarios WHERE user_validado = '{$passwordT}' AND ID = '{$ID}'", true)) > 0;
	}
	
	// Marca el usuario como usuario validado por mail
	function ValidaEmailPorID($ID){
		$ID = mysql_escape_mimic($ID);
		$this->consulta("UPDATE usuarios SET user_validado = '' WHERE ID = '{$ID}'");
	}
	
	// Retorna la ID del usuario que tiene ese NICK
	function idDesdeNick($nick){
		$nick = mysql_escape_mimic($nick);
		$nick = $this->consulta("SELECT ID FROM usuarios WHERE NICK = '{$nick}'", true)[0];
		return $nick["ID"];
	}
	
	// Retorna el NICK del usuario dada una ID
	function nickDesdeId($ID){
		$ID = mysql_escape_mimic($ID);
		$ID = $this->consulta("SELECT NICK FROM usuarios WHERE ID = '{$ID}'", true)[0];
		return $ID["NICK"];
	}
	
	// Retorna la ID
	function NickPasswordValidacion($nick, $password){
		$nick = mysql_escape_mimic($nick);
		$password = mysql_escape_mimic($password);
		return count($this->consulta("SELECT * FROM usuarios WHERE NICK = '{$nick}' AND PASSWORD = '{$password}' AND user_validado = '';")) > 0;
	}
	
	
	function SetUsuarioLogueadoPorID($ID, $passwordCookie){
		$ID = mysql_escape_mimic($ID);
		$passwordCookie = mysql_escape_mimic($passwordCookie);
		$hoy = date('Y-m-d H:i:s');
		
		// La cookie dura 1 mes
		$fechaTope = date('Y-m-d H:i:s', mktime(0, 0, 0, date("m")+1  , date("d"), date("Y")));
		$this->consulta("UPDATE usuarios SET FECHA_ULT_LOGIN = '{$hoy}' WHERE ID = '{$ID}'");
		$this->consulta("INSERT INTO login (ID_USER, COOKIE, FECHA_TOPE) VALUES ('{$ID}', '{$passwordCookie}', '{$fechaTope}') ON DUPLICATE KEY UPDATE COOKIE = '{$passwordCookie}', FECHA_TOPE = '{$fechaTope}';");
	}
	
	function SetUsuarioDeslogueadoPorID($ID){
		$ID = mysql_escape_mimic($ID);
		
		$this->consulta("DELETE FROM login WHERE ID_USER = {$ID};");
	}
	
	// Retorna falso si no es un usuario válido, o un array con los datos del usuario: Nombre, Apellidos, Nick
	// No se puede cachear ya que retornamos sala
	function validaCookieLogueado($u, $p){
		$u = mysql_escape_mimic($u);
		$p = mysql_escape_mimic($p);
		$result = $this->consulta("SELECT * FROM usuarios WHERE ID = (SELECT ID_USER FROM login WHERE ID_USER = '{$u}' AND COOKIE = '{$p}' AND FECHA_TOPE > NOW())");
		return count($result)>0?$result[0]:false;
	}
	
	// Consultar salas en curso dado un filtro (array con indices y valores)
	function consultarSalasEnCurso(&$filtro){
		$filtro['players'] = mysql_escape_mimic($filtro['players']);
		$filtro['parejas'] = mysql_escape_mimic($filtro['parejas']);
		//$filtro['llenas'] = mysql_escape_mimic($filtro['llenas']);
		$players_filtro = $filtro['players']===false?'':' AND jugadores_max = '.$filtro['players'].' ';
		//$llenas_filtro = $filtro['llenas']===true?'':' AND p_total < jugadores_max ';
		$parejas_filtro = $filtro['parejas']===true?' AND parejas = true ':' AND parejas = false ';
		return $this->consulta("SELECT salas.ID, salas.nombre, salas.jugadores_max, salas.iniciada, salas.`1`, usuarios.nick, salas.`2`, salas.`3`, salas.`4`, salas.p_total, salas.parejas FROM salas LEFT JOIN usuarios ON salas.`1` = usuarios.ID WHERE iniciada = 0 ".$players_filtro.$parejas_filtro);
	}
	
	// Crear una sala. La consulta que lo mete en la db
	function creaSala(&$filtro, $ID_creador){
		$filtro['nombre'] = mysql_escape_mimic($filtro['nombre']);
		$filtro['jugadores'] = mysql_escape_mimic($filtro['jugadores']);
		$filtro['por_parejas'] = mysql_escape_mimic($filtro['por_parejas']);
		$ID_creador = mysql_escape_mimic($ID_creador);
		
		//id_creador en única. Ya que una persona solo puede crear una sala a la vez, en caso de que intente crear otra no se le deja. En su lugar, se le indicará que ya se encuentra en una sala y se le ofrecerá la opción de terminar la partida en curso.
		$t = $this->consulta("INSERT INTO salas (`1`, nombre, jugadores_max, parejas) VALUES ".
									"('{$ID_creador}', '{$filtro['nombre']}', '{$filtro['jugadores']}', '{$filtro['por_parejas']}')");
		$this->consulta("UPDATE usuarios SET sala={$this->LAST_MYSQL_ID} WHERE ID={$ID_creador}");
		return $t;
	}
	
	// Un usuario se mete en la db en dos lugares cuando se entra en una sala. Una, en salas, la otra, en usuarios (en una columna indicando la sala actual)
	// Esta función debe llamarse en caso de poder meterse el usuario y conociendo el hueco de la sala donde meterlo, la sala y la id del usuario
	function salaMeterUsuario($ID, $sala, $p234){
		$ID = mysql_escape_mimic($ID);
		$sala = mysql_escape_mimic($sala);
		$p234 = mysql_escape_mimic($p234);
		
		$this->consulta("UPDATE salas SET `{$p234}`={$ID}, p_total = p_total + 1 WHERE ID={$sala}");
		$this->consulta("UPDATE usuarios SET sala={$sala} WHERE ID={$ID}");
	}
	
	// Quitar a un usuario de una sala
	function salaQuitarUsuario($ID, $sala, $p234){
		$ID = mysql_escape_mimic($ID);
		$sala = mysql_escape_mimic($sala);
		$p234 = mysql_escape_mimic($p234);
		
		if($p234 != '1'){
			$this->consulta("UPDATE usuarios SET sala=-1 WHERE ID={$ID}");
			$this->consulta("UPDATE salas SET `{$p234}`=-1, p_total = p_total - 1 WHERE ID={$sala}");
		}
		else{
			$this->consulta("UPDATE usuarios SET sala=-1 WHERE sala={$sala}");
			$this->consulta("DELETE FROM salas WHERE ID={$sala}");
		}
	}
	
	function salaMarcarIniciada($sala){
		$ID = mysql_escape_mimic($ID);
		$sala = mysql_escape_mimic($sala);
		$p234 = mysql_escape_mimic($p234);
		
		$this->consulta("UPDATE salas SET iniciada = 1 WHERE ID={$sala}");
	}
	
	// Un usuario se mete en la db en dos lugares cuando se entra en una sala. Una, en salas, la otra, en usuarios (en una columna indicando la sala actual)
	// Esta función debe llamarse en caso de poder meterse el usuario y conociendo el hueco de la sala donde meterlo, la sala y la id del usuario
	function salaInfo($sala){
		$sala = mysql_escape_mimic($sala);
		
		return $this->consulta("SELECT * FROM salas WHERE ID={$sala}")[0];
	}
	
	
	// STATS. agregar victorias, derrotas, y resetear la puntuación máxima
	function guardar_victoria($variante, $ID){
		if(!in_array($variante, array('online', 'cpu')))return;
		$ID = mysql_escape_mimic($ID);
		$this->consulta("UPDATE usuarios SET victorias_{$variante} = victorias_{$variante} + 1 WHERE ID={$ID};");
	}

	function guardar_derrota($variante, $ID){
		if(!in_array($variante, array('online', 'cpu')))return;
		$ID = mysql_escape_mimic($ID);
		$this->consulta("UPDATE usuarios SET derrotas_{$variante} = derrotas_{$variante} + 1 WHERE ID={$ID};");
	}

	function guardar_puntuacion_max($variante, $ID, $puntos){
		if(!in_array($variante, array('online', 'cpu')))return;
		$ID = mysql_escape_mimic($ID);
		$puntos = mysql_escape_mimic($puntos);
		$result = $this->consulta("SELECT puntuacion_maxima_{$variante} FROM usuarios WHERE ID={$ID};")[0];
		if($result['puntuacion_maxima_'.$variante] < $puntos){
			$this->consulta("UPDATE usuarios SET puntuacion_maxima_{$variante} = {$puntos} WHERE ID={$ID};");
		}
	}
	
	// --------------------------------------------------------
	
	//Cachear resultados. $consulta es el sql a cachear, $resultado es el array de la respuesta y $tiempoValido es la cantidad de segundos que se guardaré en cache (usando memcache)
	function cacheaResultado($consulta, $resultado, $tiempoValido = 3600){
		$resultado = json_encode($resultado);
		$memcache_obj = memcache_pconnect("localhost", 11211);
		$memcache_obj->add($consulta, $resultado, false, $tiempoValido);
	}
	
	//Cachear resultados. $consulta es el sql a cachear, $resultado es el array de la respuesta y $tiempoValido es la cantidad de segundos que se guardaré en cache (usando memcache)
	function consultaCache($consulta){
		$memcache_obj = memcache_pconnect("localhost", 11211);
		$resultado = $memcache_obj->get($consulta);
		return $resultado===false?false:json_decode($resultado);
	}
}


function hashea($string){
	return md5("c416=p+{29I671t".$string);
}

function generateRandomString($length = 10) {
	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$randomString = '';
	for ($i = 0; $i < $length; $i++) {
		$randomString .= $characters[rand(0, strlen($characters) - 1)];
	}
	return $randomString;
}




// Esta función retorna un array con los datos del usuario si está bien logueado, pero de lo contrario si no hay url redirección retorna false, si la hay redirecciona a esa url por header y termina el script
function detectaLogueadoORedireccion(&$database, $urlRedireccion = false){
	// Detectar si el usuario está logueado. Si no lo está, enviarlo a login. En caso de que esté logueado, pero no se corresponda el login con la base de datos, lo mismo.
	if(isset($_COOKIE['u']) && isset($_COOKIE['p']) && $datos_usuario = $database->validaCookieLogueado($_COOKIE['u'], $_COOKIE['p'])){
		return $datos_usuario;
	}
	else{
		if($urlRedireccion === false){
			return false;
		}
		else{
			header("Location: ".PATH.$urlRedireccion, true, 302);
			exit;
		}
	}
}

// Copia de mysql_real_escape_string para uso sin conexión abierta
// http://es1.php.net/mysql_real_escape_string
function mysql_escape_mimic($inp) {
    if(is_array($inp))
        return array_map(__METHOD__, $inp);

    if(!empty($inp) && is_string($inp)) {
        return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $inp);
    }

    return $inp;
}

function ClampCircular($numero, $minimo, $maximo){
	while($numero < $minimo){
		$numero += $maximo -$minimo +1;
	}
	while($numero > $maximo){
		$numero -= $maximo -$minimo +1;
	}
	return $numero;
};