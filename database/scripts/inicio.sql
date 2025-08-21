-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 21-08-2025 a las 17:14:23
-- Versión del servidor: 10.4.25-MariaDB
-- Versión de PHP: 7.4.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `el_amanecer`
--

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
-- Estructura de tabla para la tabla `bit_inv_producto_externo`
--

CREATE TABLE `bit_inv_producto_externo` (
  `id` int(11) NOT NULL,
  `usuario` int(11) NOT NULL,
  `detalle` varchar(10000) NOT NULL,
  `producto` int(11) DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `cantidad_anterior` double DEFAULT NULL,
  `cantidad_ajustada` double DEFAULT NULL,
  `cantidad_nueva` double DEFAULT NULL,
  `sucursal` int(11) NOT NULL,
  `devolucion` varchar(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bit_materia_prima`
--

CREATE TABLE `bit_materia_prima` (
  `id` int(11) NOT NULL,
  `usuario` int(11) NOT NULL,
  `detalle` varchar(10000) NOT NULL,
  `materia_prima` int(11) DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `cantidad_anterior` double DEFAULT NULL,
  `cantidad_ajuste` double DEFAULT NULL,
  `cantidad_nueva` double DEFAULT NULL,
  `sucursal` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria`
--

CREATE TABLE `categoria` (
  `id` int(11) NOT NULL,
  `categoria` varchar(30) NOT NULL,
  `estado` varchar(1) NOT NULL DEFAULT 'A',
  `codigo` varchar(9) NOT NULL,
  `logo` varchar(100) DEFAULT NULL,
  `url_imagen` varchar(1000) DEFAULT NULL,
  `posicion_menu` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `categoria`
--

INSERT INTO `categoria` (`id`, `categoria`, `estado`, `codigo`, `logo`, `url_imagen`, `posicion_menu`) VALUES
(3, 'CAFÉS Y BEBIDAS FRÍAS', 'A', 'BEBIDAS-1', NULL, NULL, 0),
(5, 'CAFÉS Y BEBIDAS CALIENTES', 'A', 'BEBIDAS-2', NULL, NULL, 1),
(6, 'DESAYUNOS', 'A', 'COCINA-1', NULL, NULL, 0),
(7, 'ALMUERZOS', 'A', 'COCINA-2', NULL, NULL, 0),
(8, 'SANDWICHES', 'A', 'COCINA-3', NULL, NULL, 0),
(9, 'CREPAS DULCES Y SALADAS', 'A', 'COCINA-4', NULL, NULL, 0),
(10, 'WAFFLES', 'A', 'COCINA-5', NULL, NULL, 0),
(11, 'BAGELS', 'I', 'COCINA-6', NULL, NULL, 0),
(12, 'HAMBURGUESAS', 'A', 'COCINA-7', NULL, NULL, 0),
(13, 'REPOSTERIA HOJALDRE', 'I', 'PANAD-1', NULL, NULL, 0),
(15, 'PAN SALADO', 'I', 'PANAD-3', NULL, NULL, 0),
(16, 'REPOSTERIA DANESA', 'I', 'PANAD-4', NULL, NULL, 0),
(17, 'TRENZA DULCE RELLENA', 'I', 'PANAD-5', NULL, NULL, 0),
(18, 'TRENZA SALADA RELLENA', 'I', 'PANAD-6', NULL, NULL, 0),
(19, 'REPOSTERIA ESPECIAL', 'I', 'PANAD-2', NULL, NULL, 0),
(20, 'PAQUETERIA', 'A', 'PANAD-7', NULL, NULL, 0),
(21, 'POSTRES', 'A', 'COCINA-8', NULL, NULL, 0),
(22, 'DOS PINOS', 'A', 'P. EXT-1', NULL, NULL, 0),
(23, 'PURO JERSEY', 'A', 'P.EXT-2', NULL, NULL, 0),
(24, 'EL MANANTIAL DEL CHIRRIPÓ', 'I', 'P.EXT-3', NULL, NULL, 0),
(25, 'PIPASA', 'A', 'P.EXT-4', NULL, NULL, 0),
(26, 'FEMSA', 'A', 'P.EXT-5', NULL, NULL, 0),
(27, 'EL ÁNGEL', 'A', 'P.EXT-6', NULL, NULL, 0),
(28, 'EL CERRO', 'A', 'P.EXT-7', NULL, NULL, 0),
(30, 'EXTRAS', 'A', 'EXTRAS-1', NULL, NULL, 0),
(31, 'CAFÉ EMPACADO', 'A', 'CAFÉ', NULL, NULL, 0),
(32, 'PROMO', 'A', 'PROMO', NULL, NULL, 0),
(33, 'BEBIDAS CALIENTES', 'A', 'Sinaí', NULL, NULL, 0),
(34, 'EMPAQUE', 'A', 'EMPAQUE', NULL, NULL, 0),
(35, 'PIZZAS', 'A', 'PIZZAS', NULL, NULL, 0),
(36, 'REPOSTERIA', 'I', 'BAKERY', NULL, NULL, 0),
(37, 'EXTERNOS', 'A', 'EXTERNOS', NULL, NULL, 0),
(38, 'ENSALADAS', 'A', '55', NULL, NULL, 0),
(39, 'PLATOS FUERTES', 'A', '89', NULL, NULL, 0),
(40, 'CREMAS', 'A', '90', NULL, NULL, 0),
(41, 'BOWLS', 'A', '91', NULL, NULL, 0),
(42, 'PASTAS', 'A', '93', NULL, NULL, 0),
(46, 'KOMBUCHAS', 'I', 'PZKOMB-1', NULL, NULL, 0),
(47, 'R. SALADO', 'A', 'BAK-1', NULL, NULL, 0),
(48, 'R. DULCE', 'A', 'BAK-2', NULL, NULL, 0),
(49, 'PAN BLANCO', 'A', 'BAK-3', NULL, NULL, 0),
(50, 'POSTRES', 'A', 'POST-PZ', NULL, NULL, 0);

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
(26, 'Cajero', 'CAJA', 'A'),
(41, 'COCINA', 'COPZ', 'A'),
(43, 'BEBIDAS', 'BB', 'A');

-- --------------------------------------------------------


--
-- Estructura de tabla para la tabla `sucursal`
--

CREATE TABLE `sucursal` (
  `id` int(11) NOT NULL,
  `descripcion` varchar(50) DEFAULT NULL,
  `estado` varchar(1) NOT NULL DEFAULT 'A',
  `cod_general` varchar(150) NOT NULL,
  `cont_ordenes` int(11) NOT NULL DEFAULT 0,
  `nombre_factura` varchar(250) DEFAULT NULL,
  `cedula_factura` varchar(50) DEFAULT NULL,
  `correo_factura` varchar(250) NOT NULL,
  `factura_iva` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `sucursal`
--

INSERT INTO `sucursal` (`id`, `descripcion`, `estado`, `cod_general`, `cont_ordenes`, `nombre_factura`, `cedula_factura`, `correo_factura`, `factura_iva`) VALUES
(1, 'Morazán', 'A', 'PZ', 34, 'Jonathan Fonseca Jimenez', '116390363', 'ELAMANECERAD@GMAIL.com', 1);

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
  `estado` int(11) DEFAULT NULL,
  `token_auth` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id`, `nombre`, `ape1`, `ape2`, `cedula`, `fecha_nacimiento`, `fecha_ingreso`, `correo`, `telefono`, `usuario`, `contra`, `sucursal`, `rol`, `estado`, `token_auth`) VALUES
(15, 'Mario A', 'Flores', 'Solis', '116990433', '1998-01-25', '2020-09-13 05:31:34', 'mario.flores251998@gmail.com', '7056418', 'mflores', '81dc9bdb52d04dc20036dbd8313ed055', 1, 25, 1, 'RxnBo0E67MBCeipxnoCeWP6gwEhBtDMEwdoAAvnf6YtHhXYWye');

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
(49, 'Productos Menú', '', 'G', 'mnu_res', 0, 11, 'mnu_res', 'fas fa-utensils', 0),
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
(66, 'Códigos Promocionales', 'mant/codPromocion', 'M', 'mant', 15, 1, 'mantCodProm', '', 0),
(67, 'Admin Ordenes ', 'facturacion/ordenesAdmin', 'M', 'fac', 4, 13, 'adm_ord', '', 0),
(68, 'Promociones Productos', 'mant/grupoPromocion', 'M', 'mant', 16, 1, 'mantPromProd', '', 0),
(69, 'Usuario Externo', '', 'G', 'usuExt', 0, 8, NULL, 'fa fa-male', 0),
(71, 'Menú externo', 'usuarioExterno/menu', 'M', 'usuExt', 1, 8, 'usuExtMnu', '', 0),
(73, 'Informes', '', 'G', 'informes', 0, 6, 'informes', 'fas fa-chart-line', 0),
(74, 'Resumen Contable', 'informes/resumencontable', 'M', 'informes', 1, 6, 'resCont', '', 0),
(75, 'Ventas por hora', 'informes/ventaXhora', 'M', 'informes', 12, 6, 'ventaXhora', '', 0),
(76, 'Ventas producto', 'informes/ventaGenProductos', 'M', 'informes', 13, 6, 'ventaGenProductos', '', 0),
(77, 'Mov Inv Productos externos', 'informes/movInvProductoExterno', 'M', 'informes', 15, 6, 'movInvProductoExterno', '', 0),
(78, 'Mov Consumo Materia Prima', 'informes/movConMateriaPrima', 'M', 'informes', 16, 6, 'movConMateriaPrima', '', 0),
(79, 'Consumo Materia Prima', 'informes/conMateriaPrima', 'M', 'informes', 17, 6, 'conMateriaPrima', '', 0),
(80, 'Crear Toma Física', 'materiaPrima/inventario/tomaFisica', 'M', 'mt_prod', 6, 12, 'mt_tomaFis', '', 0),
(81, 'Entregas', '', 'G', 'entregas', 0, 15, 'entregas', 'fas fa-truck', 0),
(82, 'Entregas ', 'entregas/entregasPendientes', 'M', 'entregas', 1, 12, 'entregas_pend', '', 0),
(83, 'Factura Electrónica', '', 'G', 'fes', 0, 16, 'fes', 'fas fa-landmark', 0),
(84, 'Facturas', 'fe/facturas', 'M', 'fes', 1, 16, 'fe_fes', '', 0),
(85, 'Gestionar Comandas ', '', 'G', 'comandasGen', 0, 17, 'comandasGen', 'fas fa-chalkboard', 0),
(86, 'Administrar Comandas', 'comandar/admin', 'M', 'comandasGen', 1, 17, 'comandasAdmin', '', 0),
(87, 'Mobiliario', '', 'G', 'mobiliarioGen', 0, 18, 'mobiliarioGen', 'fas fa-table', 0),
(88, 'Administrar Mesas', 'mobiliario/mesas/admin', 'M', 'mobiliarioGen', 1, 18, 'mesasAdmin', '', 0),
(89, 'Comandas Preparación', '', 'G', 'comandasPrep', 0, 19, 'comandasPrep', 'far fa-calendar', 0),
(90, 'Comanda General', 'comandas/preparacion/comandaGen', 'M', 'comandasPrep', 1, 19, 'comandaPrep', '', 0);


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
  `fecha_cierra` datetime DEFAULT NULL,
  `efectivo_reportado` double DEFAULT 0
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
  `estado` varchar(1000) NOT NULL DEFAULT 'A',
  `fecha_nacimiento` date DEFAULT NULL,
  `fecha_registro` date NOT NULL DEFAULT current_timestamp(),
  `contra` varchar(500) NOT NULL,
  `apellidos` varchar(100) DEFAULT NULL,
  `codigo_verificacion` varchar(50) DEFAULT NULL,
  `nueva_contra` varchar(1000) DEFAULT NULL
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
  `activo` int(11) NOT NULL DEFAULT 0,
  `cant_codigos` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comanda`
--

CREATE TABLE `comanda` (
  `id` int(11) NOT NULL,
  `sucursal` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
  `total_extras` double DEFAULT 0,
  `cod_promocion` varchar(100) DEFAULT NULL,
  `dsc_promocion` varchar(1500) DEFAULT NULL,
  `comanda` int(11) DEFAULT NULL,
  `cantidad_preparada` int(11) DEFAULT NULL,
  `cantidad_pagada` int(11) NOT NULL DEFAULT 0,
  `monto_servicio` double NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_orden_comanda`
--

CREATE TABLE `detalle_orden_comanda` (
  `id` int(11) NOT NULL,
  `orden_comanda` int(11) NOT NULL,
  `detalle_orden` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `fecha_ingreso` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_fin` datetime DEFAULT NULL,
  `usuario_gestion` int(11) DEFAULT NULL,
  `comanda` int(11) DEFAULT NULL,
  `preparado` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_pago_orden`
--

CREATE TABLE `detalle_pago_orden` (
  `id` int(11) NOT NULL,
  `pago_orden` int(11) NOT NULL,
  `detalle_orden` int(11) DEFAULT NULL,
  `cantidad_pagada` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `descuento` decimal(10,2) DEFAULT 0.00,
  `iva` decimal(10,2) DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL,
  `mto_impuesto_servicio` double NOT NULL DEFAULT 0,
  `dsc_linea` varchar(550) NOT NULL DEFAULT 'Producto'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `det_grupo_promocion`
--

CREATE TABLE `det_grupo_promocion` (
  `id` int(11) NOT NULL,
  `producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `grupo_promocion` int(11) NOT NULL,
  `tipo` varchar(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `entrega_orden`
--

CREATE TABLE `entrega_orden` (
  `id` int(11) NOT NULL,
  `orden` int(11) NOT NULL,
  `precio` double NOT NULL,
  `descripcion_lugar` varchar(3000) NOT NULL,
  `estado` int(11) NOT NULL,
  `contacto` varchar(1500) NOT NULL,
  `encargado` int(11) DEFAULT NULL,
  `url_ubicacion` varchar(1000) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `est_entrega_orden`
--

CREATE TABLE `est_entrega_orden` (
  `id` int(11) NOT NULL,
  `entrega_orden` int(11) NOT NULL,
  `estado` int(11) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `usuario` int(250) NOT NULL,
  `descripcion` varchar(1500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `est_orden`
--

CREATE TABLE `est_orden` (
  `id` int(11) NOT NULL,
  `orden` int(11) NOT NULL,
  `estado` int(11) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `usuario` int(11) NOT NULL,
  `descripcion` varchar(2500) NOT NULL
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
  `total` double DEFAULT NULL,
  `extra` int(11) DEFAULT NULL,
  `id_producto` int(11) DEFAULT NULL,
  `tipo_producto` varchar(15) DEFAULT NULL
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
  `multiple` int(11) NOT NULL DEFAULT 0,
  `materia_prima` int(11) DEFAULT NULL,
  `cant_mp` double DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fe_info`
--

CREATE TABLE `fe_info` (
  `id` int(11) NOT NULL,
  `orden` int(11) NOT NULL,
  `cedula` varchar(25) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `correo` varchar(150) NOT NULL,
  `estado` int(11) NOT NULL,
  `num_comprobante` varchar(500) DEFAULT NULL,
  `id_pago` int(11) DEFAULT NULL
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
  `proveedor` int(11) DEFAULT NULL,
  `tipo_documento` varchar(1) NOT NULL DEFAULT 'F',
  `tipo_pago` int(11) NOT NULL,
  `tipo_gasto` int(11) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `observacion` varchar(150) DEFAULT NULL,
  `ingreso` int(11) DEFAULT NULL,
  `aprobado` varchar(1) DEFAULT 'N',
  `sucursal` varchar(50) NOT NULL,
  `url_factura` varchar(300) DEFAULT NULL,
  `estado` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `grupo_promocion`
--

CREATE TABLE `grupo_promocion` (
  `id` int(11) NOT NULL,
  `descripcion` varchar(5000) NOT NULL,
  `precio` int(11) NOT NULL,
  `estado` int(1) NOT NULL DEFAULT 1,
  `categoria` int(11) DEFAULT NULL,
  `imagen` varchar(5000) DEFAULT NULL
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
  `cliente` int(11) DEFAULT NULL,
  `descripcion` varchar(300) DEFAULT NULL,
  `estado` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `log`
--

CREATE TABLE `log` (
  `id` int(11) NOT NULL,
  `descripcion` blob NOT NULL,
  `documento` varchar(1000) DEFAULT NULL
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
  `activo` int(11) NOT NULL DEFAULT 1,
  `cant_min_deseada` double DEFAULT NULL
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
(2180, 26, 64),
(2181, 26, 60),
(2182, 26, 59),
(2183, 41, 90),
(2184, 41, 89),
(2185, 43, 90),
(2186, 43, 89),
(2187, 25, 2),
(2188, 25, 3),
(2189, 25, 4),
(2190, 25, 5),
(2191, 25, 8),
(2192, 25, 9),
(2193, 25, 10),
(2194, 25, 11),
(2195, 25, 12),
(2196, 25, 30),
(2197, 25, 66),
(2198, 25, 68),
(2199, 25, 16),
(2200, 25, 18),
(2201, 25, 20),
(2202, 25, 63),
(2203, 25, 22),
(2204, 25, 54),
(2205, 25, 58),
(2206, 25, 80),
(2207, 25, 74),
(2208, 25, 75),
(2209, 25, 76),
(2210, 25, 77),
(2211, 25, 78),
(2212, 25, 79),
(2213, 25, 71),
(2214, 25, 50),
(2215, 25, 52),
(2216, 25, 57),
(2217, 25, 56),
(2218, 25, 64),
(2219, 25, 67),
(2220, 25, 60),
(2221, 25, 82),
(2222, 25, 84),
(2223, 25, 86),
(2224, 25, 88),
(2225, 25, 90),
(2226, 25, 1),
(2227, 25, 15),
(2228, 25, 19),
(2229, 25, 53),
(2230, 25, 73),
(2231, 25, 69),
(2232, 25, 49),
(2233, 25, 55),
(2234, 25, 59),
(2235, 25, 81),
(2236, 25, 83),
(2237, 25, 85),
(2238, 25, 87),
(2239, 25, 89);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mesa`
--

CREATE TABLE `mesa` (
  `id` int(11) NOT NULL,
  `sucursal` int(11) NOT NULL,
  `numero_mesa` varchar(15) NOT NULL,
  `capacidad` int(11) NOT NULL DEFAULT 0,
  `estado` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
-- Estructura de tabla para la tabla `mt_x_producto_ext`
--

CREATE TABLE `mt_x_producto_ext` (
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
  `monto_envio` double NOT NULL,
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
  `pagado` int(11) NOT NULL DEFAULT 1,
  `ind_requiere_envio` int(11) NOT NULL DEFAULT 0,
  `info_descuento` varchar(1000) DEFAULT NULL,
  `mesa` int(11) DEFAULT NULL,
  `mto_pagado` double DEFAULT 0,
  `mto_impuesto_servicio` double NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `orden_comanda`
--

CREATE TABLE `orden_comanda` (
  `id` int(11) NOT NULL,
  `orden` int(11) NOT NULL,
  `num_comanda` varchar(50) DEFAULT NULL,
  `fecha_inicio` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_fin` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pago_orden`
--

CREATE TABLE `pago_orden` (
  `id` int(11) NOT NULL,
  `orden` int(20) NOT NULL,
  `nombre_cliente` varchar(255) NOT NULL,
  `monto_tarjeta` decimal(10,2) DEFAULT 0.00,
  `monto_efectivo` decimal(10,2) DEFAULT 0.00,
  `monto_sinpe` decimal(10,2) DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `iva` decimal(10,2) DEFAULT 0.00,
  `descuento` decimal(10,2) DEFAULT 0.00,
  `impuesto_servicio` decimal(10,2) DEFAULT 0.00,
  `fecha_pago` datetime NOT NULL,
  `cod_promocion` varchar(50) DEFAULT NULL
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
(7, 1, 1, 'white', 1, 1, 15);

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

--
-- Volcado de datos para la tabla `parametros_generales`
--

INSERT INTO `parametros_generales` (`id`, `porcentaje_banco`, `tiempo_refresco_monitor_movimientos`, `inicio_mes_panaderia`, `inicio_mes_cafeteria`) VALUES
(1, 2, 1, 1, 1);

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
  `usuario_modifica` int(11) DEFAULT NULL,
  `comanda` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pm_x_sucursal`
--

CREATE TABLE `pm_x_sucursal` (
  `id` int(11) NOT NULL,
  `sucursal` int(11) NOT NULL,
  `producto_menu` int(11) NOT NULL,
  `comanda` int(11) DEFAULT NULL
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
  `descripcion` varchar(500) DEFAULT NULL,
  `url_imagen` varchar(1000) DEFAULT NULL,
  `posicion_menu` int(11) NOT NULL DEFAULT 0
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
  `tipo_comanda` varchar(3) DEFAULT NULL COMMENT 'BE : BEBIDA , CO : COMIDA',
  `producto_menucol` varchar(45) DEFAULT NULL,
  `url_imagen` varchar(1000) DEFAULT NULL,
  `receta` text DEFAULT '',
  `posicion_menu` int(11) NOT NULL DEFAULT 0
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
(4, 'Tipos Ingreso', 'GEN_INGRESOS'),
(5, 'Estado usuario cliente', 'CLI_EST_USUARIO'),
(6, 'Estados Entregas de Orden', 'EST_ENTREGAS_ORDEN'),
(7, 'Estados Facturas electrónicas', 'EST_FE_ORDEN'),
(8, 'Estado de usuario', 'est_user'),
(9, 'Estados de Gastos', 'EST_GASTOS_GEN'),
(10, 'Estados de Ingresos Contables', 'INGRESOS_EST'),
(11, 'Estados de Mesas', 'EST_MESAS');

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
(8, 'Entregada', 3, 'ORD_ENTREGADA'),
(9, 'Orden en preparación', 6, 'ENTREGA_PREPARACION_PEND'),
(10, 'Orden preparada, empacando pedido', 6, 'ENTREGA_PEND_SALIDA_LOCAL'),
(11, 'Entrega terminada', 6, 'ENTREGA_TERMINADA'),
(12, 'Entrega en camino al destino', 6, 'ENTREGA_EN_RUTA'),
(13, 'Factura electrónica Pendiente', 7, 'FE_ORDEN_PEND'),
(14, 'Factura electrónica Envíada', 7, 'FE_ORDEN_ENVIADA'),
(15, 'Factura electrónica Anulada', 7, 'FE_ORDEN_ANULADA'),
(16, 'Usuario Activo', 8, 'USU_ACT'),
(17, 'Usuario Inactivo', 8, 'USU_INACTIVO'),
(18, 'Aprobado', 9, 'EST_GASTO_APB'),
(19, 'Eliminado', 9, 'EST_GASTO_ELIMINADO'),
(20, 'Aprobado', 10, 'ING_EST_APROBADO'),
(21, 'Rechazados', 10, 'ING_EST_RECHAZADO'),
(22, 'Eliminados', 10, 'ING_EST_ELIMINADO'),
(23, 'Pendiente Aprobar', 10, 'ING_PEND_APB'),
(24, 'Disponible', 11, 'MESA_DISPONIBLE');

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
(1, 'Monto correspondiente a fondo de plata con el que inicia la caja', '30000', 'MTO_FONDO_INI_CAJA'),
(2, 'Correo usado para enviar notificaciones a clientes', 'admin@coffeetogocr.com', 'CORREO_ENVIO_NOT_CLIENTE'),
(3, 'Asunto que aparecera en el correo de verificacion de registro de usuario', 'Verificación cuenta', 'ASUNTO_VERIFICACION_CLIENTE'),
(4, 'Nombre del usuario que envia correos de notificicacion a clientes', 'COFFEE TO GO', 'NOMBRE_ENVIO_NOT_CLIENTE'),
(5, 'Correos a los que se les enviara el reporte de consumo diario, generalmente administradores del negocio (Separados por ,)', 'mario.flores251998@gmail.com,scarranzagarita30@gmail.com,juvargas018@hotmail.com', 'CORREOS_REP_CONSUMO_GEN'),
(6, 'Asunto que aparecerá en el correo de reporte de consumo general', 'Reporte de consumo diario', 'ASUNTO_REP_CONSUMO_GEN'),
(7, 'Asunto que aparecera en el correo de restauracion contraseña cliente', 'Nueva contraseña de ingreso', 'ASUNTO_REST_PSWD_CLIENTE');

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
(1, 'Administración', 'A');

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
(7, 'Cierre Caja', 'A', 'ING_CIERRE_CAJA');

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
(2, 'Tarjeta', 'A'),
(3, 'pryueba nuebo', 'I'),
(4, 'Sinpe', 'A');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `toma_fisica`
--

CREATE TABLE `toma_fisica` (
  `id` int(11) NOT NULL,
  `materia_prima` int(11) NOT NULL,
  `dsc_materia_prima` varchar(1500) NOT NULL,
  `cantidad_sistema` double NOT NULL,
  `cantidad_usuario` double NOT NULL,
  `observaciones` varchar(5000) NOT NULL,
  `usuario` int(11) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `sucursal` int(11) NOT NULL,
  `cantidad_ajuste` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

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
-- Indices de la tabla `bit_inv_producto_externo`
--
ALTER TABLE `bit_inv_producto_externo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_b_usu01` (`usuario`),
  ADD KEY `fk_prod_b_01` (`producto`),
  ADD KEY `fk_suc_b_01` (`sucursal`);

--
-- Indices de la tabla `bit_materia_prima`
--
ALTER TABLE `bit_materia_prima`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usu_bit_mp_fk01` (`usuario`),
  ADD KEY `mp_bit_mp_fk01` (`materia_prima`),
  ADD KEY `suc_b_02` (`sucursal`);

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
-- Indices de la tabla `comanda`
--
ALTER TABLE `comanda`
  ADD PRIMARY KEY (`id`),
  ADD KEY `comanda_fk01` (`sucursal`);

--
-- Indices de la tabla `detalle_orden`
--
ALTER TABLE `detalle_orden`
  ADD PRIMARY KEY (`id`),
  ADD KEY `orden_fk01` (`orden`),
  ADD KEY `detalle_orden_fk01` (`comanda`);

--
-- Indices de la tabla `detalle_orden_comanda`
--
ALTER TABLE `detalle_orden_comanda`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `detalle_pago_orden`
--
ALTER TABLE `detalle_pago_orden`
  ADD PRIMARY KEY (`id`),
  ADD KEY `det_pago_orden_fk01` (`detalle_orden`),
  ADD KEY `det_pago_orden_fk02` (`pago_orden`);

--
-- Indices de la tabla `det_grupo_promocion`
--
ALTER TABLE `det_grupo_promocion`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `entrega_orden`
--
ALTER TABLE `entrega_orden`
  ADD PRIMARY KEY (`id`),
  ADD KEY `orden_fk023` (`orden`),
  ADD KEY `estado_fk23` (`estado`),
  ADD KEY `usu_enc_fk01` (`encargado`);

--
-- Indices de la tabla `est_entrega_orden`
--
ALTER TABLE `est_entrega_orden`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_entrega_orden_01` (`entrega_orden`),
  ADD KEY `fk_entrega_orden_02` (`usuario`);

--
-- Indices de la tabla `est_orden`
--
ALTER TABLE `est_orden`
  ADD PRIMARY KEY (`id`),
  ADD KEY `est_orden_fk01` (`orden`),
  ADD KEY `est_orden_fk02` (`usuario`);

--
-- Indices de la tabla `extra_detalle_orden`
--
ALTER TABLE `extra_detalle_orden`
  ADD PRIMARY KEY (`id`);

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
-- Indices de la tabla `fe_info`
--
ALTER TABLE `fe_info`
  ADD PRIMARY KEY (`id`),
  ADD KEY `orden_fk04` (`orden`),
  ADD KEY `estado_Fe_fk01` (`estado`);

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
-- Indices de la tabla `grupo_promocion`
--
ALTER TABLE `grupo_promocion`
  ADD PRIMARY KEY (`id`);

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
  ADD KEY `ing_cliente_fk1` (`cliente`),
  ADD KEY `ingreso_fk01` (`estado`);

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
-- Indices de la tabla `mesa`
--
ALTER TABLE `mesa`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mesa_fk01` (`sucursal`),
  ADD KEY `mesa_fk02` (`estado`);

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
-- Indices de la tabla `mt_x_producto_ext`
--
ALTER TABLE `mt_x_producto_ext`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_mat_prim_01` (`materia_prima`),
  ADD KEY `fk_prd_Ext_01` (`producto`);

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
  ADD KEY `caja_fk01` (`cierre_caja`),
  ADD KEY `orden_fk05` (`mesa`);

--
-- Indices de la tabla `orden_comanda`
--
ALTER TABLE `orden_comanda`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `pago_orden`
--
ALTER TABLE `pago_orden`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pago_orden_fk01` (`orden`);

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
  ADD KEY `pexs_usuario_fk01` (`usuario_modifica`),
  ADD KEY `pe_fk01` (`comanda`);

--
-- Indices de la tabla `pm_x_sucursal`
--
ALTER TABLE `pm_x_sucursal`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pm_fk01` (`comanda`);

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
-- Indices de la tabla `toma_fisica`
--
ALTER TABLE `toma_fisica`
  ADD PRIMARY KEY (`id`),
  ADD KEY `suc_fk_tf_01` (`sucursal`),
  ADD KEY `usu_fk_tf_01` (`usuario`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usu_sucursal_fk1` (`sucursal`),
  ADD KEY `usu_rol_fk1` (`rol`),
  ADD KEY `usuario_fk01` (`estado`);

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
-- AUTO_INCREMENT de la tabla `bit_inv_producto_externo`
--
ALTER TABLE `bit_inv_producto_externo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `bit_materia_prima`
--
ALTER TABLE `bit_materia_prima`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `categoria`
--
ALTER TABLE `categoria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `comanda`
--
ALTER TABLE `comanda`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalle_orden`
--
ALTER TABLE `detalle_orden`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalle_orden_comanda`
--
ALTER TABLE `detalle_orden_comanda`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalle_pago_orden`
--
ALTER TABLE `detalle_pago_orden`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `det_grupo_promocion`
--
ALTER TABLE `det_grupo_promocion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `entrega_orden`
--
ALTER TABLE `entrega_orden`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `est_entrega_orden`
--
ALTER TABLE `est_entrega_orden`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `est_orden`
--
ALTER TABLE `est_orden`
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
-- AUTO_INCREMENT de la tabla `fe_info`
--
ALTER TABLE `fe_info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `gasto`
--
ALTER TABLE `gasto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `grupo_promocion`
--
ALTER TABLE `grupo_promocion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `impuesto`
--
ALTER TABLE `impuesto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2240;

--
-- AUTO_INCREMENT de la tabla `mesa`
--
ALTER TABLE `mesa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `mt_x_producto`
--
ALTER TABLE `mt_x_producto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `mt_x_producto_ext`
--
ALTER TABLE `mt_x_producto_ext`
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
-- AUTO_INCREMENT de la tabla `orden_comanda`
--
ALTER TABLE `orden_comanda`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pago_orden`
--
ALTER TABLE `pago_orden`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `panel_configuraciones`
--
ALTER TABLE `panel_configuraciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT de la tabla `parametros_generales`
--
ALTER TABLE `parametros_generales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT de la tabla `sis_clase`
--
ALTER TABLE `sis_clase`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `sis_estado`
--
ALTER TABLE `sis_estado`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `sis_parametro`
--
ALTER TABLE `sis_parametro`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `tipo_ingreso`
--
ALTER TABLE `tipo_ingreso`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `tipo_pago`
--
ALTER TABLE `tipo_pago`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `toma_fisica`
--
ALTER TABLE `toma_fisica`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT de la tabla `vista`
--
ALTER TABLE `vista`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `bit_inv_producto_externo`
--
ALTER TABLE `bit_inv_producto_externo`
  ADD CONSTRAINT `fk_b_usu01` FOREIGN KEY (`usuario`) REFERENCES `usuario` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_prod_b_01` FOREIGN KEY (`producto`) REFERENCES `producto_externo` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_suc_b_01` FOREIGN KEY (`sucursal`) REFERENCES `sucursal` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `bit_materia_prima`
--
ALTER TABLE `bit_materia_prima`
  ADD CONSTRAINT `mp_bit_mp_fk01` FOREIGN KEY (`materia_prima`) REFERENCES `materia_prima` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `suc_b_02` FOREIGN KEY (`sucursal`) REFERENCES `sucursal` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `usu_bit_mp_fk01` FOREIGN KEY (`usuario`) REFERENCES `usuario` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

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
-- Filtros para la tabla `comanda`
--
ALTER TABLE `comanda`
  ADD CONSTRAINT `comanda_fk01` FOREIGN KEY (`sucursal`) REFERENCES `sucursal` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `detalle_orden`
--
ALTER TABLE `detalle_orden`
  ADD CONSTRAINT `orden_fk01` FOREIGN KEY (`orden`) REFERENCES `orden` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `detalle_pago_orden`
--
ALTER TABLE `detalle_pago_orden`
  ADD CONSTRAINT `det_pago_orden_fk01` FOREIGN KEY (`detalle_orden`) REFERENCES `detalle_orden` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `det_pago_orden_fk02` FOREIGN KEY (`pago_orden`) REFERENCES `pago_orden` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `entrega_orden`
--
ALTER TABLE `entrega_orden`
  ADD CONSTRAINT `estado_fk23` FOREIGN KEY (`estado`) REFERENCES `sis_estado` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `orden_fk023` FOREIGN KEY (`orden`) REFERENCES `orden` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `usu_enc_fk01` FOREIGN KEY (`encargado`) REFERENCES `usuario` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `est_entrega_orden`
--
ALTER TABLE `est_entrega_orden`
  ADD CONSTRAINT `fk_entrega_orden_01` FOREIGN KEY (`entrega_orden`) REFERENCES `entrega_orden` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_entrega_orden_02` FOREIGN KEY (`usuario`) REFERENCES `usuario` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `est_orden`
--
ALTER TABLE `est_orden`
  ADD CONSTRAINT `est_orden_fk01` FOREIGN KEY (`orden`) REFERENCES `orden` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `est_orden_fk02` FOREIGN KEY (`usuario`) REFERENCES `usuario` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

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
-- Filtros para la tabla `fe_info`
--
ALTER TABLE `fe_info`
  ADD CONSTRAINT `estado_Fe_fk01` FOREIGN KEY (`estado`) REFERENCES `sis_estado` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `orden_fk04` FOREIGN KEY (`orden`) REFERENCES `orden` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `ingreso`
--
ALTER TABLE `ingreso`
  ADD CONSTRAINT `cod_tipo_fk56` FOREIGN KEY (`tipo`) REFERENCES `tipo_ingreso` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `ingreso_fk01` FOREIGN KEY (`estado`) REFERENCES `sis_estado` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `mesa`
--
ALTER TABLE `mesa`
  ADD CONSTRAINT `mesa_fk01` FOREIGN KEY (`sucursal`) REFERENCES `sucursal` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `mesa_fk02` FOREIGN KEY (`estado`) REFERENCES `sis_estado` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

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
-- Filtros para la tabla `mt_x_producto_ext`
--
ALTER TABLE `mt_x_producto_ext`
  ADD CONSTRAINT `fk_mat_prim_01` FOREIGN KEY (`materia_prima`) REFERENCES `materia_prima` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_prd_Ext_01` FOREIGN KEY (`producto`) REFERENCES `producto_externo` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `orden`
--
ALTER TABLE `orden`
  ADD CONSTRAINT `caja_fk01` FOREIGN KEY (`cierre_caja`) REFERENCES `cierre_caja` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `estado_fk01` FOREIGN KEY (`estado`) REFERENCES `sis_estado` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_cajero_01` FOREIGN KEY (`cajero`) REFERENCES `usuario` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_clinete01` FOREIGN KEY (`cliente`) REFERENCES `cliente` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `orden_fk05` FOREIGN KEY (`mesa`) REFERENCES `mesa` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `sucursal_fk01` FOREIGN KEY (`sucursal`) REFERENCES `sucursal` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `pago_orden`
--
ALTER TABLE `pago_orden`
  ADD CONSTRAINT `pago_orden_fk01` FOREIGN KEY (`orden`) REFERENCES `orden` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `pe_x_sucursal`
--
ALTER TABLE `pe_x_sucursal`
  ADD CONSTRAINT `pe_fk01` FOREIGN KEY (`comanda`) REFERENCES `comanda` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `pexs_pe_fk01` FOREIGN KEY (`producto_externo`) REFERENCES `producto_externo` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `pexs_sucursal_fk01` FOREIGN KEY (`sucursal`) REFERENCES `sucursal` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `pexs_usuario_fk01` FOREIGN KEY (`usuario_modifica`) REFERENCES `usuario` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `pm_x_sucursal`
--
ALTER TABLE `pm_x_sucursal`
  ADD CONSTRAINT `pm_fk01` FOREIGN KEY (`comanda`) REFERENCES `comanda` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

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

--
-- Filtros para la tabla `toma_fisica`
--
ALTER TABLE `toma_fisica`
  ADD CONSTRAINT `suc_fk_tf_01` FOREIGN KEY (`sucursal`) REFERENCES `sucursal` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `usu_fk_tf_01` FOREIGN KEY (`usuario`) REFERENCES `usuario` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `usuario_fk01` FOREIGN KEY (`estado`) REFERENCES `sis_estado` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
