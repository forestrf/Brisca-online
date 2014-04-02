<?php
	header('Content-Type: text/html; charset=UTF-8');

	
	require_once('definiciones.php');
	require_once('funciones.php');
	

	// https://developers.google.com/recaptcha/docs/php
	require_once('recaptcha/recaptchalib.php');
	$publickey = "6Lf0du8SAAAAAInVTXZh6NTya42sW8_KsOSbTUEW"; // you got this from the signup page

	
	// En esta variable guardaremos los errores del formulario usando de índice el indice del elemento POST.
	$errores = array();

	// En caso de que nos llegue el formulario, validar los datos. Si son validos, registrar usuario
	if(isset($_POST['enviar'])){
		
		// Validar nombre y apellidos
		if(!preg_match("/^[a-záéíóúàèìòùçñ ]{2,40}$/i", $_POST['nombre'])){
			$errores['nombre'] = "Nombre incorrecto";
		}
		if(!preg_match("/^[a-záéíóúàèìòùçñ ]{2,40}$/i", $_POST['apellidos'])){
			$errores['apellidos'] = "Apellidos incorrectos";
		}
		
		// validar email y repetición de email
		if(!preg_match("/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$/i", $_POST['email'])){
			$errores['email'] = "Correo electrónico incorrecto";
		}
		if($_POST['email'] !== $_POST['email2']){
			$errores['email2'] = "Debes de introducir el mismo correo electrónico 2 veces";
		}
		
		// validar nick
		if(!preg_match("/^[a-z0-9_]{3,15}$/i", $_POST['nick'])){
			$errores['nick'] = "Nick inválido";
		}
		
		// validar password y repetición de password
		if(!preg_match("/^[a-z0-9áéíóúàèìòùçñ_@!#$%&|\/\.\-:;,]{4,30}$/i", $_POST['password'])){
			$errores['password'] = "Contraseña inválida";
		}
		if($_POST['password'] !== $_POST['password2']){
			$errores['password2'] = "Debes de introducir la misma contraseña 2 veces";
		}
		
		
		
		if(count($errores) === 0){
			// Comprobar captcha
			$privatekey = "6Lf0du8SAAAAADPYrWhClrVpZleDC5OlI6zvSxW_";
			$resp = recaptcha_check_answer($privatekey,
									$_SERVER["REMOTE_ADDR"],
									$_POST["recaptcha_challenge_field"],
									$_POST["recaptcha_response_field"]);

			// Captcha inválido
			if(!$resp->is_valid){
				$errores['captcha'] = "Captcha incorrecto";
			}
			else{
				// Captcha válido. Intentar registrar usuario
				
				// Conectamos a la base de datos
				$database = new DB();
				$database->Abrir();
				
				// Comprobamos si ya existe alguien con el mismo nick.
				if($database->existeNick($_POST['nick'])){
					$errores['nick'] = "Nick en uso. Por favor, elige uno distinto";
				}
				else{
					// No guardar la contraseña en texto plano, guardar un hash de la misma
					$password = hashea($_POST['password']);
					if($database->insertaUsuario($_POST['nombre'], $_POST['apellidos'], $_POST['email'], $_POST['nick'], $password, true) === true){
						// El usuario ha sido registrado correctamente
						// Enviar correo de confirmación
						
						// Retorna la id de un usuario a partir del nick
						$idUser = $database->idDesdeNick($_POST['nick']);
						
						$passwordValidacion = generateRandomString(15);
						
						$database->setEmailPasswordValidacion($idUser, $passwordValidacion);
						
						$para		= $_POST['email'];
						$titulo		= 'Correo de validación de '.DOMINIO;
						$mensaje	= "Es necesario que valides el correo clicando en el siguiente enlace: <a href='http://".DOMINIO.PATH."validaremail.php?n={$idUser}&p=".urlencode($passwordValidacion)."'>http://".DOMINIO.PATH."validaremail.php?n={$idUser}&p=".urlencode($passwordValidacion)."</a>";
						$cabeceras	= "From: do-not-reply@".DOMINIO."\r\n";

						mail($para, $titulo, $mensaje, $cabeceras);
						
						// Redireccionar a la página de registro correcto donde se pide que se confirme el correo y terminar el script
						
						
						echo $mensaje;
						//header("Location: ".PATH."compruebatucorreo.html", true, 302);
						exit;
					}
					else{
						// El registro del usuario a fallado
						echo "Ha ocurrido un fallo intentando registrar al usuario";
					}
				}
			}
		}
	}

?>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
		<title>Registro de usuarios</title>
	</head>
	<body>
		<h3>Formulario de registro</h3>
		<form method="POST" action="">
			<table>
				<tr>
					<td>Nombre:</td>
					<td><input type="text" name="nombre" value="<?php if(isset($_POST['nombre']) && !isset($errores['nombre'])) echo htmlspecialchars($_POST['nombre'])?>"> <?php if(isset($errores['nombre'])) echo $errores['nombre']?></td>
				</tr>
				<tr>
					<td>Apellidos:</td>
					<td><input type="text" name="apellidos" value="<?php if(isset($_POST['apellidos']) && !isset($errores['apellidos'])) echo htmlspecialchars($_POST['apellidos'])?>"> <?php if(isset($errores['apellidos'])) echo $errores['apellidos']?></td>
				</tr>
				<tr>
					<td>E-Mail:</td>
					<td><input type="text" name="email" value="<?php if(isset($_POST['email']) && !isset($errores['email']) && !isset($errores['email2'])) echo htmlspecialchars($_POST['email'])?>"> <?php if(isset($errores['email'])) echo $errores['email']?></td>
				</tr>
				<tr>
					<td>Repite E-Mail:</td>
					<td><input type="text" name="email2" value="<?php if(isset($_POST['email2']) && !isset($errores['email']) && !isset($errores['email2'])) echo htmlspecialchars($_POST['email2'])?>"> <?php if(isset($errores['email2'])) echo $errores['email2']?></td>
				</tr>
				<tr>
					<td>Nick:</td>
					<td><input type="text" name="nick" value="<?php if(isset($_POST['nick']) && !isset($errores['nick'])) echo htmlspecialchars($_POST['nick'])?>"> <?php if(isset($errores['nick'])) echo $errores['nick']?></td>
				</tr>
				<tr>
					<td>Contraseña:</td>
					<td><input type="password" name="password" value=""> <?php if(isset($errores['password'])) echo $errores['password']?></td>
				</tr>
				<tr>
					<td>Repite Contraseña:</td>
					<td><input type="password" name="password2" value=""> <?php if(isset($errores['password2'])) echo $errores['password2']?></td>
				</tr>
				<tr>
					<td>Captcha:</td>
					<td><?php echo recaptcha_get_html($publickey);?><?php if(isset($errores['captcha'])) echo $errores['captcha']?></td>
				</tr>
			</table>
			<input type="submit" name="enviar" value="Registrarme">
		</form>
	</body>
</html>