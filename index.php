<?php
require_once('definiciones.php');
require_once('funciones.php');

$database = new DB();
?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
		<title>Proyecto final de curso - Andrés Leone</title>
		<link type="text/css" rel="stylesheet" href="css/reset.css">
		<link type="text/css" rel="stylesheet" href="css/index.css">
	</head>
	<body>
		<div id="contenedor">
			<div class="titulo">La Brisca online</div>
			<?php if(!$usuario = detectaLogueadoORedireccion($database)){?>
				<a href="login.php" class="login">Login</a>
				<a href="registro.php" class="registro">Registro</a>
				<a href="vscpu.html" class="cpu">Jugar contra la máquina<br>(no requiere registrarse)</a>
			<?php } else { ?>
				<a href="vscpu.html" class="l_cpu">Jugar contra la máquina</a><br>
				<a href="vsplayerlobby.php" class="l_online">Jugar contra otros jugadores (Ir a la sala de espera)</a><br>
				<a href="logout.php" class="logout">Cerrar sesión</a><br>
				<br>
				<table class="puntuaciones">
					<tbody>
						<tr>
							<td></td>
							<td class="b b1 b2">Mayor puntuación</td>
							<td class="b b2">Partidas ganadas</td>
							<td class="b b2">Partidas pertidas</td>
						</tr>
						<tr>
							<td class="b b3 b4">VS otros jugadores</td>
							<td><?php echo $usuario['puntuacion_maxima_online']?></td>
							<td><?php echo $usuario['victorias_online']?></td>
							<td><?php echo $usuario['derrotas_online']?></td>
						</tr>
						<tr>
							<td class="b b4">VS máquina</td>
							<td><?php echo $usuario['puntuacion_maxima_cpu']?></td>
							<td><?php echo $usuario['victorias_cpu']?></td>
							<td><?php echo $usuario['derrotas_cpu']?></td>
						</tr>
					</tbody>
				</table>
			<?php } ?>
		</div>
	</body>
</html>