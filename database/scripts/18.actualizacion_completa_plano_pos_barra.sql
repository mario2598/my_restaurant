-- =============================================================================
-- Actualización completa: Plano de mesas + áreas configurables + POS Barra
-- Ejecutar en MySQL sobre la BD del restaurante (phpMyAdmin, Workbench, CLI).
-- Es idempotente: puede volver a ejecutarse; omite lo que ya exista.
-- =============================================================================
SET NAMES utf8mb4;
SET @db := DATABASE();

-- -----------------------------------------------------------------------------
-- 1) MESA: columnas del plano (script 15)
-- -----------------------------------------------------------------------------
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'mesa' AND COLUMN_NAME = 'plano_x') = 0,
  'ALTER TABLE `mesa`
     ADD COLUMN `plano_x` decimal(6,2) NULL DEFAULT NULL COMMENT ''Posición X en %% del plano'' AFTER `estado`,
     ADD COLUMN `plano_y` decimal(6,2) NULL DEFAULT NULL COMMENT ''Posición Y en %% del plano'' AFTER `plano_x`,
     ADD COLUMN `plano_ancho` decimal(6,2) NOT NULL DEFAULT 7.00 COMMENT ''Ancho %% plano'' AFTER `plano_y`,
     ADD COLUMN `plano_alto` decimal(6,2) NOT NULL DEFAULT 7.00 COMMENT ''Alto %% plano'' AFTER `plano_ancho`,
     ADD COLUMN `forma` varchar(20) NOT NULL DEFAULT ''rectangular'' AFTER `plano_alto`,
     ADD COLUMN `zona` varchar(40) NULL DEFAULT NULL COMMENT ''Código área: salon, terraza'' AFTER `forma`',
  'SELECT ''mesa: columnas plano ya existen'' AS info'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- -----------------------------------------------------------------------------
-- 2) SUCURSAL_PLANO (script 15)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sucursal_plano` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sucursal` int(11) NOT NULL,
  `nombre` varchar(80) NOT NULL DEFAULT 'Plano principal',
  `ancho_referencia` int(11) NOT NULL DEFAULT 100,
  `alto_referencia` int(11) NOT NULL DEFAULT 100,
  `zonas_json` mediumtext NULL COMMENT 'LEGACY: migrar a sucursal_plano_area',
  `activo` char(1) NOT NULL DEFAULT 'S',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_sucursal_plano_sucursal` (`sucursal`),
  CONSTRAINT `fk_sucursal_plano_sucursal` FOREIGN KEY (`sucursal`) REFERENCES `sucursal` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------
-- 3) SUCURSAL_PLANO_AREA — áreas configurables (script 15 / 17)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sucursal_plano_area` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sucursal` int(11) NOT NULL,
  `codigo` varchar(40) NOT NULL,
  `nombre` varchar(80) NOT NULL,
  `color` varchar(20) NOT NULL DEFAULT '#e9ecef',
  `plano_x` decimal(6,2) NULL DEFAULT NULL,
  `plano_y` decimal(6,2) NULL DEFAULT NULL,
  `plano_ancho` decimal(6,2) NULL DEFAULT NULL,
  `plano_alto` decimal(6,2) NULL DEFAULT NULL,
  `orden` int(11) NOT NULL DEFAULT 0,
  `activo` char(1) NOT NULL DEFAULT 'S',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_plano_area_sucursal_codigo` (`sucursal`, `codigo`),
  KEY `idx_plano_area_sucursal` (`sucursal`),
  CONSTRAINT `fk_plano_area_sucursal` FOREIGN KEY (`sucursal`) REFERENCES `sucursal` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------
-- 4) Vista y menú: Plano de mesas (script 15)
-- -----------------------------------------------------------------------------
INSERT INTO `vista` (`id`, `titulo`, `ruta`, `tipo`, `codigo_grupo`, `orden`, `peso_general`, `codigo_pantalla`, `icon`, `inactivo`)
SELECT NULL, 'Plano de mesas', 'mobiliario/mesas/plano', 'M', 'mobiliarioGen', 2, 18, 'mesasPlano', '', 0
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `vista` WHERE `codigo_pantalla` = 'mesasPlano');

INSERT INTO `menu` (`rol`, `vista`)
SELECT m.`rol`, v_plano.`id`
FROM `menu` m
INNER JOIN `vista` v_admin ON v_admin.`codigo_pantalla` = 'mesasAdmin' AND v_admin.`id` = m.`vista`
INNER JOIN `vista` v_plano ON v_plano.`codigo_pantalla` = 'mesasPlano'
WHERE NOT EXISTS (
  SELECT 1 FROM `menu` m2 WHERE m2.`rol` = m.`rol` AND m2.`vista` = v_plano.`id`
);

-- -----------------------------------------------------------------------------
-- 5) POS BARRA: cuenta_barra + orden.cuenta_barra_id (script 16)
-- -----------------------------------------------------------------------------
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

SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'orden' AND COLUMN_NAME = 'cuenta_barra_id') = 0,
  'ALTER TABLE `orden`
     ADD COLUMN `cuenta_barra_id` int(11) NULL DEFAULT NULL COMMENT ''FK cuenta_barra (POS barra)'' AFTER `mesa`,
     ADD KEY `idx_orden_cuenta_barra` (`cuenta_barra_id`),
     ADD CONSTRAINT `fk_orden_cuenta_barra` FOREIGN KEY (`cuenta_barra_id`) REFERENCES `cuenta_barra` (`id`) ON DELETE SET NULL ON UPDATE CASCADE',
  'SELECT ''orden: cuenta_barra_id ya existe'' AS info'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Vista POS Barra (id 94)
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

-- Menú POS Barra: mismos roles que tienen POS (facFac)
INSERT INTO `menu` (`rol`, `vista`)
SELECT m.`rol`, v_barra.`id`
FROM `menu` m
INNER JOIN `vista` v_pos ON v_pos.`codigo_pantalla` = 'facFac' AND v_pos.`id` = m.`vista`
INNER JOIN `vista` v_barra ON v_barra.`codigo_pantalla` = 'posBarra'
WHERE NOT EXISTS (
  SELECT 1 FROM `menu` m2 WHERE m2.`rol` = m.`rol` AND m2.`vista` = v_barra.`id`
);

-- =============================================================================
-- Fin. Verifique:
--   SELECT * FROM vista WHERE codigo_pantalla IN ('mesasPlano','posBarra');
--   SHOW TABLES LIKE 'sucursal_plano%';
--   SHOW TABLES LIKE 'cuenta_barra';
-- =============================================================================
