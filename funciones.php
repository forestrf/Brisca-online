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
		
		$resultado = $this->mysqli->query($query, MYSQLI_USE_RESULT);
		if($resultado === false){
			throw new Exception("Error: ".$this->mysqli->error);
			return false;
		}
		if($resultado === true){
			return true;
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
		return $this->consulta("SELECT * FROM usuarios WHERE NICK = '".$nick."'", true) !== null;
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
		return $this->consulta("SELECT * FROM usuarios WHERE user_validado = '{$passwordT}' AND ID = '{$ID}'", true) !== null;
	}
	
	// Marca el usuario como usuario validado por mail
	function ValidaEmailPorID($ID){
		$ID = mysql_escape_mimic($ID);
		$this->consulta("UPDATE usuarios SET user_validado = '' WHERE ID = '{$ID}'");
	}
	
	// Retorna la ID del usuario que tiene ese NICK
	function idDesdeNick($nick){
		$nick = mysql_escape_mimic($nick);
		$nick = $this->consulta("SELECT ID FROM usuarios WHERE NICK = '$nick'", true)[0];
		return $nick["ID"];
	}
	
	// Retorna la ID
	function NickPasswordValidacion($nick, $password){
		$nick = mysql_escape_mimic($nick);
		$password = mysql_escape_mimic($password);
		return $this->consulta("SELECT * FROM usuarios WHERE NICK = '$nick' AND PASSWORD = '$password' AND user_validado = ''") !== null;
	}
	
	
	function SetUsuarioLogueadoPorID($ID, $passwordCookie){
		$ID = mysql_escape_mimic($ID);
		$passwordCookie = mysql_escape_mimic($passwordCookie);
		$hoy = date('Y-m-d H:i:s');
		
		// La cookie dura 1 mes
		$fechaTope = date('Y-m-d H:i:s', mktime(0, 0, 0, date("m")+1  , date("d"), date("Y")));
		$this->consulta("UPDATE usuarios SET FECHA_ULT_LOGIN = '".$hoy."' WHERE ID = '$ID'");
		$this->consulta("INSERT INTO login (ID_USER, COOKIE, FECHA_TOPE) VALUES ('$ID', '$passwordCookie', '$fechaTope') ON DUPLICATE KEY UPDATE COOKIE = '$passwordCookie', FECHA_TOPE = '$fechaTope'");
	}
	
	// Retorna falso si no es un usuario válido, o un array con los datos del usuario: Nombre, Apellidos, Nick
	function validaCookieLogueado($u, $p){
		$u = mysql_escape_mimic($u);
		$p = mysql_escape_mimic($p);
		return $this->consulta("SELECT NOMBRE, APELLIDO, NICK FROM usuarios WHERE ID = (SELECT ID_USER FROM login WHERE ID_USER = '$u' AND COOKIE = '$p' AND FECHA_TOPE > NOW())", true)[0];
	}
	
	// Consultar salas en curso dado un filtro (array con indices y valores)
	function consultarSalasEnCurso(&$filtro){
		$filtro['players'] = mysql_escape_mimic($filtro['players']);
		$filtro['llenas'] = mysql_escape_mimic($filtro['llenas']);
		$players_filtro = $filtro['players']===false?'':' AND jugadores_max = '.$filtro['players'].' ';
		$llenas_filtro = $filtro['llenas']===true?'':' AND p_total < jugadores_max ';
		$parejas_filtro = $filtro['parejas']===true?' AND parejas = true ':' AND parejas = false ';
		return $this->consulta("SELECT salas.ID, salas.nombre, salas.jugadores_max, salas.iniciada, salas.id_creador, usuarios.nick, salas.p2_id, salas.p3_id, salas.p4_id, salas.p_total, salas.parejas FROM salas LEFT JOIN usuarios ON salas.id_creador = usuarios.ID WHERE iniciada = 0 ".$players_filtro.$llenas_filtro.$parejas_filtro);
	
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