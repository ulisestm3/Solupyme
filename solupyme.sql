-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 17-10-2025 a las 14:22:02
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


--
-- Base de datos: `solupyme`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `idcategoria` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `activo` bit(1) DEFAULT b'1',
  `usuarioregistra` int(11) DEFAULT NULL,
  `fecharegistro` datetime DEFAULT current_timestamp(),
  `usuarioactualiza` int(11) DEFAULT NULL,
  `fechaactualizacion` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`idcategoria`, `nombre`, `activo`, `usuarioregistra`, `fecharegistro`, `usuarioactualiza`, `fechaactualizacion`) VALUES
(10, 'Construcción', b'1', 1, '2025-08-01 07:26:32', NULL, NULL),
(11, 'Electricos', b'1', 1, '2025-08-01 18:43:02', NULL, NULL),
(12, 'Muebles', b'1', 1, '2025-08-01 21:22:26', NULL, NULL),
(13, 'Hierro', b'1', 3, '2025-09-28 22:49:10', NULL, NULL),
(14, 'Laminas de zinc de 12\"', b'1', 3, '2025-09-29 21:26:00', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `idcliente` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `identificacion` varchar(20) NOT NULL,
  `tipo_identificacion` enum('CEDULA','RUC','PASAPORTE','OTRO') NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `ciudad` varchar(50) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `usuarioregistra` int(11) NOT NULL,
  `fecharegistro` datetime DEFAULT current_timestamp(),
  `usuarioactualiza` int(11) DEFAULT NULL,
  `fechaactualizacion` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`idcliente`, `nombre`, `apellido`, `identificacion`, `tipo_identificacion`, `telefono`, `email`, `direccion`, `ciudad`, `activo`, `usuarioregistra`, `fecharegistro`, `usuarioactualiza`, `fechaactualizacion`) VALUES
(1, 'Ulises', 'Zuniga', '0070204850001R', 'CEDULA', '84659917', 'ulisestm3@hotmail.com', 'Masaya', 'Masaya', 1, 1, '2025-08-15 03:03:53', 1, '2025-08-15 03:36:08'),
(2, 'Jose', 'Flores', '4813567820003E', 'CEDULA', '87938885', 'josef3708@gmail.com', 'Del arbolito 1c. al lago', 'Managua', 1, 3, '2025-09-28 21:17:14', NULL, NULL),
(3, 'Juan', 'Perez', '0012205120004G', 'CEDULA', '22568732', 'juan.perez@gmail.com', 'Bo. Haileah del sombrero 20 vrs. abajo', 'Managua', 1, 3, '2025-09-28 21:24:27', 3, '2025-09-28 22:53:05'),
(4, 'Mario', 'Lopez', '0012509890023Q', 'CEDULA', '88765432', 'mlopez@gmail.com', 'De donde fue el cine cabrera 1c. al lago', 'Managua', 1, 3, '2025-09-28 23:00:59', 3, '2025-09-29 21:31:04');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compras`
--

CREATE TABLE `compras` (
  `idcompra` int(11) NOT NULL,
  `idproveedor` int(11) NOT NULL,
  `numero_factura` varchar(100) DEFAULT NULL,
  `fecha_factura` date NOT NULL,
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `iva` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `idusuario` int(11) NOT NULL,
  `activo` bit(1) DEFAULT b'1',
  `fecharegistro` datetime DEFAULT current_timestamp(),
  `usuarioactualiza` int(11) DEFAULT NULL,
  `fechaactualizacion` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `compras`
--

INSERT INTO `compras` (`idcompra`, `idproveedor`, `numero_factura`, `fecha_factura`, `subtotal`, `iva`, `total`, `idusuario`, `activo`, `fecharegistro`, `usuarioactualiza`, `fechaactualizacion`) VALUES
(1, 1, 'S0001', '2025-09-25', 3200.00, 480.00, 3680.00, 1, b'1', '2025-09-25 22:28:50', NULL, NULL),
(2, 1, '8765', '2025-09-26', 225.00, 33.75, 258.75, 3, b'1', '2025-09-28 21:16:13', NULL, NULL),
(3, 3, '5678', '2025-09-26', 1750.00, 262.50, 2012.50, 3, b'1', '2025-09-28 21:25:46', NULL, NULL),
(4, 4, '0987', '2025-09-26', 850.00, 127.50, 977.50, 3, b'1', '2025-09-28 22:36:50', NULL, NULL),
(5, 4, '45', '2025-09-25', 300.00, 45.00, 345.00, 3, b'1', '2025-09-28 22:50:13', NULL, NULL),
(6, 4, '578', '2025-09-27', 525.00, 78.75, 603.75, 3, b'1', '2025-09-28 22:54:57', NULL, NULL),
(7, 1, '98', '2025-09-27', 490.00, 73.50, 563.50, 3, b'1', '2025-09-28 22:59:46', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compras_detalle`
--

