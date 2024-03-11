-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 26-09-2023 a las 01:17:25
-- Versión del servidor: 10.4.20-MariaDB
-- Versión de PHP: 7.3.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `gym_bar`
--
CREATE DATABASE IF NOT EXISTS `gym_bar` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `gym_bar`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bitacora_inicio_sesion`
--

CREATE TABLE `bitacora_inicio_sesion` (
  `id` int(11) NOT NULL,
  `usuario` varchar(25) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `estado` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bitacora_modificacion`
--

CREATE TABLE `bitacora_modificacion` (
  `id` int(11) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `fecha` datetime NOT NULL,
  `tabla` varchar(50) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `id_entidad` int(11) NOT NULL,
  `total` double DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria`
--

CREATE TABLE `categoria` (
  `id` int(11) NOT NULL,
  `categoria` varchar(30) NOT NULL,
  `estado` varchar(1) NOT NULL DEFAULT 'A',
  `codigo` varchar(9) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cierre_caja`
--

CREATE TABLE `cierre_caja` (
  `id` int(11) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `cajero` int(11) NOT NULL,
  `fondo` double DEFAULT 0,
  `monto_sinpe` double DEFAULT NULL,
  `monto_efectivo` double DEFAULT NULL,
  `monto_tarjeta` double DEFAULT NULL,
  `ingreso` int(11) DEFAULT NULL,
  `estado` int(11) NOT NULL,
  `sucursal` int(11) NOT NULL,
  `fecha_cierra` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente`
--

CREATE TABLE `cliente` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `telefono` varchar(14) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `ubicacion` varchar(300) DEFAULT NULL,
  `estado` varchar(1) NOT NULL DEFAULT 'A'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `codigo_descuento`
--

CREATE TABLE `codigo_descuento` (
  `id` int(11) NOT NULL,
  `tipo` int(11) NOT NULL,
  `descuento` int(11) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `descripcion` varchar(2000) NOT NULL,
  `codigo` varchar(500) NOT NULL,
  `activo` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `codigo_descuento`
--

INSERT INTO `codigo_descuento` (`id`, `tipo`, `descuento`, `fecha_inicio`, `fecha_fin`, `descripcion`, `codigo`, `activo`) VALUES
(1, 2, 10, '2023-08-24', '2023-08-27', 'Gracias por preferir GYM BAR', 'MARIO2598', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_orden`
--

CREATE TABLE `detalle_orden` (
  `id` int(11) NOT NULL,
  `cantidad` int(11) DEFAULT 0,
  `codigo_producto` varchar(15) DEFAULT NULL,
  `nombre_producto` varchar(50) DEFAULT NULL,
  `precio_unidad` double DEFAULT 0,
  `impuesto` double DEFAULT 0,
  `total` double DEFAULT 0,
  `subtotal` double DEFAULT 0,
  `descuento` double DEFAULT 0,
  `orden` int(11) DEFAULT NULL,
  `tipo_producto` varchar(15) DEFAULT NULL COMMENT 'Producido, restaurante ,\r\nproveedor',
  `servicio_mesa` varchar(1) NOT NULL DEFAULT 'N' COMMENT 'Porcentaje de servicio a la mesa (S=Sí, N=No)',
  `observacion` varchar(3000) DEFAULT NULL,
  `tipo_comanda` varchar(2) DEFAULT NULL COMMENT 'CO : COCINA , BE : Bebidas',
  `total_extras` double DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `extra_detalle_orden`
--

CREATE TABLE `extra_detalle_orden` (
  `id` int(11) NOT NULL,
  `orden` int(11) NOT NULL,
  `detalle` int(11) NOT NULL,
  `descripcion_extra` varchar(1500) NOT NULL,
  `total` double DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `extra_producto_externo`
--

CREATE TABLE `extra_producto_externo` (
  `id` int(11) NOT NULL,
  `descripcion` varchar(500) NOT NULL,
  `precio` int(11) NOT NULL,
  `producto` int(11) NOT NULL,
  `dsc_grupo` varchar(500) NOT NULL,
  `es_requerido` int(11) DEFAULT 0,
  `multiple` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `extra_producto_menu`
--

CREATE TABLE `extra_producto_menu` (
  `id` int(11) NOT NULL,
  `descripcion` varchar(500) NOT NULL,
  `precio` int(11) NOT NULL,
  `producto` int(11) NOT NULL,
  `dsc_grupo` varchar(500) NOT NULL,
  `es_requerido` int(11) DEFAULT 0,
  `multiple` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `gasto`
--

CREATE TABLE `gasto` (
  `id` int(11) NOT NULL,
  `monto` double NOT NULL,
  `descripcion` varchar(150) NOT NULL,
  `num_factura` varchar(50) DEFAULT NULL,
  `usuario` int(11) NOT NULL,
  `proveedor` int(11) NOT NULL,
  `tipo_documento` varchar(1) NOT NULL DEFAULT 'F',
  `tipo_pago` int(11) NOT NULL,
  `tipo_gasto` int(11) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `observacion` varchar(150) DEFAULT NULL,
  `ingreso` int(11) DEFAULT NULL,
  `aprobado` varchar(1) DEFAULT 'N',
  `sucursal` varchar(50) NOT NULL,
  `url_factura` varchar(300) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `impuesto`
--

CREATE TABLE `impuesto` (
  `id` int(11) NOT NULL,
  `descripcion` varchar(50) NOT NULL,
  `impuesto` float NOT NULL DEFAULT 0,
  `estado` varchar(1) NOT NULL DEFAULT 'A'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `impuesto`
--

INSERT INTO `impuesto` (`id`, `descripcion`, `impuesto`, `estado`) VALUES
(1, 'Al valor agregado 13%', 14, 'I'),
(2, 'IVA 13', 13, 'A'),
(3, 'IVA EX', 0, 'I'),
(4, 'IVA 1%', 1, 'A');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ingreso`
--

CREATE TABLE `ingreso` (
  `id` bigint(20) NOT NULL,
  `monto_efectivo` double NOT NULL DEFAULT 0,
  `monto_tarjeta` double NOT NULL DEFAULT 0,
  `monto_sinpe` double NOT NULL DEFAULT 0,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `tipo` int(11) NOT NULL,
  `observacion` varchar(150) DEFAULT NULL,
  `usuario` int(11) NOT NULL,
  `sucursal` int(11) NOT NULL,
  `aprobado` varchar(1) NOT NULL DEFAULT 'N',
  `cliente` int(11) DEFAULT NULL,
  `descripcion` varchar(300) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `log`
--

CREATE TABLE `log` (
  `id` int(11) NOT NULL,
  `descripcion` blob NOT NULL,
  `usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `materia_prima`
--

CREATE TABLE `materia_prima` (
  `id` int(11) NOT NULL,
  `nombre` varchar(5000) NOT NULL,
  `proveedor` int(11) NOT NULL,
  `unidad_medida` varchar(500) NOT NULL,
  `precio` double NOT NULL,
  `activo` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `menu`
--

CREATE TABLE `menu` (
  `id` int(11) NOT NULL,
  `rol` int(11) NOT NULL,
  `vista` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `menu`
--

INSERT INTO `menu` (`id`, `rol`, `vista`) VALUES
(7, 36, 2),
(8, 36, 3),
(9, 36, 4),
(10, 36, 5),
(11, 36, 7),
(12, 36, 8),
(13, 36, 1),
(57, 37, 16),
(58, 37, 15),
(310, 26, 60),
(311, 26, 59),
(373, 25, 2),
(374, 25, 3),
(375, 25, 4),
(376, 25, 5),
(377, 25, 8),
(378, 25, 9),
(379, 25, 10),
(380, 25, 11),
(381, 25, 12),
(382, 25, 30),
(383, 25, 16),
(384, 25, 18),
(385, 25, 20),
(386, 25, 63),
(387, 25, 22),
(388, 25, 54),
(389, 25, 58),
(390, 25, 50),
(391, 25, 52),
(392, 25, 57),
(393, 25, 56),
(394, 25, 64),
(395, 25, 65),
(396, 25, 60),
(397, 25, 1),
(398, 25, 15),
(399, 25, 19),
(400, 25, 53),
(401, 25, 49),
(402, 25, 55),
(403, 25, 59);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mt_x_extra`
--

CREATE TABLE `mt_x_extra` (
  `id` int(11) NOT NULL,
  `materia_prima` int(11) NOT NULL,
  `extra` int(11) NOT NULL,
  `cantidad` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mt_x_producto`
--

CREATE TABLE `mt_x_producto` (
  `id` int(11) NOT NULL,
  `materia_prima` int(11) NOT NULL,
  `producto` int(11) NOT NULL,
  `cantidad` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mt_x_sucursal`
--

CREATE TABLE `mt_x_sucursal` (
  `id` int(11) NOT NULL,
  `materia_prima` int(11) NOT NULL,
  `sucursal` int(11) NOT NULL,
  `cantidad` double NOT NULL,
  `ultima_modificacion` date NOT NULL DEFAULT current_timestamp(),
  `usuario_modifica` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `orden`
--

CREATE TABLE `orden` (
  `id` int(11) NOT NULL,
  `numero_orden` varchar(1100) NOT NULL,
  `fecha_inicio` datetime DEFAULT NULL,
  `cliente` int(11) DEFAULT NULL,
  `tipo` int(11) DEFAULT NULL,
  `fecha_fin` datetime DEFAULT NULL,
  `nombre_cliente` varchar(100) DEFAULT NULL,
  `estado` int(11) DEFAULT NULL,
  `total` double DEFAULT NULL,
  `subtotal` double DEFAULT NULL,
  `impuesto` double DEFAULT NULL,
  `descuento` double DEFAULT NULL,
  `cajero` int(11) DEFAULT NULL,
  `monto_sinpe` double DEFAULT 0,
  `monto_tarjeta` double DEFAULT 0,
  `monto_efectivo` double DEFAULT 0,
  `factura_electronica` varchar(1) DEFAULT 'N' COMMENT 'S :Si , N : NO',
  `ingreso` int(11) DEFAULT NULL,
  `sucursal` int(11) DEFAULT NULL,
  `fecha_preparado` datetime DEFAULT NULL,
  `fecha_entregado` datetime DEFAULT NULL,
  `cocina_terminado` varchar(1) DEFAULT '' COMMENT 'S :SI , N : NO',
  `bebida_terminado` varchar(1) DEFAULT NULL COMMENT 'S SI , N NO',
  `caja_cerrada` varchar(1) NOT NULL DEFAULT 'N',
  `periodo` int(11) DEFAULT NULL,
  `total_con_descuento` double DEFAULT 0,
  `cierre_caja` int(11) DEFAULT NULL,
  `pagado` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `panel_configuraciones`
--

CREATE TABLE `panel_configuraciones` (
  `id` int(11) NOT NULL,
  `color_fondo` int(11) NOT NULL DEFAULT 1,
  `color_sidebar` int(11) NOT NULL DEFAULT 1,
  `color_tema` varchar(15) NOT NULL DEFAULT 'white',
  `mini_sidebar` int(11) NOT NULL DEFAULT 1,
  `sticky_topbar` int(11) NOT NULL DEFAULT 1,
  `usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `panel_configuraciones`
--

INSERT INTO `panel_configuraciones` (`id`, `color_fondo`, `color_sidebar`, `color_tema`, `mini_sidebar`, `sticky_topbar`, `usuario`) VALUES
(7, 1, 1, 'white', 1, 1, 15),
(8, 1, 1, 'white', 1, 1, 14),
(11, 1, 1, 'white', 1, 1, 14),
(12, 1, 1, 'white', 1, 1, 14),
(13, 1, 1, 'white', 1, 1, 14),
(14, 1, 1, 'white', 1, 1, 14),
(15, 1, 1, 'white', 1, 1, 14),
(16, 1, 1, 'white', 1, 1, 14),
(17, 1, 1, 'white', 1, 1, 14),
(18, 1, 1, 'white', 1, 1, 14),
(19, 1, 1, 'white', 1, 1, 14),
(20, 1, 1, 'white', 1, 1, 14),
(21, 1, 1, 'white', 1, 1, 14),
(22, 1, 1, 'white', 1, 1, 14),
(23, 1, 1, 'white', 1, 1, 15),
(24, 1, 1, 'white', 1, 1, 15),
(25, 1, 1, 'white', 1, 1, 15),
(26, 1, 1, 'white', 1, 1, 15),
(27, 1, 1, 'white', 1, 1, 14),
(28, 1, 1, 'white', 1, 1, 14),
(29, 1, 1, 'white', 1, 1, 14),
(30, 1, 1, 'white', 1, 1, 15),
(31, 1, 1, 'white', 1, 1, 14),
(32, 1, 1, 'white', 1, 1, 15),
(33, 1, 1, 'white', 1, 1, 14),
(34, 1, 1, 'white', 1, 1, 14),
(35, 1, 1, 'white', 1, 1, 15),
(36, 1, 1, 'white', 1, 1, 15),
(37, 1, 1, 'white', 1, 1, 15),
(38, 1, 1, 'white', 1, 1, 15),
(39, 1, 1, 'white', 1, 1, 15),
(40, 1, 1, 'white', 1, 1, 15),
(41, 1, 1, 'white', 1, 1, 15),
(42, 1, 1, 'white', 1, 1, 15),
(43, 1, 1, 'white', 1, 1, 15),
(44, 1, 1, 'white', 1, 1, 15),
(45, 1, 1, 'white', 1, 1, 15),
(46, 1, 1, 'white', 1, 1, 15),
(47, 1, 1, 'white', 1, 1, 15),
(48, 1, 1, 'white', 1, 1, 17),
(49, 1, 1, 'white', 1, 1, 18),
(50, 1, 1, 'white', 1, 1, 17),
(51, 1, 1, 'white', 1, 1, 17),
(52, 1, 1, 'white', 1, 1, 17);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `parametros_generales`
--

CREATE TABLE `parametros_generales` (
  `id` int(11) NOT NULL,
  `porcentaje_banco` float NOT NULL DEFAULT 0,
  `tiempo_refresco_monitor_movimientos` int(11) NOT NULL DEFAULT 5,
  `inicio_mes_panaderia` double DEFAULT 0,
  `inicio_mes_cafeteria` double NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pe_x_sucursal`
--

CREATE TABLE `pe_x_sucursal` (
  `id` int(11) NOT NULL,
  `producto_externo` int(11) NOT NULL,
  `cantidad` int(11) DEFAULT 0,
  `sucursal` int(11) NOT NULL,
  `ultima_modificacion` datetime NOT NULL DEFAULT current_timestamp(),
  `usuario_modifica` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pm_x_sucursal`
--

CREATE TABLE `pm_x_sucursal` (
  `id` int(11) NOT NULL,
  `sucursal` int(11) NOT NULL,
  `producto_menu` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto_externo`
--

CREATE TABLE `producto_externo` (
  `id` int(11) NOT NULL,
  `codigo_barra` varchar(25) DEFAULT NULL,
  `nombre` varchar(50) DEFAULT NULL,
  `precio_compra` double DEFAULT NULL,
  `impuesto` int(11) DEFAULT NULL,
  `categoria` int(11) DEFAULT NULL,
  `precio` double DEFAULT NULL,
  `estado` varchar(1) NOT NULL DEFAULT 'A',
  `proveedor` int(11) DEFAULT NULL,
  `descripcion` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto_menu`
--

CREATE TABLE `producto_menu` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) DEFAULT NULL,
  `descripcion` varchar(500) DEFAULT NULL,
  `categoria` int(11) DEFAULT NULL,
  `impuesto` int(11) DEFAULT NULL,
  `imagen` varchar(300) DEFAULT NULL,
  `precio` double DEFAULT 0,
  `estado` varchar(1) NOT NULL DEFAULT 'A',
  `codigo` varchar(15) DEFAULT NULL,
  `tipo_comanda` varchar(3) DEFAULT NULL COMMENT 'BE : BEBIDA , CO : COMIDA'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedor`
--

CREATE TABLE `proveedor` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) DEFAULT NULL,
  `descripcion` varchar(200) DEFAULT NULL,
  `estado` varchar(1) NOT NULL DEFAULT 'A'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

CREATE TABLE `rol` (
  `id` int(11) NOT NULL,
  `rol` varchar(50) NOT NULL,
  `codigo` varchar(15) NOT NULL,
  `estado` varchar(1) NOT NULL DEFAULT 'A'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `rol`
--

INSERT INTO `rol` (`id`, `rol`, `codigo`, `estado`) VALUES
(25, 'Administrador', 'admin', 'A'),
(26, 'Cajero', 'caj', 'A'),
(27, 'Super Usuario', 'su', 'A'),
(28, 'Prueba', 'pb', 'I'),
(29, 'Prueba', 'pb', 'I'),
(30, 'kkk', 'k', 'I'),
(35, 'Bodeguero', 'bdg', 'I'),
(36, 'CEO', 'MARIO', 'A'),
(37, 'Encargado gasto', 'SOLO_GASTO', 'A');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sis_clase`
--

CREATE TABLE `sis_clase` (
  `id` int(11) NOT NULL,
  `nombre` varchar(1000) NOT NULL,
  `cod_general` varchar(1000) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `sis_clase`
--

INSERT INTO `sis_clase` (`id`, `nombre`, `cod_general`) VALUES
(1, 'Estados de Cierre de caja\r\n', 'CIERRE_CAJA'),
(2, 'Tipos descuento codigo descuento', 'DESCUENTOS_COD_PROM'),
(3, 'Estados Orden / Factura', 'EST_FACTURAS'),
(4, 'Tipos Ingreso', 'GEN_INGRESOS');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sis_estado`
--

CREATE TABLE `sis_estado` (
  `id` int(11) NOT NULL,
  `nombre` varchar(5000) NOT NULL,
  `clase` int(11) NOT NULL,
  `cod_general` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `sis_estado`
--

INSERT INTO `sis_estado` (`id`, `nombre`, `clase`, `cod_general`) VALUES
(1, 'Cierre Caja Abierto', 1, 'CAJA_ABIERTO'),
(2, 'CIERRE CAJA FINALIZADO', 1, 'CAJA_FINALIZADO'),
(3, 'CIERRE CAJA CANCELADA', 1, 'CIERRE_CANCELADO'),
(4, 'Facturada', 3, 'ORD_FACTURADA'),
(5, 'Anulada', 3, 'ORD_ANULADA'),
(6, 'En preparación', 3, 'ORD_EN_PREPARACION'),
(7, 'Para entrega', 3, 'ORD_PARA_ENTREGA'),
(8, 'Entregada', 3, 'ORD_ENTREGADA');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sis_parametro`
--

CREATE TABLE `sis_parametro` (
  `id` int(11) NOT NULL,
  `descripcion` varchar(1500) NOT NULL,
  `valor` varchar(1500) NOT NULL,
  `cod_general` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `sis_parametro`
--

INSERT INTO `sis_parametro` (`id`, `descripcion`, `valor`, `cod_general`) VALUES
(1, 'Monto correspondiente a fondo de plata con el que inicia la caja', '30000', 'MTO_FONDO_INI_CAJA');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sis_tipo`
--

CREATE TABLE `sis_tipo` (
  `id` int(11) NOT NULL,
  `nombre` varchar(1500) NOT NULL,
  `clase` int(11) NOT NULL,
  `cod_general` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `sis_tipo`
--

INSERT INTO `sis_tipo` (`id`, `nombre`, `clase`, `cod_general`) VALUES
(1, 'Absoluto', 2, 'DESCUENTO_ABSOLUTO'),
(2, 'Porciento', 2, 'DESCUENTO_PORCENTAJE'),
(3, 'Cierre de Caja', 4, 'ING_CIERRE_CAJA');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sucursal`
--

CREATE TABLE `sucursal` (
  `id` int(11) NOT NULL,
  `descripcion` varchar(50) DEFAULT NULL,
  `estado` varchar(1) NOT NULL DEFAULT 'A',
  `cod_general` varchar(150) NOT NULL,
  `cont_ordenes` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `sucursal`
--

INSERT INTO `sucursal` (`id`, `descripcion`, `estado`, `cod_general`, `cont_ordenes`) VALUES
(1, 'El carmen', 'A', 'CTG', 47),
(2, 'La Merced Heredia', 'A', 'MH', 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_gasto`
--

CREATE TABLE `tipo_gasto` (
  `id` int(11) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `estado` varchar(1) NOT NULL DEFAULT 'A'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `tipo_gasto`
--

INSERT INTO `tipo_gasto` (`id`, `tipo`, `estado`) VALUES
(1, 'Administración', 'A'),
(2, 'Cafetería', 'I'),
(3, 'Cocina', 'I'),
(5, 'Cafeteria', 'I'),
(6, 'Gasolinera', 'A'),
(7, 'Planilla', 'A');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_ingreso`
--

CREATE TABLE `tipo_ingreso` (
  `id` int(11) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `estado` varchar(1) NOT NULL DEFAULT 'A',
  `cod_general` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `tipo_ingreso`
--

INSERT INTO `tipo_ingreso` (`id`, `tipo`, `estado`, `cod_general`) VALUES
(1, 'Cierre Caja', 'A', 'ING_CIERRE_CAJA'),
(2, 'Cafetería', 'I', 'ING_CAFE'),
(3, 'Admin', 'A', 'ING_ADMIN'),
(6, 'PRUEBA', 'A', 'PRUEBA');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_pago`
--

CREATE TABLE `tipo_pago` (
  `id` int(11) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `estado` varchar(1) NOT NULL DEFAULT 'A'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `tipo_pago`
--

INSERT INTO `tipo_pago` (`id`, `tipo`, `estado`) VALUES
(1, 'Efectivo', 'A'),
(2, 'Transferencia', 'A'),
(3, 'pryueba nuebo', 'I'),
(4, 'Sinpe', 'A');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id` int(11) NOT NULL,
  `nombre` varchar(25) NOT NULL,
  `ape1` varchar(25) NOT NULL,
  `ape2` varchar(25) DEFAULT NULL,
  `cedula` varchar(15) DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `fecha_ingreso` datetime DEFAULT current_timestamp(),
  `correo` varchar(100) DEFAULT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `usuario` varchar(25) NOT NULL,
  `contra` varchar(150) NOT NULL,
  `sucursal` int(11) NOT NULL,
  `rol` int(11) NOT NULL,
  `estado` varchar(1) NOT NULL DEFAULT 'A'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id`, `nombre`, `ape1`, `ape2`, `cedula`, `fecha_nacimiento`, `fecha_ingreso`, `correo`, `telefono`, `usuario`, `contra`, `sucursal`, `rol`, `estado`) VALUES
(14, 'Usuario', 'Prueba', NULL, '1169099', '1998-01-25', '2020-09-12 23:12:05', NULL, '70565419', 'prueba', '81dc9bdb52d04dc20036dbd8313ed055', 1, 25, 'A'),
(15, 'Mario', 'Flores', 'Solis', '116990433', '1998-01-25', '2020-09-13 05:31:34', 'mario.flores251998@gmail.com', '70565419', 'mflores', '81dc9bdb52d04dc20036dbd8313ed055', 1, 25, 'A'),
(17, 'Sebastian', 'Carranza', 'Garita', '116990434', '1998-08-14', '2023-06-20 14:39:24', NULL, '70565419', 'scarranza', '81dc9bdb52d04dc20036dbd8313ed055', 2, 26, 'A'),
(18, 'Julisa', 'Zuñiga', NULL, '1515151551', '1998-01-25', '2023-06-21 20:02:57', NULL, '50505050', 'jzuniga', '81dc9bdb52d04dc20036dbd8313ed055', 1, 37, 'A');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vista`
--

CREATE TABLE `vista` (
  `id` int(11) NOT NULL,
  `titulo` varchar(30) NOT NULL,
  `ruta` varchar(50) NOT NULL,
  `tipo` varchar(1) NOT NULL DEFAULT 'M',
  `codigo_grupo` varchar(15) NOT NULL,
  `orden` int(11) NOT NULL,
  `peso_general` int(11) NOT NULL,
  `codigo_pantalla` varchar(30) DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `inactivo` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `vista`
--

INSERT INTO `vista` (`id`, `titulo`, `ruta`, `tipo`, `codigo_grupo`, `orden`, `peso_general`, `codigo_pantalla`, `icon`, `inactivo`) VALUES
(1, 'Mantenimientos', '', 'G', 'mant', 0, 1, 'mant', 'fas fa-cogs', 0),
(2, 'Usuarios', 'mant/usuarios', 'M', 'mant', 1, 1, 'mantUsu', '', 0),
(3, 'Roles', 'mant/roles', 'M', 'mant', 2, 1, 'mantRol', '', 0),
(4, 'Sucursales', 'mant/sucursales', 'M', 'mant', 3, 1, 'mantSuc', '', 0),
(5, 'Proveedores', 'mant/proveedores', 'M', 'mant', 4, 1, 'mantPro', '', 0),
(8, 'Impuestos', 'mant/impuestos', 'M', 'mant', 7, 1, 'mantImp', '', 0),
(9, 'Categoria Productos', 'mant/categoria', 'M', 'mant', 8, 1, 'mantCat', '', 0),
(10, 'Tipos de Gastos', 'mant/tiposgasto', 'M', 'mant', 9, 1, 'mantTipGast', '', 0),
(11, 'Tipos de Pagos', 'mant/tipospago', 'M', 'mant', 10, 1, 'mantTipPag', '', 0),
(12, 'Tipos de Ingreso', 'mant/tiposingreso', 'M', 'mant', 11, 1, 'mantTipIng', '', 0),
(15, 'Gastos', '', 'G', 'gastos', 0, 3, 'gastos', 'fas fa-file-export', 0),
(16, 'Registrar', 'gastos/nuevo', 'M', 'gastos', 1, 3, 'gastNue', '', 0),
(18, 'Todos los gastos', 'gastos/administracion', 'M', 'gastos', 3, 3, 'gastTodos', '', 0),
(19, 'Ingresos', '', 'G', 'ingresos', 0, 4, 'ingresos', 'fas fa-file-import', 0),
(20, 'Registrar', 'ingresos/nuevo', 'M', 'ingresos', 1, 4, 'ingNue', '', 0),
(22, 'Todos los ingresos', 'ingresos/administracion', 'M', 'ingresos', 3, 4, 'ingTodos', '', 0),
(30, 'Parámetros Generales', 'mant/parametrosgenerales', 'M', 'mant', 12, 1, 'mantParGen', '', 0),
(49, 'Menú', '', 'G', 'mnu_res', 0, 11, 'mnu_res', 'fas fa-utensils', 0),
(50, 'Productos Menú', 'menu/productos', 'M', 'mnu_res', 1, 11, 'prod_mnu', '', 0),
(52, 'Menús', 'menu/menus', 'M', 'mnu_res', 2, 11, 'mnus', '', 0),
(53, 'Materia Prima', '', 'G', 'mt_prod', 0, 4, 'mt_prod', 'fas fa-utensils', 0),
(54, 'Materia Prima', 'materiaPrima/productos', 'M', 'mt_prod', 1, 11, 'mt_product', '', 0),
(55, 'Productos Externos', '', 'G', 'cod_ext', 0, 12, 'cod_ext', 'fas fa-utensils', 0),
(56, 'Inventarios', 'productoExterno/inventario/inventarios', 'M', 'cod_ext', 2, 12, 'prod_ext_inv', '', 0),
(57, 'Productos Externos', 'productoExterno/productos', 'M', 'cod_ext', 1, 12, 'prod_ext_prods', '', 0),
(58, 'Inventarios', 'materiaPrima/inventario/inventarios', 'M', 'mt_prod', 5, 12, 'mt_inv', '', 0),
(59, 'Facturación', '', 'G', 'fac', 0, 14, 'fac', 'fas fa-file-invoice', 0),
(60, 'POS', 'facturacion/pos', 'M', 'fac', 1, 14, 'facFac', NULL, 0),
(63, 'Pendientes aprobar', 'ingresos/pendientes', 'M', 'ingresos', 2, 4, 'ingPendApr', '', 0),
(64, 'Ordenes Lista Entregar', 'facturacion/ordenesEntrega', 'M', 'fac', 2, 13, 'ordList_cmds', '', 0),
(65, 'Ordenes En Preparación', 'facturacion/ordenesPreparacion', 'M', 'fac', 3, 13, 'ordList_prep', '', 0);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `bitacora_inicio_sesion`
--
ALTER TABLE `bitacora_inicio_sesion`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `bitacora_modificacion`
--
ALTER TABLE `bitacora_modificacion`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cat_unq01` (`codigo`);

--
-- Indices de la tabla `cierre_caja`
--
ALTER TABLE `cierre_caja`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cj_usuario_fk01` (`cajero`),
  ADD KEY `estado_fk_01` (`estado`),
  ADD KEY `suc_cierre_fk01` (`sucursal`);

--
-- Indices de la tabla `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `codigo_descuento`
--
ALTER TABLE `codigo_descuento`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cod_tipo_fk` (`tipo`);

--
-- Indices de la tabla `detalle_orden`
--
ALTER TABLE `detalle_orden`
  ADD PRIMARY KEY (`id`),
  ADD KEY `orden_fk01` (`orden`);

--
-- Indices de la tabla `extra_detalle_orden`
--
ALTER TABLE `extra_detalle_orden`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_orden_02` (`orden`),
  ADD KEY `fk_detalle_orden01` (`detalle`);

--
-- Indices de la tabla `extra_producto_externo`
--
ALTER TABLE `extra_producto_externo`
  ADD KEY `prod_fk02` (`producto`);

--
-- Indices de la tabla `extra_producto_menu`
--
ALTER TABLE `extra_producto_menu`
  ADD PRIMARY KEY (`id`),
  ADD KEY `prod_fk01` (`producto`);

--
-- Indices de la tabla `gasto`
--
ALTER TABLE `gasto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `gas_usuario_fk1` (`usuario`),
  ADD KEY `gas_proveedor_fk1` (`proveedor`),
  ADD KEY `gas_tipo_gasto_fk1` (`tipo_gasto`),
  ADD KEY `gas_tipo_pago_fk1` (`tipo_pago`);

--
-- Indices de la tabla `impuesto`
--
ALTER TABLE `impuesto`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `ingreso`
--
ALTER TABLE `ingreso`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ing_tipo_ingreso_fk1` (`tipo`),
  ADD KEY `ing_cliente_fk1` (`cliente`);

--
-- Indices de la tabla `log`
--
ALTER TABLE `log`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `materia_prima`
--
ALTER TABLE `materia_prima`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mnu_rol_fk01` (`rol`),
  ADD KEY `mnu_vista_fk01` (`vista`);

--
-- Indices de la tabla `mt_x_extra`
--
ALTER TABLE `mt_x_extra`
  ADD KEY `extra_fk013` (`materia_prima`);

--
-- Indices de la tabla `mt_x_producto`
--
ALTER TABLE `mt_x_producto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_mt_01` (`materia_prima`),
  ADD KEY `fk_prod_01` (`producto`);

--
-- Indices de la tabla `mt_x_sucursal`
--
ALTER TABLE `mt_x_sucursal`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `orden`
--
ALTER TABLE `orden`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_clinete01` (`cliente`),
  ADD KEY `fk_cajero_01` (`cajero`),
  ADD KEY `estado_fk01` (`estado`),
  ADD KEY `sucursal_fk01` (`sucursal`),
  ADD KEY `caja_fk01` (`cierre_caja`);

--
-- Indices de la tabla `panel_configuraciones`
--
ALTER TABLE `panel_configuraciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pc_usuario_fk01` (`usuario`);

--
-- Indices de la tabla `parametros_generales`
--
ALTER TABLE `parametros_generales`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `pe_x_sucursal`
--
ALTER TABLE `pe_x_sucursal`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pexs_sucursal_fk01` (`sucursal`),
  ADD KEY `pexs_pe_fk01` (`producto_externo`),
  ADD KEY `pexs_usuario_fk01` (`usuario_modifica`);

--
-- Indices de la tabla `pm_x_sucursal`
--
ALTER TABLE `pm_x_sucursal`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `producto_externo`
--
ALTER TABLE `producto_externo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pe_categoria_fk01` (`categoria`),
  ADD KEY `pe_impuiesto_fk01` (`impuesto`),
  ADD KEY `pe_proveedor_fk01` (`proveedor`);

--
-- Indices de la tabla `producto_menu`
--
ALTER TABLE `producto_menu`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pm_categoria_fk01` (`categoria`),
  ADD KEY `pm_impuesto_fk01` (`impuesto`);

--
-- Indices de la tabla `proveedor`
--
ALTER TABLE `proveedor`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `rol`
--
ALTER TABLE `rol`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `sis_clase`
--
ALTER TABLE `sis_clase`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `sis_estado`
--
ALTER TABLE `sis_estado`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `sis_parametro`
--
ALTER TABLE `sis_parametro`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `sis_tipo`
--
ALTER TABLE `sis_tipo`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `sucursal`
--
ALTER TABLE `sucursal`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tipo_gasto`
--
ALTER TABLE `tipo_gasto`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tipo_ingreso`
--
ALTER TABLE `tipo_ingreso`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tipo_pago`
--
ALTER TABLE `tipo_pago`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usu_sucursal_fk1` (`sucursal`),
  ADD KEY `usu_rol_fk1` (`rol`);

--
-- Indices de la tabla `vista`
--
ALTER TABLE `vista`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `bitacora_inicio_sesion`
--
ALTER TABLE `bitacora_inicio_sesion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `bitacora_modificacion`
--
ALTER TABLE `bitacora_modificacion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `categoria`
--
ALTER TABLE `categoria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cierre_caja`
--
ALTER TABLE `cierre_caja`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cliente`
--
ALTER TABLE `cliente`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `codigo_descuento`
--
ALTER TABLE `codigo_descuento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `detalle_orden`
--
ALTER TABLE `detalle_orden`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `extra_detalle_orden`
--
ALTER TABLE `extra_detalle_orden`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `extra_producto_menu`
--
ALTER TABLE `extra_producto_menu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `gasto`
--
ALTER TABLE `gasto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `impuesto`
--
ALTER TABLE `impuesto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `ingreso`
--
ALTER TABLE `ingreso`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `log`
--
ALTER TABLE `log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `materia_prima`
--
ALTER TABLE `materia_prima`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `menu`
--
ALTER TABLE `menu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=404;

--
-- AUTO_INCREMENT de la tabla `mt_x_producto`
--
ALTER TABLE `mt_x_producto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `mt_x_sucursal`
--
ALTER TABLE `mt_x_sucursal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `orden`
--
ALTER TABLE `orden`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `panel_configuraciones`
--
ALTER TABLE `panel_configuraciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT de la tabla `parametros_generales`
--
ALTER TABLE `parametros_generales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pe_x_sucursal`
--
ALTER TABLE `pe_x_sucursal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pm_x_sucursal`
--
ALTER TABLE `pm_x_sucursal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `producto_externo`
--
ALTER TABLE `producto_externo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `producto_menu`
--
ALTER TABLE `producto_menu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `proveedor`
--
ALTER TABLE `proveedor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `rol`
--
ALTER TABLE `rol`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT de la tabla `sis_clase`
--
ALTER TABLE `sis_clase`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `sis_estado`
--
ALTER TABLE `sis_estado`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `sis_parametro`
--
ALTER TABLE `sis_parametro`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `sis_tipo`
--
ALTER TABLE `sis_tipo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `sucursal`
--
ALTER TABLE `sucursal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `tipo_gasto`
--
ALTER TABLE `tipo_gasto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `tipo_ingreso`
--
ALTER TABLE `tipo_ingreso`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `tipo_pago`
--
ALTER TABLE `tipo_pago`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `vista`
--
ALTER TABLE `vista`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `cierre_caja`
--
ALTER TABLE `cierre_caja`
  ADD CONSTRAINT `cj_usuario_fk01` FOREIGN KEY (`cajero`) REFERENCES `usuario` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `estado_fk_01` FOREIGN KEY (`estado`) REFERENCES `sis_estado` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `suc_cierre_fk01` FOREIGN KEY (`sucursal`) REFERENCES `sucursal` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `codigo_descuento`
--
ALTER TABLE `codigo_descuento`
  ADD CONSTRAINT `cod_tipo_fk` FOREIGN KEY (`tipo`) REFERENCES `sis_tipo` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `detalle_orden`
--
ALTER TABLE `detalle_orden`
  ADD CONSTRAINT `orden_fk01` FOREIGN KEY (`orden`) REFERENCES `orden` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `extra_detalle_orden`
--
ALTER TABLE `extra_detalle_orden`
  ADD CONSTRAINT `fk_detalle_orden01` FOREIGN KEY (`detalle`) REFERENCES `detalle_orden` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_orden_02` FOREIGN KEY (`orden`) REFERENCES `orden` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `extra_producto_externo`
--
ALTER TABLE `extra_producto_externo`
  ADD CONSTRAINT `prod_fk02` FOREIGN KEY (`producto`) REFERENCES `producto_externo` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `extra_producto_menu`
--
ALTER TABLE `extra_producto_menu`
  ADD CONSTRAINT `prod_fk01` FOREIGN KEY (`producto`) REFERENCES `producto_menu` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `ingreso`
--
ALTER TABLE `ingreso`
  ADD CONSTRAINT `cod_tipo_fk56` FOREIGN KEY (`tipo`) REFERENCES `sis_tipo` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `mt_x_extra`
--
ALTER TABLE `mt_x_extra`
  ADD CONSTRAINT `extra_fk013` FOREIGN KEY (`materia_prima`) REFERENCES `materia_prima` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `mt_x_producto`
--
ALTER TABLE `mt_x_producto`
  ADD CONSTRAINT `fk_mt_01` FOREIGN KEY (`materia_prima`) REFERENCES `materia_prima` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_prod_01` FOREIGN KEY (`producto`) REFERENCES `producto_menu` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `orden`
--
ALTER TABLE `orden`
  ADD CONSTRAINT `caja_fk01` FOREIGN KEY (`cierre_caja`) REFERENCES `cierre_caja` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `estado_fk01` FOREIGN KEY (`estado`) REFERENCES `sis_estado` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_cajero_01` FOREIGN KEY (`cajero`) REFERENCES `usuario` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_clinete01` FOREIGN KEY (`cliente`) REFERENCES `cliente` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `sucursal_fk01` FOREIGN KEY (`sucursal`) REFERENCES `sucursal` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `pe_x_sucursal`
--
ALTER TABLE `pe_x_sucursal`
  ADD CONSTRAINT `pexs_pe_fk01` FOREIGN KEY (`producto_externo`) REFERENCES `producto_externo` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `pexs_sucursal_fk01` FOREIGN KEY (`sucursal`) REFERENCES `sucursal` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `pexs_usuario_fk01` FOREIGN KEY (`usuario_modifica`) REFERENCES `usuario` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `producto_externo`
--
ALTER TABLE `producto_externo`
  ADD CONSTRAINT `pe_categoria_fk01` FOREIGN KEY (`categoria`) REFERENCES `categoria` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `pe_impuiesto_fk01` FOREIGN KEY (`impuesto`) REFERENCES `impuesto` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `pe_proveedor_fk01` FOREIGN KEY (`proveedor`) REFERENCES `proveedor` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `producto_menu`
--
ALTER TABLE `producto_menu`
  ADD CONSTRAINT `pm_categoria_fk01` FOREIGN KEY (`categoria`) REFERENCES `categoria` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `pm_impuesto_fk01` FOREIGN KEY (`impuesto`) REFERENCES `impuesto` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
