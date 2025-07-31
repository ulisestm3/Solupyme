-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 31-07-2025 a las 08:14:55
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `awferreteria`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permisos`
--

CREATE TABLE `permisos` (
  `idpermiso` int(11) NOT NULL,
  `idrol` int(11) NOT NULL,
  `pagina` varchar(255) NOT NULL,
  `activo` bit(1) NOT NULL DEFAULT b'1',
  `usuarioregistra` int(11) DEFAULT NULL,
  `fecharegistro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `permisos`
--

INSERT INTO `permisos` (`idpermiso`, `idrol`, `pagina`, `activo`, `usuarioregistra`, `fecharegistro`) VALUES
(44, 1, 'asignar_menu_usuario.php', b'1', 1, '2025-07-30 22:33:04'),
(45, 1, 'asignar_permisos.php', b'1', 1, '2025-07-30 22:33:04'),
(46, 1, 'permisos_por_rol.php', b'1', 1, '2025-07-30 22:33:04'),
(47, 1, 'permisos_usuarios_menus.php', b'1', 1, '2025-07-30 22:33:04'),
(48, 1, 'roles.php', b'1', 1, '2025-07-30 22:33:04'),
(49, 1, 'usuarios.php', b'1', 1, '2025-07-30 22:33:04'),
(54, 2, 'roles.php', b'1', 1, '2025-07-30 23:59:58'),
(55, 2, 'usuarios.php', b'1', 1, '2025-07-30 23:59:58');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permisos_menus`
--

CREATE TABLE `permisos_menus` (
  `idpermisomenu` int(11) NOT NULL,
  `idusuario` int(11) NOT NULL,
  `clave` varchar(255) NOT NULL,
  `activo` bit(1) NOT NULL,
  `usuarioregistra` int(11) DEFAULT NULL,
  `fecharegistro` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `permisos_menus`
--

INSERT INTO `permisos_menus` (`idpermisomenu`, `idusuario`, `clave`, `activo`, `usuarioregistra`, `fecharegistro`) VALUES
(35, 2, 'gestion_usuarios', b'1', 1, '2025-07-30 23:59:52'),
(36, 2, 'roles', b'1', 1, '2025-07-30 23:59:52'),
(37, 2, 'usuarios', b'1', 1, '2025-07-30 23:59:52'),
(38, 1, 'asignar_menu', b'1', 1, '2025-07-31 00:05:13'),
(39, 1, 'asignar_pagina', b'1', 1, '2025-07-31 00:05:13'),
(40, 1, 'gestion_usuarios', b'1', 1, '2025-07-31 00:05:13'),
(41, 1, 'permiso_menu', b'1', 1, '2025-07-31 00:05:13'),
(42, 1, 'permiso_pagina', b'1', 1, '2025-07-31 00:05:13'),
(43, 1, 'roles', b'1', 1, '2025-07-31 00:05:13'),
(44, 1, 'usuarios', b'1', 1, '2025-07-31 00:05:13');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `idrol` int(11) NOT NULL,
  `nombrerol` varchar(50) NOT NULL,
  `descripcion` varchar(100) DEFAULT NULL,
  `activo` bit(1) NOT NULL DEFAULT b'1',
  `usuarioregistra` int(11) NOT NULL,
  `fecharegistro` datetime NOT NULL DEFAULT current_timestamp(),
  `usuarioactualiza` int(11) DEFAULT NULL,
  `fechaactualizacion` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`idrol`, `nombrerol`, `descripcion`, `activo`, `usuarioregistra`, `fecharegistro`, `usuarioactualiza`, `fechaactualizacion`) VALUES
(1, 'Administrador', 'Rol con acceso total al sistema', b'1', 1, '2025-07-29 01:34:59', NULL, NULL),
(2, 'Usuario', 'Acceso parcial al sistema', b'1', 1, '2025-07-29 21:19:20', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles_paginas`
--

