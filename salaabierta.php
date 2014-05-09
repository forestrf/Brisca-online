<?php
	if(isset($_GET['sala']) && is_numeric($_GET['sala']) && $_GET['sala'] >=0){
		$sala = $_GET['sala'];
	}
	else{
		header('Location: /', true, 302);
		exit;
	}
	header('Content-Type: text/html; charset=UTF-8');
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
				Ya estás en una sala, no puedes estar en varias a la vez.
				<input type="hidden" name="abandonar" value="1">
				<table class="tabla_salaabierta">
					<tbody>
						<tr>
							<td>
								<form method="POST" action="vsplayerlobbyconsultas.php">
								<input type="hidden" name="accion" value="abandonarsala">
								<input type="submit" class="boton_en_listado ancho pointer modbutton" value="Abandonar sala">
								</form>
							</td><td>
								<form method="GET" action="vsplayer.php">
								<input type="hidden" name="sala" value="<?php echo $sala?>">
								<input type="submit" class="boton_en_listado ancho pointer modbutton" value="Reabrir sala">
								</form>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</body>
</html>