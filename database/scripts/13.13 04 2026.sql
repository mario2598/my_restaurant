-- Monedas, tipo de cambio histórico, columnas en pago_orden e ingreso_pago
-- Convención tipo_cambio: unidades de MONEDA BASE por 1 unidad de la moneda foránea
--   (ej. base CRC, 1 USD = 520 CRC → tipo_cambio = 520). monto_base = monto_moneda * tipo_cambio.
-- La tabla orden NO se modifica; montos de orden siguen en moneda base.

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `sis_moneda` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cod_general` varchar(30) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `simbolo` varchar(15) NOT NULL DEFAULT '',
  `decimales` tinyint(4) NOT NULL DEFAULT 2,
  `es_base` char(1) NOT NULL DEFAULT 'N' COMMENT 'S = moneda contable base',
  `estado` char(1) NOT NULL DEFAULT 'A',
  `orden_visual` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_sis_moneda_cod` (`cod_general`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `sis_tipo_cambio` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `moneda_id` int(11) NOT NULL COMMENT 'FK sis_moneda (divisa distinta de base)',
  `fecha_hora` datetime NOT NULL DEFAULT current_timestamp(),
  `tipo_cambio` decimal(18,6) NOT NULL COMMENT 'Unidades moneda_base por 1 unidad moneda_id',
  `usuario` int(11) DEFAULT NULL,
  `sucursal` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_tc_moneda_fecha` (`moneda_id`, `fecha_hora`),
  CONSTRAINT `fk_sis_tc_moneda` FOREIGN KEY (`moneda_id`) REFERENCES `sis_moneda` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `sis_moneda` (`id`, `cod_general`, `nombre`, `simbolo`, `decimales`, `es_base`, `estado`, `orden_visual`) VALUES
(1, 'CRC', 'Colones costarricenses', '₡', 2, 'S', 'A', 0),
(2, 'USD', 'Dólares estadounidenses', 'US$', 2, 'N', 'A', 1),
(3, 'EUR', 'Euros', '€', 2, 'N', 'A', 2)
ON DUPLICATE KEY UPDATE
  `nombre` = VALUES(`nombre`),
  `simbolo` = VALUES(`simbolo`),
  `decimales` = VALUES(`decimales`),
  `es_base` = VALUES(`es_base`),
  `estado` = VALUES(`estado`),
  `orden_visual` = VALUES(`orden_visual`);

-- Tipos de cambio de ejemplo (último registro por moneda_id = el que usa el POS vía MAX(id)).
-- Ajuste los valores según su operación; convención: CRC por 1 USD / 1 EUR.
INSERT INTO `sis_tipo_cambio` (`moneda_id`, `fecha_hora`, `tipo_cambio`) VALUES
(2, NOW(), 520.000000),
(3, NOW(), 560.000000);

ALTER TABLE `pago_orden`
  ADD COLUMN `moneda_factura_id` int(11) NULL DEFAULT NULL COMMENT 'FK sis_moneda; NULL = cobro en moneda base' AFTER `impuesto_servicio`,
  ADD COLUMN `tipo_cambio_snapshot` decimal(18,6) NULL DEFAULT NULL COMMENT 'Base por 1 unidad moneda_factura al momento del cobro' AFTER `moneda_factura_id`,
  ADD COLUMN `total_moneda_doc` decimal(18,4) NULL DEFAULT NULL AFTER `tipo_cambio_snapshot`,
  ADD COLUMN `subtotal_moneda_doc` decimal(18,4) NULL DEFAULT NULL AFTER `total_moneda_doc`,
  ADD COLUMN `iva_moneda_doc` decimal(18,4) NULL DEFAULT NULL AFTER `subtotal_moneda_doc`,
  ADD COLUMN `descuento_moneda_doc` decimal(18,4) NULL DEFAULT NULL AFTER `iva_moneda_doc`,
  ADD COLUMN `impuesto_servicio_moneda_doc` decimal(18,4) NULL DEFAULT NULL AFTER `descuento_moneda_doc`,
  ADD KEY `idx_pago_orden_moneda` (`moneda_factura_id`),
  ADD CONSTRAINT `fk_pago_orden_moneda` FOREIGN KEY (`moneda_factura_id`) REFERENCES `sis_moneda` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

CREATE TABLE IF NOT EXISTS `ingreso_pago` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `ingreso` bigint(20) NOT NULL,
  `medio_pago` varchar(20) NOT NULL COMMENT 'EFECTIVO, TARJETA, SINPE',
  `moneda_id` int(11) NOT NULL,
  `monto_moneda` decimal(18,4) NOT NULL,
  `tipo_cambio_snapshot` decimal(18,6) NOT NULL,
  `monto_base` decimal(18,4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_ingreso_pago_ingreso` (`ingreso`),
  CONSTRAINT `fk_ingreso_pago_ingreso` FOREIGN KEY (`ingreso`) REFERENCES `ingreso` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ingreso_pago_moneda` FOREIGN KEY (`moneda_id`) REFERENCES `sis_moneda` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