CREATE TABLE `roles_paginas` (
  `idrol` int(11) NOT NULL,
  `pagina` varchar(100) NOT NULL,
  `activo` bit(1) NOT NULL DEFAULT b'1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles_paginas`
--

INSERT INTO `roles_paginas` (`idrol`, `pagina`, `activo`) VALUES
(1, 'asignar_menu_usuario.php', b'1'),
(1, 'asignar_permisos.php', b'1'),
(1, 'permisos_por_rol.php', b'1'),
(1, 'permisos_usuarios_menus.php', b'1'),
(1, 'roles.php', b'1'),
(1, 'usuarios.php', b'1'),
(2, 'roles.php', b'1'),
(2, 'usuarios.php', b'1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `idusuario` int(11) NOT NULL,
  `nombrecompleto` varchar(100) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `idrol` int(11) NOT NULL,
  `activo` bit(1) NOT NULL DEFAULT b'1',
  `usuarioregistra` int(11) NOT NULL,
  `fecharegistro` datetime NOT NULL DEFAULT current_timestamp(),
  `usuarioactualiza` int(11) DEFAULT NULL,
  `fechaactualizacion` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`idusuario`, `nombrecompleto`, `usuario`, `contrasena`, `correo`, `telefono`, `idrol`, `activo`, `usuarioregistra`, `fecharegistro`, `usuarioactualiza`, `fechaactualizacion`) VALUES
(1, 'Usuario Administrador', 'admin', 'a665a45920422f9d417e4867efdc4fb8a04a1f3fff1fa07e998e86f7f7a27ae3', 'admin@awferreteria.com', '0000-0000', 1, b'1', 1, '2025-07-29 01:39:25', NULL, NULL),
(2, 'Ulises Zuniga', 'uzuniga', 'a665a45920422f9d417e4867efdc4fb8a04a1f3fff1fa07e998e86f7f7a27ae3', 'uzuniga@gmail.com', '84659917', 2, b'1', 1, '2025-07-29 22:11:28', NULL, '2025-07-29 22:30:47');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios_menus`
--

CREATE TABLE `usuarios_menus` (
  `idusuario` int(11) NOT NULL,
  `clave` varchar(255) NOT NULL,
  `activo` bit(1) NOT NULL DEFAULT b'1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios_menus`
--

INSERT INTO `usuarios_menus` (`idusuario`, `clave`, `activo`) VALUES
(1, 'asignar_menu', b'1'),
(1, 'asignar_pagina', b'1'),
(1, 'gestion_usuarios', b'1'),
(1, 'permiso_menu', b'1'),
(1, 'permiso_pagina', b'1'),
(1, 'roles', b'1'),
(1, 'usuarios', b'1'),
(2, 'gestion_usuarios', b'1'),
(2, 'roles', b'1'),
(2, 'usuarios', b'1');

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_permisos_menus`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_permisos_menus` (
`idusuario` int(11)
,`usuario` varchar(50)
,`clave` varchar(255)
,`activo` bit(1)
,`usuarioregistra` int(11)
,`fecharegistro` datetime
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_roles_permisos`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_roles_permisos` (
`idrol` int(11)
,`nombrerol` varchar(50)
,`pagina` varchar(255)
,`activo` bit(1)
,`usuarioregistra` int(11)
,`fecharegistro` datetime
);

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_permisos_menus`
--
DROP TABLE IF EXISTS `vista_permisos_menus`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_permisos_menus`  AS SELECT `pm`.`idusuario` AS `idusuario`, `u`.`usuario` AS `usuario`, `pm`.`clave` AS `clave`, `pm`.`activo` AS `activo`, `pm`.`usuarioregistra` AS `usuarioregistra`, `pm`.`fecharegistro` AS `fecharegistro` FROM (`permisos_menus` `pm` join `usuarios` `u` on(`pm`.`idusuario` = `u`.`idusuario`)) WHERE `pm`.`activo` = 1 ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_roles_permisos`
--
DROP TABLE IF EXISTS `vista_roles_permisos`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_roles_permisos`  AS SELECT `r`.`idrol` AS `idrol`, `r`.`nombrerol` AS `nombrerol`, `p`.`pagina` AS `pagina`, `p`.`activo` AS `activo`, `p`.`usuarioregistra` AS `usuarioregistra`, `p`.`fecharegistro` AS `fecharegistro` FROM (`roles` `r` left join `permisos` `p` on(`r`.`idrol` = `p`.`idrol`)) WHERE `r`.`activo` = 0x01 ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `permisos`
--
ALTER TABLE `permisos`
  ADD PRIMARY KEY (`idpermiso`),
  ADD KEY `idrol` (`idrol`);

--
-- Indices de la tabla `permisos_menus`
--
ALTER TABLE `permisos_menus`
  ADD PRIMARY KEY (`idpermisomenu`),
  ADD KEY `idusuario` (`idusuario`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`idrol`),
  ADD UNIQUE KEY `nombrerol` (`nombrerol`);

--
-- Indices de la tabla `roles_paginas`
--
ALTER TABLE `roles_paginas`
  ADD PRIMARY KEY (`idrol`,`pagina`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`idusuario`),
  ADD UNIQUE KEY `usuario` (`usuario`),
  ADD KEY `FK_usuarios_roles` (`idrol`);

--
-- Indices de la tabla `usuarios_menus`
--
ALTER TABLE `usuarios_menus`
  ADD PRIMARY KEY (`idusuario`,`clave`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `permisos`
--
ALTER TABLE `permisos`
  MODIFY `idpermiso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT de la tabla `permisos_menus`
--
ALTER TABLE `permisos_menus`
  MODIFY `idpermisomenu` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `idrol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `idusuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `permisos`
--
ALTER TABLE `permisos`
  ADD CONSTRAINT `permisos_ibfk_1` FOREIGN KEY (`idrol`) REFERENCES `roles` (`idrol`);

--
-- Filtros para la tabla `permisos_menus`
--
ALTER TABLE `permisos_menus`
  ADD CONSTRAINT `permisos_menus_ibfk_1` FOREIGN KEY (`idusuario`) REFERENCES `usuarios` (`idusuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `roles_paginas`
--
ALTER TABLE `roles_paginas`
  ADD CONSTRAINT `roles_paginas_ibfk_1` FOREIGN KEY (`idrol`) REFERENCES `roles` (`idrol`);

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `FK_usuarios_roles` FOREIGN KEY (`idrol`) REFERENCES `roles` (`idrol`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
