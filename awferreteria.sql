-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 21-08-2025 a las 02:36:46
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
(12, 'Muebles', b'1', 1, '2025-08-01 21:22:26', NULL, NULL);

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
(1, 'Ulises', 'Zuniga', '0070204850001R', 'CEDULA', '84659917', 'ulisestm3@hotmail.com', 'Masaya', 'Masaya', 1, 1, '2025-08-15 03:03:53', 1, '2025-08-15 03:36:08');

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
(1, 1, 'S0001', '2025-08-10', 7800.00, 1170.00, 8970.00, 1, b'0', '2025-08-10 04:56:43', 1, '2025-08-10 05:03:20'),
(2, 1, 'S0001', '2025-08-10', 4050.00, 607.50, 4657.50, 1, b'1', '2025-08-10 05:10:49', NULL, NULL);

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
(9, 1, 2, 15, 180.0000, 2700.00),
(10, 1, 3, 10, 150.0000, 1500.00),
(11, 1, 1, 10, 360.0000, 3600.00),
(12, 2, 1, 5, 360.0000, 1800.00),
(13, 2, 2, 10, 180.0000, 1800.00),
(14, 2, 3, 3, 150.0000, 450.00);

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
(1, 1, 1, 2, 280.00, 560.00),
(2, 2, 2, 5, 180.00, 900.00),
(3, 3, 1, 1, 280.00, 280.00),
(4, 4, 1, 1, 280.00, 280.00),
(5, 4, 2, 1, 180.00, 180.00),
(6, 5, 2, 3, 180.00, 540.00),
(8, 7, 3, 1, 230.00, 230.00),
(9, 6, 3, 2, 230.00, 460.00),
(10, 8, 1, 1, 280.00, 280.00),
(11, 9, 2, 1, 180.00, 180.00);

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
(1, 1, 1, '2025-08-20 13:03:35', 644.00, 84.00, b'1', 1, '2025-08-20 13:03:35', NULL, NULL),
(2, 1, 1, '2025-08-20 13:10:15', 1035.00, 135.00, b'1', 1, '2025-08-20 13:10:15', NULL, NULL),
(3, 1, 1, '2025-08-20 13:24:27', 322.00, 42.00, b'1', 1, '2025-08-20 13:24:27', NULL, NULL),
(4, 1, 1, '2025-08-20 13:34:42', 529.00, 69.00, b'1', 1, '2025-08-20 13:34:42', NULL, NULL),
(5, 1, 1, '2025-08-20 13:38:54', 621.00, 81.00, b'1', 1, '2025-08-20 13:38:54', NULL, NULL),
(6, 1, 1, '2025-08-20 13:47:03', 529.00, 69.00, b'1', 1, '2025-08-20 13:47:03', 1, '2025-08-20 16:16:33'),
(7, 1, 1, '2025-08-20 13:55:20', 264.50, 34.50, b'0', 1, '2025-08-20 13:55:20', 1, '2025-08-20 16:15:43'),
(8, 1, 1, '2025-08-20 17:56:14', 322.00, 42.00, b'1', 1, '2025-08-20 17:56:14', NULL, NULL),
(9, 1, 1, '2025-08-20 18:07:27', 207.00, 27.00, b'1', 1, '2025-08-20 18:07:27', NULL, NULL);

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
(1, 1, 'entrada', 3, 'Compra de producto', '2025-08-10 04:56:43', 1, b'1'),
(2, 2, 'entrada', 5, 'Compra de producto', '2025-08-10 04:56:43', 1, b'1'),
(3, 3, 'entrada', 10, 'Compra de producto', '2025-08-10 04:56:43', 1, b'1'),
(4, 1, 'salida', 3, 'Anulación por actualización', '2025-08-10 04:57:58', 1, b'1'),
(5, 2, 'salida', 5, 'Anulación por actualización', '2025-08-10 04:57:58', 1, b'1'),
(6, 3, 'salida', 10, 'Anulación por actualización', '2025-08-10 04:57:58', 1, b'1'),
(7, 1, 'entrada', 3, 'Actualización de compra', '2025-08-10 04:57:58', 1, b'1'),
(8, 3, 'entrada', 10, 'Actualización de compra', '2025-08-10 04:57:58', 1, b'1'),
(9, 2, 'entrada', 10, 'Actualización de compra', '2025-08-10 04:57:58', 1, b'1'),
(10, 1, 'salida', 3, 'Anulación por actualización', '2025-08-10 04:59:18', 1, b'1'),
(11, 3, 'salida', 10, 'Anulación por actualización', '2025-08-10 04:59:18', 1, b'1'),
(12, 2, 'salida', 10, 'Anulación por actualización', '2025-08-10 04:59:18', 1, b'1'),
(13, 3, 'entrada', 10, 'Actualización de compra', '2025-08-10 04:59:18', 1, b'1'),
(14, 2, 'entrada', 15, 'Actualización de compra', '2025-08-10 04:59:18', 1, b'1'),
(15, 3, 'salida', 10, 'Anulación por actualización', '2025-08-10 05:00:24', 1, b'1'),
(16, 2, 'salida', 15, 'Anulación por actualización', '2025-08-10 05:00:24', 1, b'1'),
(17, 2, 'entrada', 15, 'Actualización de compra', '2025-08-10 05:00:24', 1, b'1'),
(18, 3, 'entrada', 10, 'Actualización de compra', '2025-08-10 05:00:24', 1, b'1'),
(19, 1, 'entrada', 10, 'Actualización de compra', '2025-08-10 05:00:24', 1, b'1'),
(20, 2, 'salida', 15, 'Ajuste de compra anulada', '2025-08-10 05:03:20', 1, b'1'),
(21, 3, 'salida', 10, 'Ajuste de compra anulada', '2025-08-10 05:03:20', 1, b'1'),
(22, 1, 'salida', 10, 'Ajuste de compra anulada', '2025-08-10 05:03:20', 1, b'1'),
(23, 1, 'entrada', 5, 'Compra de producto', '2025-08-10 05:10:49', 1, b'1'),
(24, 2, 'entrada', 10, 'Compra de producto', '2025-08-10 05:10:49', 1, b'1'),
(25, 3, 'entrada', 3, 'Compra de producto', '2025-08-10 05:10:49', 1, b'1'),
(26, 1, 'salida', 2, 'Factura #1', '2025-08-20 13:03:35', 1, b'1'),
(27, 2, 'salida', 5, 'Factura #2', '2025-08-20 13:10:15', 1, b'1'),
(28, 1, 'salida', 1, 'Factura #3', '2025-08-20 13:24:27', 1, b'1'),
(29, 1, 'salida', 1, 'Factura #4', '2025-08-20 13:34:42', 1, b'1'),
(30, 2, 'salida', 1, 'Factura #4', '2025-08-20 13:34:42', 1, b'1'),
(31, 2, 'salida', 3, 'Factura #5', '2025-08-20 13:38:54', 1, b'1'),
(32, 3, 'salida', 1, 'Factura #6', '2025-08-20 13:47:03', 1, b'1'),
(33, 3, 'salida', 1, 'Factura #7', '2025-08-20 13:55:20', 1, b'1'),
(36, 3, 'entrada', 1, 'Anulación Factura #7', '2025-08-20 16:15:43', 1, b'1'),
(37, 3, 'entrada', 1, 'Actualización Factura #6', '2025-08-20 16:16:33', 1, b'1'),
(38, 3, 'salida', 2, 'Actualización Factura #6', '2025-08-20 16:16:33', 1, b'1'),
(39, 1, 'salida', 1, 'Factura #8', '2025-08-20 17:56:14', 1, b'1'),
(40, 2, 'salida', 1, 'Factura #9', '2025-08-20 18:07:27', 1, b'1');

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
(146, 1, 'asignar_menu_usuario.php', b'1', 1, '2025-08-20 11:05:52'),
(147, 1, 'asignar_permisos.php', b'1', 1, '2025-08-20 11:05:52'),
(148, 1, 'categorias.php', b'1', 1, '2025-08-20 11:05:52'),
(149, 1, 'clientes.php', b'1', 1, '2025-08-20 11:05:52'),
(150, 1, 'compras.php', b'1', 1, '2025-08-20 11:05:52'),
(151, 1, 'facturas.php', b'1', 1, '2025-08-20 11:05:52'),
(152, 1, 'movimientos.php', b'1', 1, '2025-08-20 11:05:52'),
(153, 1, 'permisos_por_rol.php', b'1', 1, '2025-08-20 11:05:52'),
(154, 1, 'permisos_usuarios_menus.php', b'1', 1, '2025-08-20 11:05:52'),
(155, 1, 'productos.php', b'1', 1, '2025-08-20 11:05:52'),
(156, 1, 'proveedores.php', b'1', 1, '2025-08-20 11:05:52'),
(157, 1, 'roles.php', b'1', 1, '2025-08-20 11:05:52'),
(158, 1, 'stock_bajo.php', b'1', 1, '2025-08-20 11:05:52'),
(159, 1, 'usuarios.php', b'1', 1, '2025-08-20 11:05:52');

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
(281, 1, '1.0.gestion_usuarios', b'1', 1, '2025-08-20 11:06:03'),
(282, 1, '1.1.usuarios', b'1', 1, '2025-08-20 11:06:03'),
(283, 1, '1.2.roles', b'1', 1, '2025-08-20 11:06:03'),
(284, 1, '1.3.asignar_pagina', b'1', 1, '2025-08-20 11:06:03'),
(285, 1, '1.4.permiso_pagina', b'1', 1, '2025-08-20 11:06:03'),
(286, 1, '1.5.asignar_menu', b'1', 1, '2025-08-20 11:06:03'),
(287, 1, '1.6.permiso_menu', b'1', 1, '2025-08-20 11:06:03'),
(288, 1, '2.0.gestion_productos', b'1', 1, '2025-08-20 11:06:03'),
(289, 1, '2.1.productos', b'1', 1, '2025-08-20 11:06:03'),
(290, 1, '2.2.categorias', b'1', 1, '2025-08-20 11:06:03'),
(291, 1, '2.3.movimientos', b'1', 1, '2025-08-20 11:06:03'),
(292, 1, '2.4.stock_bajo', b'1', 1, '2025-08-20 11:06:03'),
(293, 1, '2.5.proveedores', b'1', 1, '2025-08-20 11:06:03'),
(294, 1, '2.6.compras', b'1', 1, '2025-08-20 11:06:03'),
(295, 1, '2.7.clientes', b'1', 1, '2025-08-20 11:06:03'),
(296, 1, '2.8.facturas', b'1', 1, '2025-08-20 11:06:03');

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
(1, 'Cemento Canal 25kg', 10, 'Cemento Canal 25kg', 0, 5, 280.00, b'1', 1, '2025-08-01 17:21:30', NULL, NULL),
(2, 'Bujía 5W', 11, 'Bujía 5W', 0, 10, 180.00, b'1', 1, '2025-08-01 18:56:36', NULL, NULL),
(3, 'Lampara 30w', 11, 'Lampara 30w', 1, 5, 230.00, b'1', 2, '2025-08-01 22:44:46', 1, '2025-08-15 04:44:24');

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
(1, 'Sinsa,S.A.', 'Juan Perez', '88884444', 'sinsa.info@gmail.com', 'KM.8 1/2 carrtera Managua - Masaya', b'1', 1, '2025-08-10 02:12:30', NULL, NULL);

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
(1, 'categorias.php', b'1'),
(1, 'clientes.php', b'1'),
(1, 'compras.php', b'1'),
(1, 'facturas.php', b'1'),
(1, 'movimientos.php', b'1'),
(1, 'permisos_por_rol.php', b'1'),
(1, 'permisos_usuarios_menus.php', b'1'),
(1, 'productos.php', b'1'),
(1, 'proveedores.php', b'1'),
(1, 'roles.php', b'1'),
(1, 'stock_bajo.php', b'1'),
(1, 'usuarios.php', b'1'),
(2, 'categorias.php', b'1'),
(2, 'movimientos.php', b'1'),
(2, 'productos.php', b'1'),
(2, 'proveedores.php', b'1'),
(2, 'stock_bajo.php', b'1');

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
(2, 'Ulises Zuniga', 'uzuniga', 'a665a45920422f9d417e4867efdc4fb8a04a1f3fff1fa07e998e86f7f7a27ae3', 'uzuniga@gmail.com', '84659917', 2, b'1', 1, '2025-07-29 22:11:28', NULL, '2025-07-31 21:05:46');

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
(1, '2.0.gestion_productos', b'1'),
(1, '2.1.productos', b'1'),
(1, '2.2.categorias', b'1'),
(1, '2.3.movimientos', b'1'),
(1, '2.4.stock_bajo', b'1'),
(1, '2.5.proveedores', b'1'),
(1, '2.6.compras', b'1'),
(1, '2.7.clientes', b'1'),
(1, '2.8.facturas', b'1'),
(2, '2.0.gestion_productos', b'1'),
(2, '2.1.productos', b'1'),
(2, '2.2.categorias', b'1'),
(2, '2.3.movimientos', b'1'),
(2, '2.4.stock_bajo', b'1'),
(2, '2.5.proveedores', b'1');

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

