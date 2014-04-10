<?php

// Dominio donde está la aplicación.
if(!defined('DOMINIO'))
	define('DOMINIO', 'localhost');

// Carpeta donde está la aplicación
if(!defined('PATH'))
	define('PATH', '/');

// Usar memcache
if(!defined('MEMCACHE'))
	define('MEMCACHE', false);

// Usar memcache
if(!defined('CAPTCHA_PUBLICKEY'))
	define('CAPTCHA_PUBLICKEY', '6Lf0du8SAAAAAInVTXZh6NTya42sW8_KsOSbTUEW');

// Usar memcache
if(!defined('CAPTCHA_PRIVATEKEY'))
	define('CAPTCHA_PRIVATEKEY', '6Lf0du8SAAAAADPYrWhClrVpZleDC5OlI6zvSxW_');