<?php

require_once('definiciones.php');
require_once('funciones.php');

$database = new DB();


// Detectar si el usuario está logueado. Si no lo está, enviarlo a login. En caso de que está logueado, pero no se corresponda el login con la base de datos, lo mismo.
if(!detectaLogueadoORedireccion($database)){
	exit;
}

if(isset($_POST['accion'])){
	//Mirar si el usuario está logueado. validar y después operar.
	
	switch($_POST['accion']){
		case 'consultar':
			if(
				!isset($_POST['players']) ||
				!isset($_POST['llenas']) ||
				!isset($_POST['parejas'])
			){
				exit;
			}
			$posibles=array(
				'players' => array('0','2','3','4'),
				'llenas' => array('true','false','1','0'),
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
	
	
	}



}