CREATE ALGORITHM=UNDEFINED   SQL SECURITY DEFINER VIEW `vista_permisos_menus`  AS SELECT `pm`.`idusuario` AS `idusuario`, `u`.`usuario` AS `usuario`, `pm`.`clave` AS `clave`, `pm`.`activo` AS `activo`, `pm`.`usuarioregistra` AS `usuarioregistra`, `pm`.`fecharegistro` AS `fecharegistro` FROM (`permisos_menus` `pm` join `usuarios` `u` on(`pm`.`idusuario` = `u`.`idusuario`)) WHERE `pm`.`activo` = 1 ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_roles_permisos`
--
DROP TABLE IF EXISTS `vista_roles_permisos`;

CREATE ALGORITHM=UNDEFINED   SQL SECURITY DEFINER VIEW `vista_roles_permisos`  AS SELECT `r`.`idrol` AS `idrol`, `r`.`nombrerol` AS `nombrerol`, `p`.`pagina` AS `pagina`, `p`.`activo` AS `activo`, `p`.`usuarioregistra` AS `usuarioregistra`, `p`.`fecharegistro` AS `fecharegistro` FROM (`roles` `r` left join `permisos` `p` on(`r`.`idrol` = `p`.`idrol`)) WHERE `r`.`activo` = 0x01 ;

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
  MODIFY `idcategoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `idcliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `compras`
--
ALTER TABLE `compras`
  MODIFY `idcompra` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `compras_detalle`
--
ALTER TABLE `compras_detalle`
  MODIFY `idcompra_detalle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `detalle_factura`
--
ALTER TABLE `detalle_factura`
  MODIFY `iddetalle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `facturas`
--
ALTER TABLE `facturas`
  MODIFY `idfactura` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `movimientos`
--
ALTER TABLE `movimientos`
  MODIFY `idmovimiento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT de la tabla `permisos`
--
ALTER TABLE `permisos`
  MODIFY `idpermiso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=160;

--
-- AUTO_INCREMENT de la tabla `permisos_menus`
--
ALTER TABLE `permisos_menus`
  MODIFY `idpermisomenu` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=297;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `idproducto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `idproveedor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
