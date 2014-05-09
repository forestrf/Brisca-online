<?php

require_once 'definiciones.php';
require_once 'funciones.php';
require_once 'smsfunciones.php';

$database = new DB();


// Detectar si el usuario está logueado. Si no lo está, enviarlo a login. En caso de que está logueado, pero no se corresponda el login con la base de datos, lo mismo.
if(!$usuario = detectaLogueadoORedireccion($database)){
	exit;
}

if(isset($_POST['accion'])){
	//Mirar si el usuario está logueado. validar y después operar.
	
	switch($_POST['accion']){
		case 'consultar':
			if(
				!isset($_POST['players']) ||
				//!isset($_POST['llenas']) ||
				!isset($_POST['parejas'])
			){
				exit;
			}
			$posibles=array(
				'players' => array('0','2','3','4'),
				//'llenas' => array('true','false','1','0'),
				'parejas' => array('true','false','1','0'),
			);
			$filtro = array();
			foreach($posibles as $key=>$elem){
				foreach($elem as $posib){
					if($posib === $_POST[$key]){
						if($posib === '0')$filtro[$key] = false;
						elseif($posib === '1')$filtro[$key] = true;
						elseif($posib === 'false')$filtro[$key] = false;
						elseif($posib === 'true')$filtro[$key] = true;
						else $filtro[$key] = $posib;
						continue 2;
					}
				}
				exit;
			}
			//Si el código ha llegado a este punto, los valores son válidos y pueden ser usados
			//print_r($filtro);
			
			echo json_encode($database->consultarSalasEnCurso($filtro));
		break;
		
		
		case 'crearsala':
			if(
				!isset($_POST['nombre']) ||
				!isset($_POST['jugadores'])
			){
				exit;
			}
			if(!isset($_POST['por_parejas'])){
				$_POST['por_parejas'] = '0';
			}
			else{
				$_POST['por_parejas'] = '1';
			}
			if(!preg_match('@^[a-z_áéíóúàèìòù -]+$@i',$_POST['nombre'])){
				header('Location: /vsplayercreasala.php?nombre='.urlencode('Nombre inválido'), true, 302);
				exit;
			}
			$posibles=array(
				'jugadores' => array('2','3','4'),
				'por_parejas' => array('true','false','1','0'),
			);
			foreach($posibles as $key=>$elem){
				foreach($elem as $posib){
					if($posib === $_POST[$key]){
						if($posib === '0')$_POST[$key] = false;
						elseif($posib === '1')$_POST[$key] = true;
						elseif($posib === 'false')$_POST[$key] = false;
						elseif($posib === 'true')$_POST[$key] = true;
						else $_POST[$key] = $posib;
						continue 2;
					}
				}
				exit;
			}
			//Si el código ha llegado a este punto, los valores son válidos y pueden ser usados
			//var_dump($_POST);
			$filtro = array(
				'nombre'=>$_POST['nombre'],
				'jugadores'=>$_POST['jugadores'],
				'por_parejas'=>$_POST['por_parejas']
			);
			
			$respuesta = $database->creaSala($filtro, $usuario['ID']);
			
			
			if($respuesta === false){
				// El usuario tenía ya abierta una sala. Pedir que la cierre o se una a la partida
				if($usuario['sala'] !== '-1'){
					header('Location: /salaabierta.php?sala='.$usuario['sala'], true, 302);
					exit;
				}
			}
			else{
				// Sala creada con éxito. Ir a la sala creada.
				header('Location: /vsplayer.php?sala='.$database->LAST_MYSQL_ID, true, 302);
			}
		break;
		
		case 'abandonarsala':
			$salaInfo = $database->salaInfo($usuario['sala']);
			$soy = '-1';
			for($i=1; $i<=4; ++$i){
				if($salaInfo[$i] === $usuario['ID']){
					$soy = $i;
					break;
				}
			}
			$database->salaQuitarUsuario($usuario['ID'], $usuario['sala'], $soy);
			
			$salaInfo = $database->salaInfo($usuario['sala']);
			
			$dbsqlite = abredbsqlitesala($usuario['sala']);
			
			quitar_usuario($dbsqlite, $usuario, $salaInfo);
			
			header('Location: /vsplayerlobby.php', true, 302);
		break;
	
	
	}



}