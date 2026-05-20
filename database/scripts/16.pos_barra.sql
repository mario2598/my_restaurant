-- POS Barra: cuentas abiertas (tabs) y vínculo con órdenes
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `cuenta_barra` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sucursal` int(11) NOT NULL,
  `cierre_caja` int(11) NOT NULL,
  `etiqueta` varchar(80) NOT NULL,
  `mesa` int(11) NULL DEFAULT NULL,
  `orden_activa` int(11) NULL DEFAULT NULL,
  `estado` char(1) NOT NULL DEFAULT 'A' COMMENT 'A=abierta, C=cerrada',
  `fecha_apertura` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_cierre` datetime NULL DEFAULT NULL,
  `usuario` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_cuenta_barra_caja` (`cierre_caja`, `estado`),
  KEY `idx_cuenta_barra_sucursal` (`sucursal`),
  CONSTRAINT `fk_cuenta_barra_sucursal` FOREIGN KEY (`sucursal`) REFERENCES `sucursal` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `fk_cuenta_barra_mesa` FOREIGN KEY (`mesa`) REFERENCES `mesa` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_cuenta_barra_orden` FOREIGN KEY (`orden_activa`) REFERENCES `orden` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `orden`
  ADD COLUMN `cuenta_barra_id` int(11) NULL DEFAULT NULL COMMENT 'FK cuenta_barra (POS barra)' AFTER `mesa`,
  ADD KEY `idx_orden_cuenta_barra` (`cuenta_barra_id`),
  ADD CONSTRAINT `fk_orden_cuenta_barra` FOREIGN KEY (`cuenta_barra_id`) REFERENCES `cuenta_barra` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- Vista POS Barra (grupo fac, junto al POS)
INSERT INTO `vista` (`id`, `titulo`, `ruta`, `tipo`, `codigo_grupo`, `orden`, `peso_general`, `codigo_pantalla`, `icon`, `inactivo`) VALUES
(94, 'POS Barra', 'facturacion/posBarra', 'M', 'fac', 2, 14, 'posBarra', 'fas fa-cocktail', 0)
ON DUPLICATE KEY UPDATE
  `titulo` = VALUES(`titulo`),
  `ruta` = VALUES(`ruta`),
  `codigo_grupo` = VALUES(`codigo_grupo`),
  `orden` = VALUES(`orden`),
  `peso_general` = VALUES(`peso_general`),
  `codigo_pantalla` = VALUES(`codigo_pantalla`),
  `icon` = VALUES(`icon`);

