<?php
	header('Content-Type: text/html; charset=UTF-8');
	
	require_once('definiciones.php');
	require_once('funciones.php');
	
	require_once('recaptcha/recaptchalib.php');
	$publickey = CAPTCHA_PUBLICKEY; // you got this from the signup page
	
	// En esta variable guardaremos los errores del formulario usando de índice el indice del elemento POST.
	$errores = array();
	
	if(isset($_POST['enviar'])){
		// validar nick
		if(!preg_match("/^[a-z0-9_]{3,15}$/i", $_POST['nick'])){
			$errores['nick'] = "Nick inválido";
		}
		
		// validar password y repetición de password
		if(!preg_match("/^[a-z0-9áéíóúàèìòùçñ_@!#$%&|\/\.\-:;,]{4,30}$/i", $_POST['password'])){
			$errores['password'] = "Contraseña inválida";
		}
		
		if(count($errores)==0){
			
			// Comprobar captcha
			$privatekey = CAPTCHA_PRIVATEKEY;
			$resp = recaptcha_check_answer($privatekey,
									$_SERVER["REMOTE_ADDR"],
									$_POST["recaptcha_challenge_field"],
									$_POST["recaptcha_response_field"]);

			// Captcha inválido
			if(!$resp->is_valid){
				$errores['captcha'] = "Captcha incorrecto";
			}
			else{
				
				error_reporting(-1);
				
				$database = new DB();
				
				$password = hashea($_POST['password']);
				
				// Comprobamos si ya existe alguien con el mismo nick.
				if($database->NickPasswordValidacion($_POST['nick'], $password)){
					// Se trata de un usuario correcto
					
					$cookie = md5(generateRandomString(10));
					
					// Generar cookie y marcar el usuario como logueado
					$ID = $database->idDesdeNick($_POST['nick']);
					$database->SetUsuarioLogueadoPorID($ID, $cookie);
					
					// 1 mes
					setcookie("u", $ID, time()+2592000);
					setcookie("p", $cookie, time()+2592000);
					
					// Redireccionar al usuario a la página de logueados
					header("Location: ".PATH."portada.php", true, 302);
					
				}
				else{
					// Los datos de login son incorrectos o el usuario no ha validado el correo. Ambos casos son mostrados como un error de fallo en usuario/contraseña
					// Es importante no dar mucha información sobre la razón del fallo ya que una información concreta puede facilitar ataques.
					$errores['FALLO'] = "Nick/Contraseña erroneos";
				}
			}
		}
	}
?>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
		<title>Login de usuarios</title>
	</head>
	<body>
		<h3>Formulario de login</h3>
		<form method="POST" action="">
			<?php
				if(isset($errores['FALLO'])){
					echo $errores['FALLO'];
				}
			?>
			<table>
				<tr>
					<td>Nick:</td>
					<td><input type="text" name="nick" value="<?php if(isset($_POST['nick']) && !isset($errores['nick'])) echo htmlspecialchars($_POST['nick'])?>"> <?php if(isset($errores['nick'])) echo $errores['nick']?></td>
				</tr>
				<tr>
					<td>Contraseña:</td>
					<td><input type="password" name="password" value="<?php if(isset($_POST['password']) && !isset($errores['password']) && !isset($errores['password2'])) echo htmlspecialchars($_POST['password'])?>"> <?php if(isset($errores['password'])) echo $errores['password']?></td>
				</tr>
				<tr>
					<td>Captcha:</td>
					<td><?php echo recaptcha_get_html($publickey);?><?php if(isset($errores['captcha'])) echo $errores['captcha']?></td>
				</tr>
			</table>
			<input type="submit" name="enviar" value="Login">
		</form>
	</body>
</html>