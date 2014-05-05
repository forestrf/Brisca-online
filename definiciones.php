<?php

// Dominio donde está la aplicación. Se usa en el email
if(!defined('DOMINIO'))
	define('DOMINIO', 'localhost');

// Carpeta donde está la aplicación
if(!defined('PATH'))
	define('PATH', '/');

// Usar memcache
if(!defined('MEMCACHE'))
	define('MEMCACHE', false);