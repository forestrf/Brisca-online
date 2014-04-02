<?php

require_once('definiciones.php');
require_once('funciones.php');


$database = new DB();
$database->Abrir();


// Detectar si el usuario está logueado. Si no lo está, enviarlo a login. En caso de que esté logueado, pero no se corresponda el login con la base de datos, lo mismo.
detectaLogueadoORedireccion($database, "login.php");



?>
Estás logueado<p>
<a href="vscpu.php">Jugar contra la máquina (No puntua)</a><br>
<a href="vshumanroom.php">Jugar contra otros jugadores (Ir a la sala de espera)</a><br>
<br>
Partidas ganadas: xx<br>
Partidas perdidas: xx<br>

