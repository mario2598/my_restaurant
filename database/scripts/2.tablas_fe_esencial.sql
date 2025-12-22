-- =====================================================
-- TABLAS ESENCIALES PARA FACTURACIÓN ELECTRÓNICA
-- Sistema:Mi Restaurante
-- =====================================================

-- Tabla para información adicional de productos (FE)
CREATE TABLE IF NOT EXISTS `producto_fe_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigo_producto` varchar(50) NOT NULL COMMENT 'ID del producto (menú o externo)',
  `tipo_producto` enum('MENU','EXTERNO') NOT NULL COMMENT 'Tipo de producto',
  `codigo_cabys` varchar(20) NOT NULL COMMENT 'Código de clasificación arancelaria',
  `unidad_medida` varchar(10) NOT NULL DEFAULT 'Un' COMMENT 'Unidad de medida',
  `tarifa_impuesto` decimal(5,2) NOT NULL DEFAULT 13.00 COMMENT 'Porcentaje de IVA',
  `tipo_codigo` varchar(2) NOT NULL DEFAULT '04' COMMENT 'Tipo de código del producto',
  `exento` enum('S','N') NOT NULL DEFAULT 'N' COMMENT 'S: Exento de IVA, N: No exento',
  `impuesto_incluido` enum('S','N') NOT NULL DEFAULT 'N' COMMENT 'S: IVA incluido en precio',
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `identificacion` VARCHAR(30) NULL,
  PRIMARY KEY (`id`),
  KEY `idx_codigo_cabys` (`codigo_cabys`),
  KEY `idx_tipo_codigo` (`tipo_codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Información de productos para Facturación Electrónica';

CREATE TABLE `cliente_fe_info` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL COMMENT 'ID del cliente',
  `codigo_actividad` varchar(10) NOT NULL DEFAULT '722003' COMMENT 'Código de actividad económica',
  `tipo_identificacion` varchar(2) NOT NULL DEFAULT '01' COMMENT '01: Cédula Física, 02: Cédula Jurídica',
  `nombre_comercial` varchar(200) DEFAULT NULL COMMENT 'Nombre comercial del cliente',
  `direccion` text DEFAULT NULL COMMENT 'Dirección completa del cliente',
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `fecha_modificacion` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `identificacion` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Información de clientes para Facturación Electrónica';