CREATE TABLE `compras_detalle` (
  `idcompra_detalle` int(11) NOT NULL,
  `idcompra` int(11) NOT NULL,
  `idproducto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(12,4) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `compras_detalle`
--

INSERT INTO `compras_detalle` (`idcompra_detalle`, `idcompra`, `idproducto`, `cantidad`, `precio_unitario`, `subtotal`) VALUES
(1, 1, 1, 10, 320.0000, 3200.00),
(2, 2, 2, 5, 45.0000, 225.00),
(3, 3, 1, 5, 350.0000, 1750.00),
(4, 4, 2, 10, 85.0000, 850.00),
(5, 5, 3, 5, 60.0000, 300.00),
(6, 6, 4, 21, 25.0000, 525.00),
(7, 7, 2, 5, 98.0000, 490.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_factura`
--

CREATE TABLE `detalle_factura` (
  `iddetalle` int(11) NOT NULL,
  `idfactura` int(11) NOT NULL,
  `idproducto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalle_factura`
--

INSERT INTO `detalle_factura` (`iddetalle`, `idfactura`, `idproducto`, `cantidad`, `precio`, `subtotal`) VALUES
(12, 2, 1, 5, 280.00, 1400.00),
(13, 3, 2, 5, 180.00, 900.00),
(16, 6, 1, 1, 280.00, 280.00),
(17, 7, 2, 5, 180.00, 900.00),
(18, 8, 2, 2, 180.00, 360.00),
(20, 10, 4, 5, 50.00, 250.00),
(21, 11, 2, 4, 180.00, 720.00),
(22, 12, 1, 2, 280.00, 560.00),
(23, 13, 2, 1, 180.00, 180.00),
(24, 14, 4, 5, 50.00, 250.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empresa`
--

CREATE TABLE `empresa` (
  `idempresa` int(11) NOT NULL,
  `nombrecormercial` varchar(100) NOT NULL,
  `razonsocial` varchar(100) NOT NULL,
  `ruc` varchar(50) NOT NULL,
  `direccion` varchar(255) NOT NULL,
  `contacto` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `empresa`
--

INSERT INTO `empresa` (`idempresa`, `nombrecormercial`, `razonsocial`, `ruc`, `direccion`, `contacto`) VALUES
(1, 'SOLUPYME', 'SOLUPYME,S.A.', 'J000000000000000', 'Managua', 88888888);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facturas`
--

CREATE TABLE `facturas` (
  `idfactura` int(11) NOT NULL,
  `idcliente` int(11) NOT NULL,
  `idusuario` int(11) NOT NULL,
  `fecha` datetime DEFAULT current_timestamp(),
  `total` decimal(10,2) NOT NULL,
  `iva` decimal(10,2) DEFAULT 0.00,
  `activo` bit(1) DEFAULT b'1',
  `usuarioregistra` int(11) DEFAULT NULL,
  `fecharegistro` datetime DEFAULT current_timestamp(),
  `usuarioactualiza` int(11) DEFAULT NULL,
  `fechaactualizacion` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `facturas`
--

INSERT INTO `facturas` (`idfactura`, `idcliente`, `idusuario`, `fecha`, `total`, `iva`, `activo`, `usuarioregistra`, `fecharegistro`, `usuarioactualiza`, `fechaactualizacion`) VALUES
(2, 1, 1, '2025-09-28 20:12:56', 1610.00, 210.00, b'1', 1, '2025-09-28 20:12:56', NULL, NULL),
(3, 2, 3, '2025-09-28 21:17:46', 1035.00, 135.00, b'1', 3, '2025-09-28 21:17:46', NULL, NULL),
(6, 3, 3, '2025-09-28 21:26:02', 322.00, 42.00, b'1', 3, '2025-09-28 21:26:02', NULL, NULL),
(7, 3, 3, '2025-09-28 22:37:18', 1035.00, 135.00, b'1', 3, '2025-09-28 22:37:18', NULL, NULL),
(8, 3, 3, '2025-09-28 22:50:30', 414.00, 54.00, b'1', 3, '2025-09-28 22:50:30', NULL, NULL),
(10, 3, 3, '2025-09-28 22:55:20', 287.50, 37.50, b'1', 3, '2025-09-28 22:55:20', NULL, NULL),
(11, 2, 3, '2025-09-28 23:00:02', 828.00, 108.00, b'1', 3, '2025-09-28 23:00:02', NULL, NULL),
(12, 4, 3, '2025-09-29 21:27:50', 644.00, 84.00, b'1', 3, '2025-09-29 21:27:50', NULL, NULL),
(13, 2, 3, '2025-09-29 21:31:21', 207.00, 27.00, b'1', 3, '2025-09-29 21:31:21', NULL, NULL),
(14, 1, 1, '2025-10-16 20:14:52', 287.50, 37.50, b'1', 1, '2025-10-16 20:14:52', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimientos`
--

CREATE TABLE `movimientos` (
  `idmovimiento` int(11) NOT NULL,
  `idproducto` int(11) NOT NULL,
  `tipo` enum('entrada','salida') NOT NULL,
  `cantidad` int(11) NOT NULL,
  `comentario` varchar(255) DEFAULT NULL,
  `fecha` datetime DEFAULT current_timestamp(),
  `idusuario` int(11) DEFAULT NULL,
  `Activo` bit(1) NOT NULL DEFAULT b'1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `movimientos`
--

INSERT INTO `movimientos` (`idmovimiento`, `idproducto`, `tipo`, `cantidad`, `comentario`, `fecha`, `idusuario`, `Activo`) VALUES
(1, 1, 'entrada', 10, 'Compra de producto', '2025-09-25 22:28:50', 1, b'1'),
(2, 1, 'salida', 5, 'Factura #2', '2025-09-28 20:12:56', 1, b'1'),
(3, 2, 'entrada', 5, 'Compra de producto', '2025-09-28 21:16:13', 3, b'1'),
(4, 2, 'salida', 5, 'Factura #3', '2025-09-28 21:17:46', 3, b'1'),
(5, 1, 'salida', 5, 'Venta de cemento canal 25kg, cliente externo', '2025-09-28 21:22:23', 3, b'1'),
(6, 1, 'entrada', 5, 'Compra de producto', '2025-09-28 21:25:46', 3, b'1'),
(7, 1, 'salida', 1, 'Factura #6', '2025-09-28 21:26:02', 3, b'1'),
(8, 2, 'entrada', 10, 'Compra de producto', '2025-09-28 22:36:50', 3, b'1'),
(9, 2, 'salida', 5, 'Factura #7', '2025-09-28 22:37:18', 3, b'1'),
(10, 3, 'entrada', 5, 'Compra de producto', '2025-09-28 22:50:13', 3, b'1'),
(11, 2, 'salida', 2, 'Factura #8', '2025-09-28 22:50:30', 3, b'1'),
(12, 4, 'entrada', 21, 'Compra de producto', '2025-09-28 22:54:57', 3, b'1'),
(13, 4, 'salida', 5, 'Factura #10', '2025-09-28 22:55:20', 3, b'1'),
(14, 2, 'entrada', 5, 'Compra de producto', '2025-09-28 22:59:46', 3, b'1'),
(15, 2, 'salida', 4, 'Factura #11', '2025-09-28 23:00:02', 3, b'1'),
(16, 1, 'salida', 2, 'Factura #12', '2025-09-29 21:27:50', 3, b'1'),
(17, 2, 'salida', 1, 'Factura #13', '2025-09-29 21:31:21', 3, b'1'),
(18, 4, 'salida', 5, 'Factura #14', '2025-10-16 20:14:52', 1, b'1');

--
-- Disparadores `movimientos`
--
DELIMITER $$
CREATE TRIGGER `trg_actualizar_stock` AFTER INSERT ON `movimientos` FOR EACH ROW BEGIN
    IF NEW.tipo = 'entrada' THEN
        UPDATE productos SET stock = stock + NEW.cantidad WHERE idproducto = NEW.idproducto;
    ELSEIF NEW.tipo = 'salida' THEN
        UPDATE productos SET stock = stock - NEW.cantidad WHERE idproducto = NEW.idproducto;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_validar_stock_salida` BEFORE INSERT ON `movimientos` FOR EACH ROW BEGIN
    DECLARE stock_actual INT;
    
    IF NEW.tipo = 'salida' THEN
        SELECT stock INTO stock_actual FROM productos WHERE idproducto = NEW.idproducto;

        IF stock_actual < NEW.cantidad THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Stock insuficiente para la salida.';
        END IF;
    END IF;
END
$$
DELIMITER ;

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
(175, 3, 'categorias.php', b'1', 1, '2025-09-21 14:47:19'),
(176, 3, 'clientes.php', b'1', 1, '2025-09-21 14:47:19'),
(177, 3, 'compras.php', b'1', 1, '2025-09-21 14:47:19'),
(178, 3, 'facturas.php', b'1', 1, '2025-09-21 14:47:19'),
(179, 3, 'movimientos.php', b'1', 1, '2025-09-21 14:47:19'),
(180, 3, 'productos.php', b'1', 1, '2025-09-21 14:47:19'),
(181, 3, 'proveedores.php', b'1', 1, '2025-09-21 14:47:19'),
(182, 3, 'stock_bajo.php', b'1', 1, '2025-09-21 14:47:19'),
(216, 1, 'asignar_menu_usuario.php', b'1', 1, '2025-10-17 03:34:53'),
(217, 1, 'asignar_permisos.php', b'1', 1, '2025-10-17 03:34:53'),
(218, 1, 'categorias.php', b'1', 1, '2025-10-17 03:34:53'),
(219, 1, 'clientes.php', b'1', 1, '2025-10-17 03:34:53'),
(220, 1, 'compras.php', b'1', 1, '2025-10-17 03:34:53'),
(221, 1, 'costo_producto.php', b'1', 1, '2025-10-17 03:34:53'),
(222, 1, 'facturas.php', b'1', 1, '2025-10-17 03:34:53'),
(223, 1, 'movimientos.php', b'1', 1, '2025-10-17 03:34:53'),
(224, 1, 'parametros.php', b'1', 1, '2025-10-17 03:34:53'),
(225, 1, 'permisos_por_rol.php', b'1', 1, '2025-10-17 03:34:53'),
(226, 1, 'permisos_usuarios_menus.php', b'1', 1, '2025-10-17 03:34:53'),
(227, 1, 'precio_venta.php', b'1', 1, '2025-10-17 03:34:53'),
(228, 1, 'productos.php', b'1', 1, '2025-10-17 03:34:53'),
(229, 1, 'proveedores.php', b'1', 1, '2025-10-17 03:34:53'),
(230, 1, 'roles.php', b'1', 1, '2025-10-17 03:34:53'),
(231, 1, 'stock_bajo.php', b'1', 1, '2025-10-17 03:34:53'),
(232, 1, 'usuarios.php', b'1', 1, '2025-10-17 03:34:53'),
(233, 1, 'ventas_proyectadas.php', b'1', 1, '2025-10-17 03:34:53');

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
(246, 2, '2.0.gestion_productos', b'1', 1, '2025-08-10 02:25:22'),
(247, 2, '2.1.productos', b'1', 1, '2025-08-10 02:25:22'),
(248, 2, '2.2.categorias', b'1', 1, '2025-08-10 02:25:22'),
(249, 2, '2.3.movimientos', b'1', 1, '2025-08-10 02:25:22'),
(250, 2, '2.4.stock_bajo', b'1', 1, '2025-08-10 02:25:22'),
(251, 2, '2.5.proveedores', b'1', 1, '2025-08-10 02:25:22'),
(314, 3, '2.0.gestion_productos', b'1', 1, '2025-09-21 14:47:53'),
(315, 3, '2.1.productos', b'1', 1, '2025-09-21 14:47:53'),
(316, 3, '2.2.categorias', b'1', 1, '2025-09-21 14:47:53'),
(317, 3, '2.3.movimientos', b'1', 1, '2025-09-21 14:47:53'),
(318, 3, '2.4.stock_bajo', b'1', 1, '2025-09-21 14:47:53'),
(319, 3, '2.5.proveedores', b'1', 1, '2025-09-21 14:47:53'),
(320, 3, '2.6.compras', b'1', 1, '2025-09-21 14:47:53'),
(321, 3, '2.7.clientes', b'1', 1, '2025-09-21 14:47:53'),
(322, 3, '2.8.facturas', b'1', 1, '2025-09-21 14:47:53'),
(360, 4, '1.0.gestion_usuarios', b'1', 1, '2025-10-16 20:58:38'),
(361, 4, '1.1.usuarios', b'1', 1, '2025-10-16 20:58:38'),
(362, 4, '1.2.roles', b'1', 1, '2025-10-16 20:58:38'),
(363, 4, '1.3.asignar_pagina', b'1', 1, '2025-10-16 20:58:38'),
(364, 4, '1.4.permiso_pagina', b'1', 1, '2025-10-16 20:58:38'),
(365, 4, '1.5.asignar_menu', b'1', 1, '2025-10-16 20:58:38'),
(366, 4, '1.6.permiso_menu', b'1', 1, '2025-10-16 20:58:38'),
(367, 4, '1.7.parametros', b'1', 1, '2025-10-16 20:58:38'),
(368, 4, '2.0.gestion_productos', b'1', 1, '2025-10-16 20:58:38'),
(369, 4, '2.1.productos', b'1', 1, '2025-10-16 20:58:38'),
(370, 4, '2.10.costo_producto', b'1', 1, '2025-10-16 20:58:38'),
(371, 4, '2.2.categorias', b'1', 1, '2025-10-16 20:58:38'),
(372, 4, '2.3.movimientos', b'1', 1, '2025-10-16 20:58:38'),
(373, 4, '2.4.stock_bajo', b'1', 1, '2025-10-16 20:58:38'),
(374, 4, '2.5.proveedores', b'1', 1, '2025-10-16 20:58:38'),
(375, 4, '2.6.compras', b'1', 1, '2025-10-16 20:58:38'),
(376, 4, '2.7.clientes', b'1', 1, '2025-10-16 20:58:38'),
(377, 4, '2.8.facturas', b'1', 1, '2025-10-16 20:58:38'),
(378, 4, '2.9.precio_venta', b'1', 1, '2025-10-16 20:58:38'),
(379, 1, '1.0.gestion_usuarios', b'1', 1, '2025-10-17 03:35:09'),
(380, 1, '1.1.usuarios', b'1', 1, '2025-10-17 03:35:09'),
(381, 1, '1.2.roles', b'1', 1, '2025-10-17 03:35:09'),
(382, 1, '1.3.asignar_pagina', b'1', 1, '2025-10-17 03:35:09'),
(383, 1, '1.4.permiso_pagina', b'1', 1, '2025-10-17 03:35:09'),
(384, 1, '1.5.asignar_menu', b'1', 1, '2025-10-17 03:35:09'),
(385, 1, '1.6.permiso_menu', b'1', 1, '2025-10-17 03:35:09'),
(386, 1, '1.7.parametros', b'1', 1, '2025-10-17 03:35:09'),
(387, 1, '2.0.gestion_productos', b'1', 1, '2025-10-17 03:35:09'),
(388, 1, '2.1.productos', b'1', 1, '2025-10-17 03:35:09'),
(389, 1, '2.10.costo_producto', b'1', 1, '2025-10-17 03:35:09'),
(390, 1, '2.11.ventas_proyectadas', b'1', 1, '2025-10-17 03:35:09'),
(391, 1, '2.2.categorias', b'1', 1, '2025-10-17 03:35:09'),
(392, 1, '2.3.movimientos', b'1', 1, '2025-10-17 03:35:09'),
(393, 1, '2.4.stock_bajo', b'1', 1, '2025-10-17 03:35:09'),
(394, 1, '2.5.proveedores', b'1', 1, '2025-10-17 03:35:09'),
(395, 1, '2.6.compras', b'1', 1, '2025-10-17 03:35:09'),
(396, 1, '2.7.clientes', b'1', 1, '2025-10-17 03:35:09'),
(397, 1, '2.8.facturas', b'1', 1, '2025-10-17 03:35:09'),
(398, 1, '2.9.precio_venta', b'1', 1, '2025-10-17 03:35:09');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `idproducto` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `idcategoria` int(11) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  `stock_minimo` int(11) DEFAULT 5,
  `precio` decimal(10,2) NOT NULL,
  `activo` bit(1) DEFAULT b'1',
  `usuarioregistra` int(11) DEFAULT NULL,
  `fecharegistro` datetime DEFAULT current_timestamp(),
  `usuarioactualiza` int(11) DEFAULT NULL,
  `fechaactualizacion` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`idproducto`, `nombre`, `idcategoria`, `descripcion`, `stock`, `stock_minimo`, `precio`, `activo`, `usuarioregistra`, `fecharegistro`, `usuarioactualiza`, `fechaactualizacion`) VALUES
(1, 'Cemento Canal 25kg', 10, 'Cemento Canal 25 kg', 2, 5, 280.00, b'1', 1, '2025-08-01 17:21:30', 3, '2025-09-29 21:25:39'),
(2, 'Bujía 5W', 11, 'Bujía 5W', 3, 10, 180.00, b'1', 1, '2025-08-01 18:56:36', NULL, NULL),
(3, 'Lampara 30w', 11, 'Lampara 30w', 5, 5, 230.00, b'1', 2, '2025-08-01 22:44:46', 1, '2025-08-15 04:44:24'),
(4, 'Varilla de hierro corrugado 1/2', 13, 'Varilla de hierro corrugado 1/2 para construccion', 11, 5, 50.00, b'1', 3, '2025-09-28 22:52:12', NULL, NULL),
(5, 'lamina de zinc 12\"', 14, 'Para techo', 0, 5, 280.00, b'1', 3, '2025-09-29 21:30:12', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `idproveedor` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `contacto` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `activo` bit(1) DEFAULT b'1',
  `usuarioregistra` int(11) NOT NULL,
  `fecharegistro` datetime DEFAULT current_timestamp(),
  `usuarioactualiza` int(11) DEFAULT NULL,
  `fechaactualizacion` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `proveedores`
--

INSERT INTO `proveedores` (`idproveedor`, `nombre`, `contacto`, `telefono`, `correo`, `direccion`, `activo`, `usuarioregistra`, `fecharegistro`, `usuarioactualiza`, `fechaactualizacion`) VALUES
(1, 'Sinsa,S.A.', 'Juan Perez', '88884444', 'sinsa.info@gmail.com', 'KM.8 1/2 carrtera Managua - Masaya', b'1', 1, '2025-08-10 02:12:30', NULL, NULL),
(2, 'Ferreteria El Gigante', '', '22567483', 'ventas@ferreteriaelgigante.com.ni', 'Managua, de Walmart Altagracia, 1c. al Oeste', b'1', 3, '2025-09-28 21:18:59', 3, '2025-09-29 21:30:43'),
(3, 'Ferreteria El Buen Fierro', 'Manuel Perez', '22678743', 'ventas@ferreteriaelgigante.com.ni', 'Cementerio General 25 vrs. arriba, mano izquierda', b'1', 3, '2025-09-28 21:23:32', 3, '2025-09-28 22:52:34'),
(4, 'Ferreteria El Baraton', 'Juan Rugama', '22764356', 'ventas@ferreteriaelbaraton.com.ni', 'Monseñor Lezcano de la estatua 1c. al norte 1/2 c. al sur', b'1', 3, '2025-09-28 22:36:22', 3, '2025-09-29 21:26:28');

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
(2, 'Usuario', 'Acceso parcial al sistema', b'1', 1, '2025-07-29 21:19:20', NULL, NULL),
(3, 'Contabilidad', 'Gestion Contable', b'1', 1, '2025-09-21 14:46:04', NULL, NULL);

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
(1, 'categorias.php', b'1'),
(1, 'clientes.php', b'1'),
(1, 'compras.php', b'1'),
(1, 'costo_producto.php', b'1'),
(1, 'facturas.php', b'1'),
(1, 'movimientos.php', b'1'),
(1, 'parametros.php', b'1'),
(1, 'permisos_por_rol.php', b'1'),
(1, 'permisos_usuarios_menus.php', b'1'),
(1, 'precio_venta.php', b'1'),
(1, 'productos.php', b'1'),
(1, 'proveedores.php', b'1'),
(1, 'roles.php', b'1'),
(1, 'stock_bajo.php', b'1'),
(1, 'usuarios.php', b'1'),
(1, 'ventas_proyectadas.php', b'1'),
(2, 'categorias.php', b'1'),
(2, 'movimientos.php', b'1'),
(2, 'productos.php', b'1'),
(2, 'proveedores.php', b'1'),
(2, 'stock_bajo.php', b'1'),
(3, 'categorias.php', b'1'),
(3, 'clientes.php', b'1'),
(3, 'compras.php', b'1'),
(3, 'facturas.php', b'1'),
(3, 'movimientos.php', b'1'),
(3, 'productos.php', b'1'),
(3, 'proveedores.php', b'1'),
(3, 'stock_bajo.php', b'1');

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
(2, 'Ulises Zuniga', 'uzuniga', 'a665a45920422f9d417e4867efdc4fb8a04a1f3fff1fa07e998e86f7f7a27ae3', 'uzuniga@gmail.com', '84659917', 2, b'1', 1, '2025-07-29 22:11:28', NULL, '2025-07-31 21:05:46'),
(3, 'Usuario Contable', 'Contabilidad', 'a665a45920422f9d417e4867efdc4fb8a04a1f3fff1fa07e998e86f7f7a27ae3', '', '', 3, b'1', 1, '2025-09-21 14:46:39', NULL, NULL),
(4, 'Evaluador Hackaton', 'evaluador', '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92', '', '', 1, b'1', 1, '2025-10-16 20:56:02', NULL, NULL);

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
(1, '1.0.gestion_usuarios', b'1'),
(1, '1.1.usuarios', b'1'),
(1, '1.2.roles', b'1'),
(1, '1.3.asignar_pagina', b'1'),
(1, '1.4.permiso_pagina', b'1'),
(1, '1.5.asignar_menu', b'1'),
(1, '1.6.permiso_menu', b'1'),
(1, '1.7.parametros', b'1'),
(1, '2.0.gestion_productos', b'1'),
(1, '2.1.productos', b'1'),
(1, '2.10.costo_producto', b'1'),
(1, '2.11.ventas_proyectadas', b'1'),
(1, '2.2.categorias', b'1'),
(1, '2.3.movimientos', b'1'),
(1, '2.4.stock_bajo', b'1'),
(1, '2.5.proveedores', b'1'),
(1, '2.6.compras', b'1'),
(1, '2.7.clientes', b'1'),
(1, '2.8.facturas', b'1'),
(1, '2.9.precio_venta', b'1'),
(2, '2.0.gestion_productos', b'1'),
(2, '2.1.productos', b'1'),
(2, '2.2.categorias', b'1'),
(2, '2.3.movimientos', b'1'),
(2, '2.4.stock_bajo', b'1'),
(2, '2.5.proveedores', b'1'),
(3, '2.0.gestion_productos', b'1'),
(3, '2.1.productos', b'1'),
(3, '2.2.categorias', b'1'),
(3, '2.3.movimientos', b'1'),
(3, '2.4.stock_bajo', b'1'),
(3, '2.5.proveedores', b'1'),
(3, '2.6.compras', b'1'),
(3, '2.7.clientes', b'1'),
(3, '2.8.facturas', b'1'),
(4, '1.0.gestion_usuarios', b'1'),
(4, '1.1.usuarios', b'1'),
(4, '1.2.roles', b'1'),
(4, '1.3.asignar_pagina', b'1'),
(4, '1.4.permiso_pagina', b'1'),
(4, '1.5.asignar_menu', b'1'),
(4, '1.6.permiso_menu', b'1'),
(4, '1.7.parametros', b'1'),
(4, '2.0.gestion_productos', b'1'),
(4, '2.1.productos', b'1'),
(4, '2.10.costo_producto', b'1'),
(4, '2.2.categorias', b'1'),
(4, '2.3.movimientos', b'1'),
(4, '2.4.stock_bajo', b'1'),
(4, '2.5.proveedores', b'1'),
(4, '2.6.compras', b'1'),
(4, '2.7.clientes', b'1'),
(4, '2.8.facturas', b'1'),
(4, '2.9.precio_venta', b'1');

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
-- Estructura Stand-in para la vista `vista_precios_productos`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_precios_productos` (
`idproducto` int(11)
,`nombre` varchar(150)
,`numero_factura` varchar(100)
,`fecha_factura` date
,`stock` int(11)
,`ultimo_precio` decimal(12,4)
,`promedio_precio` decimal(16,8)
,`precio_venta` decimal(18,8)
,`costo_producto` decimal(26,8)
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
-- Estructura Stand-in para la vista `vista_ventas_proyectadas`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_ventas_proyectadas` (
`idproducto` int(11)
,`nombre` varchar(150)
,`mes_actual` varchar(7)
,`total_cantidad_vendida` decimal(32,0)
,`total_venta` decimal(32,2)
,`precio_venta` decimal(18,8)
,`mes_proyectado` varchar(7)
,`proyeccion_unidades` decimal(33,0)
,`total_venta_proyectada` decimal(51,8)
);

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_permisos_menus`
--
DROP TABLE IF EXISTS `vista_permisos_menus`;

CREATE  SQL SECURITY DEFINER VIEW `vista_permisos_menus`  AS SELECT `pm`.`idusuario` AS `idusuario`, `u`.`usuario` AS `usuario`, `pm`.`clave` AS `clave`, `pm`.`activo` AS `activo`, `pm`.`usuarioregistra` AS `usuarioregistra`, `pm`.`fecharegistro` AS `fecharegistro` FROM (`permisos_menus` `pm` join `usuarios` `u` on(`pm`.`idusuario` = `u`.`idusuario`)) WHERE `pm`.`activo` = 1 ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_precios_productos`
--
DROP TABLE IF EXISTS `vista_precios_productos`;

CREATE  SQL SECURITY DEFINER VIEW `vista_precios_productos`  AS SELECT `cd`.`idproducto` AS `idproducto`, `p`.`nombre` AS `nombre`, `c`.`numero_factura` AS `numero_factura`, `c`.`fecha_factura` AS `fecha_factura`, `p`.`stock` AS `stock`, `cd`.`precio_unitario` AS `ultimo_precio`, (select avg(`cd2`.`precio_unitario`) from `compras_detalle` `cd2` where `cd2`.`idproducto` = `cd`.`idproducto`) AS `promedio_precio`, `cd`.`precio_unitario`/ 0.85 AS `precio_venta`, `p`.`stock`* (select avg(`cd2`.`precio_unitario`) from `compras_detalle` `cd2` where `cd2`.`idproducto` = `cd`.`idproducto`) AS `costo_producto` FROM ((`compras_detalle` `cd` join `compras` `c` on(`c`.`idcompra` = `cd`.`idcompra`)) join `productos` `p` on(`p`.`idproducto` = `cd`.`idproducto`)) WHERE `c`.`fecha_factura` = (select max(`c2`.`fecha_factura`) from (`compras_detalle` `cd2` join `compras` `c2` on(`c2`.`idcompra` = `cd2`.`idcompra`)) where `cd2`.`idproducto` = `cd`.`idproducto`) ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_roles_permisos`
--
DROP TABLE IF EXISTS `vista_roles_permisos`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_roles_permisos`  AS SELECT `r`.`idrol` AS `idrol`, `r`.`nombrerol` AS `nombrerol`, `p`.`pagina` AS `pagina`, `p`.`activo` AS `activo`, `p`.`usuarioregistra` AS `usuarioregistra`, `p`.`fecharegistro` AS `fecharegistro` FROM (`roles` `r` left join `permisos` `p` on(`r`.`idrol` = `p`.`idrol`)) WHERE `r`.`activo` = 1 ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_ventas_proyectadas`
--
DROP TABLE IF EXISTS `vista_ventas_proyectadas`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_ventas_proyectadas`  AS SELECT `df`.`idproducto` AS `idproducto`, `p`.`nombre` AS `nombre`, date_format(`f`.`fecha`,'%Y-%m') AS `mes_actual`, sum(`df`.`cantidad`) AS `total_cantidad_vendida`, sum(`df`.`subtotal`) AS `total_venta`, `vpp`.`precio_venta` AS `precio_venta`, date_format(`f`.`fecha` + interval 1 month,'%Y-%m') AS `mes_proyectado`, sum(`df`.`cantidad`) + 10 AS `proyeccion_unidades`, (sum(`df`.`cantidad`) + 10) * `vpp`.`precio_venta` AS `total_venta_proyectada` FROM (((`detalle_factura` `df` join `productos` `p` on(`p`.`idproducto` = `df`.`idproducto`)) join `facturas` `f` on(`f`.`idfactura` = `df`.`idfactura`)) join `vista_precios_productos` `vpp` on(`vpp`.`idproducto` = `df`.`idproducto`)) WHERE year(`f`.`fecha`) = year(curdate() - interval 1 month) AND month(`f`.`fecha`) = month(curdate() - interval 1 month) GROUP BY `df`.`idproducto`, `p`.`nombre`, date_format(`f`.`fecha`,'%Y-%m'), date_format(`f`.`fecha` + interval 1 month,'%Y-%m'), `vpp`.`precio_venta` ORDER BY `df`.`idproducto` ASC ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`idcategoria`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`idcliente`),
  ADD UNIQUE KEY `identificacion` (`identificacion`);

--
-- Indices de la tabla `compras`
--
ALTER TABLE `compras`
  ADD PRIMARY KEY (`idcompra`),
  ADD KEY `idproveedor` (`idproveedor`);

--
-- Indices de la tabla `compras_detalle`
--
ALTER TABLE `compras_detalle`
  ADD PRIMARY KEY (`idcompra_detalle`),
  ADD KEY `idcompra` (`idcompra`),
  ADD KEY `idproducto` (`idproducto`);

--
-- Indices de la tabla `detalle_factura`
--
ALTER TABLE `detalle_factura`
  ADD PRIMARY KEY (`iddetalle`),
  ADD KEY `idfactura` (`idfactura`),
  ADD KEY `idproducto` (`idproducto`);

--
-- Indices de la tabla `facturas`
--
ALTER TABLE `facturas`
  ADD PRIMARY KEY (`idfactura`),
  ADD KEY `idcliente` (`idcliente`),
  ADD KEY `idusuario` (`idusuario`);

--
-- Indices de la tabla `movimientos`
--
ALTER TABLE `movimientos`
  ADD PRIMARY KEY (`idmovimiento`),
  ADD KEY `idproducto` (`idproducto`);

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
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`idproducto`),
  ADD KEY `idcategoria` (`idcategoria`);

--
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`idproveedor`);

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
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `idcategoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `idcliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `compras`
--
ALTER TABLE `compras`
  MODIFY `idcompra` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `compras_detalle`
--
ALTER TABLE `compras_detalle`
  MODIFY `idcompra_detalle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `detalle_factura`
--
ALTER TABLE `detalle_factura`
  MODIFY `iddetalle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `facturas`
--
ALTER TABLE `facturas`
  MODIFY `idfactura` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `movimientos`
--
ALTER TABLE `movimientos`
  MODIFY `idmovimiento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `permisos`
--
ALTER TABLE `permisos`
  MODIFY `idpermiso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=234;

--
-- AUTO_INCREMENT de la tabla `permisos_menus`
--
ALTER TABLE `permisos_menus`
  MODIFY `idpermisomenu` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=399;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `idproducto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `idproveedor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `idrol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `idusuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `compras`
--
ALTER TABLE `compras`
  ADD CONSTRAINT `compras_ibfk_1` FOREIGN KEY (`idproveedor`) REFERENCES `proveedores` (`idproveedor`);

--
-- Filtros para la tabla `compras_detalle`
--
ALTER TABLE `compras_detalle`
  ADD CONSTRAINT `compras_detalle_ibfk_1` FOREIGN KEY (`idcompra`) REFERENCES `compras` (`idcompra`) ON DELETE CASCADE,
  ADD CONSTRAINT `compras_detalle_ibfk_2` FOREIGN KEY (`idproducto`) REFERENCES `productos` (`idproducto`);

--
-- Filtros para la tabla `detalle_factura`
--
ALTER TABLE `detalle_factura`
  ADD CONSTRAINT `detalle_factura_ibfk_1` FOREIGN KEY (`idfactura`) REFERENCES `facturas` (`idfactura`),
  ADD CONSTRAINT `detalle_factura_ibfk_2` FOREIGN KEY (`idproducto`) REFERENCES `productos` (`idproducto`);

--
-- Filtros para la tabla `facturas`
--
ALTER TABLE `facturas`
  ADD CONSTRAINT `facturas_ibfk_1` FOREIGN KEY (`idcliente`) REFERENCES `clientes` (`idcliente`),
  ADD CONSTRAINT `facturas_ibfk_2` FOREIGN KEY (`idusuario`) REFERENCES `usuarios` (`idusuario`);

--
-- Filtros para la tabla `movimientos`
--
ALTER TABLE `movimientos`
  ADD CONSTRAINT `movimientos_ibfk_1` FOREIGN KEY (`idproducto`) REFERENCES `productos` (`idproducto`);

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
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`idcategoria`) REFERENCES `categorias` (`idcategoria`);

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


