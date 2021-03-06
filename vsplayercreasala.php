<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
		<title>Jugar contra otros jugadores</title>
		<link type="text/css" rel="stylesheet" href="css/reset.css">
		<link type="text/css" rel="stylesheet" href="css/lobby.css">
	</head>
	<body>
		<div id="contenedor">
			<a class="boton_sala home" href="/">Ir al home</a>
			<a class="boton_sala crear" href="/vsplayerlobby.php">Listado de salas</a>
			<div class="salas_abiertas">
				Crear una nueva sala
				<form method="POST" action="/vsplayerlobbyconsultas.php" onsubmit="if(!validaNombre(this.elements['nombre'])){alert('Nombre de sala incorrecto');return false;}">
					<table class="form_crear_sala">
						<tr>
							<td>
								Nombre de la sala
							</td>
							<td>
								<input type="text" name="nombre" onkeyup="validaNombre(this)" class="boton_en_listado ancho <?php if(isset($_GET['nombre']))echo 'error'?>" placeholder="nombre de la sala..." value="<?php if(isset($_GET['nombre'])) echo $_GET['nombre'].'.';?>">
							</td>
						</tr>
						<tr>
							<td>
								Número de jugadores
							</td>
							<td>
								<div class="boton_en_listado ancho">
									<select name="jugadores" id="jugadores">
										<option value="2">2 Jugadores</option>
										<option value="3">3 Jugadores</option>
										<option value="4" selected>4 Jugadores</option>
									</select>
								</div>
							</td>
						</tr>
						<tr>
							<td>
								Por parejas
							</td>
							<td>
								<label class="boton_en_listado pointer ancho" for="filtro_parejas">
									<input type="checkbox" name="por_parejas" id="filtro_parejas">
									Por parejas
								</label>
							</td>
						</tr>
						<tr>
							<td></td>
							<td>
								<input type="submit" class="boton_en_listado pointer ancho modbutton" name="submit" value="Crear sala">
							</td>
						</tr>
					</table>
					<input type="hidden" name="accion" value="crearsala">
				</form>
			</div>
		</div>
		<script>
			var nombreRegex = /^[a-z_áéíóúàèìòù -]+$/;
			function validaNombre(input){
				if(nombreRegex.test(input.value)){
					if(input.className.indexOf("error")>=0){
						input.className = input.className.split("error").join("");
					}
					return true;
				}
				else{
					if(input.className.indexOf("error")===-1){
						input.className += " error";
					}
					return false;
				}
			}
			
			document.getElementById('jugadores').onchange = function(){
				enable_disable_filtro_parejas(this);
			}
			enable_disable_filtro_parejas(document.getElementById('jugadores'));
			
			function enable_disable_filtro_parejas(t){
				if(t.value === '4'){
					document.getElementById('filtro_parejas').disabled = false;
				}
				else{
					document.getElementById('filtro_parejas').checked = false;
					document.getElementById('filtro_parejas').disabled = true;
				}
			}
		</script>
	</body>
</html>