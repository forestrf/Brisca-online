-- phpMyAdmin SQL Dump
-- version 4.1.6
-- http://www.phpmyadmin.net
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 10-04-2014 a las 18:45:40
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
  `COOKIE` varchar(32) CHARACTER SET latin1 NOT NULL,
  `FECHA_TOPE` datetime NOT NULL,
  PRIMARY KEY (`ID_USER`),
  KEY `ID_USER` (`ID_USER`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE IF NOT EXISTS `usuarios` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `NOMBRE` varchar(40) CHARACTER SET latin1 NOT NULL,
  `APELLIDO` varchar(40) CHARACTER SET latin1 NOT NULL,
  `NICK` varchar(15) CHARACTER SET latin1 NOT NULL,
  `PASSWORD` varchar(32) CHARACTER SET latin1 NOT NULL,
  `EMAIL` text CHARACTER SET latin1 NOT NULL,
  `FECHA_REGISTRO` datetime NOT NULL,
  `FECHA_ULT_LOGIN` datetime NOT NULL,
  `IP_ULT_LOGIN` text CHARACTER SET latin1 NOT NULL,
  `user_validado` text CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `NICK` (`NICK`),
  UNIQUE KEY `ID` (`ID`),
  KEY `ID_2` (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=9 ;


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
