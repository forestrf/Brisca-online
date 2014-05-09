<?php
	header('Content-Type: text/html; charset=UTF-8');
	
	require_once('definiciones.php');
	require_once('funciones.php');

	$database = new DB();
	
	
	// En esta variable guardaremos los errores del formulario usando de índice el indice del elemento POST.
	$errores = array();
	
	if(isset($_POST['enviar'])){
		// validar nick
		if(!preg_match("/^[a-z0-9_]{3,15}$/i", $_POST['nick'])){
			$errores['nick'] = 'Nick inválido';
		}
		
		// validar password y repetición de password
		if(!preg_match("/^[a-z0-9áéíóúàèìòùçñ_@!#$%&|\/\.\-:;,]{4,30}$/i", $_POST['password'])){
			$errores['password'] = 'Contraseña inválida';
		}
		
		if(count($errores)==0){
			
			$password = hashea($_POST['password']);
			
			// Comprobamos si ya existe alguien con el mismo nick.
			if($database->NickPasswordValidacion($_POST['nick'], $password)){
				// Se trata de un usuario correcto
				
				$cookie = md5(generateRandomString(10));
				
				// Generar cookie y marcar el usuario como logueado
				$ID = $database->idDesdeNick($_POST['nick']);
				$database->SetUsuarioLogueadoPorID($ID, $cookie);
				
				// 1 mes
				setcookie('u', $ID, time()+2592000);
				setcookie('p', $cookie, time()+2592000);
				
				// Redireccionar al usuario a la página de logueados
				header('Location: /index.php', true, 302);
				
			}
			else{
				// Los datos de login son incorrectos o el usuario no ha validado el correo. Ambos casos son mostrados como un error de fallo en usuario/contraseña
				// Es importante no dar mucha información sobre la razón del fallo ya que una información concreta puede facilitar ataques.
				$errores['FALLO'] = 'Nick/Contraseña erroneos';
			}
		
		}
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
		<title>Login</title>
		<link type="text/css" rel="stylesheet" href="css/reset.css">
		<link type="text/css" rel="stylesheet" href="css/index.css">
		<link type="text/css" rel="stylesheet" href="css/lobby.css">
	</head>
	<body>
		<div id="contenedor">
			<div class="titulo">La Brisca online</div>
			<div class="salas_abiertas div_login">
				Formulario de login
				<form method="POST" action="">
					<table class="form_crear_sala">
						<tbody>
							<tr>
								<td>Nick:</td>
								<td><input type="text" name="nick" class="boton_en_listado ancho <?php if(isset($errores['FALLO']) || isset($errores['nick']))echo 'error';?>" value="<?php if(isset($_POST['nick']) && !isset($errores['nick'])) echo htmlspecialchars($_POST['nick'])?>"></td>
							</tr>
							<tr>
								<td>Contraseña:</td>
								<td><input type="password" name="password" class="boton_en_listado ancho <?php if(isset($errores['FALLO']) || isset($errores['password']))echo 'error';?>" value="<?php if(isset($_POST['password']) && !isset($errores['password']) && !isset($errores['password2'])) echo htmlspecialchars($_POST['password'])?>"></td>
							</tr>
							<tr>
								<td></td>
								<td>
									<input type="submit" name="enviar" class="boton_en_listado pointer ancho modbutton" value="Login">
								 </td>
							 </tr>
						</tbody>
					</table>
				</form>
			</div>
		</div>
	</body>
</html>