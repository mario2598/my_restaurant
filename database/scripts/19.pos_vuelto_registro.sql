-- Registro de vueltos en cobros multimoneda (POS) para conciliar efectivo en caja.
-- vuelto_moneda_doc = divisa entregada al cliente; vuelto_moneda_base = colones entregados;
-- monto_retenido_doc = divisa que permanece en caja (recibido - vuelto en esa moneda).

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `pos_vuelto_registro` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `cierre_caja` int(11) NOT NULL,
  `pago_orden` bigint(20) DEFAULT NULL,
  `orden` bigint(20) DEFAULT NULL,
  `numero_orden` varchar(50) DEFAULT NULL,
  `moneda_cobro_id` int(11) NOT NULL,
  `tipo_cambio_snapshot` decimal(18,6) NOT NULL,
  `total_pagar_doc` decimal(18,4) NOT NULL,
  `monto_recibido_doc` decimal(18,4) NOT NULL,
  `vuelto_moneda_doc` decimal(18,4) NOT NULL DEFAULT 0.0000,
  `vuelto_moneda_base` decimal(18,4) NOT NULL DEFAULT 0.0000,
  `monto_retenido_doc` decimal(18,4) NOT NULL DEFAULT 0.0000 COMMENT 'Divisa que queda en caja',
  `usuario` int(11) DEFAULT NULL,
  `sucursal` int(11) DEFAULT NULL,
  `fecha_hora` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_pos_vuelto_cierre` (`cierre_caja`),
  KEY `idx_pos_vuelto_pago` (`pago_orden`),
  KEY `idx_pos_vuelto_orden` (`orden`),
  CONSTRAINT `fk_pos_vuelto_moneda` FOREIGN KEY (`moneda_cobro_id`) REFERENCES `sis_moneda` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
