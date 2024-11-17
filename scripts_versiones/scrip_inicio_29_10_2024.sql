-- MySQL dump 10.19  Distrib 10.3.39-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: my_restaurant
-- ------------------------------------------------------
-- Server version	10.3.39-MariaDB-0ubuntu0.20.04.2

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `bit_inv_producto_externo`
--

DROP TABLE IF EXISTS `bit_inv_producto_externo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bit_inv_producto_externo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario` int(11) NOT NULL,
  `detalle` varchar(10000) NOT NULL,
  `producto` int(11) DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `cantidad_anterior` double DEFAULT NULL,
  `cantidad_ajustada` double DEFAULT NULL,
  `cantidad_nueva` double DEFAULT NULL,
  `sucursal` int(11) NOT NULL,
  `devolucion` varchar(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_b_usu01` (`usuario`),
  KEY `fk_prod_b_01` (`producto`),
  KEY `fk_suc_b_01` (`sucursal`),
  CONSTRAINT `fk_b_usu01` FOREIGN KEY (`usuario`) REFERENCES `usuario` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_prod_b_01` FOREIGN KEY (`producto`) REFERENCES `producto_externo` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_suc_b_01` FOREIGN KEY (`sucursal`) REFERENCES `sucursal` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bit_inv_producto_externo`
--

LOCK TABLES `bit_inv_producto_externo` WRITE;
/*!40000 ALTER TABLE `bit_inv_producto_externo` DISABLE KEYS */;
/*!40000 ALTER TABLE `bit_inv_producto_externo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bit_materia_prima`
--

DROP TABLE IF EXISTS `bit_materia_prima`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bit_materia_prima` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario` int(11) NOT NULL,
  `detalle` varchar(10000) NOT NULL,
  `materia_prima` int(11) DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `cantidad_anterior` double DEFAULT NULL,
  `cantidad_ajuste` double DEFAULT NULL,
  `cantidad_nueva` double DEFAULT NULL,
  `sucursal` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `usu_bit_mp_fk01` (`usuario`),
  KEY `mp_bit_mp_fk01` (`materia_prima`),
  KEY `suc_b_02` (`sucursal`),
  CONSTRAINT `mp_bit_mp_fk01` FOREIGN KEY (`materia_prima`) REFERENCES `materia_prima` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `suc_b_02` FOREIGN KEY (`sucursal`) REFERENCES `sucursal` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `usu_bit_mp_fk01` FOREIGN KEY (`usuario`) REFERENCES `usuario` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bit_materia_prima`
--

LOCK TABLES `bit_materia_prima` WRITE;
/*!40000 ALTER TABLE `bit_materia_prima` DISABLE KEYS */;
/*!40000 ALTER TABLE `bit_materia_prima` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bitacora_inicio_sesion`
--

DROP TABLE IF EXISTS `bitacora_inicio_sesion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bitacora_inicio_sesion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario` varchar(25) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `estado` varchar(15) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bitacora_inicio_sesion`
--

LOCK TABLES `bitacora_inicio_sesion` WRITE;
/*!40000 ALTER TABLE `bitacora_inicio_sesion` DISABLE KEYS */;
/*!40000 ALTER TABLE `bitacora_inicio_sesion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bitacora_modificacion`
--

DROP TABLE IF EXISTS `bitacora_modificacion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bitacora_modificacion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario` varchar(50) NOT NULL,
  `fecha` datetime NOT NULL,
  `tabla` varchar(50) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `id_entidad` int(11) NOT NULL,
  `total` double DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bitacora_modificacion`
--

LOCK TABLES `bitacora_modificacion` WRITE;
/*!40000 ALTER TABLE `bitacora_modificacion` DISABLE KEYS */;
/*!40000 ALTER TABLE `bitacora_modificacion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categoria`
--

DROP TABLE IF EXISTS `categoria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categoria` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `categoria` varchar(30) NOT NULL,
  `estado` varchar(1) NOT NULL DEFAULT 'A',
  `codigo` varchar(9) NOT NULL,
  `logo` varchar(100) DEFAULT NULL,
  `url_imagen` varchar(1000) DEFAULT NULL,
  `posicion_menu` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cat_unq01` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categoria`
--

LOCK TABLES `categoria` WRITE;
/*!40000 ALTER TABLE `categoria` DISABLE KEYS */;
INSERT INTO `categoria` VALUES (4,'Bebidas Calientes','A','BEB-CAL','fas fa-mug-hot','categorias/UHUc616R6Lf1xmTOTIHp8kBRMyw2w9SU4R5V61aA.png',1),(5,'Bebidas Frías','A','BEB-FRI','fas fa-mug-hot','categorias/D1jaNhTb0L1Jtutg8pgHjtKbnmZ5gYLvOCTL6u1H.png',2),(6,'Milkshakes','A','MLSK','fas fa-coffee','categorias/EmGIHWpl4YiVtBplE4kvNfOVEqijkubKSZwFidvk.png',5),(7,'Smoothies','A','SMTH','fas fa-coffee','categorias/63OWXlqUSEpPrJEJQ5FjfxsPmiBJoWtdT9IMuGco.png',4),(8,'Repostería Salada','A','REP-SAL','fas fa-utensils','categorias/RfE9kTnronOLIEatiAWkjAL1hFZXqZv8ekL4v9Cz.png',7),(9,'Repostería Dulce','A','REP-DUL','fas fa-utensils','categorias/n1FHEFl4tj0seEwQZwgmomKLUYoq7E4Wwv8QQ1Bk.png',8),(10,'Repostería Especial','A','REP-ESP','fas fa-utensils','categorias/Ley2Hh7wVFaVQHa3FXDMZK9O6XChHfzKZQudAYcG.png',9),(11,'Té','A','BEB-TÉ','fas fa-mug-hot','categorias/894v5YCQzmkHpJouLZfwbGL5Gc4anRh6yXB04Nwb.png',3),(13,'Otras Bebidas','A','OTR-BEB','fas fa-coffee','categorias/sQnwecFlmcW7ZdQ8lELPd9Na1JWbSbwwnDYrqvl6.png',6),(14,'Opciones de Almuerzo','A','OPC-ALZ','fas fa-utensils','categorias/jQBYNbiotGdyubSdOJZTrkDQGSg2kgFYdUTCz73g.png',10),(15,'Bowl de Frutas','A','BWL-FRT','fas fa-utensils','categorias/OkGPZU8v0GuvpGIGxFw8zngRqLWHpsSRRg7uEzxV.png',11),(16,'Promociónes','A','PROMO','fas fa-tag','categorias/jVmaer5ZlCSD7eY3HtcSH0wXcVOT4kSFrnpuvp9s.png',0);
/*!40000 ALTER TABLE `categoria` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cierre_caja`
--

DROP TABLE IF EXISTS `cierre_caja`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cierre_caja` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  PRIMARY KEY (`id`),
  KEY `cj_usuario_fk01` (`cajero`),
  KEY `estado_fk_01` (`estado`),
  KEY `suc_cierre_fk01` (`sucursal`),
  CONSTRAINT `cj_usuario_fk01` FOREIGN KEY (`cajero`) REFERENCES `usuario` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `estado_fk_01` FOREIGN KEY (`estado`) REFERENCES `sis_estado` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `suc_cierre_fk01` FOREIGN KEY (`sucursal`) REFERENCES `sucursal` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cierre_caja`
--

LOCK TABLES `cierre_caja` WRITE;
/*!40000 ALTER TABLE `cierre_caja` DISABLE KEYS */;
/*!40000 ALTER TABLE `cierre_caja` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cliente`
--

DROP TABLE IF EXISTS `cliente`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cliente` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `nueva_contra` varchar(1000) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cliente`
--

LOCK TABLES `cliente` WRITE;
/*!40000 ALTER TABLE `cliente` DISABLE KEYS */;
/*!40000 ALTER TABLE `cliente` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `codigo_descuento`
--

DROP TABLE IF EXISTS `codigo_descuento`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `codigo_descuento` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo` int(11) NOT NULL,
  `descuento` int(11) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `descripcion` varchar(2000) NOT NULL,
  `codigo` varchar(500) NOT NULL,
  `activo` int(11) NOT NULL DEFAULT 0,
  `cant_codigos` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `cod_tipo_fk` (`tipo`),
  CONSTRAINT `cod_tipo_fk` FOREIGN KEY (`tipo`) REFERENCES `sis_tipo` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `codigo_descuento`
--

LOCK TABLES `codigo_descuento` WRITE;
/*!40000 ALTER TABLE `codigo_descuento` DISABLE KEYS */;
/*!40000 ALTER TABLE `codigo_descuento` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comanda`
--

DROP TABLE IF EXISTS `comanda`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comanda` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sucursal` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `comanda_fk01` (`sucursal`),
  CONSTRAINT `comanda_fk01` FOREIGN KEY (`sucursal`) REFERENCES `sucursal` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comanda`
--

LOCK TABLES `comanda` WRITE;
/*!40000 ALTER TABLE `comanda` DISABLE KEYS */;
/*!40000 ALTER TABLE `comanda` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `det_grupo_promocion`
--

DROP TABLE IF EXISTS `det_grupo_promocion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `det_grupo_promocion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `grupo_promocion` int(11) NOT NULL,
  `tipo` varchar(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `det_grupo_promocion`
--

LOCK TABLES `det_grupo_promocion` WRITE;
/*!40000 ALTER TABLE `det_grupo_promocion` DISABLE KEYS */;
/*!40000 ALTER TABLE `det_grupo_promocion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalle_orden`
--

DROP TABLE IF EXISTS `detalle_orden`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `detalle_orden` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `monto_servicio` double NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `orden_fk01` (`orden`),
  KEY `detalle_orden_fk01` (`comanda`),
  CONSTRAINT `orden_fk01` FOREIGN KEY (`orden`) REFERENCES `orden` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_orden`
--

LOCK TABLES `detalle_orden` WRITE;
/*!40000 ALTER TABLE `detalle_orden` DISABLE KEYS */;
/*!40000 ALTER TABLE `detalle_orden` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalle_orden_comanda`
--

DROP TABLE IF EXISTS `detalle_orden_comanda`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `detalle_orden_comanda` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orden_comanda` int(11) NOT NULL,
  `detalle_orden` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `fecha_ingreso` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_fin` datetime DEFAULT NULL,
  `usuario_gestion` int(11) DEFAULT NULL,
  `comanda` int(11) DEFAULT NULL,
  `preparado` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_orden_comanda`
--

LOCK TABLES `detalle_orden_comanda` WRITE;
/*!40000 ALTER TABLE `detalle_orden_comanda` DISABLE KEYS */;
/*!40000 ALTER TABLE `detalle_orden_comanda` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalle_pago_orden`
--

DROP TABLE IF EXISTS `detalle_pago_orden`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `detalle_pago_orden` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pago_orden` int(11) NOT NULL,
  `detalle_orden` int(11) DEFAULT NULL,
  `cantidad_pagada` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `descuento` decimal(10,2) DEFAULT 0.00,
  `iva` decimal(10,2) DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL,
  `mto_impuesto_servicio` double NOT NULL DEFAULT 0,
  `dsc_linea` varchar(550) NOT NULL DEFAULT 'Producto',
  PRIMARY KEY (`id`),
  KEY `det_pago_orden_fk01` (`detalle_orden`),
  KEY `det_pago_orden_fk02` (`pago_orden`),
  CONSTRAINT `det_pago_orden_fk01` FOREIGN KEY (`detalle_orden`) REFERENCES `detalle_orden` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `det_pago_orden_fk02` FOREIGN KEY (`pago_orden`) REFERENCES `pago_orden` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_pago_orden`
--

LOCK TABLES `detalle_pago_orden` WRITE;
/*!40000 ALTER TABLE `detalle_pago_orden` DISABLE KEYS */;
/*!40000 ALTER TABLE `detalle_pago_orden` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `entrega_orden`
--

DROP TABLE IF EXISTS `entrega_orden`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `entrega_orden` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orden` int(11) NOT NULL,
  `precio` double NOT NULL,
  `descripcion_lugar` varchar(3000) NOT NULL,
  `estado` int(11) NOT NULL,
  `contacto` varchar(1500) NOT NULL,
  `encargado` int(11) DEFAULT NULL,
  `url_ubicacion` varchar(1000) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `orden_fk023` (`orden`),
  KEY `estado_fk23` (`estado`),
  KEY `usu_enc_fk01` (`encargado`),
  CONSTRAINT `estado_fk23` FOREIGN KEY (`estado`) REFERENCES `sis_estado` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `orden_fk023` FOREIGN KEY (`orden`) REFERENCES `orden` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `usu_enc_fk01` FOREIGN KEY (`encargado`) REFERENCES `usuario` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `entrega_orden`
--

LOCK TABLES `entrega_orden` WRITE;
/*!40000 ALTER TABLE `entrega_orden` DISABLE KEYS */;
/*!40000 ALTER TABLE `entrega_orden` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `est_entrega_orden`
--

DROP TABLE IF EXISTS `est_entrega_orden`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `est_entrega_orden` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entrega_orden` int(11) NOT NULL,
  `estado` int(11) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `usuario` int(250) NOT NULL,
  `descripcion` varchar(1500) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_entrega_orden_01` (`entrega_orden`),
  KEY `fk_entrega_orden_02` (`usuario`),
  CONSTRAINT `fk_entrega_orden_01` FOREIGN KEY (`entrega_orden`) REFERENCES `entrega_orden` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_entrega_orden_02` FOREIGN KEY (`usuario`) REFERENCES `usuario` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `est_entrega_orden`
--

LOCK TABLES `est_entrega_orden` WRITE;
/*!40000 ALTER TABLE `est_entrega_orden` DISABLE KEYS */;
/*!40000 ALTER TABLE `est_entrega_orden` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `est_orden`
--

DROP TABLE IF EXISTS `est_orden`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `est_orden` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orden` int(11) NOT NULL,
  `estado` int(11) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `usuario` int(11) NOT NULL,
  `descripcion` varchar(2500) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `est_orden_fk01` (`orden`),
  KEY `est_orden_fk02` (`usuario`),
  CONSTRAINT `est_orden_fk01` FOREIGN KEY (`orden`) REFERENCES `orden` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `est_orden_fk02` FOREIGN KEY (`usuario`) REFERENCES `usuario` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `est_orden`
--

LOCK TABLES `est_orden` WRITE;
/*!40000 ALTER TABLE `est_orden` DISABLE KEYS */;
/*!40000 ALTER TABLE `est_orden` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `extra_detalle_orden`
--

DROP TABLE IF EXISTS `extra_detalle_orden`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `extra_detalle_orden` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orden` int(11) NOT NULL,
  `detalle` int(11) NOT NULL,
  `descripcion_extra` varchar(1500) NOT NULL,
  `total` double DEFAULT NULL,
  `extra` int(11) DEFAULT NULL,
  `id_producto` int(11) DEFAULT NULL,
  `tipo_producto` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `extra_detalle_orden`
--

LOCK TABLES `extra_detalle_orden` WRITE;
/*!40000 ALTER TABLE `extra_detalle_orden` DISABLE KEYS */;
/*!40000 ALTER TABLE `extra_detalle_orden` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `extra_producto_externo`
--

DROP TABLE IF EXISTS `extra_producto_externo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `extra_producto_externo` (
  `id` int(11) NOT NULL,
  `descripcion` varchar(500) NOT NULL,
  `precio` int(11) NOT NULL,
  `producto` int(11) NOT NULL,
  `dsc_grupo` varchar(500) NOT NULL,
  `es_requerido` int(11) DEFAULT 0,
  `multiple` int(11) NOT NULL DEFAULT 0,
  KEY `prod_fk02` (`producto`),
  CONSTRAINT `prod_fk02` FOREIGN KEY (`producto`) REFERENCES `producto_externo` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `extra_producto_externo`
--

LOCK TABLES `extra_producto_externo` WRITE;
/*!40000 ALTER TABLE `extra_producto_externo` DISABLE KEYS */;
/*!40000 ALTER TABLE `extra_producto_externo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `extra_producto_menu`
--

DROP TABLE IF EXISTS `extra_producto_menu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `extra_producto_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(500) NOT NULL,
  `precio` int(11) NOT NULL,
  `producto` int(11) NOT NULL,
  `dsc_grupo` varchar(500) NOT NULL,
  `es_requerido` int(11) DEFAULT 0,
  `multiple` int(11) NOT NULL DEFAULT 0,
  `materia_prima` int(11) DEFAULT NULL,
  `cant_mp` double DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `prod_fk01` (`producto`),
  CONSTRAINT `prod_fk01` FOREIGN KEY (`producto`) REFERENCES `producto_menu` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=2309 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `extra_producto_menu`
--

LOCK TABLES `extra_producto_menu` WRITE;
/*!40000 ALTER TABLE `extra_producto_menu` DISABLE KEYS */;
INSERT INTO `extra_producto_menu` VALUES (68,'Azúcar Extra',0,25,'Endulzante',1,0,24,2),(69,'Azúcar Extra',0,26,'Endulzante',1,0,24,2),(70,'Azúcar Extra',0,27,'Endulzante',1,0,24,2),(71,'Azúcar Extra',0,30,'Endulzante',1,0,24,2),(72,'Azúcar Extra',0,31,'Endulzante',1,0,24,2),(73,'Azúcar Extra',0,32,'Endulzante',1,0,24,2),(74,'Azúcar Extra',0,33,'Endulzante',1,0,24,2),(75,'Azúcar Extra',0,34,'Endulzante',1,0,24,2),(76,'Azúcar Extra',0,35,'Endulzante',1,0,24,2),(77,'Azúcar Extra',0,38,'Endulzante',1,0,24,2),(78,'Azúcar Extra',0,39,'Endulzante',1,0,24,2),(81,'Azúcar Extra',0,88,'Endulzante',1,0,24,2),(82,'Azúcar Extra',0,89,'Endulzante',1,0,24,2),(83,'Azúcar Extra',0,44,'Endulzante',1,0,24,2),(84,'Azúcar Extra',0,45,'Endulzante',1,0,24,2),(131,'Sin Azúcar Extra',0,25,'Endulzante',1,0,NULL,NULL),(132,'Sin Azúcar Extra',0,26,'Endulzante',1,0,NULL,NULL),(133,'Sin Azúcar Extra',0,27,'Endulzante',1,0,NULL,NULL),(134,'Sin Azúcar Extra',0,30,'Endulzante',1,0,NULL,NULL),(135,'Sin Azúcar Extra',0,31,'Endulzante',1,0,NULL,NULL),(136,'Sin Azúcar Extra',0,32,'Endulzante',1,0,NULL,NULL),(137,'Sin Azúcar Extra',0,33,'Endulzante',1,0,NULL,NULL),(138,'Sin Azúcar Extra',0,34,'Endulzante',1,0,NULL,NULL),(139,'Sin Azúcar Extra',0,35,'Endulzante',1,0,NULL,NULL),(140,'Sin Azúcar Extra',0,38,'Endulzante',1,0,NULL,NULL),(141,'Sin Azúcar Extra',0,39,'Endulzante',1,0,NULL,NULL),(144,'Sin Azúcar Extra',0,88,'Endulzante',1,0,NULL,NULL),(145,'Sin Azúcar Extra',0,89,'Endulzante',1,0,NULL,NULL),(146,'Sin Azúcar Extra',0,44,'Endulzante',1,0,NULL,NULL),(147,'Sin Azúcar Extra',0,45,'Endulzante',1,0,NULL,NULL),(194,'Sustituto',0,25,'Endulzante',1,0,29,2),(195,'Sustituto',0,26,'Endulzante',1,0,29,2),(196,'Sustituto',0,27,'Endulzante',1,0,29,2),(197,'Sustituto',0,30,'Endulzante',1,0,29,2),(198,'Sustituto',0,31,'Endulzante',1,0,29,2),(199,'Sustituto',0,32,'Endulzante',1,0,29,2),(200,'Sustituto',0,33,'Endulzante',1,0,29,2),(201,'Sustituto',0,34,'Endulzante',1,0,29,2),(202,'Sustituto',0,35,'Endulzante',1,0,29,2),(203,'Sustituto',0,38,'Endulzante',1,0,29,2),(204,'Sustituto',0,39,'Endulzante',1,0,29,2),(207,'Sustituto',0,88,'Endulzante',1,0,29,2),(208,'Sustituto',0,89,'Endulzante',1,0,29,2),(209,'Sustituto',0,44,'Endulzante',1,0,29,2),(210,'Sustituto',0,45,'Endulzante',1,0,29,2),(257,'Chocolate',250,25,'Jarabe',0,1,NULL,NULL),(258,'Chocolate',250,26,'Jarabe',0,1,NULL,NULL),(259,'Chocolate',250,27,'Jarabe',0,1,NULL,NULL),(320,'Caramelo',250,25,'Jarabe',0,1,NULL,NULL),(321,'Caramelo',250,26,'Jarabe',0,1,NULL,NULL),(322,'Caramelo',250,27,'Jarabe',0,1,NULL,NULL),(383,'Leche Entera',400,25,'Extra Leche',0,0,25,250),(384,'Leche Entera',400,26,'Extra Leche',0,0,25,250),(385,'Leche Entera',400,27,'Extra Leche',0,0,25,250),(386,'Leche Entera',400,30,'Extra Leche',0,0,25,250),(387,'Leche Entera',400,31,'Extra Leche',0,0,25,250),(446,'Leche Almendras',500,25,'Extra Leche',0,0,26,250),(447,'Leche Almendras',500,26,'Extra Leche',0,0,26,250),(448,'Leche Almendras',500,27,'Extra Leche',0,0,26,250),(449,'Leche Almendras',500,30,'Extra Leche',0,0,26,250),(450,'Leche Almendras',500,31,'Extra Leche',0,0,26,250),(509,'Amaretto',250,25,'Extra Sirope',0,0,NULL,NULL),(510,'Amaretto',250,26,'Extra Sirope',0,0,NULL,NULL),(511,'Amaretto',250,27,'Extra Sirope',0,0,NULL,NULL),(572,'Vainilla',250,25,'Extra Sirope',0,0,NULL,NULL),(573,'Vainilla',250,26,'Extra Sirope',0,0,NULL,NULL),(574,'Vainilla',250,27,'Extra Sirope',0,0,NULL,NULL),(635,'Caramelo',250,25,'Extra Sirope',0,0,NULL,NULL),(636,'Caramelo',250,26,'Extra Sirope',0,0,NULL,NULL),(637,'Caramelo',250,27,'Extra Sirope',0,0,NULL,NULL),(698,'Crema Irlandesa',250,25,'Extra Sirope',0,0,NULL,NULL),(699,'Crema Irlandesa',250,26,'Extra Sirope',0,0,NULL,NULL),(700,'Crema Irlandesa',250,27,'Extra Sirope',0,0,NULL,NULL),(761,'Crema Batida',250,25,'Otros Extras',0,1,NULL,NULL),(762,'Crema Batida',250,26,'Otros Extras',0,1,NULL,NULL),(763,'Crema Batida',250,27,'Otros Extras',0,1,NULL,NULL),(824,'Chispas de chocolate',250,25,'Otros Extras',0,1,NULL,NULL),(825,'Chispas de chocolate',250,26,'Otros Extras',0,1,NULL,NULL),(826,'Chispas de chocolate',250,27,'Otros Extras',0,1,NULL,NULL),(887,'Almendras',500,25,'Otros Extras',0,1,NULL,NULL),(888,'Almendras',500,26,'Otros Extras',0,1,NULL,NULL),(889,'Almendras',500,27,'Otros Extras',0,1,NULL,NULL),(950,'Frutos Rojos',300,25,'Otros Extras',0,1,NULL,NULL),(951,'Frutos Rojos',300,26,'Otros Extras',0,1,NULL,NULL),(952,'Frutos Rojos',300,27,'Otros Extras',0,1,NULL,NULL),(1013,'Helado Vainilla',600,25,'Otros Extras',0,1,NULL,NULL),(1014,'Helado Vainilla',600,26,'Otros Extras',0,1,NULL,NULL),(1015,'Helado Vainilla',600,27,'Otros Extras',0,1,NULL,NULL),(1076,'Shot café',350,25,'Otros Extras',0,1,NULL,NULL),(1077,'Shot café',350,26,'Otros Extras',0,1,NULL,NULL),(1078,'Shot café',350,27,'Otros Extras',0,1,NULL,NULL),(1139,'Frío',0,87,'Tipo',1,0,5,1),(1140,'Caliente',0,87,'Tipo',1,0,3,1),(1141,'Frío',0,86,'Tipo',1,0,5,1),(1142,'Caliente',0,86,'Tipo',1,0,3,1),(1143,'Caliente',0,81,'Tipo',1,0,3,1),(1144,'Frío',0,81,'Tipo',1,0,5,1),(1145,'Frío',0,85,'Tipo',1,0,5,1),(1146,'Caliente',0,85,'Tipo',1,0,3,1),(1147,'Caliente',0,84,'Tipo',1,0,3,1),(1148,'Frío',0,84,'Tipo',1,0,5,1),(1149,'Frío',0,78,'Tipo',1,0,5,1),(1150,'Caliente',0,78,'Tipo',1,0,3,1),(1151,'Leche Descremada',400,25,'Extra Leche',0,0,27,250),(1152,'Leche Descremada',400,26,'Extra Leche',0,0,27,250),(1153,'Leche Descremada',400,27,'Extra Leche',0,0,27,250),(1154,'Leche Descremada',400,30,'Extra Leche',0,0,27,250),(1155,'Leche Descremada',400,31,'Extra Leche',0,0,27,250),(1214,'Leche Deslactosada',400,25,'Extra Leche',0,0,19,250),(1215,'Leche Deslactosada',400,26,'Extra Leche',0,0,19,250),(1216,'Leche Deslactosada',400,27,'Extra Leche',0,0,19,250),(1217,'Leche Deslactosada',400,30,'Extra Leche',0,0,19,250),(1218,'Leche Deslactosada',400,31,'Extra Leche',0,0,19,250),(1279,'Leche Deslactosada',0,93,'Tipo Leche',1,0,19,50),(1282,'Leche Entera',0,93,'Tipo Leche',1,0,25,50),(1285,'Leche Almendras',0,93,'Tipo Leche',1,0,26,50),(1288,'Leche Descremada',0,93,'Tipo Leche',1,0,27,50),(1453,'Leche Descremada',0,60,'Tipo Leche',1,0,27,90),(1454,'Leche Descremada',0,62,'Tipo Leche',1,0,27,90),(1456,'Leche Almendras',0,60,'Tipo Leche',1,0,26,90),(1457,'Leche Almendras',0,62,'Tipo Leche',1,0,26,90),(1459,'Leche Entera',0,60,'Tipo Leche',1,0,25,90),(1460,'Leche Entera',0,62,'Tipo Leche',1,0,25,90),(1462,'Leche Deslactosada',0,60,'Tipo Leche',1,0,19,90),(1463,'Leche Deslactosada',0,62,'Tipo Leche',1,0,19,90),(1469,'Leche Descremada',0,48,'Tipo Leche',1,0,27,115),(1470,'Leche Descremada',0,90,'Tipo Leche',1,0,27,115),(1472,'Leche Almendras',0,48,'Tipo Leche',1,0,26,115),(1473,'Leche Almendras',0,90,'Tipo Leche',1,0,26,115),(1475,'Leche Entera',0,48,'Tipo Leche',1,0,25,115),(1476,'Leche Entera',0,90,'Tipo Leche',1,0,25,115),(1478,'Leche Deslactosada',0,48,'Tipo Leche',1,0,19,115),(1479,'Leche Deslactosada',0,90,'Tipo Leche',1,0,19,115),(1481,'Leche Descremada',0,61,'Tipo Leche',1,0,27,125),(1482,'Leche Descremada',0,63,'Tipo Leche',1,0,27,125),(1484,'Leche Almendras',0,61,'Tipo Leche',1,0,26,125),(1485,'Leche Almendras',0,63,'Tipo Leche',1,0,26,125),(1487,'Leche Entera',0,61,'Tipo Leche',1,0,25,125),(1488,'Leche Entera',0,63,'Tipo Leche',1,0,25,125),(1490,'Leche Deslactosada',0,61,'Tipo Leche',1,0,19,125),(1491,'Leche Deslactosada',0,63,'Tipo Leche',1,0,19,125),(1496,'Leche Almendras',0,58,'Tipo Leche',1,0,26,130),(1499,'Leche Entera',0,58,'Tipo Leche',1,0,25,130),(1502,'Leche Deslactosada',0,58,'Tipo Leche',1,0,19,130),(1505,'Leche Descremada',0,64,'Tipo Leche',1,0,27,140),(1506,'Leche Almendras',0,64,'Tipo Leche',1,0,26,140),(1507,'Leche Entera',0,64,'Tipo Leche',1,0,25,140),(1508,'Leche Deslactosada',0,64,'Tipo Leche',1,0,19,140),(1509,'Leche Descremada',0,44,'Tipo Leche',1,0,27,145),(1510,'Leche Almendras',0,44,'Tipo Leche',1,0,26,145),(1511,'Leche Entera',0,44,'Tipo Leche',1,0,25,145),(1512,'Leche Deslactosada',0,44,'Tipo Leche',1,0,19,145),(1517,'Leche Descremada',0,65,'Tipo Leche',1,0,27,175),(1518,'Leche Almendras',0,65,'Tipo Leche',1,0,26,175),(1519,'Leche Entera',0,65,'Tipo Leche',1,0,25,175),(1520,'Leche Deslactosada',0,65,'Tipo Leche',1,0,19,175),(1533,'Leche Descremada',0,40,'Tipo Leche',1,0,27,200),(1534,'Leche Descremada',0,59,'Tipo Leche',1,0,27,200),(1536,'Leche Almendras',0,40,'Tipo Leche',1,0,26,200),(1537,'Leche Almendras',0,59,'Tipo Leche',1,0,26,200),(1539,'Leche Entera',0,40,'Tipo Leche',1,0,25,200),(1540,'Leche Entera',0,59,'Tipo Leche',1,0,25,200),(1542,'Leche Deslactosada',0,40,'Tipo Leche',1,0,19,200),(1543,'Leche Deslactosada',0,59,'Tipo Leche',1,0,19,200),(1545,'Leche Descremada',0,49,'Tipo Leche',1,0,27,210),(1546,'Leche Descremada',0,91,'Tipo Leche',1,0,27,210),(1548,'Leche Almendras',0,49,'Tipo Leche',1,0,26,210),(1549,'Leche Almendras',0,91,'Tipo Leche',1,0,26,210),(1551,'Leche Entera',0,49,'Tipo Leche',1,0,25,210),(1552,'Leche Entera',0,91,'Tipo Leche',1,0,25,210),(1554,'Leche Deslactosada',0,49,'Tipo Leche',1,0,19,210),(1555,'Leche Deslactosada',0,91,'Tipo Leche',1,0,19,210),(1557,'Leche Descremada',0,52,'Tipo Leche',1,0,27,230),(1558,'Leche Almendras',0,52,'Tipo Leche',1,0,26,230),(1559,'Leche Entera',0,52,'Tipo Leche',1,0,25,230),(1560,'Leche Deslactosada',0,52,'Tipo Leche',1,0,19,230),(1561,'Leche Descremada',0,45,'Tipo Leche',1,0,27,240),(1562,'Leche Almendras',0,45,'Tipo Leche',1,0,26,240),(1563,'Leche Entera',0,45,'Tipo Leche',1,0,25,240),(1564,'Leche Deslactosada',0,45,'Tipo Leche',1,0,19,240),(1585,'Leche Descremada',0,53,'Tipo Leche',1,0,27,330),(1586,'Leche Almendras',0,53,'Tipo Leche',1,0,26,330),(1587,'Leche Entera',0,53,'Tipo Leche',1,0,25,330),(1588,'Leche Deslactosada',0,53,'Tipo Leche',1,0,19,330),(1589,'Leche Descremada',0,78,'Tipo Leche',1,0,27,350),(1590,'Leche Almendras',0,78,'Tipo Leche',1,0,26,350),(1591,'Leche Entera',0,78,'Tipo Leche',1,0,25,350),(1592,'Leche Deslactosada',0,78,'Tipo Leche',1,0,19,350),(1597,'Sin Azúcar Extra',0,78,'Endulzante',1,0,NULL,NULL),(1598,'Sin Azúcar Extra',0,80,'Endulzante',1,0,NULL,NULL),(1599,'Sin Azúcar Extra',0,81,'Endulzante',1,0,NULL,NULL),(1600,'Sin Azúcar Extra',0,82,'Endulzante',1,0,NULL,NULL),(1601,'Sin Azúcar Extra',0,83,'Endulzante',1,0,NULL,NULL),(1602,'Sin Azúcar Extra',0,84,'Endulzante',1,0,NULL,NULL),(1603,'Sin Azúcar Extra',0,85,'Endulzante',1,0,NULL,NULL),(1604,'Sin Azúcar Extra',0,86,'Endulzante',1,0,NULL,NULL),(1605,'Sin Azúcar Extra',0,87,'Endulzante',1,0,NULL,NULL),(1612,'Azúcar Extra',0,78,'Endulzante',1,0,24,2),(1613,'Azúcar Extra',0,80,'Endulzante',1,0,24,2),(1614,'Azúcar Extra',0,81,'Endulzante',1,0,24,2),(1615,'Azúcar Extra',0,82,'Endulzante',1,0,24,2),(1616,'Azúcar Extra',0,83,'Endulzante',1,0,24,2),(1617,'Azúcar Extra',0,84,'Endulzante',1,0,24,2),(1618,'Azúcar Extra',0,85,'Endulzante',1,0,24,2),(1619,'Azúcar Extra',0,86,'Endulzante',1,0,24,2),(1620,'Azúcar Extra',0,87,'Endulzante',1,0,24,2),(1627,'Sustituto',0,78,'Endulzante',1,0,29,2),(1628,'Sustituto',0,80,'Endulzante',1,0,29,2),(1629,'Sustituto',0,81,'Endulzante',1,0,29,2),(1630,'Sustituto',0,82,'Endulzante',1,0,29,2),(1631,'Sustituto',0,83,'Endulzante',1,0,29,2),(1632,'Sustituto',0,84,'Endulzante',1,0,29,2),(1633,'Sustituto',0,85,'Endulzante',1,0,29,2),(1634,'Sustituto',0,86,'Endulzante',1,0,29,2),(1635,'Sustituto',0,87,'Endulzante',1,0,29,2),(1642,'Vainilla',600,28,'Helado',0,1,34,100),(1646,'Vainilla',0,90,'Sirope',1,0,37,30),(1647,'Vainilla',0,91,'Sirope',1,0,37,45),(1648,'Caramelo',0,91,'Sirope',1,0,37,45),(1653,'Caramelo',0,90,'Sirope',1,0,37,30),(1685,'Azúcar Extra',0,100,'Endulzante',1,0,24,2),(1686,'Sin Azúcar Extra',0,100,'Endulzante',1,0,NULL,NULL),(1687,'Sustituto',0,100,'Endulzante',1,0,29,2),(1688,'Chocolate',250,100,'Jarabe',0,1,NULL,NULL),(1689,'Caramelo',250,100,'Jarabe',0,1,NULL,NULL),(1690,'Leche Entera',400,100,'Extra Leche',0,0,25,250),(1691,'Leche Almendras',500,100,'Extra Leche',0,0,26,250),(1692,'Amaretto',250,100,'Extra Sirope',0,0,NULL,NULL),(1693,'Vainilla',250,100,'Extra Sirope',0,0,NULL,NULL),(1694,'Caramelo',250,100,'Extra Sirope',0,0,NULL,NULL),(1695,'Crema Irlandesa',250,100,'Extra Sirope',0,0,NULL,NULL),(1696,'Crema Batida',250,100,'Otros Extras',0,1,NULL,NULL),(1697,'Chispas de chocolate',250,100,'Otros Extras',0,1,NULL,NULL),(1698,'Almendras',500,100,'Otros Extras',0,1,NULL,NULL),(1699,'Frutos Rojos',300,100,'Otros Extras',0,1,NULL,NULL),(1700,'Helado Vainilla',600,100,'Otros Extras',0,1,NULL,NULL),(1701,'Shot café',350,100,'Otros Extras',0,1,NULL,NULL),(1702,'Leche Descremada',400,100,'Extra Leche',0,0,27,250),(1703,'Leche Deslactosada',400,100,'Extra Leche',0,0,19,250),(1716,'Leche Descremada',0,100,'Tipo Leche',1,0,27,300),(1717,'Leche Almendras',0,100,'Tipo Leche',1,0,26,300),(1718,'Leche Entera',0,100,'Tipo Leche',1,0,25,300),(1719,'Leche Deslactosada',0,100,'Tipo Leche',1,0,19,300),(1720,'Azúcar Extra',0,103,'Endulzante',1,0,24,2),(1721,'Sin Azúcar Extra',0,103,'Endulzante',1,0,NULL,NULL),(1722,'Sustituto',0,103,'Endulzante',1,0,29,2),(1723,'Chocolate',250,103,'Jarabe',0,1,NULL,NULL),(1724,'Caramelo',250,103,'Jarabe',0,1,NULL,NULL),(1725,'Leche Entera',400,103,'Extra Leche',0,0,25,250),(1726,'Leche Almendras',500,103,'Extra Leche',0,0,26,250),(1727,'Amaretto',250,103,'Extra Sirope',0,0,NULL,NULL),(1728,'Vainilla',250,103,'Extra Sirope',0,0,NULL,NULL),(1729,'Caramelo',250,103,'Extra Sirope',0,0,NULL,NULL),(1730,'Crema Irlandesa',250,103,'Extra Sirope',0,0,NULL,NULL),(1731,'Crema Batida',250,103,'Otros Extras',0,1,NULL,NULL),(1732,'Chispas de chocolate',250,103,'Otros Extras',0,1,NULL,NULL),(1733,'Almendras',500,103,'Otros Extras',0,1,NULL,NULL),(1734,'Frutos Rojos',300,103,'Otros Extras',0,1,NULL,NULL),(1735,'Helado Vainilla',600,103,'Otros Extras',0,1,NULL,NULL),(1736,'Shot café',350,103,'Otros Extras',0,1,NULL,NULL),(1737,'Leche Descremada',400,103,'Extra Leche',0,0,27,250),(1738,'Leche Deslactosada',400,103,'Extra Leche',0,0,19,250),(1739,'Leche Descremada',0,103,'Tipo Leche',1,0,27,300),(1740,'Leche Almendras',0,103,'Tipo Leche',1,0,26,300),(1741,'Leche Entera',0,103,'Tipo Leche',1,0,25,300),(1742,'Leche Deslactosada',0,103,'Tipo Leche',1,0,19,300),(1751,'Azúcar Extra',0,104,'Endulzante',1,0,24,2),(1752,'Sin Azúcar Extra',0,104,'Endulzante',1,0,NULL,NULL),(1753,'Sustituto',0,104,'Endulzante',1,0,29,2),(1754,'Chocolate',250,104,'Jarabe',0,1,NULL,NULL),(1755,'Caramelo',250,104,'Jarabe',0,1,NULL,NULL),(1756,'Leche Entera',400,104,'Extra Leche',0,0,25,250),(1757,'Leche Almendras',500,104,'Extra Leche',0,0,26,250),(1758,'Amaretto',250,104,'Extra Sirope',0,0,NULL,NULL),(1759,'Vainilla',250,104,'Extra Sirope',0,0,NULL,NULL),(1760,'Caramelo',250,104,'Extra Sirope',0,0,NULL,NULL),(1761,'Crema Irlandesa',250,104,'Extra Sirope',0,0,NULL,NULL),(1762,'Crema Batida',250,104,'Otros Extras',0,1,NULL,NULL),(1763,'Chispas de chocolate',250,104,'Otros Extras',0,1,NULL,NULL),(1764,'Almendras',500,104,'Otros Extras',0,1,NULL,NULL),(1765,'Frutos Rojos',300,104,'Otros Extras',0,1,NULL,NULL),(1766,'Helado Vainilla',600,104,'Otros Extras',0,1,NULL,NULL),(1767,'Shot café',350,104,'Otros Extras',0,1,NULL,NULL),(1768,'Leche Descremada',400,104,'Extra Leche',0,0,27,250),(1769,'Leche Deslactosada',400,104,'Extra Leche',0,0,19,250),(1770,'Leche Descremada',0,104,'Tipo Leche',1,0,27,300),(1771,'Leche Almendras',0,104,'Tipo Leche',1,0,26,300),(1773,'Leche Entera',0,104,'Tipo Leche',1,0,25,300),(1774,'Leche Deslactosada',0,104,'Tipo Leche',1,0,19,300),(1782,'Azúcar Extra',0,105,'Endulzante',1,0,24,2),(1783,'Sin Azúcar Extra',0,105,'Endulzante',1,0,NULL,NULL),(1784,'Sustituto',0,105,'Endulzante',1,0,29,2),(1785,'Chocolate',250,105,'Jarabe',0,1,NULL,NULL),(1786,'Caramelo',250,105,'Jarabe',0,1,NULL,NULL),(1787,'Leche Entera',400,105,'Extra Leche',0,0,25,250),(1788,'Leche Almendras',500,105,'Extra Leche',0,0,26,250),(1789,'Amaretto',250,105,'Extra Sirope',0,0,NULL,NULL),(1790,'Vainilla',250,105,'Extra Sirope',0,0,NULL,NULL),(1791,'Caramelo',250,105,'Extra Sirope',0,0,NULL,NULL),(1792,'Crema Irlandesa',250,105,'Extra Sirope',0,0,NULL,NULL),(1793,'Crema Batida',250,105,'Otros Extras',0,1,NULL,NULL),(1794,'Chispas de chocolate',250,105,'Otros Extras',0,1,NULL,NULL),(1795,'Almendras',500,105,'Otros Extras',0,1,NULL,NULL),(1796,'Frutos Rojos',300,105,'Otros Extras',0,1,NULL,NULL),(1797,'Helado Vainilla',600,105,'Otros Extras',0,1,NULL,NULL),(1798,'Shot café',350,105,'Otros Extras',0,1,NULL,NULL),(1799,'Leche Descremada',400,105,'Extra Leche',0,0,27,250),(1800,'Leche Deslactosada',400,105,'Extra Leche',0,0,19,250),(1801,'Leche Descremada',0,105,'Tipo Leche',1,0,27,300),(1802,'Leche Almendras',0,105,'Tipo Leche',1,0,26,300),(1803,'Leche Entera',0,105,'Tipo Leche',1,0,25,300),(1804,'Leche Deslactosada',0,105,'Tipo Leche',1,0,19,300),(1827,'Leche Descremada',0,92,'Tipo Leche',1,0,27,30),(1828,'Leche Entera',0,92,'Tipo Leche',1,0,25,30),(1829,'Leche Deslactosada',0,92,'Tipo Leche',1,0,19,30),(1830,'Leche Almendras',0,92,'Tipo Leche',1,0,26,30),(1831,'Leche entera',0,94,'Tipo Leche',1,0,25,30),(1832,'Leche almendras',0,94,'Tipo Leche',1,0,26,30),(1833,'Leche deslactosada',0,94,'Tipo Leche',1,0,19,30),(1834,'Leche descremada',0,94,'Tipo Leche',1,0,27,30),(1840,'Azúcar Extra',0,108,'Endulzante',1,0,24,2),(1841,'Sin Azúcar Extra',0,108,'Endulzante',1,0,NULL,NULL),(1842,'Sustituto',0,108,'Endulzante',1,0,29,2),(1843,'Chocolate',250,108,'Jarabe',0,1,NULL,NULL),(1844,'Caramelo',250,108,'Jarabe',0,1,NULL,NULL),(1845,'Leche Entera',400,108,'Extra Leche',0,0,25,250),(1846,'Leche Almendras',500,108,'Extra Leche',0,0,26,250),(1847,'Amaretto',250,108,'Extra Sirope',0,0,NULL,NULL),(1848,'Vainilla',250,108,'Extra Sirope',0,0,NULL,NULL),(1849,'Caramelo',250,108,'Extra Sirope',0,0,NULL,NULL),(1850,'Crema Irlandesa',250,108,'Extra Sirope',0,0,NULL,NULL),(1851,'Crema Batida',250,108,'Otros Extras',0,1,NULL,NULL),(1852,'Chispas de chocolate',250,108,'Otros Extras',0,1,NULL,NULL),(1853,'Almendras',500,108,'Otros Extras',0,1,NULL,NULL),(1854,'Frutos Rojos',300,108,'Otros Extras',0,1,NULL,NULL),(1855,'Helado Vainilla',600,108,'Otros Extras',0,1,NULL,NULL),(1856,'Shot café',350,108,'Otros Extras',0,1,NULL,NULL),(1857,'Leche Descremada',400,108,'Extra Leche',0,0,27,250),(1858,'Leche Deslactosada',400,108,'Extra Leche',0,0,19,250),(1859,'Leche Descremada',0,108,'Tipo Leche',1,0,27,125),(1860,'Leche Almendras',0,108,'Tipo Leche',1,0,26,125),(1861,'Leche Entera',0,108,'Tipo Leche',1,0,25,125),(1862,'Leche Deslactosada',0,108,'Tipo Leche',1,0,19,125),(1871,'Azúcar Extra',0,107,'Endulzante',1,0,24,2),(1872,'Sin Azúcar Extra',0,107,'Endulzante',1,0,NULL,NULL),(1873,'Sustituto',0,107,'Endulzante',1,0,29,2),(1874,'Chocolate',250,107,'Jarabe',0,1,NULL,NULL),(1875,'Caramelo',250,107,'Jarabe',0,1,NULL,NULL),(1876,'Leche Descremada',400,107,'Extra Leche',0,0,27,90),(1877,'Leche Almendras',500,107,'Extra Leche',0,0,26,90),(1878,'Leche Entera',400,107,'Extra Leche',0,0,25,90),(1879,'Leche Deslactosada',400,107,'Extra Leche',0,0,19,90),(1880,'Amaretto',250,107,'Extra Sirope',0,0,NULL,NULL),(1881,'Vainilla',250,107,'Extra Sirope',0,0,NULL,NULL),(1882,'Caramelo',250,107,'Extra Sirope',0,0,NULL,NULL),(1883,'Crema Irlandesa',250,107,'Extra Sirope',0,0,NULL,NULL),(1884,'Crema Batida',250,107,'Otros Extras',0,1,NULL,NULL),(1885,'Chispas de chocolate',250,107,'Otros Extras',0,1,NULL,NULL),(1886,'Almendras',500,107,'Otros Extras',0,1,NULL,NULL),(1887,'Frutos Rojos',300,107,'Otros Extras',0,1,NULL,NULL),(1888,'Helado Vainilla',600,107,'Otros Extras',0,1,NULL,NULL),(1889,'Shot café',350,107,'Otros Extras',0,1,NULL,NULL),(1890,'Leche Descremada',0,107,'Tipo Leche',1,0,27,90),(1891,'Leche Almendras',0,107,'Tipo Leche',1,0,26,90),(1892,'Leche Entera',0,107,'Tipo Leche',1,0,25,90),(1893,'Leche Deslactosada',0,107,'Tipo Leche',1,0,19,90),(1902,'Leche entera',0,32,'Tipo Leche',1,0,25,200),(1903,'Leche deslactosada',0,32,'Tipo Leche',1,0,19,200),(1904,'Leche descremada',0,32,'Tipo Leche',1,0,27,200),(1905,'Leche almendras',0,32,'Tipo Leche',1,0,26,200),(1906,'Leche entera',0,88,'Tipo Leche',1,0,25,200),(1907,'Leche deslactosada',0,88,'Tipo Leche',1,0,19,200),(1908,'Leche descremada',0,88,'Tipo Leche',1,0,27,200),(1909,'Leche almendras',0,88,'Tipo Leche',1,0,26,200),(1910,'Leche entera',0,34,'Tipo Leche',1,0,25,200),(1911,'Leche deslactosada',0,34,'Tipo Leche',1,0,19,200),(1912,'Leche descremada',0,34,'Tipo Leche',1,0,27,200),(1913,'Leche almendras',0,34,'Tipo Leche',1,0,26,200),(1915,'Leche entera',0,35,'Tipo Leche',1,0,25,250),(1916,'Leche deslactosada',0,35,'Tipo Leche',1,0,19,250),(1917,'Leche descremada',0,35,'Tipo Leche',1,0,27,250),(1918,'Leche almendras',0,35,'Tipo Leche',1,0,26,250),(1919,'Leche entera',0,38,'Tipo Leche',1,0,25,200),(1920,'Leche deslactosada',0,38,'Tipo Leche',1,0,19,200),(1921,'Leche descremada',0,38,'Tipo Leche',1,0,27,200),(1922,'Leche almendras',0,38,'Tipo Leche',1,0,26,200),(1923,'Leche entera',0,41,'Tipo Leche',1,0,25,250),(1924,'Leche deslactosada',0,41,'Tipo Leche',1,0,19,250),(1925,'Leche descremada',0,41,'Tipo Leche',1,0,27,250),(1926,'Leche almendras',0,41,'Tipo Leche',1,0,26,250),(1933,'Leche entera',0,95,'Tipo Leche',1,0,25,50),(1934,'Leche deslactosada',0,95,'Tipo Leche',1,0,19,50),(1935,'Leche descremada',0,95,'Tipo Leche',1,0,27,50),(1936,'Leche almendras',0,95,'Tipo Leche',1,0,26,50),(1937,'Azúcar extra',0,52,'Extra Azúcar',0,0,24,2),(1938,'Sustituto extra',0,52,'Extra Azúcar',0,0,29,2),(1939,'Sustituto extra',0,53,'Extra Azúcar',0,0,29,2),(1940,'Azúcar extra',0,53,'Extra Azúcar',0,0,24,2),(1944,'Azúcar extra',0,40,'Extra Azúcar',0,0,24,2),(1945,'Sustituto extra',0,40,'Extra Azúcar',0,0,29,2),(1946,'Leche entera',400,85,'Extra Leche',0,0,25,350),(1947,'Leche deslactosada',400,85,'Extra Leche',0,0,19,350),(1948,'Leche descremada',400,85,'Extra Leche',0,0,27,350),(1949,'Leche almendras',500,85,'Extra Leche',0,0,26,350),(1950,'Azúcar extra',0,66,'Extra Azúcar',0,0,24,2),(1951,'Sustituto extra',0,66,'Extra Azúcar',0,0,29,2),(1953,'Azúcar extra',0,67,'Extra Azúcar',0,0,24,2),(1954,'Azúcar extra',0,68,'Extra Azúcar',0,0,24,2),(1955,'Sustituto extra',0,68,'Extra Azúcar',0,0,29,2),(1956,'Sustituto extra',0,76,'Extra Azúcar',0,0,29,2),(1957,'Azúcar extra',0,76,'Extra Azúcar',0,0,24,2),(1958,'Azúcar extra',0,77,'Extra Azúcar',0,0,24,2),(1959,'Sustituto extra',0,77,'Extra Azúcar',0,0,29,2),(1960,'Sustituto extra',0,70,'Extra Azúcar',0,0,29,2),(1961,'Azúcar extra',0,70,'Extra Azúcar',0,0,24,2),(1962,'Azúcar extra',0,71,'Extra Azúcar',0,0,24,2),(1963,'Sustituto extra',0,71,'Extra Azúcar',0,0,29,2),(1964,'Sustituto extra',0,72,'Extra Azúcar',0,0,29,2),(1965,'Azúcar extra',0,72,'Extra Azúcar',0,0,24,2),(1966,'Azúcar extra',0,73,'Extra Azúcar',0,0,24,2),(1967,'Sustituto extra',0,73,'Extra Azúcar',0,0,29,2),(1968,'Azúcar extra',0,74,'Extra Azúcar',0,0,24,2),(1969,'Sustituto extra',0,74,'Extra Azúcar',0,0,29,2),(1970,'Sustituto extra',0,75,'Extra Azúcar',0,0,29,2),(1971,'Azúcar extra',0,75,'Extra Azúcar',0,0,24,2),(1990,'Azúcar extra',0,41,'Extra Azúcar',0,0,24,2),(1991,'Sustituto extra',0,41,'Extra Azúcar',0,0,29,2),(1992,'Piña',0,98,'Frutas',0,1,69,130),(1993,'Papaya',0,98,'Frutas',0,1,70,130),(1994,'Banano',0,98,'Frutas',0,1,71,1),(1995,'Fresa',0,98,'Frutas',0,1,68,130),(1996,'Helado de Vainilla',600,98,'Otros Extras',0,1,34,100),(1997,'Granola',350,98,'Otros Extras',0,1,76,45),(1998,'Yogurt natural',350,98,'Otros Extras',0,1,75,45),(1999,'Miel',150,98,'Otros Extras',0,1,77,20),(2000,'Mango',0,99,'Frutas',0,1,73,130),(2001,'Fresa',0,99,'Frutas',0,1,68,130),(2002,'Kiwi',0,99,'Frutas',0,1,72,50),(2003,'Arándanos',0,99,'Frutas',0,1,74,45),(2004,'Simple',0,106,'Volumen espresso',1,0,1,14),(2005,'Doble',0,106,'Volumen espresso',1,0,1,21),(2008,'Leche entera',0,39,'Tipo Leche',1,0,25,250),(2009,'Leche deslactosada',0,39,'Tipo Leche',1,0,19,250),(2010,'Leche descremada',0,39,'Tipo Leche',1,0,27,250),(2011,'Leche almendras',0,39,'Tipo Leche',1,0,26,250),(2012,'Leche almendras',0,33,'Tipo Leche',1,0,26,250),(2013,'Leche entera',0,33,'Tipo Leche',1,0,25,250),(2014,'Leche deslactosada',0,33,'Tipo Leche',1,0,19,250),(2015,'Leche descremada',0,33,'Tipo Leche',1,0,27,250),(2016,'Leche descremada',0,89,'Tipo Leche',1,0,27,250),(2017,'Leche deslactosada',0,89,'Tipo Leche',1,0,19,250),(2018,'Leche entera',0,89,'Tipo Leche',1,0,25,250),(2019,'Leche almendras',0,89,'Tipo Leche',1,0,26,250),(2020,'Avellana',0,90,'Sirope',1,0,55,30),(2021,'Avellana',0,91,'Sirope',1,0,55,45),(2030,'Agua',0,109,'Tipo',1,0,20,1),(2031,'Leche',0,109,'Tipo',1,0,20,1),(2032,'Leche entera',0,109,'Tipo Leche',0,0,25,200),(2033,'Leche deslactosada',0,109,'Tipo Leche',0,0,19,200),(2034,'Leche descremada',0,109,'Tipo Leche',0,0,27,200),(2035,'Leche almendras',0,109,'Tipo Leche',0,0,26,200),(2036,'Agua',0,110,'Tipo',1,0,3,1),(2037,'Leche',0,110,'Tipo',1,0,3,1),(2038,'Leche entera',0,110,'Tipo Leche',0,0,25,250),(2039,'Leche deslactosada',0,110,'Tipo Leche',0,0,19,250),(2040,'Leche descremada',0,110,'Tipo Leche',0,0,27,250),(2041,'Leche almendras',0,110,'Tipo Leche',0,0,26,250),(2042,'Crema batida',250,88,'Otros Extras',0,1,41,30),(2043,'Vainilla',250,88,'Otros Extras',0,1,37,30),(2044,'Caramelo',250,88,'Otros Extras',0,1,39,30),(2046,'Chocolate Hershey´s',250,88,'Otros Extras',0,1,42,40),(2047,'Caramelo Hershey´s',250,88,'Otros Extras',0,1,42,40),(2048,'Crema batida',250,89,'Otros Extras',0,1,41,30),(2049,'Vainilla',250,89,'Otros Extras',0,1,37,30),(2050,'Caramelo',250,89,'Otros Extras',0,1,39,30),(2051,'Caramelo Hershey´s',250,89,'Otros Extras',0,1,43,40),(2052,'Chocolate Hershey´s',250,89,'Otros Extras',0,1,42,40),(2053,'Azúcar',0,113,'Endulzante',1,0,24,2),(2054,'Sustituto',0,113,'Endulzante',1,0,29,2),(2055,'Sin Azúcar',0,113,'Endulzante',1,0,23,0),(2056,'Leche entera',0,113,'Tipo Leche',1,0,25,200),(2057,'Leche descremada',0,113,'Tipo Leche',1,0,27,200),(2058,'Leche deslactosada',0,113,'Tipo Leche',1,0,19,200),(2059,'Leche almendras',0,113,'Tipo Leche',1,0,26,200),(2060,'Azúcar',0,114,'Endulzante',1,0,24,2),(2061,'Sustituto',0,114,'Endulzante',1,0,29,2),(2062,'Sin azúcar',0,114,'Endulzante',1,0,23,0),(2063,'Leche entera',0,114,'Tipo Leche',1,0,25,200),(2064,'Leche descremada',0,114,'Tipo Leche',1,0,27,200),(2065,'Leche deslactosada',0,114,'Tipo Leche',1,0,19,200),(2066,'Leche almendras',0,114,'Tipo Leche',1,0,26,200),(2071,'Chocolate Hershey´s',250,40,'Otros Extras',0,1,42,40),(2072,'Caramelo Hershey´s',250,40,'Otros Extras',0,1,43,40),(2073,'Crema batida',250,40,'Otros Extras',0,1,41,40),(2074,'Helado Vainilla',600,40,'Otros Extras',0,1,34,80),(2075,'Chocolate Hershey´s',250,41,'Otros Extras',0,1,42,40),(2076,'Caramelo Hershey´s',250,41,'Otros Extras',0,1,43,40),(2077,'Crema batida',250,41,'Otros Extras',0,1,41,40),(2078,'Helado Vainilla',600,41,'Otros Extras',0,1,34,80),(2079,'Sirope Vainilla',250,32,'Otros Extras',0,1,37,30),(2080,'Sirope Caramelo',250,32,'Otros Extras',0,1,39,30),(2081,'Crema batida',250,32,'Otros Extras',0,1,41,40),(2082,'Sirope Vainilla',250,33,'Otros Extras',0,1,37,30),(2083,'Sirope Caramelo',250,33,'Otros Extras',0,1,39,30),(2084,'Crema batida',250,33,'Otros Extras',0,1,41,40),(2085,'Azúcar',0,112,'Endulzante',1,0,24,2),(2086,'Sustituto',0,112,'Endulzante',1,0,29,2),(2087,'Sin Azúcar',0,112,'Endulzante',1,0,23,0),(2088,'Azúcar',0,111,'Endulzante',1,0,24,2),(2089,'Sustituto',0,111,'Endulzante',1,0,29,2),(2090,'Sin Azúcar',0,111,'Endulzante',1,0,23,0),(2091,'Crema batida',250,34,'Otros Extras',0,1,41,40),(2092,'Sirope Vainilla',250,34,'Otros Extras',0,1,37,30),(2093,'Crema batida',250,35,'Otros Extras',0,1,41,40),(2094,'Sirope Vainilla',250,35,'Otros Extras',0,1,37,30),(2095,'Crema batida',250,38,'Otros Extras',0,1,41,40),(2096,'Crema batida',250,39,'Otros Extras',0,1,41,40),(2097,'Sirope Vainilla',250,94,'Otros Extras',0,1,37,30),(2098,'Sirope Caramelo',250,94,'Otros Extras',0,1,39,30),(2099,'Chocolate Hershey´s',250,94,'Otros Extras',0,1,42,40),(2100,'Caramelo Hershey´s',250,94,'Otros Extras',0,1,43,40),(2101,'Crema batida',250,94,'Otros Extras',0,1,41,40),(2102,'Almendras',300,94,'Otros Extras',0,1,48,30),(2103,'Sirope Vainilla',250,95,'Otros Extras',0,1,37,30),(2104,'Sirope Caramelo',250,95,'Otros Extras',0,1,39,30),(2105,'Chocolate Hershey´s',250,95,'Otros Extras',0,1,42,40),(2106,'Caramelo Hershey´s',250,95,'Otros Extras',0,1,43,40),(2107,'Crema batida',250,95,'Otros Extras',0,1,41,40),(2109,'Almendras',300,95,'Otros Extras',0,1,48,30),(2110,'Sirope Vainilla',250,92,'Otros Extras',0,1,37,30),(2111,'Sirope Caramelo',250,92,'Otros Extras',0,1,39,30),(2112,'Chocolate Hershey´s',250,92,'Otros Extras',0,1,42,40),(2113,'Caramelo Hershey´s',250,92,'Otros Extras',0,1,43,40),(2114,'Crema batida',250,92,'Otros Extras',0,1,41,40),(2115,'Helado Vainilla',600,92,'Otros Extras',0,1,34,80),(2116,'Sirope Vainilla',250,93,'Otros Extras',0,1,37,30),(2117,'Sirope Caramelo',250,93,'Otros Extras',0,1,39,30),(2118,'Chocolate Hershey´s',250,93,'Otros Extras',0,1,42,40),(2119,'Caramelo Hershey´s',250,93,'Otros Extras',0,1,43,40),(2120,'Crema batida',250,93,'Otros Extras',0,1,41,40),(2121,'Helado Vainilla',600,93,'Otros Extras',0,1,34,100),(2122,'Sirope Vainilla',250,90,'Otros Extras',0,1,37,30),(2123,'Sirope Caramelo',250,90,'Otros Extras',0,1,39,30),(2124,'Sirope Avellana',250,90,'Otros Extras',0,1,55,30),(2125,'Chocolate Hershey´s',250,90,'Otros Extras',0,1,42,40),(2126,'Caramelo Hershey´s',250,90,'Otros Extras',0,1,43,40),(2127,'Crema batida',250,90,'Otros Extras',0,1,41,40),(2128,'Almendras',250,90,'Otros Extras',0,1,48,30),(2129,'Helado Vainilla',600,90,'Otros Extras',0,1,34,80),(2130,'Sirope Vainilla',250,91,'Otros Extras',0,1,37,30),(2131,'Sirope Caramelo',250,91,'Otros Extras',0,1,39,30),(2132,'Sirope Avellana',250,91,'Otros Extras',0,1,55,30),(2133,'Chocolate Hershey´s',250,91,'Otros Extras',0,1,42,40),(2134,'Caramelo Hershey´s',250,91,'Otros Extras',0,1,43,40),(2135,'Crema batida',250,91,'Otros Extras',0,1,41,40),(2136,'Almendras',250,91,'Otros Extras',0,1,48,30),(2137,'Helado Vainilla',600,91,'Otros Extras',0,1,34,100),(2138,'Helado Vainilla',600,101,'Otros Extras',0,1,34,100),(2139,'Crema batida',250,101,'Otros Extras',0,1,41,30),(2140,'Chocolate Hershey´s',250,52,'Otros Extras',0,1,42,40),(2141,'Crema batida',250,52,'Otros Extras',0,1,41,40),(2142,'Helado',600,52,'Otros Extras',0,1,34,80),(2143,'Chocolate Hershey´s',250,53,'Otros Extras',0,1,42,40),(2144,'Crema batida',250,53,'Otros Extras',0,1,41,40),(2145,'Helado Vainilla',600,53,'Otros Extras',0,1,34,100),(2146,'Crema batida',250,44,'Otros Extras',0,1,41,40),(2147,'Crema batida',250,45,'Otros Extras',0,1,41,40),(2148,'Sirope Vainilla',250,48,'Otros Extras',0,1,37,30),(2149,'Sirope Caramelo',250,48,'Otros Extras',0,1,39,30),(2150,'Caramelo Hershey´s',250,48,'Otros Extras',0,1,43,40),(2151,'Crema batida',250,48,'Otros Extras',0,1,41,40),(2152,'Sirope Vainilla',250,49,'Otros Extras',0,1,37,30),(2153,'Sirope Caramelo',250,49,'Otros Extras',0,1,39,30),(2154,'Caramelo Hershey´s',250,49,'Otros Extras',0,1,43,40),(2155,'Crema batida',250,49,'Otros Extras',0,1,41,40),(2156,'Leche entera',400,66,'Extra Leche',0,0,25,250),(2157,'Leche descremada',400,66,'Extra Leche',0,0,27,250),(2158,'Leche deslactosada',400,66,'Extra Leche',0,0,19,250),(2159,'Leche almendras',500,66,'Extra Leche',0,0,26,250),(2162,'Hierbabuena',300,66,'Otros Extras',0,1,64,1),(2163,'Crema batida',250,66,'Otros Extras',0,1,41,40),(2164,'Helado Vainilla',600,66,'Otros Extras',0,1,34,80),(2165,'Sustituto extra',0,67,'Extra Azúcar',0,0,29,2),(2166,'Leche entera',400,67,'Extra Leche',0,0,25,350),(2167,'Leche descremada',400,67,'Extra Leche',0,0,27,350),(2168,'Leche deslactosada',400,67,'Extra Leche',0,0,19,350),(2169,'Leche almendras',500,67,'Extra Leche',0,0,26,350),(2170,'Hierbabuena',300,67,'Otros Extras',0,1,64,1),(2171,'Crema batida',250,67,'Otros Extras',0,1,41,40),(2172,'Helado Vainilla',600,67,'Otros Extras',0,1,34,100),(2173,'Limón',250,66,'Otros Extras',0,1,66,30),(2174,'Limón',250,67,'Otros Extras',0,1,66,30),(2175,'Leche entera',400,76,'Extra Leche',0,0,25,250),(2176,'Leche descremada',400,76,'Extra Leche',0,0,27,250),(2177,'Leche deslactosada',400,76,'Extra Leche',0,0,19,250),(2178,'Leche almendras',500,76,'Extra Leche',0,0,26,250),(2179,'Hierbabuena',300,76,'Otros Extras',0,1,64,1),(2180,'Helado Vainilla',600,76,'Otros Extras',0,1,34,80),(2181,'Crema batida',250,76,'Otros Extras',0,1,41,40),(2182,'Limón',250,76,'Otros Extras',0,1,66,30),(2187,'Leche entera',400,77,'Extra Leche',0,0,25,350),(2188,'Leche descremada',400,77,'Extra Leche',0,0,27,350),(2189,'Leche deslactosada',400,77,'Extra Leche',0,0,19,350),(2190,'Leche almendras',500,77,'Extra Leche',0,0,26,350),(2191,'Hierbabuena',300,77,'Otros Extras',0,1,64,1),(2192,'Crema batida',250,77,'Otros Extras',0,1,41,40),(2193,'Helado Vainilla',600,77,'Otros Extras',0,1,34,100),(2194,'Limón',250,77,'Otros Extras',0,1,66,30),(2195,'Hierbabuena',300,74,'Otros Extras',0,1,64,1),(2196,'Limón',250,74,'Otros Extras',0,1,66,30),(2197,'Limón',250,75,'Otros Extras',0,1,66,30),(2198,'Hierbabuena',300,75,'Otros Extras',0,1,64,1),(2199,'Leche entera',400,68,'Extra Leche',0,0,25,250),(2200,'Leche descremada',400,68,'Extra Leche',0,0,27,250),(2201,'Leche deslactosada',400,68,'Extra Leche',0,0,19,250),(2202,'Leche almendras',500,68,'Extra Leche',0,0,26,250),(2203,'Hierbabuena',300,68,'Otros Extras',0,1,64,1),(2204,'Crema batida',250,68,'Otros Extras',0,1,41,40),(2205,'Helado Vainilla',600,68,'Otros Extras',0,1,34,80),(2206,'Limón',250,68,'Otros Extras',0,1,66,30),(2207,'Azúcar extra',0,69,'Extra Azúcar',0,0,24,2),(2213,'Sustituto extra',0,69,'Extra Azúcar',0,0,29,2),(2214,'Leche entera',400,69,'Extra Leche',0,0,25,350),(2215,'Leche descremada',400,69,'Extra Leche',0,0,27,350),(2216,'Leche deslactosada',400,69,'Extra Leche',0,0,19,350),(2217,'Leche almendras',500,69,'Extra Leche',0,0,26,350),(2218,'Hierbabuena',300,69,'Otros Extras',0,1,64,1),(2219,'Crema batida',250,69,'Otros Extras',0,1,41,40),(2220,'Helado Vainilla',600,69,'Otros Extras',0,1,34,100),(2221,'Limón',250,69,'Otros Extras',0,1,66,30),(2222,'Leche entera',400,72,'Extra Leche',0,0,25,250),(2223,'Leche descremada',400,72,'Extra Leche',0,0,27,250),(2224,'Leche deslactosada',400,72,'Extra Leche',0,0,19,250),(2225,'Leche almendras',500,72,'Extra Leche',0,0,26,250),(2226,'Hierbabuena',300,72,'Otros Extras',0,1,64,1),(2227,'Crema batida',250,72,'Otros Extras',0,1,41,40),(2228,'Helado Vainilla',600,72,'Otros Extras',0,1,34,80),(2229,'Limón',250,72,'Otros Extras',0,1,66,30),(2230,'Leche entera',400,73,'Extra Leche',0,0,25,350),(2231,'Leche descremada',400,73,'Extra Leche',0,0,27,350),(2232,'Leche deslactosada',400,73,'Extra Leche',0,0,19,350),(2233,'Leche almendras',500,73,'Extra Leche',0,0,26,350),(2234,'Hierbabuena',300,73,'Otros Extras',0,1,64,1),(2235,'Crema batida',250,73,'Otros Extras',0,1,41,40),(2236,'Helado Vainilla',600,73,'Otros Extras',0,1,34,100),(2237,'Limón',250,73,'Otros Extras',0,1,66,30),(2238,'Leche entera',400,70,'Extra Leche',0,0,25,250),(2239,'Leche descremada',400,70,'Extra Leche',0,0,27,250),(2240,'Leche deslactosada',400,70,'Extra Leche',0,0,19,250),(2241,'Leche almendras',500,70,'Extra Leche',0,0,26,250),(2242,'Hierbabuena',300,70,'Otros Extras',0,1,64,1),(2243,'Crema batida',250,70,'Otros Extras',0,1,41,40),(2244,'Helado Vainilla',600,70,'Otros Extras',0,1,34,80),(2245,'Limón',250,70,'Otros Extras',0,1,66,30),(2246,'Leche entera',400,71,'Extra Leche',0,0,25,350),(2247,'Leche descremada',400,71,'Extra Leche',0,0,27,350),(2248,'Leche deslactosada',400,71,'Extra Leche',0,0,19,350),(2250,'Leche almendras',500,71,'Extra Leche',0,0,26,350),(2251,'Hierbabuena',300,71,'Otros Extras',0,1,64,1),(2252,'Crema batida',250,71,'Otros Extras',0,1,41,40),(2253,'Helado Vainilla',600,71,'Otros Extras',0,1,34,100),(2254,'Limón',250,71,'Otros Extras',0,1,66,30),(2255,'Caramelo Hershey´s',250,62,'Otros Extras',0,1,43,40),(2256,'Crema batida',250,62,'Otros Extras',0,1,41,40),(2257,'Helado Vainilla',600,62,'Otros Extras',0,1,34,80),(2258,'Caramelo Hershey´s',250,63,'Otros Extras',0,1,43,40),(2259,'Crema batida',250,63,'Otros Extras',0,1,41,40),(2260,'Helado Vainilla',600,63,'Otros Extras',0,1,34,100),(2261,'Helado Vainilla',600,64,'Otros Extras',0,1,34,80),(2262,'Crema batida',250,64,'Otros Extras',0,1,41,40),(2263,'Chocolate Hershey´s',250,64,'Otros Extras',0,1,42,40),(2264,'Chocolate Hershey´s',250,65,'Otros Extras',0,1,42,40),(2265,'Crema batida',250,65,'Otros Extras',0,1,41,40),(2266,'Helado Vainilla',600,65,'Otros Extras',0,1,34,100),(2267,'Leche entera',0,96,'Tipo Leche',1,0,25,75),(2268,'Leche descremada',0,96,'Tipo Leche',1,0,27,75),(2269,'Leche deslactosada',0,96,'Tipo Leche',1,0,19,75),(2270,'Leche almendras',0,96,'Tipo Leche',1,0,26,75),(2271,'Almendras',300,96,'Otros Extras',0,1,48,30),(2272,'Crema batida',250,96,'Otros Extras',0,1,41,40),(2273,'Helado Vainilla',600,96,'Otros Extras',0,1,34,80),(2274,'Sirope Vainilla',250,96,'Otros Extras',0,1,37,30),(2275,'Leche entera',0,97,'Tipo Leche',1,0,25,100),(2276,'Leche descremada',0,97,'Tipo Leche',1,0,27,100),(2277,'Leche deslactosada',0,97,'Tipo Leche',1,0,19,100),(2278,'Leche almendras',0,97,'Tipo Leche',1,0,26,100),(2279,'Almendras',300,97,'Otros Extras',0,1,26,30),(2280,'Crema batida',250,97,'Otros Extras',0,1,41,40),(2281,'Sirope Vainilla',250,97,'Otros Extras',0,1,37,30),(2282,'Helado Vainilla',600,97,'Otros Extras',0,1,34,100),(2283,'Leche entera',0,56,'Tipo Leche',1,0,25,200),(2284,'Leche descremada',0,56,'Tipo Leche',1,0,27,200),(2285,'Leche deslactosada',0,56,'Tipo Leche',1,0,19,200),(2286,'Leche almendras',0,56,'Tipo Leche',1,0,26,200),(2287,'Crema batida',250,56,'Otros Extras',0,1,41,40),(2288,'Helado Vainilla',600,56,'Otros Extras',0,1,34,80),(2289,'Leche entera',0,57,'Tipo Leche',1,0,25,250),(2290,'Leche descremada',0,57,'Tipo Leche',1,0,27,250),(2291,'Leche deslactosada',0,57,'Tipo Leche',1,0,19,250),(2292,'Leche almendras',0,57,'Tipo Leche',1,0,26,250),(2293,'Crema batida',250,57,'Otros Extras',0,1,41,40),(2294,'Helado Vainilla',600,57,'Otros Extras',0,1,34,100),(2295,'Leche descremada',0,58,'Tipo Leche',1,0,27,130),(2296,'Caramelo Hershey´s',250,58,'Otros Extras',0,1,43,40),(2297,'Crema batida',250,58,'Otros Extras',0,1,41,40),(2298,'Helado Vainilla',600,58,'Otros Extras',0,1,34,80),(2300,'Crema batida',250,59,'Otros Extras',0,1,41,40),(2301,'Caramelo Hershey´s',250,59,'Otros Extras',0,1,43,40),(2302,'Helado Vainilla',600,59,'Otros Extras',0,1,34,100),(2303,'Sirope Vainilla',250,60,'Otros Extras',0,1,37,30),(2304,'Crema batida',250,60,'Otros Extras',0,1,41,40),(2305,'Helado Vainilla',600,60,'Otros Extras',0,1,34,80),(2306,'Helado Vainilla',600,61,'Otros Extras',0,1,34,100),(2307,'Crema batida',250,61,'Otros Extras',0,1,41,40),(2308,'Sirope Vainilla',250,61,'Otros Extras',0,1,37,30);
/*!40000 ALTER TABLE `extra_producto_menu` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fe_info`
--

DROP TABLE IF EXISTS `fe_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fe_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orden` int(11) NOT NULL,
  `cedula` varchar(25) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `correo` varchar(150) NOT NULL,
  `estado` int(11) NOT NULL,
  `num_comprobante` varchar(500) DEFAULT NULL,
  `id_pago` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `orden_fk04` (`orden`),
  KEY `estado_Fe_fk01` (`estado`),
  CONSTRAINT `estado_Fe_fk01` FOREIGN KEY (`estado`) REFERENCES `sis_estado` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `orden_fk04` FOREIGN KEY (`orden`) REFERENCES `orden` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fe_info`
--

LOCK TABLES `fe_info` WRITE;
/*!40000 ALTER TABLE `fe_info` DISABLE KEYS */;
/*!40000 ALTER TABLE `fe_info` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gasto`
--

DROP TABLE IF EXISTS `gasto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gasto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `estado` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `gas_usuario_fk1` (`usuario`),
  KEY `gas_proveedor_fk1` (`proveedor`),
  KEY `gas_tipo_gasto_fk1` (`tipo_gasto`),
  KEY `gas_tipo_pago_fk1` (`tipo_pago`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gasto`
--

LOCK TABLES `gasto` WRITE;
/*!40000 ALTER TABLE `gasto` DISABLE KEYS */;
/*!40000 ALTER TABLE `gasto` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `grupo_promocion`
--

DROP TABLE IF EXISTS `grupo_promocion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `grupo_promocion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(5000) NOT NULL,
  `precio` int(11) NOT NULL,
  `estado` int(1) NOT NULL DEFAULT 1,
  `categoria` int(11) DEFAULT NULL,
  `imagen` varchar(5000) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `grupo_promocion`
--

LOCK TABLES `grupo_promocion` WRITE;
/*!40000 ALTER TABLE `grupo_promocion` DISABLE KEYS */;
/*!40000 ALTER TABLE `grupo_promocion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `impuesto`
--

DROP TABLE IF EXISTS `impuesto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `impuesto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(50) NOT NULL,
  `impuesto` float NOT NULL DEFAULT 0,
  `estado` varchar(1) NOT NULL DEFAULT 'A',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `impuesto`
--

LOCK TABLES `impuesto` WRITE;
/*!40000 ALTER TABLE `impuesto` DISABLE KEYS */;
INSERT INTO `impuesto` VALUES (1,'Al valor agregado 13%',14,'I'),(2,'IVA 13',13,'A'),(3,'IVA EX',0,'I'),(4,'IVA 1%',1,'A');
/*!40000 ALTER TABLE `impuesto` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ingreso`
--

DROP TABLE IF EXISTS `ingreso`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ingreso` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
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
  `estado` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ing_tipo_ingreso_fk1` (`tipo`),
  KEY `ing_cliente_fk1` (`cliente`),
  KEY `ingreso_fk01` (`estado`),
  CONSTRAINT `cod_tipo_fk56` FOREIGN KEY (`tipo`) REFERENCES `tipo_ingreso` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `ingreso_fk01` FOREIGN KEY (`estado`) REFERENCES `sis_estado` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ingreso`
--

LOCK TABLES `ingreso` WRITE;
/*!40000 ALTER TABLE `ingreso` DISABLE KEYS */;
/*!40000 ALTER TABLE `ingreso` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log`
--

DROP TABLE IF EXISTS `log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `descripcion` blob NOT NULL,
  `documento` varchar(1000) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log`
--

LOCK TABLES `log` WRITE;
/*!40000 ALTER TABLE `log` DISABLE KEYS */;
/*!40000 ALTER TABLE `log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `materia_prima`
--

DROP TABLE IF EXISTS `materia_prima`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `materia_prima` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(5000) NOT NULL,
  `proveedor` int(11) NOT NULL,
  `unidad_medida` varchar(500) NOT NULL,
  `precio` double NOT NULL,
  `activo` int(11) NOT NULL DEFAULT 1,
  `cant_min_deseada` double DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=84 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `materia_prima`
--

LOCK TABLES `materia_prima` WRITE;
/*!40000 ALTER TABLE `materia_prima` DISABLE KEYS */;
INSERT INTO `materia_prima` VALUES (1,'Café Villa Sarchí',2,'g',9.09,1,2000),(2,'Tapa Vaso Cartón 8-12oz',9,'u',0,1,50),(3,'Vaso cartón 12oz',9,'u',130,1,50),(4,'Té Matcha',17,'u',220,1,6),(5,'Vaso plástico 12oz',9,'u',130,1,50),(6,'Vaso plástico 16oz',9,'u',130,1,50),(7,'Pajilla gruesa',18,'u',32.5,1,30),(8,'Cuchillo plástico',19,'u',8,1,10),(9,'Tenedor plástico',19,'u',8,1,15),(10,'Té Manzanilla',17,'u',55,1,8),(11,'Té de Frambuesa y Granada',17,'u',88.5,1,8),(12,'Té de Frutos Rojos',17,'u',85,1,8),(13,'Té de Tilo',17,'u',55,1,8),(14,'Té Negro',17,'u',55,1,8),(15,'Té Verde',17,'u',55,1,8),(16,'Té de Menta',17,'u',55,1,8),(17,'Té Colité',17,'u',55,1,8),(18,'Tapa Vaso Plástico 12-16oz',9,'u',0,1,50),(19,'Leche deslactosada',7,'ml',0.9,1,6000),(20,'Vaso cartón 8oz',9,'u',130,1,50),(21,'Caja cartón Coffee To Go',9,'u',130,1,30),(22,'Pajilla negra',21,'u',11.4,1,20),(23,'Azúcar',7,'g',0.7,1,200),(24,'Sobre azúcar',3,'u',5.7,1,NULL),(25,'Leche entera Foodie',7,'ml',0.8,1,6000),(26,'Leche de almendras',3,'ml',1.3,1,1892),(27,'Lecha descremada',7,'ml',0.8,1,5000),(28,'Tapa Vaso plástico 12/16 oz',15,'u',0,0,NULL),(29,'Sobre sustituto',3,'u',6,1,NULL),(30,'Papel Antigrasa',4,'u',15.6,1,NULL),(31,'Bowl cartón',7,'u',97,1,10),(32,'Cuchara plástica',7,'u',7,1,NULL),(33,'Yogurt',3,'Litro',3430,0,NULL),(34,'Helado Vainilla',7,'g',1.4,1,1500),(35,'Bolsa craft',25,'u',16,1,NULL),(36,'Bolsa craft',25,'Bolsa',16,0,NULL),(37,'Sirope Vainilla',7,'ml',7,1,500),(38,'Sirope Amaretto',7,'ml',6.6,1,NULL),(39,'Sirope Caramelo',7,'ml',7,1,300),(40,'Sirope Crema Irlandesa',7,'ml',7,1,NULL),(41,'Crema Chantilly',7,'g',7,1,453),(42,'Chocolate Hershey’s',7,'g',3.4,1,300),(43,'Caramelo Hershey’s',7,'g',4.5,1,300),(44,'Aceite spray Pam',3,'u',2747.5,1,5),(45,'Marshmallows pequeños',3,'g',4.3,1,100),(46,'Canela en polvo',3,'g',4.8,1,NULL),(47,'Maní',7,'g',2.5,1,300),(48,'Almendras',3,'g',5.5,1,200),(49,'Miel de maple',3,'ml',2.1,1,NULL),(50,'Mezcla de panqueques',3,'g',1.2,1,NULL),(51,'Chispas de chocolate',3,'g',3.6,1,NULL),(52,'Cocoa dulce',7,'g',3,1,500),(53,'Sirope Marshmallow Tostado',7,'ml',9.3,1,NULL),(54,'Sirope Marshmallow Tostado',7,'ml',5256,0,NULL),(55,'Sirope Avellana',7,'ml',6.8,1,200),(56,'Portavasos cartón 2 divisiones',7,'u',85,1,NULL),(57,'Cobertura de chocolate',7,'g',5,1,300),(58,'Tapas plástico 12-16 onz',15,'Unidad',1,0,NULL),(59,'Tapas bowl cartón',7,'u',29,1,15),(60,'Fresa congelada',30,'g',1.5,1,2000),(61,'Piña congelada',30,'g',1.2,1,2000),(62,'Frutos Rojos congelados',30,'g',3.5,1,2000),(63,'Mora Congelada',30,'g',4,1,2000),(64,'Hieba Buena Congelada',32,'Porcion (15 hojas)',26.19,1,5),(65,'Portavaso individual',7,'u',1,1,15),(66,'Jugo de limón',16,'ml',3.7,1,700),(67,'Marshmallows grande',17,'g',5,1,NULL),(68,'Fresa bowl',17,'g',1,1,NULL),(69,'Piña bowl',17,'g',1,1,NULL),(70,'Papaya bowl',17,'g',1,1,NULL),(71,'Banano bowl',17,'u',1,1,NULL),(72,'Kiwi bowl',17,'g',1,1,NULL),(73,'Mango bowl',17,'g',1,1,NULL),(74,'Arándanos congelados',3,'g',4.4,1,300),(75,'Yogurt natural',17,'g',3.5,1,NULL),(76,'Granola',17,'g',7.1,1,NULL),(77,'Miel de abeja',17,'g',1,1,NULL),(78,'Azúcar glass',7,'g',1,1,NULL),(79,'Confitería de chocolate',7,'g',3.2,1,NULL),(80,'Confitería de colores',7,'g',4.8,1,NULL),(81,'Bolsa cubierto',7,'u',1,1,NULL),(82,'Dulce Molido',33,'g',1.6,1,300),(83,'Fresas Picadas',33,'g',3000,1,1);
/*!40000 ALTER TABLE `materia_prima` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menu`
--

DROP TABLE IF EXISTS `menu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rol` int(11) NOT NULL,
  `vista` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mnu_rol_fk01` (`rol`),
  KEY `mnu_vista_fk01` (`vista`)
) ENGINE=InnoDB AUTO_INCREMENT=2170 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menu`
--

LOCK TABLES `menu` WRITE;
/*!40000 ALTER TABLE `menu` DISABLE KEYS */;
INSERT INTO `menu` VALUES (7,36,2),(8,36,3),(9,36,4),(10,36,5),(11,36,7),(12,36,8),(13,36,1),(57,37,16),(58,37,15),(677,39,64),(678,39,65),(679,39,59),(978,40,71),(979,40,69),(1628,26,80),(1629,26,57),(1630,26,56),(1631,26,65),(1632,26,60),(1633,26,53),(1634,26,55),(1635,26,59),(1904,41,80),(1905,41,60),(1906,41,82),(1907,41,53),(1908,41,59),(1909,41,81),(1958,38,5),(1959,38,9),(1960,38,10),(1961,38,11),(1962,38,12),(1963,38,66),(1964,38,68),(1965,38,16),(1966,38,18),(1967,38,20),(1968,38,63),(1969,38,22),(1970,38,54),(1971,38,58),(1972,38,80),(1973,38,74),(1974,38,76),(1975,38,77),(1976,38,78),(1977,38,79),(1978,38,71),(1979,38,50),(1980,38,52),(1981,38,57),(1982,38,56),(1983,38,64),(1984,38,65),(1985,38,67),(1986,38,60),(1987,38,1),(1988,38,15),(1989,38,19),(1990,38,53),(1991,38,73),(1992,38,69),(1993,38,49),(1994,38,55),(1995,38,59),(2004,42,18),(2005,42,22),(2006,42,74),(2007,42,76),(2008,42,79),(2009,42,67),(2010,42,15),(2011,42,19),(2012,42,73),(2013,42,59),(2116,25,2),(2117,25,3),(2118,25,4),(2119,25,5),(2120,25,8),(2121,25,9),(2122,25,10),(2123,25,11),(2124,25,12),(2125,25,30),(2126,25,66),(2127,25,68),(2128,25,16),(2129,25,18),(2130,25,20),(2131,25,63),(2132,25,22),(2133,25,54),(2134,25,58),(2135,25,80),(2136,25,74),(2137,25,75),(2138,25,76),(2139,25,77),(2140,25,78),(2141,25,79),(2142,25,71),(2143,25,50),(2144,25,52),(2145,25,57),(2146,25,56),(2147,25,64),(2148,25,65),(2149,25,67),(2150,25,60),(2151,25,82),(2152,25,84),(2153,25,86),(2154,25,88),(2155,25,90),(2156,25,1),(2157,25,15),(2158,25,19),(2159,25,53),(2160,25,73),(2161,25,69),(2162,25,49),(2163,25,55),(2164,25,59),(2165,25,81),(2166,25,83),(2167,25,85),(2168,25,87),(2169,25,89);
/*!40000 ALTER TABLE `menu` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mesa`
--

DROP TABLE IF EXISTS `mesa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mesa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sucursal` int(11) NOT NULL,
  `numero_mesa` varchar(15) NOT NULL,
  `capacidad` int(11) NOT NULL DEFAULT 0,
  `estado` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mesa_fk01` (`sucursal`),
  KEY `mesa_fk02` (`estado`),
  CONSTRAINT `mesa_fk01` FOREIGN KEY (`sucursal`) REFERENCES `sucursal` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `mesa_fk02` FOREIGN KEY (`estado`) REFERENCES `sis_estado` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mesa`
--

LOCK TABLES `mesa` WRITE;
/*!40000 ALTER TABLE `mesa` DISABLE KEYS */;
/*!40000 ALTER TABLE `mesa` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mt_x_extra`
--

DROP TABLE IF EXISTS `mt_x_extra`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mt_x_extra` (
  `id` int(11) NOT NULL,
  `materia_prima` int(11) NOT NULL,
  `extra` int(11) NOT NULL,
  `cantidad` double NOT NULL,
  KEY `extra_fk013` (`materia_prima`),
  CONSTRAINT `extra_fk013` FOREIGN KEY (`materia_prima`) REFERENCES `materia_prima` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mt_x_extra`
--

LOCK TABLES `mt_x_extra` WRITE;
/*!40000 ALTER TABLE `mt_x_extra` DISABLE KEYS */;
/*!40000 ALTER TABLE `mt_x_extra` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mt_x_producto`
--

DROP TABLE IF EXISTS `mt_x_producto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mt_x_producto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `materia_prima` int(11) NOT NULL,
  `producto` int(11) NOT NULL,
  `cantidad` double NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_mt_01` (`materia_prima`),
  KEY `fk_prod_01` (`producto`),
  CONSTRAINT `fk_mt_01` FOREIGN KEY (`materia_prima`) REFERENCES `materia_prima` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_prod_01` FOREIGN KEY (`producto`) REFERENCES `producto_menu` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=533 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mt_x_producto`
--

LOCK TABLES `mt_x_producto` WRITE;
/*!40000 ALTER TABLE `mt_x_producto` DISABLE KEYS */;
INSERT INTO `mt_x_producto` VALUES (7,1,25,0.014),(8,2,25,1),(9,1,26,0.021),(10,3,26,1),(18,10,80,2),(20,16,81,2),(24,15,84,2),(31,13,83,2),(32,14,85,2),(38,11,86,2),(39,12,87,2),(44,17,82,2),(47,7,70,1),(50,6,97,1),(54,7,57,1),(55,7,66,1),(59,22,85,1),(60,22,84,1),(65,20,25,1),(66,20,30,1),(67,20,32,1),(68,20,34,1),(69,20,38,1),(70,20,40,1),(71,20,88,1),(72,2,25,1),(73,2,30,1),(74,2,32,1),(75,2,34,1),(76,2,38,1),(77,2,40,1),(78,2,88,1),(79,3,26,1),(80,3,31,1),(81,3,33,1),(82,3,35,1),(83,3,39,1),(84,3,41,1),(85,3,89,1),(86,2,26,1),(87,2,31,1),(88,2,33,1),(89,2,35,1),(90,2,39,1),(91,2,41,1),(92,2,89,1),(93,3,80,1),(94,2,80,1),(95,2,82,1),(96,3,82,1),(97,22,87,1),(98,3,83,1),(99,2,83,1),(122,5,44,1),(123,5,48,1),(124,5,52,1),(125,5,90,1),(126,5,92,1),(127,5,94,1),(129,28,44,1),(130,28,45,1),(131,28,48,1),(132,28,49,1),(133,28,52,1),(134,28,53,1),(135,28,90,1),(136,28,91,1),(137,28,92,1),(138,28,93,1),(139,28,94,1),(140,28,95,1),(144,6,45,1),(145,6,49,1),(146,6,53,1),(147,6,91,1),(148,6,93,1),(149,6,95,1),(153,7,48,1),(154,7,49,1),(155,7,52,1),(156,7,53,1),(159,7,92,1),(160,7,93,1),(161,7,94,1),(162,7,95,1),(166,7,67,1),(167,7,69,1),(168,7,71,1),(169,7,73,1),(170,7,75,1),(171,7,77,1),(181,7,68,1),(183,7,72,1),(184,7,74,1),(185,7,76,1),(187,5,66,1),(188,5,68,1),(189,5,70,1),(190,5,72,1),(191,5,74,1),(192,5,76,1),(194,6,67,1),(195,6,69,1),(196,6,71,1),(197,6,73,1),(198,6,75,1),(199,6,77,1),(201,28,66,1),(202,28,67,1),(203,28,68,1),(204,28,69,1),(205,28,70,1),(206,28,71,1),(207,28,72,1),(208,28,73,1),(209,28,74,1),(210,28,75,1),(211,28,76,1),(212,28,77,1),(216,28,56,1),(217,28,57,1),(218,28,58,1),(219,28,59,1),(220,28,60,1),(221,28,61,1),(222,28,62,1),(223,28,63,1),(224,28,64,1),(225,28,65,1),(226,28,96,1),(227,28,97,1),(231,7,56,1),(233,7,58,1),(234,7,59,1),(235,7,60,1),(236,7,61,1),(237,7,62,1),(238,7,63,1),(239,7,64,1),(240,7,65,1),(241,7,96,1),(242,7,97,1),(246,5,56,1),(247,5,58,1),(248,5,60,1),(249,5,62,1),(250,5,64,1),(251,5,96,1),(253,6,57,1),(254,6,59,1),(255,6,61,1),(256,6,63,1),(257,6,65,1),(260,30,28,1),(263,30,29,1),(264,31,28,1),(265,31,29,1),(266,8,29,1),(267,8,28,1),(268,9,28,1),(269,9,29,1),(270,31,98,1),(271,31,99,1),(272,32,98,1),(273,32,99,1),(274,9,98,1),(275,9,99,1),(276,34,29,100),(288,1,39,21),(289,1,45,35),(290,1,44,21),(291,1,90,21),(292,1,91,35),(293,1,49,35),(294,1,48,21),(295,1,92,21),(296,37,34,15),(297,37,35,30),(298,1,100,21),(299,35,100,1),(300,3,100,1),(301,2,100,1),(302,1,101,14),(303,5,101,1),(305,1,103,21),(306,3,103,1),(307,2,103,1),(308,21,103,1),(309,1,104,21),(310,3,104,1),(311,2,104,1),(312,21,104,1),(313,1,105,21),(314,2,105,1),(315,3,105,1),(316,21,105,1),(319,34,62,80),(320,34,64,80),(322,34,56,80),(323,34,58,80),(324,34,60,80),(325,34,61,100),(326,34,59,100),(327,34,57,100),(328,34,97,100),(329,34,65,100),(330,34,63,100),(334,6,108,1),(335,18,108,1),(336,7,108,1),(337,7,107,1),(338,5,107,1),(339,18,107,1),(340,53,107,60),(341,34,107,80),(342,60,66,140),(343,60,67,170),(344,60,56,140),(345,60,57,170),(346,61,72,140),(347,61,70,140),(348,61,71,170),(349,61,73,170),(355,63,68,140),(356,63,69,170),(357,62,77,170),(358,62,76,140),(359,64,73,1),(360,64,72,1),(361,64,75,1),(362,64,74,1),(363,1,93,35),(364,1,95,35),(365,1,94,21),(366,37,92,30),(367,37,93,60),(369,37,94,30),(371,37,95,60),(372,48,95,45),(373,65,30,1),(374,65,31,1),(376,57,38,30),(377,57,39,40),(378,52,40,45),(379,23,40,5),(380,45,40,10),(381,52,41,75),(382,23,41,7.5),(383,45,41,10),(386,22,44,1),(387,22,45,1),(390,37,48,30),(391,37,49,45),(392,7,90,1),(393,7,91,1),(394,48,94,30),(395,52,52,75),(396,23,52,7.5),(397,52,53,105),(398,23,53,10),(400,4,78,2),(401,22,78,1),(403,22,86,1),(404,22,81,1),(406,23,66,20),(407,23,67,25),(408,23,68,20),(409,23,69,25),(410,23,76,20),(411,23,77,25),(412,23,70,20),(413,23,71,25),(414,23,72,20),(415,23,73,25),(416,66,74,60),(417,23,74,25),(418,23,75,30),(419,66,75,90),(420,23,56,7.5),(421,23,57,10),(422,47,58,60),(423,23,58,10),(424,23,59,12.5),(425,47,59,90),(426,37,60,90),(427,37,61,120),(428,39,62,90),(429,39,63,120),(430,52,64,30),(431,52,65,45),(432,37,96,30),(433,48,96,30),(434,1,96,14),(435,34,96,80),(436,37,97,45),(437,48,97,45),(438,1,97,14),(441,41,92,30),(442,41,93,30),(443,42,93,40),(444,42,92,40),(445,41,90,30),(446,41,91,30),(447,41,101,30),(448,41,52,30),(449,41,53,30),(450,41,48,30),(451,41,49,30),(452,43,48,40),(453,43,49,40),(454,41,62,30),(455,41,63,30),(456,41,64,30),(457,41,65,30),(458,41,96,30),(459,41,97,30),(460,41,56,30),(461,41,57,30),(462,41,58,30),(463,41,59,30),(464,41,60,30),(465,41,61,30),(466,43,62,50),(467,43,63,50),(468,42,64,50),(469,42,65,50),(472,43,58,15),(473,43,59,15),(474,67,107,6),(475,41,107,40),(476,53,108,90),(477,34,108,100),(478,41,108,40),(479,67,108,6),(480,59,98,1),(481,8,98,1),(482,59,99,1),(483,8,99,1),(484,75,99,45),(485,76,99,45),(486,77,99,20),(487,1,30,14),(488,1,31,21),(489,1,88,14),(490,1,89,21),(491,1,33,21),(492,1,32,21),(493,1,35,21),(494,1,34,14),(495,1,38,14),(498,2,109,1),(499,65,109,1),(500,82,109,30),(502,2,110,1),(503,65,110,1),(504,82,110,45),(505,59,28,1),(506,59,29,1),(507,1,111,14),(508,20,111,1),(509,2,111,1),(510,1,113,14),(511,2,113,1),(512,20,113,1),(513,1,114,21),(514,2,114,1),(515,20,114,1),(516,1,112,21),(517,2,112,1),(518,20,112,1),(519,34,101,100),(520,42,101,40),(521,41,94,40),(522,41,95,40),(523,18,101,1),(524,9,101,1),(525,8,101,1),(526,74,28,70),(527,49,28,60),(528,78,28,15),(529,42,29,80),(530,74,29,70),(531,83,29,50),(532,83,28,50);
/*!40000 ALTER TABLE `mt_x_producto` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mt_x_producto_ext`
--

DROP TABLE IF EXISTS `mt_x_producto_ext`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mt_x_producto_ext` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `materia_prima` int(11) NOT NULL,
  `producto` int(11) NOT NULL,
  `cantidad` double NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_mat_prim_01` (`materia_prima`),
  KEY `fk_prd_Ext_01` (`producto`),
  CONSTRAINT `fk_mat_prim_01` FOREIGN KEY (`materia_prima`) REFERENCES `materia_prima` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_prd_Ext_01` FOREIGN KEY (`producto`) REFERENCES `producto_externo` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=111 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mt_x_producto_ext`
--

LOCK TABLES `mt_x_producto_ext` WRITE;
/*!40000 ALTER TABLE `mt_x_producto_ext` DISABLE KEYS */;
INSERT INTO `mt_x_producto_ext` VALUES (3,21,3,1),(4,21,6,1),(5,21,53,1),(6,21,55,1),(7,21,56,1),(8,21,5,1),(9,21,54,1),(10,21,7,1),(11,30,3,1),(12,30,6,1),(13,30,53,1),(14,30,55,1),(15,30,56,1),(16,30,5,1),(17,30,54,1),(18,30,7,1),(23,21,12,1),(24,21,11,1),(25,21,10,1),(26,21,13,1),(27,21,15,1),(28,21,14,1),(29,21,16,1),(33,30,16,1),(35,30,14,1),(36,30,15,1),(37,30,13,1),(38,30,10,1),(39,30,11,1),(40,30,12,1),(41,35,18,1),(42,35,17,1),(44,21,58,1),(45,30,58,1),(46,35,61,1),(47,35,62,1),(48,8,13,1),(49,9,13,1),(52,9,10,1),(53,8,10,1),(54,8,11,1),(55,9,11,1),(58,9,12,1),(59,8,12,1),(60,21,63,1),(61,30,63,1),(62,8,63,1),(63,9,63,1),(65,21,19,1),(66,30,19,1),(67,8,19,1),(68,9,19,1),(70,21,20,1),(71,30,20,1),(72,8,20,1),(73,9,20,1),(75,21,21,1),(76,30,21,1),(77,8,21,1),(78,9,21,1),(80,21,22,1),(81,30,22,1),(82,8,22,1),(83,9,22,1),(85,21,24,1),(86,30,24,1),(87,30,32,1),(88,21,32,1),(89,21,33,1),(90,30,33,1),(91,8,33,1),(92,9,33,1),(94,31,8,1),(95,59,8,1),(96,30,8,1),(97,31,9,1),(98,59,9,1),(99,30,9,1),(100,21,66,1),(101,21,68,1),(102,21,67,1),(103,21,69,1),(104,31,59,1),(105,59,59,1),(106,30,59,1),(107,30,66,1),(108,30,68,1),(109,30,67,1),(110,30,69,1);
/*!40000 ALTER TABLE `mt_x_producto_ext` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mt_x_sucursal`
--

DROP TABLE IF EXISTS `mt_x_sucursal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mt_x_sucursal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `materia_prima` int(11) NOT NULL,
  `sucursal` int(11) NOT NULL,
  `cantidad` double NOT NULL,
  `ultima_modificacion` date NOT NULL DEFAULT current_timestamp(),
  `usuario_modifica` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mt_x_sucursal`
--

LOCK TABLES `mt_x_sucursal` WRITE;
/*!40000 ALTER TABLE `mt_x_sucursal` DISABLE KEYS */;
/*!40000 ALTER TABLE `mt_x_sucursal` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orden`
--

DROP TABLE IF EXISTS `orden`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orden` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `mto_impuesto_servicio` double NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `fk_clinete01` (`cliente`),
  KEY `fk_cajero_01` (`cajero`),
  KEY `estado_fk01` (`estado`),
  KEY `sucursal_fk01` (`sucursal`),
  KEY `caja_fk01` (`cierre_caja`),
  KEY `orden_fk05` (`mesa`),
  CONSTRAINT `caja_fk01` FOREIGN KEY (`cierre_caja`) REFERENCES `cierre_caja` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `estado_fk01` FOREIGN KEY (`estado`) REFERENCES `sis_estado` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_cajero_01` FOREIGN KEY (`cajero`) REFERENCES `usuario` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_clinete01` FOREIGN KEY (`cliente`) REFERENCES `cliente` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `orden_fk05` FOREIGN KEY (`mesa`) REFERENCES `mesa` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `sucursal_fk01` FOREIGN KEY (`sucursal`) REFERENCES `sucursal` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orden`
--

LOCK TABLES `orden` WRITE;
/*!40000 ALTER TABLE `orden` DISABLE KEYS */;
/*!40000 ALTER TABLE `orden` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orden_comanda`
--

DROP TABLE IF EXISTS `orden_comanda`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orden_comanda` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orden` int(11) NOT NULL,
  `num_comanda` varchar(50) DEFAULT NULL,
  `fecha_inicio` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_fin` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orden_comanda`
--

LOCK TABLES `orden_comanda` WRITE;
/*!40000 ALTER TABLE `orden_comanda` DISABLE KEYS */;
/*!40000 ALTER TABLE `orden_comanda` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pago_orden`
--

DROP TABLE IF EXISTS `pago_orden`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pago_orden` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `cod_promocion` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pago_orden_fk01` (`orden`),
  CONSTRAINT `pago_orden_fk01` FOREIGN KEY (`orden`) REFERENCES `orden` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pago_orden`
--

LOCK TABLES `pago_orden` WRITE;
/*!40000 ALTER TABLE `pago_orden` DISABLE KEYS */;
/*!40000 ALTER TABLE `pago_orden` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `panel_configuraciones`
--

DROP TABLE IF EXISTS `panel_configuraciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `panel_configuraciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `color_fondo` int(11) NOT NULL DEFAULT 1,
  `color_sidebar` int(11) NOT NULL DEFAULT 1,
  `color_tema` varchar(15) NOT NULL DEFAULT 'white',
  `mini_sidebar` int(11) NOT NULL DEFAULT 1,
  `sticky_topbar` int(11) NOT NULL DEFAULT 1,
  `usuario` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `pc_usuario_fk01` (`usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `panel_configuraciones`
--

LOCK TABLES `panel_configuraciones` WRITE;
/*!40000 ALTER TABLE `panel_configuraciones` DISABLE KEYS */;
INSERT INTO `panel_configuraciones` VALUES (7,1,1,'white',1,1,15),(56,1,1,'white',1,1,17),(68,1,1,'orange',1,1,26);
/*!40000 ALTER TABLE `panel_configuraciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `parametros_generales`
--

DROP TABLE IF EXISTS `parametros_generales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `parametros_generales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `porcentaje_banco` float NOT NULL DEFAULT 0,
  `tiempo_refresco_monitor_movimientos` int(11) NOT NULL DEFAULT 5,
  `inicio_mes_panaderia` double DEFAULT 0,
  `inicio_mes_cafeteria` double NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `parametros_generales`
--

LOCK TABLES `parametros_generales` WRITE;
/*!40000 ALTER TABLE `parametros_generales` DISABLE KEYS */;
INSERT INTO `parametros_generales` VALUES (1,2,1,1,1);
/*!40000 ALTER TABLE `parametros_generales` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pe_x_sucursal`
--

DROP TABLE IF EXISTS `pe_x_sucursal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pe_x_sucursal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `producto_externo` int(11) NOT NULL,
  `cantidad` int(11) DEFAULT 0,
  `sucursal` int(11) NOT NULL,
  `ultima_modificacion` datetime NOT NULL DEFAULT current_timestamp(),
  `usuario_modifica` int(11) DEFAULT NULL,
  `comanda` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pexs_sucursal_fk01` (`sucursal`),
  KEY `pexs_pe_fk01` (`producto_externo`),
  KEY `pexs_usuario_fk01` (`usuario_modifica`),
  KEY `pe_fk01` (`comanda`),
  CONSTRAINT `pe_fk01` FOREIGN KEY (`comanda`) REFERENCES `comanda` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `pexs_pe_fk01` FOREIGN KEY (`producto_externo`) REFERENCES `producto_externo` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `pexs_sucursal_fk01` FOREIGN KEY (`sucursal`) REFERENCES `sucursal` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `pexs_usuario_fk01` FOREIGN KEY (`usuario_modifica`) REFERENCES `usuario` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pe_x_sucursal`
--

LOCK TABLES `pe_x_sucursal` WRITE;
/*!40000 ALTER TABLE `pe_x_sucursal` DISABLE KEYS */;
/*!40000 ALTER TABLE `pe_x_sucursal` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pm_x_sucursal`
--

DROP TABLE IF EXISTS `pm_x_sucursal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pm_x_sucursal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sucursal` int(11) NOT NULL,
  `producto_menu` int(11) NOT NULL,
  `comanda` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pm_fk01` (`comanda`),
  CONSTRAINT `pm_fk01` FOREIGN KEY (`comanda`) REFERENCES `comanda` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pm_x_sucursal`
--

LOCK TABLES `pm_x_sucursal` WRITE;
/*!40000 ALTER TABLE `pm_x_sucursal` DISABLE KEYS */;
/*!40000 ALTER TABLE `pm_x_sucursal` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `producto_externo`
--

DROP TABLE IF EXISTS `producto_externo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `producto_externo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `posicion_menu` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `pe_categoria_fk01` (`categoria`),
  KEY `pe_impuiesto_fk01` (`impuesto`),
  KEY `pe_proveedor_fk01` (`proveedor`),
  CONSTRAINT `pe_categoria_fk01` FOREIGN KEY (`categoria`) REFERENCES `categoria` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `pe_impuiesto_fk01` FOREIGN KEY (`impuesto`) REFERENCES `impuesto` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `pe_proveedor_fk01` FOREIGN KEY (`proveedor`) REFERENCES `proveedor` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `producto_externo`
--

LOCK TABLES `producto_externo` WRITE;
/*!40000 ALTER TABLE `producto_externo` DISABLE KEYS */;
INSERT INTO `producto_externo` VALUES (3,'CROI-REP','Croissant',550,2,8,550,'A',3,'Croissant de mantequilla pequeño','productos/bDRh8aZvc7L8LjWr6HXixLOsCwIQXmaF9HUuIaOA.png',7),(5,'RP-REP-SAL','Enchilada de papa',600,2,8,950,'A',23,'Enchilada salada rellena de papa','productos/WKwv6DkvL4gx0cWsHkSH6S4SUYSGh3YJYA2RdCun.png',4),(7,'RPL-REP-SAL','Pastel de pollo',600,2,8,950,'A',23,'Pastel salado relleno de pollo','productos/pvamz0m7m0vL5Zp9LYLpQGyvjdpocAPrNmuuqWfF.png',1),(8,'SJQ-REP-SAL','Sándwich de jamón y queso',874.38,2,14,1650,'A',3,'Sándwich relleno de jamón, queso y lechuga','productos/PDnSzwjIVQOl0v2491NO6J8qWgaRRSExXR5QQOm7.png',1),(9,'WJQ-REP-SAL','Wrap de jamón y queso',2995,2,14,3800,'A',3,'Wrap de jamón, queso, lechuga y tomate','productos/uIFWD3lppAJWxSw4UIDjx5fG63UpQ58VbA9eKEdn.png',2),(10,'QV-REP-DUL','Queque de vainilla',650,2,9,650,'A',3,'Queque de vainilla','productos/dC8nQ1wwYkSZLRxeLP4cQr0UHotxRnPmp6JqQzbg.png',1),(11,'QVN-REP-DUL','Queque de vainilla y nuez',650,2,9,650,'A',3,'Queque de vainilla y nuez','productos/TJBlGGgisUCSYX3miRSSNPBl11kLqFoIXMEscXwD.png',2),(12,'QZR-REP-DUL','Queque de zanahoria y Ron',850,2,9,850,'A',3,'Queque de Zanahoria y Ron','productos/fhbWnehMPAuLeQ38NP6vRMhpeDE4u713qc8KD5IO.png',3),(13,'QM-REP-DUL','Queque marmoleado',850,2,9,850,'A',3,'Queque marmoleado, compuesto por vainilla y chocolate','productos/G0y1LRamd6mPtIpwCvBHuZd3T1DZP4ANPWSqMA4Z.png',4),(14,'MC-REP-DUL','Muffin de chocolate',650,2,9,650,'A',3,'Muffin de chocolate','productos/syy9Nq1ny58aYOrYcN7Jf8w2zXxMXAZjabFFRRDY.png',5),(15,'MP-REP-DUL','Muffin de pasas',650,2,9,650,'A',3,'Muffin relleno de pasas','productos/yEoHgClrQyjsFOqSFbWMDNwkOqayyDCRIJfGlAuc.png',7),(16,'MA-REP-DUL','Muffin de arándanos',650,2,9,650,'A',3,'Muffin relleno de arándanos','productos/OiaesrNGVm998b3je5LwT3purhVkQ88zgqagK3Gd.png',6),(17,'GCC-REP-DUL','Galleta de chispas chocolate',500,2,9,500,'A',3,'Galleta de chispas de chocolate','productos/YpjDtZ72KaZPYfLMrLdSP91h7OQh9lQkSQSsP3uR.png',8),(18,'GA-REP-DUL','Galleta de avena',500,2,9,500,'A',3,'Galleta de avena','productos/cnCemDroNEhLKxTatkfeUZ3bTlGCsgzkCvPpWZQR.png',9),(19,'CHNY-REP-ESP','Cheesecake New York',1750,2,10,1750,'A',3,'Cheesecake New York (blanco)','productos/VBjfwxLUlSkU96cpfcJtZjNgN1wuUUUlR7o5NTbf.png',1),(20,'CHS-REP-ESP','Cheesecake Strawberry',1750,2,10,1750,'A',3,'Cheesecake Strawberry (blanco con morado)','productos/5LxNRWktwPbsEsk5bBOJ1CayENPpNxaS35YBuOLR.png',2),(21,'CT-REP-ESP','Cheesecake Turtle',1750,2,10,1750,'A',3,'Cheesecake Turtle (caramelo)','productos/trgdj0aFQy6IbHja5E8I8VXflXAHjHUtzmPfBJLA.png',3),(22,'CTC-REP-ESP','Cheesecake Triple Chocolate',1750,2,10,1750,'A',3,'Cheesecake Triple Chocolate','productos/AJ0KRXr1IKQOLZhRDWUyVb0lAP1tGiUaS83lVWb9.png',4),(24,'DCH-REP-ESP','Dona de chocolate',650,2,10,650,'A',3,'Dona de chocolate','productos/te5ihW4L4UXPqtxofHLU37k2X2j3dKjvNmmKfG9H.png',9),(32,'DCL-REP-ESP','Dona clásica',260.9,2,10,550,'A',16,'Dona clásica con toppings a escoger: azúcar, chocolate, caramelo, confitería.','productos/njD7b3HPfdtSJwgeOP8wFWH1esGgH2ib0AFn5hC9.png',8),(33,'TB-REP-ESP','Trío de brownie',999,2,10,1500,'A',3,'Trío de brownie: caramelo, oreo y chocolate','productos/8YoTaQJDLlaMijDH9CWoDxtzovmVZ1h8m4AEnCjR.png',5),(34,'HTS-BM','Hatsu Té Blanco y Mangostino',858.8,2,13,1400,'A',20,'Bebida a base de té, frutas y flores','productos/Dj2TAE8bS5zUE3Mq9jbLI01Gb9J06oOqqeER0qpm.png',7),(35,'HTS-RPE','Hatsu Té Rojo Frutos Rojos',858.8,2,13,1400,'A',20,'Bebida a base de té, frutas y flores','productos/gGcJrE1GJzbgcmy8krXgDubTNGsjxnLL4sFi84UR.png',12),(36,'HTS-CAFL','Hatsu Té de Carambolo Amarillo y Flor de Loto',858.8,2,13,1400,'A',20,'Bebida a base de té, frutas y flores','productos/9GIRw6hae7RyZ1K8We881x73GFsOMNmdk2uJsEQU.png',8),(37,'HTS-AP','Hatsu Té Azul de Pomegranate',858.8,2,13,1400,'A',20,'Bebida a base de Té, frutas y flores','productos/vpOON7e2S920eqbY0bGMcBawdySEgrDMbsX839cp.png',6),(38,'HTS-NL','Hatsu Té Negro y Limonada',858.8,2,13,1400,'A',20,'Bebida a base de té, frutas y flores','productos/8Q9fkkzZV17Gcjwl1PU8zwiDBKQlC94PqM6wR0q8.png',10),(39,'HTS-RL','Hatsu Té Rosas y Lychee',858.8,2,13,1400,'A',20,'Bebida a base de té, frutas y flores','productos/uBdeLogV4Q5598hD5sv0BhCxROwCDHqdQhZ4WGNT.png',11),(40,'HTS-YM','Hatsu Té Yuzu y Manzanilla',858.8,2,13,1400,'A',20,'Bebida a base de té, frutas y flores','productos/inYYHWROlpuWtYHx50ZeGCq5WfxzZhg8XySsI8qs.png',13),(41,'HTS-CL','Hatsu Té Cerezo Lila',858.8,2,13,1400,'A',20,'Bebida a base de té, frutas y flores','productos/CT1yuGXR3PD59PgjCVnW92l6BaUhcSTUPBru7vXc.png',9),(43,'CCL-ORG','Coca-Cola',650,2,13,850,'A',22,'Sabor Original 354ml','productos/N0zc3k6S51cJtWnkGr5cDRxBeiUTh1KHpnY7iyvG.png',1),(44,'CCL-SAZ','Coca-Cola Sin Azucar',650,2,13,850,'A',22,'Sin Azucar 354ml','productos/vZAYkfeVxrtI1k3FHYaznqCgOeVOGIp5LL2jIITj.png',2),(45,'FNT-NRJ','Fanta Naranja',650,2,13,850,'A',22,'Naranja 354ml','productos/6bM6i09SdGtJQhHOE8iQ5wDTetDC3KW5ehmuf8Jv.png',4),(46,'CAN-DRY','Canada Dry',650,2,13,850,'A',22,'Ginger Ale 345ml','productos/ubW0qxA74Z3xZHfdYi4IhkCeC0yr7TbDeLIrmtbg.png',3),(47,'DLV-MNG','Mango',417,2,13,550,'A',22,'Del Valle 330ml','productos/tw1U35dgdwig4NjiVGqWHyLHohccBoTKG8HlXNFW.png',14),(48,'DLV-GYB','Guayaba',417,2,13,550,'A',22,'Del Valle 330ml','productos/CFZyKWbw5nmAsnXNV6njSBeN5nO2HGg22BffrZ3u.png',15),(49,'DLV-PYM','Piña y Mandarina',417,2,13,550,'A',22,'Del Valle 330ml','productos/pa9h81tVCsOCU10e9YGyogkfWPsOCbhxRdvi1453.png',16),(50,'DLV-MNZ','Manzana',471,2,13,550,'A',22,'Del Valle 330ml','productos/XLNWqyLcyyRJ7DGI3yZuWp20DG7wBzEWGmJny2AH.png',17),(51,'DLV-MYF','Mango y Fresa',417,2,13,550,'A',22,'Del Valle 330ml','productos/ZOwzjzfC51aizGrAKoporWMUMQPgwJ70u2Rj3lh1.png',18),(52,'DLV-DZN','Durazno',3417,2,13,550,'A',22,'Del Valle 330ml','productos/qIKbEg2l5hl0O1GaS7LH3Dv5iDO9371YD9wOXmPU.png',19),(54,'RP-SAL-ECN','Enchilada de Carne',600,2,8,950,'A',23,'Enchilada Salada Rellena de Carne','productos/BAlfo0Spe0LQ8KX6clrKmvnuAhGln1Umlk5mqDDx.png',3),(57,'AGU-ALP-PUR','Agua',590,2,13,800,'A',22,'Agua pura alpina 600ml','productos/8wDrkbgZGsaPzIGrhLYuC3ox7uV5lrMBZ4YbEV0a.png',20),(58,'RP-SAL-ECN-poll','Enchilada Pollo',600,2,8,950,'A',23,'Enchilada salada rellena de pollo','productos/Zgk8t5u1bzTzBvOeJt5EjkZL7ILiLU0RInTOIuPp.png',2),(59,'RP-SAL-ARR-JYQ','Arrollado Jamón y Queso',600,2,8,950,'A',23,'Arrollado con jamón  y queso','productos/ucoCGsPazBla28Y2vEdPBd8qq0AQAUqMMbGc5kdc.png',5),(61,'FFCP-REP-DUL','Arrollado de Crema Pastelera',650,2,9,950,'A',26,'Flauta dulce con relleno de crema pastelera','productos/cVHPN74nVLIWLKWGIbjpNehkxNkoIf8WNc4JMOSP.png',5),(62,'FFPP-REP-DUL','Arrollado de piña',650,2,9,950,'A',26,'Flauta dulce de piña','productos/Jn9AphkCyqjwTfjxFV12EVzamYeqgWlKBPuzj0qv.png',6),(63,'REP-DUL-QN','Queque Navideño',650,2,9,950,'A',3,'Queque navideño relleno de nueces y almendras.','',0),(64,'RE-QN','Queque Navideño Entero',4595,2,10,7000,'A',3,'Queque navideño entero','',0),(65,'REP-DUL-QA','Queque Almendras',250,2,9,850,'A',3,'Queque de Almendras','',0),(66,'EMP-CAPR','Empanada Capresse',800,2,8,1200,'A',44,'Empanada rellena de tomate, albahaca y queso mozzarella','productos/MEOXd0Ohy1VrBcbygLV6DzaSQxgj9KJIcltgI0nz.png',0),(67,'EMP-JYQ','Empanada Calabresa',800,2,8,1200,'A',44,'Empanada rellena de salame, pepperoni y queso mozzarella','productos/oPUwbJLThniUTT2wV1DfinMYcH0m2sFFVTYO6ypE.png',0),(68,'EMP-CCH','Empanada Carne Cuchillo',800,2,8,1200,'A',44,'Empanada rellena de lomito, cebolla y ajo','productos/WdYR6Yli5cL6gJNF7tXaJoVdFlcvl3D2JBvL0EgT.png',0),(69,'EMP-PLL','Empanada Pollo',800,2,8,1200,'A',44,'Empanada rellena de pollo, cebolla y ajo','productos/WyyO31td80v0gLc9ohfdtNSThcEQwB4L6LYI1Cha.png',0);
/*!40000 ALTER TABLE `producto_externo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `producto_menu`
--

DROP TABLE IF EXISTS `producto_menu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `producto_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `posicion_menu` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `pm_categoria_fk01` (`categoria`),
  KEY `pm_impuesto_fk01` (`impuesto`),
  CONSTRAINT `pm_categoria_fk01` FOREIGN KEY (`categoria`) REFERENCES `categoria` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `pm_impuesto_fk01` FOREIGN KEY (`impuesto`) REFERENCES `impuesto` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=118 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `producto_menu`
--

LOCK TABLES `producto_menu` WRITE;
/*!40000 ALTER TABLE `producto_menu` DISABLE KEYS */;
INSERT INTO `producto_menu` VALUES (28,'Panqueques','Panqueques cubiertos de miel de maple, frutos rojos y azúcar glass',10,2,NULL,2800,'A','PQ-REP-ESP',NULL,NULL,'productos/Fo5h1u7kzc5LzEQnDczPRsNHLUL9MmAKvXQxJuIR.jpg','3 panqueques \r\nAzúcar glass arriba \r\n3 besitos de crema batida\r\nFrutos rojos\r\nMiel de maple (en tarrito)',6),(29,'Waffles','Waffles con chocolate Hershey´s, frutos rojos, helado de vainilla y crema batida',10,2,NULL,3650,'A','WF-REP-ESP',NULL,NULL,'productos/lJGTOwk9LFFTxGhLHJcCLbGZMy8qVJDV3XBtAfvv.jpg','2 waffles \r\nSalsa de chocolate por arriba \r\nScoop de helado \r\nFrutos rojos',7),(30,'Americano 8oz','Café espresso con agua casi hirviendo',4,2,NULL,1200,'A','AME8-BEB-CAL',NULL,NULL,'productos/eyqMNzzJNPbPMzBDv9ZSB9vtirOOgaddE4RDHhPx.png','1 shot de espresso\r\n250 ml de agua caliente',5),(31,'Americano 12oz','Café espresso con agua casi hirviendo',4,2,NULL,1500,'A','AME12-BEB-CAL',NULL,NULL,'productos/gryNu24bnFjrPnZXJIdtGOww3RlPDDE3VM7WrWEe.png','1.5 shots de espresso \r\n300 ml de agua caliente',1),(32,'Cappuccino 8oz','Café espresso con leche espumada en proporción 1/5.',4,2,NULL,1800,'A','CP8-BEB-CAL',NULL,NULL,'productos/R7CKIFJ1pTxJZiM6yykP8MjvMI1GFPEchk5vWSWB.png','40 ml de espresso (1 shot y 10 ml)\r\n200 ml de leche para espumar \r\nRELLENAR TODO EL VASO',7),(33,'Cappuccino 12oz','Café espresso con leche espumada en proporción 1/5.',4,2,NULL,2200,'A','CP12-BEB-CAL',NULL,NULL,'productos/xh6AbjR87gGS9QS8BSAQvfZsiZHjzoAl4jPWNT2l.png','2 shots de espresso \r\n250 ml de leche',8),(34,'Latte vainilla 8oz','Café espresso, leche espumada en proporción 1/7, vainilla.',4,2,NULL,2000,'A','LTV8-BEB-CAL',NULL,NULL,'productos/pwUDZZSe6V0NCCtEK1DWypdX4gv99eGdKOIbAN8d.png','1 shot de espresso\r\n200 ml de leche \r\n15 ml de sirope de vainilla\r\nPreparación: agregar el café, el sirope, y rellenar con la leche.',11),(35,'Latte vainilla 12oz','Café espresso, leche espumada en proporción 1/7, vainilla.',4,2,NULL,2400,'A','LTV12-BEB-CAL',NULL,NULL,'productos/CmfyBsKMR8xPCmEMEc7VCUFHocMF1MjaaKKjoOSa.png','1.5 shots de espresso \r\n250 ml de leche \r\n30 ml de sirope de vainilla\r\nPreparación: agregar el café, el sirope, y rellenar con la leche.',12),(38,'Mocha Caliente 8oz','Café espresso, leche espumada,\r\ntableta de chocolate.',4,2,NULL,2200,'A','MO8-BEB-CAL',NULL,NULL,'productos/858BaVnYSVXokgKtPvk3EeQXwvpsnZAeZCVodKzd.png','1 shot de espresso \r\n200 ml de leche \r\n30 g de tableta de chocolate\r\nPreparación: agregar la tableta de chocolate en el fondo del vaso, después el café y por último rellenar con leche.',13),(39,'Mocha Caliente 12oz','Café espresso, leche espumada,\r\ntableta de chocolate.',4,2,NULL,2500,'A','MO12-BEB-CAL',NULL,NULL,'productos/ZAvsHFZV2OJr4ozjqtQGWvJFWEeQ5CmE2cBKcyf0.png','1.5 shots de espresso \r\n250 ml de leche \r\n40 g de tableta de chocolate (no es necesario tarar)\r\nPreparación: agregar la tableta de chocolate en el fondo del vaso, después el café y por último rellenar con leche.',14),(40,'Chocolate Caliente 8oz','Chocolate en polvo, leche espumada y azúcar, marshmallows.',4,2,NULL,1400,'A','CH8-BEB-CAL',NULL,NULL,'productos/HQOoZA5VhC2paehZxCc5eTosC0XAMVhwi13FbtVb.png','3 cucharadas (15 ml c/u) de chocolate en polvo \r\n200 ml de leche \r\n5 g de azúcar \r\nMarshmallows para decorar \r\nPreparación: espumar el chocolate con la leche, poner azúcar en el vaso y echar el chocolate ya hecho, decorar con marshmallows arriba.',15),(41,'Chocolate Caliente 12oz','Chocolate en polvo, leche espumada y azúcar, marshmallows.',4,2,NULL,1700,'A','CH12-BEB-CAL',NULL,NULL,'productos/1E6XZnKBw27el0a40IETyIIEbCk4naxlq7vC0my0.png','5 cucharadas (15 ml c/u) de chocolate en polvo \r\n250 ml de leche \r\n7.5 g de azúcar \r\nMarshmallows para decorar\r\nPreparación: espumar el chocolate con la leche, poner azúcar en el vaso y echar el chocolate ya hecho, decorar con marshmallows arriba.',16),(44,'Iced coffee latte 12oz','Bebida helada con café espresso, hielo, leche según preferencia',5,2,NULL,1900,'A','ICL12-BEB-FRI',NULL,NULL,'productos/QHz0GbfFtucQuU2AyYChES3kcNFAtRAn4V8LMD0q.png','2 shots de espresso \r\n145 ml de leche \r\nHielo \r\nPrimero poner hielo hasta arriba, agregar leche y el café arriba para que difumine',3),(45,'Iced coffee latte 16oz','Bebida helada con café espresso, hielo, leche según preferencia',5,2,NULL,2300,'A','ICL16-BEB-FRI',NULL,NULL,'productos/9WkLKYRFuiRLrrZmDs1ocZd590w4MfIpsfB2bsGG.png','2.5 shots de espresso \r\n240 ml de leche \r\nHielo\r\nPrimero poner hielo hasta arriba, agregar leche y el café arriba para que difumine',4),(48,'Macchiato de caramelo 12oz','Bebida helada con jarabe de vainilla, café espresso, leche según preferencia, caramelo, y crema batida.',5,2,NULL,2800,'A','MC12-BEB-FRI',NULL,NULL,'productos/G7f2ivtiR3z54ZlFWxe9L4LRmFOLvUL94q5lcLgs.png','2 shots de espresso \r\n115 ml de leche \r\n30 ml de sirope de vainilla \r\nHielo\r\nPrimero poner hielo hasta arriba, agregar la leche, después sirope y por último café\r\nDecoración: un poco de caramelo en las paredes, crema batida y una red de caramelo arriba.',7),(49,'Macchiato de caramelo 16oz','Bebida helada con jarabe de vainilla, café espresso, leche según preferencia, caramelo, y crema batida.',5,2,NULL,3200,'A','MC16-BEB-FRI',NULL,NULL,'productos/Pax5APSRG1W5RQXqwDllxpVWYEpQg7RJEAjvJlyc.png','2.5 shots de espresso \r\n210 ml de leche \r\n45 ml de sirope de vainilla \r\nHielo\r\nPrimero poner hielo hasta arriba, agregar la leche, después sirope y por último café \r\nDecoración: un poco de caramelo en las paredes, crema batida y una red de caramelo arriba.',8),(52,'Chocolate Frío 12oz','Bebida helada de chocolate, leche según preferencia, azúcar y crema batida.',5,2,NULL,2000,'A','CH12-BEB-FRI',NULL,NULL,'productos/r0ufP0iXmzHakwPZ5LiSaozrn9rZE6OaZ48hzMJu.png','5 cucharadas de chocolate en polvo (15 ml c/u) \r\n230 ml de leche \r\n7.5 g de azúcar \r\nLicuar con hielo (unos 10 cubitos para enfriar nada más) \r\nDecoración: crema batida y confitería de chocolate',13),(53,'Chocolate Frío 16oz','Bebida helada de chocolate, leche según preferencia, azúcar y crema batida.',5,2,NULL,2400,'A','CH16-BEB-FRI',NULL,NULL,'productos/03xz2TFIAXfTCqsX3UEgpUGTErGQGFbQmnI5lwq8.png','7 cucharadas de chocolate en polvo (15 ml c/u) \r\n330 ml de leche \r\n10 g de azúcar \r\nLicuar con hielo (unos 10 cubitos para enfriar nada más) \r\nDecoración: crema batida y confitería de chocolate',14),(56,'Milkshake de fresa 12oz','Bebida helada de fresa, leche según preferencia, helado de vainilla y crema batida.',6,2,NULL,2190,'A','FR12-MK',NULL,NULL,'productos/EZg7aE0AUTPbaAFugyNHwHX2PX0xsL4lpvNhbu4l.png','140 g de fresa \r\n200 ml de leche \r\n80 g de helado de vainilla \r\n7.5 de azúcar \r\nDecoración: crema batida y confitería de colores',1),(57,'Milkshake de fresa 16oz','Bebida helada de fresa, leche según preferencia, helado de vainilla y crema batida.',6,2,NULL,2690,'A','FR16-MK',NULL,NULL,'productos/NfYAgd6MNa5sb8DZSNxk6J7FENf9oywlwwJnRB13.png','170 g de fresa \r\n250 ml de leche \r\n100 g de helado de vainilla \r\n10 de azúcar \r\nDecoración: crema batida y confitería de colores',2),(58,'Milkshake de maní 12oz','Bebida helada de maní, leche según preferencia, helado de vainilla y crema batida.',6,2,NULL,2400,'A','MA12-MK',NULL,NULL,'productos/sp2dwMJKtrhghkMS2AOSFknNDDEurP7BjM2aAcj9.png','4 cucharadas de maní \r\n130 ml de leche \r\n80 g de helado de vainilla\r\n10 g de azúcar \r\nHielo \r\nDecoración: crema batida y caramelo sobre la crema batida',3),(59,'Milkshake de maní 16oz','Bebida helada de maní, leche según preferencia, helado de vainilla y crema batida.',6,2,NULL,2900,'A','MA16-MK',NULL,NULL,'productos/a1k3uy3Mqw4TuRvIb3tbHZGJiSPzcDT05BdQXQnW.png','6 cucharadas de maní \r\n200 ml de leche \r\n100 g de helado de vainilla\r\n12.5 g de azúcar \r\nHielo \r\nDecoración: crema batida y caramelo sobre la crema batida',4),(60,'Milkshake de vainilla 12oz','Bebida helada de vainilla, leche según preferencia, helado de vainilla y crema batida.',6,2,NULL,2400,'A','V12-MK',NULL,NULL,'productos/QXIpr82vPYnEqALO0MbyxA9clfeTwuL5NiY8bC4m.png','3 onz de vainilla \r\n90 ml de leche \r\n80 g de helado de vainilla \r\n½ pala de hielo \r\nDecoración: crema batida y confitería de colores',5),(61,'Milkshake de vainilla 16oz','Bebida helada de vainilla, leche según preferencia, helado de vainilla y crema batida.',6,2,NULL,2900,'A','V16-MK',NULL,NULL,'productos/8RlRVv9DXl3vEty2J5ARURJkItAdVmjvr5saLAgm.png','4 onz de vainilla \r\n125 ml de leche \r\n100 g de helado de vainilla \r\n1 pala de hielo \r\nDecoración: crema batida y confitería de colores',6),(62,'Milkshake de caramelo 12oz','Bebida helada de caramelo, leche según preferencia, helado de vainilla y crema batida.',6,2,NULL,2400,'A','CA12-MK',NULL,NULL,'productos/Ep6T1xAoIqdWjWwQVNEXUsptneKrRCyGbIc94XvH.png','3 onz de caramelo\r\n90 ml de leche \r\n80 g de helado de vainilla \r\n½ pala de hielo \r\nDecoración: crema batida y caramelo en las paredes del vaso, en la base y encima de la crema',7),(63,'Milkshake de caramelo 16oz','Bebida helada de caramelo, leche según preferencia, helado de vainilla y crema batida.',6,2,NULL,2900,'A','CA16-MK',NULL,NULL,'productos/y9PyTLNAKjEt0qH73fqcxVytrYfRGOpzX4m0aY2h.png','4 onz de caramelo \r\n125 ml de leche \r\n100 g de helado de vainilla \r\n1 pala de hielo \r\nDecoración: crema batida y caramelo en la base, en las paredes del vaso y sobre la crema.',8),(64,'Milkshake de chocolate 12oz','Bebida helada de chocolate, leche según preferencia, helado de vainilla y crema batida.',6,2,NULL,2400,'A','CH12-MK',NULL,NULL,'productos/mPBXMc8nfF0U3ERpng4pPF04RNrBYghlcDTbwqkK.png','2 cucharadas de chocolate en polvo\r\n140 ml de leche \r\n80 g de helado de vainilla \r\n½ pala de hielo \r\nDecoración: crema batida y salsa de chocolate en las paredes del vaso, confitería de chocolate sobre la crema.',9),(65,'Milkshake de chocolate 16oz','Bebida helada de chocolate, leche según preferencia, helado de vainilla y crema batida.',6,2,NULL,2900,'A','CH16-MK',NULL,NULL,'productos/blzo9ibty050kt2CdGPcZFqvRqnYl9Z86twNykaK.png','3 cucharadas de chocolate en polvo\r\n175 ml de leche \r\n100 g de helado de vainilla \r\n1 pala de hielo \r\nDecoración: crema batida y salsa de chocolate en las paredes del vaso, confitería de chocolate sobre la crema.',10),(66,'Smoothie de fresa 12oz','Bebida helada con fresas, agua, hielo.',7,2,NULL,1300,'A','FR12-SMT',NULL,NULL,'productos/2JJ4f8Axi16KmnA87aIGWOe8MsCln1iCH2AeT5Oa.png','140 g de fresa \r\n150 ml de agua (o leche)\r\n20 g de azúcar \r\nHielo (1/2 pala)',1),(67,'Smoothie de fresa 16oz','Bebida helada con fresas, agua, hielo.',7,2,NULL,1700,'A','FR16-SMT',NULL,NULL,'productos/WdWIMOkqhtroFCAFZeOtrIE78JvWFkoMvWKZoT0g.png','170 g de fresa \r\n230 ml de agua (o leche)\r\n25 g de azúcar \r\nHielo (1/2 pala)',2),(68,'Smoothie de mora 12oz','Bebida helada con moras, agua, hielo.',7,2,NULL,1300,'A','MO12-SMT',NULL,NULL,'productos/RSyLk6utBNH2McAgwP48iRxoUIsUPj2vkEuACKfn.png','140 g de mora\r\n150 ml de agua (o leche)\r\n20 g de azúcar \r\nHielo (1/2 pala)',3),(69,'Smoothie de mora 16oz','Bebida helada con moras, agua, hielo.',7,2,NULL,1700,'A','MO16-SMT',NULL,NULL,'productos/8XtfXNaXMVRrHmAvF7zoBCGmLFEVmKto5XBat2DR.png','170 g de fresa \r\n230 ml de agua (o leche)\r\n25 g de azúcar \r\nHielo (1/2 pala)',4),(70,'Smoothie de piña 12oz','Bebida helada con piña, agua, hielo.',7,2,NULL,1300,'A','PÑ12-SMT',NULL,NULL,'productos/nLDVglyEi2Yl0FTiQ91kMzHyD7wX3HrpZg0pXmuN.png','140 g de piña\r\n150 ml de agua (o leche)\r\n20 g de azúcar \r\nHielo (1/2 pala)',5),(71,'Smoothie de piña 16oz','Bebida helada con piña, agua, hielo.',7,2,NULL,1700,'A','PÑ16-SMT',NULL,NULL,'productos/5BVlhPUzQMs19aa2XRV46n7R6BOA3iSIkAv4D4xB.png','170 g de piña \r\n230 ml de agua (o leche)\r\n25 g de azúcar \r\nHielo (1/2 pala)',6),(72,'Smoothie de piña con hb 12oz','Bebida helada de piña con hojas trituradas de hierba buena, agua, hielo.',7,2,NULL,1450,'A','PHB12-SMT',NULL,NULL,'productos/EidGqSGhUKkFf6zQt08dwfuV4C4aJUakdIvxsYor.png','140 g de piña\r\n150 ml de agua \r\n20 g de azúcar \r\nPorción de hb\r\nHielo (1/2 pala)',9),(73,'Smoothie de piña con hb 16oz','Bebida helada de piña con hojas trituradas de hierba buena, agua, hielo.',7,2,NULL,1850,'A','PHB16-SMT',NULL,NULL,'productos/fyuTRV7qHckjqjvqu13w30NUVn2OBNkv6krXbafy.png','170 g de piña \r\n230 ml de agua\r\n25 g de azúcar \r\nPorción hb \r\nHielo (1/2 pala)',10),(74,'Smoothie de limonada hb 12oz','Bebida helada de limón con hojas trituradas de hierba buena, agua, hielo.',7,2,NULL,1200,'A','LHB12-SMT',NULL,NULL,'productos/tEyS8sv4zQfmMDysVHL9YE6I6T4TxHJXyzH28Bn8.png','2 onz de jugo de limón \r\n120 ml de agua \r\n25 g de azúcar \r\nPorción de hb\r\nHielo 1 pala (que quede frozen, si con 1 pala no queda entonces agregar más)',11),(75,'Smoothie de limonada hb 16oz','Bebida helada de limón con hojas trituradas de hierba buena, agua, hielo.',7,2,NULL,1600,'A','LHB16-SMT',NULL,NULL,'productos/YUl0MB2d2aujdGqyUBAoJAVIhwwXdAJ8JhgbDDtB.png','3 onz de jugo de limón \r\n180 ml de agua \r\n30 g de azúcar \r\n15 hojas de hb \r\nHielo 1 pala (que quede frozen, si con 1 pala no queda entonces agregar más)',12),(76,'Smoothie de frutos rojos 12oz','Bebida helada con frutos rojos silvestres, agua, hielo.',7,2,NULL,1300,'A','FTR12-SMT',NULL,NULL,'productos/6FIAJ8y78JgCKtYKJUUnjtSKHYpYEb9CmOtibMJw.png','140 g de frutos rojos\r\n150 ml de agua (o leche)\r\n20 g de azúcar \r\nHielo (1/2 pala)',7),(77,'Smoothie de frutos rojos 16oz','Bebida helada con frutos rojos silvestres, agua, hielo.',7,2,NULL,1700,'A','FTR16-SMT',NULL,NULL,'productos/fPedOEEww5XNBbO6u48c4mhDVBOOJyca08yaYrNs.png','170 g de frutos rojos\r\n230 ml de agua (o leche)\r\n25 g de azúcar \r\nHielo (1/2 pala)',8),(78,'Té de Matcha Menta Frío/Caliente 12oz','Té a base de Matcha frío, en vaso de 12oz',11,2,NULL,2200,'A','TMC-ICE-MD',NULL,NULL,'productos/8HwjyNdllNaA7BlauVguC3yjLlTE1l0aLhiCdIyu.png','2 paquetes de matcha \r\n350 ml de leche \r\n(Si es frío llenar el vaso con hielo)',1),(80,'Té de Manzanilla Caliente 12oz','Bebida caliente a base de manzanilla, servida en un vaso de 12oz',11,2,NULL,950,'A','TMZ-HOT-MD',NULL,NULL,'productos/V4k7xL8c2eT4hEL6jY7aPbOEdqTG16aYrgNebXDj.png','2 bolsas de té \r\nAgua caliente',7),(81,'Té de Menta Frío/Caliente 12oz','Bebida a base de infusiones naturales',11,2,NULL,950,'A','TÉ-MEN',NULL,NULL,'productos/qfIQHyBmbyTG6jGcrGBw406oAubcHRPus4HUuTuT.png','2 bolsas de té \r\nAgua caliente o agua fría (llenar vaso con hielo)',4),(82,'Té Colité Caliente 12oz','Bebida a base de infusiones naturales',11,2,NULL,950,'A','TÉ-COL',NULL,NULL,'productos/kqTRdrwwKK6fUTMer45vjH6HEh70bHMKB2UVh1Dj.png','2 bolsas de té \r\nAgua caliente',9),(83,'Té de Tilo Caliente 12oz','Bebida a base de infusiones naturales',11,2,NULL,950,'A','TÉ-TIL',NULL,NULL,'productos/3v9BzEfbdrdZANTiEUojZG4usT653clxBgzsoNmi.png','2 bolsas de té \r\nAgua caliente',8),(84,'Té Verde Frío/Caliente 12oz','Bebida a base de infusiones naturales de té verde, puede ser caliente o frío',11,2,NULL,950,'A','TÉ-VER',NULL,NULL,'productos/OYw7LCHKCRn6Z0SaPIaXdZfXBaYVCe5vgKKzDBAI.png','2 bolsas de té \r\nAgua caliente o agua fría (llenar vaso con hielo)',5),(85,'Té Negro Frío/Caliente 12oz','Bebida a base de infusiones naturales de té negro con agua fría o caliente',11,2,NULL,950,'A','TÉ-NEG',NULL,NULL,'productos/qJBwzVYET6isoqrdridnbnBAqpyyAwZtyVcw5B82.png','2 bolsas de té \r\nAgua caliente o agua fría (llenar vaso con hielo)\r\nSi lleva leche se pone totalmente',6),(86,'Té de frambuesa y Granada Frío/Caliente 12oz','Bebida a base de infusiones naturales',11,2,NULL,950,'A','TÉ-FYG',NULL,NULL,'productos/WpTTz5dwg05nhLSwNmeFjs9OEHoh6XGJfKnGmaE7.png','2 bolsas de té \r\nAgua caliente o agua fría (llenar vaso con hielo)',3),(87,'Té de Frutos Rojos Frío/Caliente 12oz','Bebida a base de infusiones naturales de frutos rojos, puede ser caliente o frió.',11,2,NULL,950,'A','TÉ-FTR',NULL,NULL,'productos/25riHWOS714Ns04b6TKgB8JeCBj5j0KK2scvZ0wV.png','2 bolsas de té \r\nAgua caliente o agua fría (llenar el vaso con hielo)',2),(88,'Latte 8oz','Café espresso con leche espumada en proporción 1/7.',4,2,NULL,1900,'A','BEB-CAL-LAT',NULL,NULL,'productos/qrt8VyOhsalXvwCmLEcXowXJG4K9BDJy9tGfKSBn.png','1 shot de espresso \r\n200 ml de leche',9),(89,'Latte 12oz','Café espresso con leche espumada en proporción 1/7.',4,2,NULL,2300,'A','BEB-CAL-LATT',NULL,NULL,'productos/NVVoMUXpr9xm7ACjoj5fHuuNWK7XjPhM4c15tXdK.png','1.5 shots de espresso \r\n250 ml de leche',10),(90,'Iced Coffe Latte(Especial)12oz','Bebida helada con café espresso, hielo, leche según preferencia y crema batida. Sabor a escoger: vainilla, caramelo, amaretto, crema irlandesa.',5,2,NULL,2200,'A','BEB-FR-ICL',NULL,NULL,'productos/zMTUrJYX3PU2M0m8f5OhJKZJX3pizGvuHAGpnKHI.png','2 shots de espresso \r\n115 ml de leche \r\n30 ml del sirope escogido \r\nHielo\r\nPoca crema batida para decorar \r\nPrimero poner hielo hasta arriba, agregar leche, sirope, el café, crema batida.',5),(91,'Iced Coffe Latte (Especial)16oz','Bebida helada con café espresso, hielo, leche según preferencia y crema batida. Sabor a escoger: vainilla, caramelo, amaretto, crema irlandesa.',5,2,NULL,2600,'A','BEB-FRI-ICLT',NULL,NULL,'productos/iG9P8z59HlAZKgVq3RN8TtYVct5kFSPbS2rZXIb3.png','2.5 shots de espresso \r\n210 ml de leche \r\n45 ml del sirope escogido \r\nHielo \r\nPoca crema batida para decorar\r\nPrimero poner hielo hasta arriba, agregar leche, sirope y el café, crema batida',6),(92,'Frappuccino Mocha 12oz','Bebida helada de chocolate, café espresso, leche según preferencia, vainilla, chocolate y crema batida.',5,2,NULL,2400,'A','BEB-FR-FMC',NULL,NULL,'productos/725aoMpZOeEPR3KPo1IWqVz9lryBURmfswlaM7BI.png','2 shots de espresso \r\n30 ml de leche \r\n30 ml de sirope de vainilla \r\n(LICUAR FROZEN) \r\nDecoración: salsa de chocolate en la base y los lados del vaso, crema batida y decoración de confitería de chocolate',9),(93,'Frappuccino Mocha 16oz','Bebida helada de chocolate, café espresso, leche según preferencia, vainilla, chocolate y crema batida.',5,2,NULL,2800,'A','BEB-FR-FMH',NULL,NULL,'productos/iguLSBwfY3LjHX9j9ncLVkTHL5P9XYcWTxIps5yg.png','2.5 shots de espresso \r\n50 ml de leche \r\n60 ml de sirope de vainilla \r\n(LICUAR FROZEN) \r\nDecoración: salsa de chocolate en la base y los lados del vaso, crema batida y decoración de confitería de chocolate',10),(94,'Frappuccino Coffee To Go 12oz','Bebida helada de café espresso, leche según preferencia, almendras, amaretto y crema batida.',5,2,NULL,2900,'A','BEB-FR-FCTG',NULL,NULL,'productos/kerlOU9VVrVQfbxXapz7O5bTYvStGYgBWMB2uf0h.png','2 shots de espresso \r\n30 ml de leche \r\n30 ml de vainilla \r\n2 cucharadas de almendras trituradas (cuchara blanca)\r\nBastante hielo \r\n(LICUAR FROZEN)\r\nDecoración: crema batida y Almendras trituradas arriba.',11),(95,'Frappuccino Coffee To Go 16oz','Bebida helada de café espresso, leche según preferencia, almendras, amaretto y crema batida.',5,2,NULL,3300,'A','BEB-FR-FCTG.',NULL,NULL,'productos/5KV1ZIzP07x84sNSh8wBMQtvgJxqYcfU7is5nkaJ.png','2.5 shots de espresso \r\n50 ml de leche \r\n60 ml de vainilla \r\n2 1/2 cucharadas de almendras trituradas (cuchara blanca) \r\nBastante hielo \r\n(LICUAR FROZEN) \r\nDecoración: crema batida y Almendras trituradas arriba',12),(96,'Milkshake Coffee To Go 12oz','Bebida helada de café, leche según preferencia, helado de vainilla, almendras, vainilla y crema batida.',6,2,NULL,2600,'A','CTG-12-MLK',NULL,NULL,'productos/tVAE3kwn2VkkhPEe8ByrhiG1MGsJGLi9MaTjXqDm.png','75 ml de leche \r\n30 ml de vainilla \r\n2 cucharadas de Almendras (1 de las blancas) \r\n80 g de helado \r\n15 ml de café \r\n1/2 pala de hielo \r\n\r\nDecoración: crema batida y Almendras sobre la crema',11),(97,'Milkshake Coffee To Go 16oz','Bebida helada de café, leche según preferencia, helado de vainilla, almendras, vainilla y crema batida.',6,2,NULL,3200,'A','CTG-12-MLK.',NULL,NULL,'productos/z7JNVBsd7EuZAJi7A0MvfcpZRXFg9JM3KrxNrejA.png','100 ml de leche \r\n45 ml de vainilla \r\n2 1/2 cucharadas de Almendras (cuchara de las blancas) \r\n100 g de helado \r\n30 ml de café \r\n1/2 pala de hielo (si falta que salga tipo frozen agregar un poco más) \r\n\r\nDecoración: crema batida y Almendras sobre la crema',12),(98,'Ensalada de Frutas','Bowl de fresa,\r\nbanano, piña, y papaya.',15,2,NULL,1800,'A','BWL-FRT',NULL,NULL,'productos/6gwLCyvU5XHi5D6dbdYWmOl0hzXO074Piyda9H7g.png','Piña 130 g\r\nPapaya 130 g \r\nBanano 1 u \r\nFresa 130 g',1),(99,'Ensalada de Frutas Premium','Bowl de fresa, arándano,\r\nKiwi, mango, yogurt natural,\r\ngranola, miel de abeja.',15,2,NULL,2600,'A','BWL-FRT.',NULL,NULL,'productos/s6C9OROFWss7brUXPwO14SUdJwn9sIx653zLwG0g.png','Mango 130 g\r\nFresa 130 g \r\nKiwi 5 rodajas \r\nArándanos 1 scoop y ½ del blanco \r\nYogurt 1 scoop y ½ del blanco\r\nGranola 1 scoop y ½ del blanco\r\nMiel de topping',2),(100,'Capuchino 12oz + 2 galletas chocochips','Disfruta de tu capuchino de 12 mas dos deliciosas galletas de chocochips',16,2,NULL,2800,'A','Prom-01-capgall',NULL,NULL,'productos/qumasorEt4yvtLIgy5N0TcMNmrN1ZH6f5Gbl2f2B.png','2 shots de espresso \r\n300 ml de leche\r\n\r\nLa promo lleva 2 GALLETAS ADICIONALES',1),(101,'Affogato','Helado Vainilla y café espresso',5,2,NULL,2450,'A','CAF-AFFG',NULL,NULL,'productos/JGgelySuGp85ouiDZA4XhOce2mgU7HmSkHdEmZcd.png','100 g de helado de vainilla\r\n1 shot de espresso\r\nDecoración: chocolate en las paredes y 2 puntos de crema batida',0),(106,'Shot Extra','Shot extra',4,2,NULL,500,'A','CAF-SHOT-ESS',NULL,NULL,'productos/r6dis8EZfJHKZ5Mh2pxFK2OWVlsowREAEBjJd8BZ.jpg','14 g o 21 g de café (Dependerá si es simple o doble)\r\nEl tiempo de extracción es de 20-25 seg',19),(107,'Toasted Marshmallow Xmas  MilkShake 12oz','Milkshake a base de helado con escencia de marshmallows tostados',6,2,NULL,2600,'A','NAV-MLK-SM',NULL,NULL,'productos/2jVA98sTIkHJiuE2hNlEh2Cs6wqy5aIjKppRaMdc.png','2 onz de sirope de marshmallows tostado \r\n90 ml de leche \r\n80 g de helado \r\n1/2 pala de hielo (que quede con textura)\r\n \r\nDecoración: crema batida dejando hueco en el centro, un marshmallows tostado, un árbol de crema verde y confitería de colores.',0),(108,'Toasted Marshmallow Xmas  MilkShake 16oz','Milkshake a base de helado con escencia de Marshmallows tostados',6,2,NULL,2950,'A','NAV-MLK-LG',NULL,NULL,'productos/c81IWEdPbDO6GKtJYm6QoU0vBjIbzPoe6EcL1nzr.png','3 onz de sirope de marshmallows tostado \r\n125 ml de leche \r\n100 g de helado \r\n1 pala de hielo (que quede con textura) \r\n\r\nDecoración: crema batida dejando hueco en el centro, un marshmallows tostado, un árbol de crema verde y confitería de colores.',0),(109,'Agua Dulce 8 onz','Bebida caliente de dulce molido',4,2,NULL,1400,'A','BEB-CAL-AD8',NULL,NULL,'productos/EkN6BlzTBLs7MbKQUOSuLHGx8FOLNvuy1ZsQ8Hb3.png','30 g de dulce molido\r\n300 ml de agua (200 ml de leche)',17),(110,'Agua Dulce 12 onz','Bebida caliente de dulce molido',4,2,NULL,1700,'A','BEB-CAL-AD12',NULL,NULL,'productos/q5HsF3CWmlzXJDgpxImuVfVZVRlo9ChMIK0KRZvn.png','45 g de dulce molido\r\n450 ml de agua (250 ml de leche)',18),(111,'Espresso Simple','Shot de espresso sencillo',4,2,NULL,1000,'A','ESP-SD',NULL,NULL,'productos/K0jjV677zZrgzfpwMpeoTp3qZr4KgYQJjvYd7wyD.jpg','1 shot de espresso',1),(112,'Espresso Doble','Shot de espresso doble',4,2,NULL,1200,'A','ESP-D',NULL,NULL,'productos/XMwMRttjJvfkBSzdEEoGhmEU6wzB3I7yRzypZLad.jpg','2 shots de espresso',2),(113,'Espresso Cortado Simple','Shot de espresso sencillo cortado con una pequeña cantidad de leche',4,2,NULL,1300,'A','ESP-CS',NULL,NULL,'productos/cJVR9sY4JzjhH6iFKgEtZFvGJxaxgWWLBcfYKV8t.jpg','1 shot de espresso \r\n200 ml de leche para espumar \r\n(SOLO SE AGREGA LA CANTIDAD DE LECHE QUE EL CLIENTE GUSTE)',3),(114,'Espresso Cortado Doble','Shot de espresso doble cortado con una pequeña cantidad de leche',4,2,NULL,1500,'A','ESP-CD',NULL,NULL,'productos/O8Bs9OQppyMlv40O6337mlPR7Sht6fEgEq4dCiRp.jpg','2 shots de espresso \r\n200 ml de leche para espumar \r\n(SOLO SE AGREGA LA CANTIDAD DE LECHE QUE EL CLIENTE GUSTE)',4),(115,'3x2 Capuchino 12 oz','Lleva 3 capuchinos por el precio de 2 (Promoción válida para delivery)',16,2,NULL,4400,'A','PR-3CGD',NULL,NULL,'productos/FttmgM0fEOzJWeKtLBOvcgcp6JaHtJ30xCZL4RB0.jpg',NULL,0),(116,'Iced Coffee 12 onz','Bebida fría de hielo, agua fría y shots de espresso',5,2,NULL,1500,'A','BEB-FR-IC12',NULL,NULL,'productos/gnox5g4mtZdbjFQu53tBnbIOCnYE0LsJzPrBygxD.jpg','2 shots de espresso \r\n175 ml de agua\r\nHielo \r\nPreparación: llenar el vaso con hielo, agregar el agua y por último el café',1),(117,'Iced Coffee 16 onz','Bebida fría de hielo, agua fría y shots de espresso',5,2,NULL,1900,'A','BEB-FR-IC16',NULL,NULL,'productos/FTh5qVwH8qBrEzUVELwlWmWFCWpdTOM7afDD7qBS.jpg','2.5 shots de espresso \r\n230 ml de agua\r\nHielo \r\nPreparación: llenar el vaso con hielo, agregar el agua y por último el café',2);
/*!40000 ALTER TABLE `producto_menu` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `proveedor`
--

DROP TABLE IF EXISTS `proveedor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `proveedor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) DEFAULT NULL,
  `descripcion` varchar(200) DEFAULT NULL,
  `estado` varchar(1) NOT NULL DEFAULT 'A',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `proveedor`
--

LOCK TABLES `proveedor` WRITE;
/*!40000 ALTER TABLE `proveedor` DISABLE KEYS */;
INSERT INTO `proveedor` VALUES (1,'Brumas del Zurqui','Productor de café','I'),(2,'Brumas del Zurquí','Café Villa Sarchí','A'),(3,'PriceSmart','Insumos','A'),(4,'Procut','Papel antigrasa','A'),(5,'Sellos Jireh','Sellos para cajas','A'),(6,'Liberty','Internet','A'),(7,'Mayca','Insumos','A'),(8,'Christian Peraza','Delantales CTG','A'),(9,'Alibaba','Empaques CTG','A'),(10,'TecnoBot CR','Cajón metálico para dinero','A'),(11,'Wilfredo Sequeira','Mantenimiento máquina de café','A'),(12,'Metrotec Store','Selladora al vacío','A'),(13,'iShop','iPad PV','A'),(14,'Innovali','Curso Manipulación de Alimentos','A'),(15,'Vasos Alibaba','Proveedor Vasos de Alibaba','A'),(16,'Global Partners','Donas','A'),(17,'Waltmart','Waltmart','A'),(18,'JMF','JMF pz','A'),(19,'Pequeño mundo','Pequeño mundo','A'),(20,'Habla bebidas','Proveedor de hatsu','A'),(21,'Tips','Insumos Varios','A'),(22,'Coca-Cola Femsa','Coca Cola Bebidas','A'),(23,'Bocadillos P&F','Repostería Salada','A'),(24,'Bocadillos P&F','Repostería Salada','I'),(25,'Mercado Central','En el mercado de SJ','A'),(26,'Panadería Suárez',NULL,'A'),(27,'BAC TASA 0',NULL,'A'),(28,'Planilla Administrativa',NULL,'A'),(29,'Caja Chica',NULL,'A'),(30,'SAMOFRUT','Fruta Congelada','A'),(31,'Juan de Dios Pastora Arrendamiento','Encargado del local que alquilamos','A'),(32,'Juan Mora','Provedor Hierba Buena','A'),(33,'AutoMercado',NULL,'A'),(34,'Almacenes El Rey','Materia prima y utensilios','A'),(35,'Office Depot','Administrativas','A'),(36,'Jose Carranza','Préstamo','A'),(37,'INS',NULL,'A'),(38,'AYA','Servicio de agua','A'),(39,'CNFL','Servicio de luz','A'),(40,'Xismed','Guantes','A'),(41,'Javier Solís','Diseño e impresiones','A'),(42,'Epa',NULL,'A'),(43,'Administración',NULL,'A'),(44,'Empanadas Ruca Che','Empanadas argentinas','A'),(45,'Pega y Despega','Sellos de seguridad','A'),(46,'Froilan Rótulos','Rotulación','A');
/*!40000 ALTER TABLE `proveedor` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rol`
--

DROP TABLE IF EXISTS `rol`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rol` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rol` varchar(50) NOT NULL,
  `codigo` varchar(15) NOT NULL,
  `estado` varchar(1) NOT NULL DEFAULT 'A',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rol`
--

LOCK TABLES `rol` WRITE;
/*!40000 ALTER TABLE `rol` DISABLE KEYS */;
INSERT INTO `rol` VALUES (25,'Administrador','admin','A'),(26,'Cajero','caj','A'),(27,'Super Usuario','su','I'),(28,'Prueba','pb','I'),(29,'Prueba','pb','I'),(30,'kkk','k','I'),(35,'Bodeguero','bdg','I'),(36,'CEO','MARIO','I'),(37,'Encargado gasto','SOLO_GASTO','I'),(38,'Administrador S','AdmSuc','A'),(39,'Barista','barista','A'),(40,'Menú externo','MenuExterno','A'),(41,'Entrega','ENT','A'),(42,'VS','VISOR','A');
/*!40000 ALTER TABLE `rol` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sis_clase`
--

DROP TABLE IF EXISTS `sis_clase`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sis_clase` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(1000) NOT NULL,
  `cod_general` varchar(1000) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sis_clase`
--

LOCK TABLES `sis_clase` WRITE;
/*!40000 ALTER TABLE `sis_clase` DISABLE KEYS */;
INSERT INTO `sis_clase` VALUES (1,'Estados de Cierre de caja\r\n','CIERRE_CAJA'),(2,'Tipos descuento codigo descuento','DESCUENTOS_COD_PROM'),(3,'Estados Orden / Factura','EST_FACTURAS'),(4,'Tipos Ingreso','GEN_INGRESOS'),(5,'Estado usuario cliente','CLI_EST_USUARIO'),(6,'Estados Entregas de Orden','EST_ENTREGAS_ORDEN'),(7,'Estados Facturas electrónicas','EST_FE_ORDEN'),(8,'Estado de usuario','est_user'),(9,'Estados de Gastos','EST_GASTOS_GEN'),(10,'Estados de Ingresos Contables','INGRESOS_EST'),(11,'Estados de Mesas','EST_MESAS');
/*!40000 ALTER TABLE `sis_clase` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sis_estado`
--

DROP TABLE IF EXISTS `sis_estado`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sis_estado` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(5000) NOT NULL,
  `clase` int(11) NOT NULL,
  `cod_general` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sis_estado`
--

LOCK TABLES `sis_estado` WRITE;
/*!40000 ALTER TABLE `sis_estado` DISABLE KEYS */;
INSERT INTO `sis_estado` VALUES (1,'Cierre Caja Abierto',1,'CAJA_ABIERTO'),(2,'CIERRE CAJA FINALIZADO',1,'CAJA_FINALIZADO'),(3,'CIERRE CAJA CANCELADA',1,'CIERRE_CANCELADO'),(4,'Facturada',3,'ORD_FACTURADA'),(5,'Anulada',3,'ORD_ANULADA'),(6,'En preparación',3,'ORD_EN_PREPARACION'),(7,'Para entrega',3,'ORD_PARA_ENTREGA'),(8,'Entregada',3,'ORD_ENTREGADA'),(9,'Orden en preparación',6,'ENTREGA_PREPARACION_PEND'),(10,'Orden preparada, empacando pedido',6,'ENTREGA_PEND_SALIDA_LOCAL'),(11,'Entrega terminada',6,'ENTREGA_TERMINADA'),(12,'Entrega en camino al destino',6,'ENTREGA_EN_RUTA'),(13,'Factura electrónica Pendiente',7,'FE_ORDEN_PEND'),(14,'Factura electrónica Envíada',7,'FE_ORDEN_ENVIADA'),(15,'Factura electrónica Anulada',7,'FE_ORDEN_ANULADA'),(16,'Usuario Activo',8,'USU_ACT'),(17,'Usuario Inactivo',8,'USU_INACTIVO'),(18,'Aprobado',9,'EST_GASTO_APB'),(19,'Eliminado',9,'EST_GASTO_ELIMINADO'),(20,'Aprobado',10,'ING_EST_APROBADO'),(21,'Rechazados',10,'ING_EST_RECHAZADO'),(22,'Eliminados',10,'ING_EST_ELIMINADO'),(23,'Pendiente Aprobar',10,'ING_PEND_APB'),(24,'Disponible',11,'MESA_DISPONIBLE');
/*!40000 ALTER TABLE `sis_estado` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sis_parametro`
--

DROP TABLE IF EXISTS `sis_parametro`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sis_parametro` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(1500) NOT NULL,
  `valor` varchar(1500) NOT NULL,
  `cod_general` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sis_parametro`
--

LOCK TABLES `sis_parametro` WRITE;
/*!40000 ALTER TABLE `sis_parametro` DISABLE KEYS */;
INSERT INTO `sis_parametro` VALUES (1,'Monto correspondiente a fondo de plata con el que inicia la caja','30000','MTO_FONDO_INI_CAJA'),(2,'Correo usado para enviar notificaciones a clientes','admin@coffeetogocr.com','CORREO_ENVIO_NOT_CLIENTE'),(3,'Asunto que aparecera en el correo de verificacion de registro de usuario','Verificación cuenta','ASUNTO_VERIFICACION_CLIENTE'),(4,'Nombre del usuario que envia correos de notificicacion a clientes','{{ env('APP_NAME', 'SPACE SOFTWARE CR') }}','NOMBRE_ENVIO_NOT_CLIENTE'),(5,'Correos a los que se les enviara el reporte de consumo diario, generalmente administradores del negocio (Separados por ,)','mario.flores251998@gmail.com,scarranzagarita30@gmail.com,juvargas018@hotmail.com','CORREOS_REP_CONSUMO_GEN'),(6,'Asunto que aparecerá en el correo de reporte de consumo general','Reporte de consumo diario','ASUNTO_REP_CONSUMO_GEN'),(7,'Asunto que aparecera en el correo de restauracion contraseña cliente','Nueva contraseña de ingreso','ASUNTO_REST_PSWD_CLIENTE');
/*!40000 ALTER TABLE `sis_parametro` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sis_tipo`
--

DROP TABLE IF EXISTS `sis_tipo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sis_tipo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(1500) NOT NULL,
  `clase` int(11) NOT NULL,
  `cod_general` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sis_tipo`
--

LOCK TABLES `sis_tipo` WRITE;
/*!40000 ALTER TABLE `sis_tipo` DISABLE KEYS */;
INSERT INTO `sis_tipo` VALUES (1,'Absoluto',2,'DESCUENTO_ABSOLUTO'),(2,'Porciento',2,'DESCUENTO_PORCENTAJE'),(3,'Cierre de Caja',4,'ING_CIERRE_CAJA');
/*!40000 ALTER TABLE `sis_tipo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sucursal`
--

DROP TABLE IF EXISTS `sucursal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sucursal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(50) DEFAULT NULL,
  `estado` varchar(1) NOT NULL DEFAULT 'A',
  `cod_general` varchar(150) NOT NULL,
  `cont_ordenes` int(11) NOT NULL DEFAULT 0,
  `nombre_factura` varchar(250) DEFAULT NULL,
  `cedula_factura` varchar(50) DEFAULT NULL,
  `correo_factura` varchar(250) NOT NULL,
  `factura_iva` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sucursal`
--

LOCK TABLES `sucursal` WRITE;
/*!40000 ALTER TABLE `sucursal` DISABLE KEYS */;
INSERT INTO `sucursal` VALUES (1,'Amon SJ','A','CTG',4927,'dsfsdvcds','dsfdsfds','dsfsdf@dfkdfds.com',1),(2,'La Merced Heredia','I','MH',0,NULL,NULL,'',0);
/*!40000 ALTER TABLE `sucursal` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tipo_gasto`
--

DROP TABLE IF EXISTS `tipo_gasto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tipo_gasto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo` varchar(50) NOT NULL,
  `estado` varchar(1) NOT NULL DEFAULT 'A',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tipo_gasto`
--

LOCK TABLES `tipo_gasto` WRITE;
/*!40000 ALTER TABLE `tipo_gasto` DISABLE KEYS */;
INSERT INTO `tipo_gasto` VALUES (1,'Administración','A'),(2,'Cafetería','I'),(3,'Cocina','I'),(5,'Cafeteria','I'),(6,'Gasolinera','A'),(7,'Planilla','A'),(8,'Materia Prima','A'),(9,'Caja Chica','A'),(10,'Caja Chica','I');
/*!40000 ALTER TABLE `tipo_gasto` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tipo_ingreso`
--

DROP TABLE IF EXISTS `tipo_ingreso`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tipo_ingreso` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo` varchar(50) NOT NULL,
  `estado` varchar(1) NOT NULL DEFAULT 'A',
  `cod_general` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tipo_ingreso`
--

LOCK TABLES `tipo_ingreso` WRITE;
/*!40000 ALTER TABLE `tipo_ingreso` DISABLE KEYS */;
INSERT INTO `tipo_ingreso` VALUES (1,'Cierre Caja','A','ING_CIERRE_CAJA'),(2,'Cafetería','I','ING_CAFE'),(3,'Admin','A','ING_ADMIN'),(6,'PRUEBA','A','PRUEBA');
/*!40000 ALTER TABLE `tipo_ingreso` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tipo_pago`
--

DROP TABLE IF EXISTS `tipo_pago`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tipo_pago` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo` varchar(50) NOT NULL,
  `estado` varchar(1) NOT NULL DEFAULT 'A',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tipo_pago`
--

LOCK TABLES `tipo_pago` WRITE;
/*!40000 ALTER TABLE `tipo_pago` DISABLE KEYS */;
INSERT INTO `tipo_pago` VALUES (1,'Efectivo','A'),(2,'Tarjeta','A'),(3,'pryueba nuebo','I'),(4,'Sinpe','A');
/*!40000 ALTER TABLE `tipo_pago` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `toma_fisica`
--

DROP TABLE IF EXISTS `toma_fisica`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `toma_fisica` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `materia_prima` int(11) NOT NULL,
  `dsc_materia_prima` varchar(1500) NOT NULL,
  `cantidad_sistema` double NOT NULL,
  `cantidad_usuario` double NOT NULL,
  `observaciones` varchar(5000) NOT NULL,
  `usuario` int(11) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `sucursal` int(11) NOT NULL,
  `cantidad_ajuste` double NOT NULL,
  PRIMARY KEY (`id`),
  KEY `suc_fk_tf_01` (`sucursal`),
  KEY `usu_fk_tf_01` (`usuario`),
  CONSTRAINT `suc_fk_tf_01` FOREIGN KEY (`sucursal`) REFERENCES `sucursal` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `usu_fk_tf_01` FOREIGN KEY (`usuario`) REFERENCES `usuario` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `toma_fisica`
--

LOCK TABLES `toma_fisica` WRITE;
/*!40000 ALTER TABLE `toma_fisica` DISABLE KEYS */;
/*!40000 ALTER TABLE `toma_fisica` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuario`
--

DROP TABLE IF EXISTS `usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuario` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `token_auth` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `usu_sucursal_fk1` (`sucursal`),
  KEY `usu_rol_fk1` (`rol`),
  KEY `usuario_fk01` (`estado`),
  CONSTRAINT `usuario_fk01` FOREIGN KEY (`estado`) REFERENCES `sis_estado` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuario`
--

LOCK TABLES `usuario` WRITE;
/*!40000 ALTER TABLE `usuario` DISABLE KEYS */;
INSERT INTO `usuario` VALUES (15,'Mario A','Flores','Solis','116990433','1998-01-25','2020-09-13 05:31:34','mario.flores251998@gmail.com','7056418','mflores','81dc9bdb52d04dc20036dbd8313ed055',1,25,1,'jJdNwQpbr2z3vYP1qrtD4JRBuy5o1Bm17c0fx2VgFEiYkkr6XN'),(17,'Sebastian','Carranza','Garita','116990434','1998-08-14','2023-06-20 14:39:24',NULL,'70565419','scarranza','bded06a0e5672759a342295f3a16489a',1,26,1,NULL),(26,'Ricardo','Padilla','Solis','11111111',NULL,'2024-04-16 12:11:52',NULL,'70565719','rPadilla','d9fb37fb9c508e971d995d5306983526',1,42,1,NULL);
/*!40000 ALTER TABLE `usuario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vista`
--

DROP TABLE IF EXISTS `vista`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vista` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(30) NOT NULL,
  `ruta` varchar(50) NOT NULL,
  `tipo` varchar(1) NOT NULL DEFAULT 'M',
  `codigo_grupo` varchar(15) NOT NULL,
  `orden` int(11) NOT NULL,
  `peso_general` int(11) NOT NULL,
  `codigo_pantalla` varchar(30) DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `inactivo` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=91 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vista`
--

LOCK TABLES `vista` WRITE;
/*!40000 ALTER TABLE `vista` DISABLE KEYS */;
INSERT INTO `vista` VALUES (1,'Mantenimientos','','G','mant',0,1,'mant','fas fa-cogs',0),(2,'Usuarios','mant/usuarios','M','mant',1,1,'mantUsu','',0),(3,'Roles','mant/roles','M','mant',2,1,'mantRol','',0),(4,'Sucursales','mant/sucursales','M','mant',3,1,'mantSuc','',0),(5,'Proveedores','mant/proveedores','M','mant',4,1,'mantPro','',0),(8,'Impuestos','mant/impuestos','M','mant',7,1,'mantImp','',0),(9,'Categoria Productos','mant/categoria','M','mant',8,1,'mantCat','',0),(10,'Tipos de Gastos','mant/tiposgasto','M','mant',9,1,'mantTipGast','',0),(11,'Tipos de Pagos','mant/tipospago','M','mant',10,1,'mantTipPag','',0),(12,'Tipos de Ingreso','mant/tiposingreso','M','mant',11,1,'mantTipIng','',0),(15,'Gastos','','G','gastos',0,3,'gastos','fas fa-file-export',0),(16,'Registrar','gastos/nuevo','M','gastos',1,3,'gastNue','',0),(18,'Todos los gastos','gastos/administracion','M','gastos',3,3,'gastTodos','',0),(19,'Ingresos','','G','ingresos',0,4,'ingresos','fas fa-file-import',0),(20,'Registrar','ingresos/nuevo','M','ingresos',1,4,'ingNue','',0),(22,'Todos los ingresos','ingresos/administracion','M','ingresos',3,4,'ingTodos','',0),(30,'Parámetros Generales','mant/parametrosgenerales','M','mant',12,1,'mantParGen','',0),(49,'Productos Menú','','G','mnu_res',0,11,'mnu_res','fas fa-utensils',0),(50,'Productos Menú','menu/productos','M','mnu_res',1,11,'prod_mnu','',0),(52,'Menús','menu/menus','M','mnu_res',2,11,'mnus','',0),(53,'Materia Prima','','G','mt_prod',0,4,'mt_prod','fas fa-utensils',0),(54,'Materia Prima','materiaPrima/productos','M','mt_prod',1,11,'mt_product','',0),(55,'Productos Externos','','G','cod_ext',0,12,'cod_ext','fas fa-utensils',0),(56,'Inventarios','productoExterno/inventario/inventarios','M','cod_ext',2,12,'prod_ext_inv','',0),(57,'Productos Externos','productoExterno/productos','M','cod_ext',1,12,'prod_ext_prods','',0),(58,'Inventarios','materiaPrima/inventario/inventarios','M','mt_prod',5,12,'mt_inv','',0),(59,'Facturación','','G','fac',0,14,'fac','fas fa-file-invoice',0),(60,'POS','facturacion/pos','M','fac',1,14,'facFac',NULL,0),(63,'Pendientes aprobar','ingresos/pendientes','M','ingresos',2,4,'ingPendApr','',0),(64,'Ordenes Lista Entregar','facturacion/ordenesEntrega','M','fac',2,13,'ordList_cmds','',0),(66,'Códigos Promocionales','mant/codPromocion','M','mant',15,1,'mantCodProm','',0),(67,'Admin Ordenes ','facturacion/ordenesAdmin','M','fac',4,13,'adm_ord','',0),(68,'Promociones Productos','mant/grupoPromocion','M','mant',16,1,'mantPromProd','',0),(69,'Usuario Externo','','G','usuExt',0,8,NULL,'fa fa-male',0),(71,'Menú externo','usuarioExterno/menu','M','usuExt',1,8,'usuExtMnu','',0),(73,'Informes','','G','informes',0,6,'informes','fas fa-chart-line',0),(74,'Resumen Contable','informes/resumencontable','M','informes',1,6,'resCont','',0),(75,'Ventas por hora','informes/ventaXhora','M','informes',12,6,'ventaXhora','',0),(76,'Ventas producto','informes/ventaGenProductos','M','informes',13,6,'ventaGenProductos','',0),(77,'Mov Inv Productos externos','informes/movInvProductoExterno','M','informes',15,6,'movInvProductoExterno','',0),(78,'Mov Consumo Materia Prima','informes/movConMateriaPrima','M','informes',16,6,'movConMateriaPrima','',0),(79,'Consumo Materia Prima','informes/conMateriaPrima','M','informes',17,6,'conMateriaPrima','',0),(80,'Crear Toma Física','materiaPrima/inventario/tomaFisica','M','mt_prod',6,12,'mt_tomaFis','',0),(81,'Entregas','','G','entregas',0,15,'entregas','fas fa-truck',0),(82,'Entregas ','entregas/entregasPendientes','M','entregas',1,12,'entregas_pend','',0),(83,'Factura Electrónica','','G','fes',0,16,'fes','fas fa-landmark',0),(84,'Facturas','fe/facturas','M','fes',1,16,'fe_fes','',0),(85,'Gestionar Comandas ','','G','comandasGen',0,17,'comandasGen','fas fa-chalkboard',0),(86,'Administrar Comandas','comandar/admin','M','comandasGen',1,17,'comandasAdmin','',0),(87,'Mobiliario','','G','mobiliarioGen',0,18,'mobiliarioGen','fas fa-table',0),(88,'Administrar Mesas','mobiliario/mesas/admin','M','mobiliarioGen',1,18,'mesasAdmin','',0),(89,'Comandas Preparación','','G','comandasPrep',0,19,'comandasPrep','far fa-calendar',0),(90,'Comanda General','comandas/preparacion/comandaGen','M','comandasPrep',1,19,'comandaPrep','',0);
/*!40000 ALTER TABLE `vista` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-10-29 22:24:21
