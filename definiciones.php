<?php

// Dominio donde está la aplicación. Se usa en el email
if(!defined('DOMINIO'))
	define('DOMINIO', 'localhost');

// Usar memcache
if(!defined('MEMCACHE'))
	define('MEMCACHE', false);