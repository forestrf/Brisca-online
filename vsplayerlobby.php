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
			<a class="boton_sala crear" href="/vsplayercreasala.php">Crear sala</a>
			<div class="salas_abiertas">
				<div class="filtro_salas">
					Filtrar salas: 
					<div class="boton_en_listado">
						<select id="filtro_jugadores">
							<option value="0" selected>2-4 jugadores</option>
							<option value="2">2 Jugadores</option>
							<option value="3">3 Jugadores</option>
							<option value="4">4 Jugadores</option>
						</select>
					</div><label class="boton_en_listado pointer" for="filtro_parejas">
						<input type="checkbox" id="filtro_parejas">
						Por parejas
					</label><label class="boton_en_listado pointer" for="filtro_llenas">
						<input type="checkbox" id="filtro_llenas">
						Mostrar salas llenas
					</label>
				</div><div class="boton_en_listado pointer" id="boton_refrescar" style="float:right">
					Actualizar listado de salas
				</div><label class="boton_en_listado pointer" for="auto_update" style="float:right">
					<input type="checkbox" id="auto_update" checked>
					<span id="auto_txt">Auto</span>
				</label>
				<table id="salas_listado" class="salas_listado">
					<thead>
						<tr>
							<th>
								Nombre
							</th>
							<th>
								Creador
							</th>
							<th>
								Jugadores
							</th>
							<th>
								Parejas
							</th>
							<th>
								Llena
							</th>
						</tr>
					</thead>
					<tbody id="salas_listado_tbody"></tbody>
				</table>
			</div>
		</div>
		<script>
			var filtro_jugadores = document.getElementById("filtro_jugadores");
			var filtro_llenas = document.getElementById("filtro_llenas");
			var filtro_parejas = document.getElementById("filtro_parejas");
			var boton_refrescar = document.getElementById("boton_refrescar");
			var auto_update = document.getElementById("auto_update");
			
			filtro_jugadores.onchange = refrescar;
			filtro_llenas.onchange = refrescar;
			filtro_parejas.onchange = refrescar;
			boton_refrescar.onclick = refrescar;
			
			// Tiempo del auto update: 5 segundos
			var tiempoInterval = 5;
			
			var contador = 0;
			var autoupdate = auto_update.checked;
			var autoupdate_sto = autoupdate?setInterval(auto, 1000):null;
			if(!autoupdate)
				refrescar();
			
			auto_update.onclick = function(){
				autoupdate = !autoupdate;
				if(autoupdate){
					autoupdate_sto = setInterval(auto, 1000);
					contador = tiempoInterval;
				}
				else{
					clearInterval(autoupdate_sto);
					document.getElementById("auto_txt").innerHTML = "Auto";
				}
			};
			
			function auto(){
				document.getElementById("auto_txt").innerHTML = "Auto: "+contador;
				if(contador <= 0){
					refrescar();
					contador = tiempoInterval;
					return;
				}
				--contador;
			}
			
			function refrescar(){
				var ajax = new XMLHttpRequest();
				var url = "vsplayerlobbyconsultas.php";
				var params = "accion=consultar&players=" + filtro_jugadores.value + "&llenas=" + filtro_llenas.checked + "&parejas=" + filtro_parejas.checked;
				ajax.open("POST", url, true);
				ajax.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
				ajax.setRequestHeader("Content-length", params.length);
				ajax.setRequestHeader("Connection", "close");
				ajax.onreadystatechange = function(){
					if(ajax.readyState == 4){
						if(ajax.status == 200){
							parsearJSONSalas(ajax.responseText);
						}
					}
				};
				ajax.send(params);
			}
			
			function parsearJSONSalas(json){
				json = JSON.parse(json);
				//console.log(json);
				var salas_listado = document.getElementById("salas_listado_tbody");
				salas_listado.innerHTML = '';
				for(var i = 0; i<json.length; ++i){
					var row = document.createElement("tr");
					
					var nombre = document.createElement("td");
					var creador = document.createElement("td");
					var jugadores = document.createElement("td");
					var parejas = document.createElement("td");
					var llena = document.createElement("td");
					
					row.appendChild(nombre);
					row.appendChild(creador);
					row.appendChild(jugadores);
					row.appendChild(parejas);
					row.appendChild(llena);
					
					creador.innerHTML = json[i].nick;
					jugadores.innerHTML = json[i].p_total+"/"+json[i].jugadores_max;
					nombre.innerHTML = json[i].nombre;
					llena.innerHTML = json[i].p_total > json[i].jugadores_max ? 'Sí':'No';
					parejas.innerHTML = json[i].parejas === "1" ? 'Sí':'No';
					
					salas_listado.appendChild(row);
				}
			}
		</script>
	</body>
</html>


crear sala
	cuantos jugadores
	contraseña

meterse en una sala
	ver jugadores en la sala
	en caso de poder meterte
		meter contraseña si hay una

Una sala se cierra cuando el creador se va o la partida se acaba.
El creador se irá en un momento u otro
