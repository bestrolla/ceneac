-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 01-06-2026 a las 02:42:13
-- Versión del servidor: 9.1.0
-- Versión de PHP: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `ceneac`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `calendario`
--

DROP TABLE IF EXISTS `calendario`;
CREATE TABLE IF NOT EXISTS `calendario` (
  `id_calendario` int NOT NULL AUTO_INCREMENT,
  `id_cursos` int NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `horario` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `dias` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `dias_festivo` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id_calendario`),
  KEY `fk_calendario_curso` (`id_cursos`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `calendario`
--

INSERT INTO `calendario` (`id_calendario`, `id_cursos`, `fecha_inicio`, `fecha_fin`, `horario`, `dias`, `dias_festivo`) VALUES
(5, 6, '2025-08-27', '2025-09-12', '09:00 - 13:00', 'Lunes,Miércoles,Viernes', NULL),
(6, 7, '2025-08-26', '2025-09-11', '09:00 - 13:00', 'Martes,Jueves', NULL),
(7, 20, '2025-08-26', '2025-09-11', '13:00 - 17:00', 'Martes,Jueves', NULL),
(8, 21, '2025-08-26', '2025-09-12', '13:00 - 17:00', 'Lunes,Miércoles,Viernes', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clases_programadas`
--

DROP TABLE IF EXISTS `clases_programadas`;
CREATE TABLE IF NOT EXISTS `clases_programadas` (
  `id_clase` int NOT NULL AUTO_INCREMENT,
  `id_calendario` int NOT NULL,
  `fecha_clase` date NOT NULL,
  `horario` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `estado` enum('programada','festivo_reprogramado','realizada') COLLATE utf8mb4_general_ci DEFAULT 'programada',
  PRIMARY KEY (`id_clase`),
  KEY `id_calendario` (`id_calendario`),
  KEY `idx_fecha_clase` (`fecha_clase`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clases_reprogramadas`
--

DROP TABLE IF EXISTS `clases_reprogramadas`;
CREATE TABLE IF NOT EXISTS `clases_reprogramadas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `original_course_id` int NOT NULL,
  `class_number` int NOT NULL,
  `original_date` datetime NOT NULL,
  `new_date` datetime NOT NULL,
  `reason` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_clase_reprogramada_unica` (`original_course_id`,`class_number`,`original_date`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clases_reprogramadas`
--

INSERT INTO `clases_reprogramadas` (`id`, `original_course_id`, `class_number`, `original_date`, `new_date`, `reason`, `created_at`) VALUES
(1, 26, 1, '2025-07-02 00:00:00', '2025-07-10 00:00:00', 'Reprogramación manual', '2025-07-14 23:30:56');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cursos`
--

DROP TABLE IF EXISTS `cursos`;
CREATE TABLE IF NOT EXISTS `cursos` (
  `id_cursos` int NOT NULL AUTO_INCREMENT,
  `id_profesor` int DEFAULT NULL,
  `nombre_curso` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `nivel_cursos` int DEFAULT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `N_salon` int DEFAULT NULL,
  `duracion` int NOT NULL,
  `N_clases` int NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id_cursos`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cursos`
--

INSERT INTO `cursos` (`id_cursos`, `id_profesor`, `nombre_curso`, `nivel_cursos`, `descripcion`, `N_salon`, `duracion`, `N_clases`, `status`) VALUES
(1, 1, 'PHP', 1, NULL, 1, 0, 0, 'activo'),
(2, 2, 'JAVA', 1, NULL, 1, 0, 0, 'inactivo'),
(3, NULL, 'phyton', 1, NULL, NULL, 6, 0, 'inactivo'),
(6, 3, 'phyton', 2, NULL, NULL, 6, 0, 'activo'),
(7, NULL, 'php', 2, NULL, NULL, 6, 0, 'activo'),
(14, NULL, 'JAVA', 1, NULL, NULL, 6, 0, 'inactivo'),
(20, NULL, 'JS', 2, NULL, NULL, 6, 0, 'activo'),
(21, NULL, 'JS', 1, NULL, NULL, 6, 0, 'activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `dias_festivos`
--

DROP TABLE IF EXISTS `dias_festivos`;
CREATE TABLE IF NOT EXISTS `dias_festivos` (
  `id_festivo` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `fecha` date NOT NULL,
  `tipo` enum('nacional','local') COLLATE utf8mb4_general_ci DEFAULT 'nacional',
  `recurrente` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id_festivo`),
  KEY `idx_fecha_festivo` (`fecha`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `dias_festivos`
--

INSERT INTO `dias_festivos` (`id_festivo`, `nombre`, `fecha`, `tipo`, `recurrente`) VALUES
(1, 'Año Nuevo', '2025-01-01', 'nacional', 0),
(2, 'Día de Reyes', '2025-01-06', 'nacional', 0),
(3, 'Carnaval Lunes', '2025-02-24', 'nacional', 0),
(4, 'Carnaval Martes', '2025-02-25', 'nacional', 0),
(5, 'Jueves Santo', '2025-04-17', 'nacional', 0),
(6, 'Viernes Santo', '2025-04-18', 'nacional', 0),
(7, 'Declaración de la Independencia', '2025-04-19', 'nacional', 0),
(8, 'Día del Trabajador', '2025-05-01', 'nacional', 0),
(9, 'Batalla de Carabobo', '2025-06-24', 'nacional', 0),
(10, 'Día de la Independencia', '2025-07-05', 'nacional', 0),
(11, 'Natalicio del Libertador', '2025-07-24', 'nacional', 0),
(12, 'Día de la Resistencia Indígena', '2025-10-12', 'nacional', 0),
(13, 'Víspera de Navidad', '2025-12-24', 'nacional', 0),
(14, 'Navidad', '2025-12-25', 'nacional', 0),
(15, 'Víspera de Año Nuevo', '2025-12-31', 'nacional', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estudiante`
--

DROP TABLE IF EXISTS `estudiante`;
CREATE TABLE IF NOT EXISTS `estudiante` (
  `id_estudiante` int NOT NULL AUTO_INCREMENT,
  `id_persona` int NOT NULL,
  `id_curso` int NOT NULL,
  `estatus` enum('activo','inactivo','espera','aprobado') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'activo',
  `fecha_inscripcion` date NOT NULL,
  PRIMARY KEY (`id_estudiante`),
  UNIQUE KEY `uk_persona_curso` (`id_persona`,`id_curso`)
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estudiante`
--

INSERT INTO `estudiante` (`id_estudiante`, `id_persona`, `id_curso`, `estatus`, `fecha_inscripcion`) VALUES
(46, 28, 20, 'espera', '2025-08-25'),
(47, 29, 20, 'aprobado', '2025-08-25'),
(48, 36, 1, 'inactivo', '2025-09-08'),
(49, 36, 20, 'activo', '2025-09-08');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `eventos`
--

DROP TABLE IF EXISTS `eventos`;
CREATE TABLE IF NOT EXISTS `eventos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `start` datetime NOT NULL,
  `end` datetime DEFAULT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `tipo_evento` varchar(50) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'general',
  `id_profesor_persona` int DEFAULT NULL,
  `id_salon` int DEFAULT NULL,
  `allDay` tinyint(1) NOT NULL DEFAULT '0',
  `color` varchar(7) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `clases_tomadas` int DEFAULT '0',
  `total_clases` int DEFAULT '0',
  `is_rescheduled` tinyint(1) NOT NULL DEFAULT '0',
  `original_course_id` int DEFAULT NULL,
  `class_number` int DEFAULT NULL,
  `class_dates_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  PRIMARY KEY (`id`),
  KEY `idx_start_end` (`start`,`end`),
  KEY `idx_tipo_evento` (`tipo_evento`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `eventos`
--

INSERT INTO `eventos` (`id`, `title`, `start`, `end`, `description`, `tipo_evento`, `id_profesor_persona`, `id_salon`, `allDay`, `color`, `clases_tomadas`, `total_clases`, `is_rescheduled`, `original_course_id`, `class_number`, `class_dates_json`) VALUES
(1, 'Clase de PHP - Sesión 1', '2025-07-14 09:00:00', '2025-07-14 12:00:00', 'Primera sesión del curso de Programación PHP.', 'clase', 38, NULL, 0, '#007bff', 1, 6, 0, NULL, NULL, NULL),
(2, 'Reunión de Coordinación', '2025-08-10 14:00:00', '2025-08-10 15:30:00', 'Reunión para planificar el próximo trimestre.', 'general', 30, 3, 0, NULL, 0, 0, 0, NULL, NULL, NULL),
(3, 'Feriado Nacional', '2025-08-15 00:00:00', '2025-08-15 23:59:59', 'Día festivo por la Asunción de la Virgen.', 'feriado', NULL, NULL, 1, '#DC3545', 0, 0, 0, NULL, NULL, NULL),
(4, 'Taller de JavaScript', '2025-08-21 10:00:00', '2025-08-21 13:00:00', 'Introducción a JavaScript y DOM.', 'clase', NULL, NULL, 0, NULL, 0, 0, 0, NULL, NULL, NULL),
(5, 'Clase de JAVA - Sesión 1', '2025-07-15 12:00:00', '2025-07-15 15:00:00', 'Primera sesión del curso de JAVA.', 'clase', 39, NULL, 0, NULL, 4, 8, 0, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `persona`
--

DROP TABLE IF EXISTS `persona`;
CREATE TABLE IF NOT EXISTS `persona` (
  `id_persona` int NOT NULL AUTO_INCREMENT,
  `cedula` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  `nombre` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `apellido` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `telefono` varchar(15) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `fecha_nacimiento` date NOT NULL,
  `correo` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id_persona`),
  UNIQUE KEY `cedula` (`cedula`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `persona`
--

INSERT INTO `persona` (`id_persona`, `cedula`, `nombre`, `apellido`, `telefono`, `fecha_nacimiento`, `correo`) VALUES
(1, '12345678', 'Maria', 'Perez', '04121234567', '1985-06-15', 'maria.perez@example.com'),
(2, '1234567', 'juan', 'perez', NULL, '2005-07-01', NULL),
(23, '30729911', 'angel nahin', 'manzano manzano', '04241827066', '0000-00-00', 'angelmanzano01092003@gmail.com'),
(25, '16392828', 'nini', 'manzano', '04241827066', '0000-00-00', 'isisshesatr@gmail.com'),
(26, '3443545543', 'gfdfgfggdffg', 'dfgfgdgdfg', '4354353455534', '0000-00-00', 'fgfhg@gmai.com'),
(27, '666666666', 'ghggfhghf', 'fghgghffghfhg', '5657567', '0000-00-00', 'dfggdfh@gmail.com'),
(28, 'dfgdfgfdg', 'dfgdfgfg', 'ddfgdfg', '657675676657', '2000-01-01', 'ninguno'),
(29, '-345445456', 'dfsfsdfsfd', 'sdfdfsfsd', '-54356456', '2000-01-01', 'ninguno'),
(30, '4354353454', '232434', '324234234', '43534543543', '0000-00-00', 'gggg@gmail.com'),
(31, '434324234', 'ghfhhgfg', 'gdfdfgfgdf', '23423423423', '0000-00-00', '4325435@gmail.com'),
(32, '29797215', 'Pablito', 'Raul', '04125678909', '0000-00-00', 'sdfgasvdfg@gmail.com'),
(33, '45366234', 'Pedro', 'faraon', '34562345634', '0000-00-00', 'uwsdhfgawsyud@gmail.com'),
(34, '23453245', 'Pedro', 'raul', '34561238790', '0000-00-00', 'sdfg@gmail.com'),
(35, '34567823', 'Fran', 'Raul', '04125678909', '0000-00-00', 'sdfgasvdfg@gmail.com'),
(36, '30078665', 'paul', 'rico', '0987654', '2000-01-01', 'ninguno');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `profesor`
--

DROP TABLE IF EXISTS `profesor`;
CREATE TABLE IF NOT EXISTS `profesor` (
  `id_profe` int NOT NULL AUTO_INCREMENT,
  `id_persona` int NOT NULL,
  `especialidad` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('activo','inactivo','ausente') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'activo',
  `historial_ausencias` longtext COLLATE utf8mb4_general_ci,
  `fecha_registro` datetime DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `fecha_inicio_ausencia` date DEFAULT NULL,
  `razon_ausencia` enum('reposo','jubilacion','otra','renuncia','despido') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `detalles_ausencia` text COLLATE utf8mb4_general_ci,
  `fecha_registro_ausencia` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_profe`),
  KEY `id_persona` (`id_persona`)
) ENGINE=InnoDB AUTO_INCREMENT=125 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `profesor`
--

INSERT INTO `profesor` (`id_profe`, `id_persona`, `especialidad`, `status`, `historial_ausencias`, `fecha_registro`, `fecha_actualizacion`, `fecha_inicio_ausencia`, `razon_ausencia`, `detalles_ausencia`, `fecha_registro_ausencia`) VALUES
(119, 23, 'JS', 'ausente', 'Reposo médico', '2025-08-23 21:04:53', '2025-08-25 18:08:19', '2025-08-26', 'reposo', 'gfddgfdfgg', '2025-08-25 22:08:19'),
(121, 25, 'php', 'activo', NULL, '2025-08-24 10:03:34', NULL, NULL, NULL, NULL, NULL),
(122, 27, 'ffff', 'activo', NULL, '2025-08-25 18:06:31', NULL, NULL, NULL, NULL, NULL),
(123, 30, 'fffff', 'activo', NULL, '2025-08-25 18:32:13', NULL, NULL, NULL, NULL, NULL),
(124, 31, 'fffff', 'activo', NULL, '2025-08-25 18:37:53', NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

DROP TABLE IF EXISTS `rol`;
CREATE TABLE IF NOT EXISTS `rol` (
  `id_rol` int NOT NULL AUTO_INCREMENT,
  `nombre_rol` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id_rol`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rol`
--

INSERT INTO `rol` (`id_rol`, `nombre_rol`, `descripcion`) VALUES
(1, 'estudiante', 'Usuario estudiante'),
(2, 'profesor', 'Usuario profesor'),
(3, 'administrador', 'Usuario administrador'),
(4, 'secretaria', 'Usuario secretaria');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `salon`
--

DROP TABLE IF EXISTS `salon`;
CREATE TABLE IF NOT EXISTS `salon` (
  `id_salon` int NOT NULL AUTO_INCREMENT,
  `nombre_salon` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('activo','inactivo','ocupado','') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `matricula` int NOT NULL,
  `motivo` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id_salon`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `salon`
--

INSERT INTO `salon` (`id_salon`, `nombre_salon`, `status`, `matricula`, `motivo`) VALUES
(1, 'manzano', 'activo', 30, NULL),
(2, 'angel', 'activo', 30, NULL),
(3, 'fdgdfgggfd', 'activo', 39, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `secretaria`
--

DROP TABLE IF EXISTS `secretaria`;
CREATE TABLE IF NOT EXISTS `secretaria` (
  `id_secre` int NOT NULL AUTO_INCREMENT,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date DEFAULT NULL,
  `status` enum('activo','inactivo','jubilado','reposo','ausente','retirado') COLLATE utf8mb4_general_ci DEFAULT 'activo',
  `razon` text COLLATE utf8mb4_general_ci,
  `fecha_estado` datetime DEFAULT NULL,
  `id_persona` int NOT NULL,
  `id_usuario` int NOT NULL,
  `estado_razon` text COLLATE utf8mb4_general_ci,
  `estado_fecha` date DEFAULT NULL,
  `fecha_retiro` datetime DEFAULT NULL,
  `razon_retiro` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id_secre`),
  UNIQUE KEY `id_persona` (`id_persona`),
  UNIQUE KEY `id_usuario` (`id_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `secretaria`
--

INSERT INTO `secretaria` (`id_secre`, `fecha_inicio`, `fecha_fin`, `status`, `razon`, `fecha_estado`, `id_persona`, `id_usuario`, `estado_razon`, `estado_fecha`, `fecha_retiro`, `razon_retiro`) VALUES
(1, '2025-07-25', NULL, 'ausente', NULL, NULL, 1, 1, '', '2025-08-25', NULL, NULL),
(2, '2025-07-25', NULL, 'activo', NULL, NULL, 3, 3, NULL, NULL, NULL, NULL),
(7, '2025-08-10', NULL, 'activo', NULL, NULL, 41, 17, NULL, NULL, NULL, NULL),
(8, '2025-08-10', NULL, 'activo', NULL, NULL, 42, 18, NULL, NULL, NULL, NULL),
(9, '2025-08-10', NULL, 'inactivo', NULL, NULL, 44, 19, NULL, NULL, NULL, NULL),
(10, '2025-08-10', NULL, 'reposo', NULL, NULL, 46, 20, 'reposo por 5 dias', '2025-08-17', NULL, NULL),
(11, '2025-08-11', NULL, 'ausente', NULL, NULL, 47, 21, 'AUSENTE POR MOTIVO DE VIAJE', '2025-08-17', NULL, NULL),
(12, '2025-08-11', NULL, 'ausente', NULL, NULL, 48, 22, 'cita medica', NULL, NULL, NULL),
(13, '2025-08-11', NULL, 'activo', NULL, '2025-08-23 18:17:16', 49, 23, 'hace mas de 1000', '2025-08-23', NULL, NULL),
(14, '2025-08-11', NULL, 'activo', NULL, NULL, 50, 24, NULL, NULL, NULL, NULL),
(15, '2025-08-11', NULL, 'activo', NULL, NULL, 51, 25, NULL, NULL, NULL, NULL),
(16, '2025-08-16', NULL, 'activo', NULL, NULL, 52, 26, NULL, NULL, NULL, NULL),
(17, '2025-08-18', NULL, 'activo', NULL, NULL, 53, 27, NULL, NULL, NULL, NULL),
(19, '2025-08-18', NULL, 'activo', NULL, NULL, 55, 29, NULL, NULL, NULL, NULL),
(20, '2025-08-18', NULL, 'activo', NULL, NULL, 56, 30, NULL, NULL, NULL, NULL),
(21, '2025-08-18', NULL, 'reposo', NULL, NULL, 57, 31, 'tiene gonosifilis', '2025-08-23', NULL, NULL),
(22, '2025-08-23', NULL, 'jubilado', NULL, NULL, 58, 32, '', '2025-08-23', NULL, NULL),
(23, '2025-08-26', NULL, 'retirado', NULL, NULL, 26, 10, NULL, NULL, '2025-08-25 18:40:16', 'cvbcvbbvc'),
(24, '2025-08-27', NULL, 'inactivo', NULL, NULL, 32, 11, 'feo', '2025-10-06', NULL, NULL),
(25, '2025-08-27', NULL, 'activo', NULL, NULL, 33, 12, NULL, NULL, NULL, NULL),
(26, '2025-08-27', NULL, 'activo', NULL, NULL, 34, 13, NULL, NULL, NULL, NULL),
(27, '2025-08-27', NULL, 'retirado', NULL, NULL, 35, 14, '', '2025-09-08', '2025-10-06 02:52:28', 'feo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

DROP TABLE IF EXISTS `usuario`;
CREATE TABLE IF NOT EXISTS `usuario` (
  `id_usuario` int NOT NULL AUTO_INCREMENT,
  `nombre_usuario` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `contrasena` varchar(300) COLLATE utf8mb4_general_ci NOT NULL,
  `id_persona` int NOT NULL,
  `id_rol` int NOT NULL DEFAULT '1',
  `status` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `nombre_usuario` (`nombre_usuario`),
  KEY `id_rol` (`id_rol`),
  KEY `fk_usuario_persona` (`id_persona`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id_usuario`, `nombre_usuario`, `contrasena`, `id_persona`, `id_rol`, `status`) VALUES
(1, 'secretaria02', '$argon2id$v=19$m=65536,t=4,p=1$YWkveUd2bHNQdTRDRGJhbw$cAbD3sxxHt8h5pLf6FexFHW01hZaDkq1IcJj5l2FEtw', 1, 4, ''),
(2, 'juanadmin', '$argon2id$v=19$m=65536,t=4,p=1$bTRXcnR4dHQvOUJoMjc5Ng$ZbX1esUfQzKHrxAYrYuJgFDSPgHdIuaznSiOhs9Wky4', 2, 3, ''),
(10, 'gfdfgfggdffg09$', 'dfgfgdgdfg4354*=', 26, 4, ''),
(11, 'Pedro09$', 'Raul0412*=', 32, 4, ''),
(12, 'Pedro09$_1', 'faraon3456*=', 33, 4, ''),
(13, 'Pedro09$_2', 'raul3456*=', 34, 4, ''),
(14, 'Fran09$', 'Raul0412*=', 35, 4, '');

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `calendario`
--
ALTER TABLE `calendario`
  ADD CONSTRAINT `fk_calendario_curso` FOREIGN KEY (`id_cursos`) REFERENCES `cursos` (`id_cursos`) ON DELETE CASCADE;

--
-- Filtros para la tabla `estudiante`
--
ALTER TABLE `estudiante`
  ADD CONSTRAINT `fk_estudiante_persona` FOREIGN KEY (`id_persona`) REFERENCES `persona` (`id_persona`) ON DELETE CASCADE;

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `fk_usuario_persona` FOREIGN KEY (`id_persona`) REFERENCES `persona` (`id_persona`) ON DELETE CASCADE,
  ADD CONSTRAINT `usuario_ibfk_2` FOREIGN KEY (`id_rol`) REFERENCES `rol` (`id_rol`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
