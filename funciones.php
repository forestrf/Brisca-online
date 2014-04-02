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
	
	function Abrir($host=null, $user=null, $pass=null, $bd=null){
		if($host !== null)
			$this->host = $host;
		if($user !== null)
			$this->user = $user;
		if($pass !== null)
			$this->pass = $pass;
		if($bd !== null)
			$this->bd = $bd;
			
		
		$this->mysqli = new mysqli($this->host, $this->user, $this->pass, $this->bd);
		if ($this->mysqli->connect_errno) {
			echo "Fallo al contectar a MySQL: (" . $this->mysqli->connect_errno . ") " . $this->mysqli->connect_error;
			return false;
		}
		$this->mysqli->set_charset("utf8");
		return true;
	}
	
	// Realizar una consulta sql. Retorna false en caso de error, además de imprimir el error en pantalla
	private function consulta($query){
		$resultado = $this->mysqli->query($query, MYSQLI_USE_RESULT);
		if($resultado === false){
			throw new Exception("Error: ".$this->mysqli->error);
			return false;
		}
		if($resultado === true){
			return true;
		}
		return $resultado->fetch_array(MYSQLI_ASSOC);
	}
	
	// FUNCIONES QUE SE USAN EN LA APLICACIÓN
	
	// Consultar si existe un nick en la base de datos
	function existeNick($nick){
		$nick = $this->mysqli->real_escape_string($nick);
		return $this->consulta("SELECT * FROM usuarios WHERE NICK = '".$nick."'") !== null;
	}
	
	// Insertar un usuario en la base de datos
	function insertaUsuario($nombre, $apellidos, $email, $nick, $password, $requiereValidacion=true){
		$nombre = $this->mysqli->real_escape_string($nombre);
		$apellidos = $this->mysqli->real_escape_string($apellidos);
		$email = $this->mysqli->real_escape_string($email);
		$nick = $this->mysqli->real_escape_string($nick);
		$password = $this->mysqli->real_escape_string($password);
		$hoy = date('Y-m-d H:i:s');
		return $this->consulta("INSERT INTO usuarios (NOMBRE, APELLIDO, NICK, PASSWORD, EMAIL, FECHA_REGISTRO, FECHA_ULT_LOGIN, IP_ULT_LOGIN) VALUES ".
												"('$nombre', '$apellidos', '$nick', '$password', '$email', '$hoy', '$hoy', '{$_SERVER["REMOTE_ADDR"]}')") === true;
	}
	
	// Indicar la password temporal que se usará para la confirmación por correo electrónico
	function setEmailPasswordValidacion($ID, $passwordT){
		$ID = $this->mysqli->real_escape_string($ID);
		$passwordT = $this->mysqli->real_escape_string($passwordT);
		$this->consulta("UPDATE usuarios SET user_validado = '{$passwordT}' WHERE ID = '{$ID}'");
	}
	
	// Retorna true si el email ahora está validado
	function CompruebaEmailValidacion($ID, $passwordT){
		$ID = $this->mysqli->real_escape_string($ID);
		$passwordT = $this->mysqli->real_escape_string($passwordT);
		return $this->consulta("SELECT * FROM usuarios WHERE user_validado = '{$passwordT}' AND ID = '{$ID}'") !== null;
	}
	
	// Marca el usuario como usuario validado por mail
	function ValidaEmailPorID($ID){
		$ID = $this->mysqli->real_escape_string($ID);
		$this->consulta("UPDATE usuarios SET user_validado = '' WHERE ID = '{$ID}'");
	}
	
	// Retorna la ID del usuario que tiene ese NICK
	function idDesdeNick($nick){
		$nick = $this->mysqli->real_escape_string($nick);
		return $this->consulta("SELECT ID FROM usuarios WHERE NICK = '$nick'")["ID"];
	}
	
	// Retorna la ID
	function NickPasswordValidacion($nick, $password){
		$nick = $this->mysqli->real_escape_string($nick);
		$password = $this->mysqli->real_escape_string($password);
		return $this->consulta("SELECT * FROM usuarios WHERE NICK = '$nick' AND PASSWORD = '$password' AND user_validado = ''") !== null;
	}
	
	
	function SetUsuarioLogueadoPorID($ID, $passwordCookie){
		$ID = $this->mysqli->real_escape_string($ID);
		$passwordCookie = $this->mysqli->real_escape_string($passwordCookie);
		$hoy = date('Y-m-d H:i:s');
		
		// La cookie dura 1 mes
		$fechaTope = date('Y-m-d H:i:s', mktime(0, 0, 0, date("m")+1  , date("d"), date("Y")));
		$this->consulta("UPDATE usuarios SET FECHA_ULT_LOGIN = '".$hoy."' WHERE ID = '$ID'");
		$this->consulta("INSERT INTO login (ID_USER, COOKIE, FECHA_TOPE) VALUES ('$ID', '$passwordCookie', '$fechaTope') ON DUPLICATE KEY UPDATE COOKIE = '$passwordCookie', FECHA_TOPE = '$fechaTope'");
	}
	
	// Retorna falso si no es un usuario válido, o un array con los datos del usuario: Nombre, Apellidos, Nick
	function validaCookieLogueado($u, $p){
		$u = $this->mysqli->real_escape_string($u);
		$p = $this->mysqli->real_escape_string($p);
		return $this->consulta("SELECT NOMBRE, APELLIDO, NICK FROM usuarios WHERE ID = (SELECT ID FROM login WHERE ID_USER = '$u' AND COOKIE = '$p' AND FECHA_TOPE > NOW())") !== null;
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




// Esta función retorna true si está bien logueado, pero de lo contrario si no hay url redirección retorna false, si la hay redirecciona a esa url por header y termina el script
function detectaLogueadoORedireccion(&$database, $urlRedireccion = false){
	// Detectar si el usuario está logueado. Si no lo está, enviarlo a login. En caso de que esté logueado, pero no se corresponda el login con la base de datos, lo mismo.
	if(isset($_COOKIE['u']) && isset($_COOKIE['p']) && $database->validaCookieLogueado($_COOKIE['u'], $_COOKIE['p'])){
		return true;
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