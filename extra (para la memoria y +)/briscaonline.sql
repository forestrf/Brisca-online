-- phpMyAdmin SQL Dump
-- version 4.1.6
-- http://www.phpmyadmin.net
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 09-05-2014 a las 20:50:42
-- Versión del servidor: 5.5.32
-- Versión de PHP: 5.4.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de datos: `briscaonline`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `login`
--

CREATE TABLE IF NOT EXISTS `login` (
  `ID_USER` int(11) NOT NULL,
  `COOKIE` varchar(32) COLLATE utf8_bin NOT NULL,
  `FECHA_TOPE` datetime NOT NULL,
  PRIMARY KEY (`ID_USER`),
  KEY `ID_USER` (`ID_USER`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `salas`
--

CREATE TABLE IF NOT EXISTS `salas` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `p_total` tinyint(4) NOT NULL DEFAULT '1',
  `jugadores_max` tinyint(4) NOT NULL,
  `iniciada` tinyint(1) NOT NULL DEFAULT '0',
  `1` int(11) NOT NULL,
  `2` int(11) NOT NULL DEFAULT '-1',
  `3` int(11) NOT NULL DEFAULT '-1',
  `4` int(11) NOT NULL DEFAULT '-1',
  `parejas` tinyint(1) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `id_creador_2` (`1`),
  KEY `id_creador` (`1`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=28 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE IF NOT EXISTS `usuarios` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `NOMBRE` varchar(40) COLLATE utf8_bin NOT NULL,
  `APELLIDO` varchar(40) COLLATE utf8_bin NOT NULL,
  `NICK` varchar(15) COLLATE utf8_bin NOT NULL,
  `PASSWORD` varchar(32) COLLATE utf8_bin NOT NULL,
  `EMAIL` text COLLATE utf8_bin NOT NULL,
  `FECHA_REGISTRO` datetime NOT NULL,
  `FECHA_ULT_LOGIN` datetime NOT NULL,
  `IP_ULT_LOGIN` text COLLATE utf8_bin NOT NULL,
  `user_validado` text COLLATE utf8_bin NOT NULL,
  `sala` int(11) NOT NULL DEFAULT '-1',
  `victorias_online` int(11) NOT NULL DEFAULT '0',
  `derrotas_online` int(11) NOT NULL DEFAULT '0',
  `puntuacion_maxima_online` int(11) NOT NULL DEFAULT '0',
  `victorias_cpu` int(11) NOT NULL DEFAULT '0',
  `derrotas_cpu` int(11) NOT NULL DEFAULT '0',
  `puntuacion_maxima_cpu` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `NICK` (`NICK`),
  UNIQUE KEY `ID` (`ID`),
  KEY `ID_2` (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=14 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
