-- Plano de sucursal: posición de mesas y zonas del local
SET NAMES utf8mb4;

ALTER TABLE `mesa`
  ADD COLUMN `plano_x` decimal(6,2) NULL DEFAULT NULL COMMENT 'Posición X en % del plano (0-100)' AFTER `estado`,
  ADD COLUMN `plano_y` decimal(6,2) NULL DEFAULT NULL COMMENT 'Posición Y en % del plano (0-100)' AFTER `plano_x`,
  ADD COLUMN `plano_ancho` decimal(6,2) NOT NULL DEFAULT 7.00 COMMENT 'Ancho en % del plano' AFTER `plano_y`,
  ADD COLUMN `plano_alto` decimal(6,2) NOT NULL DEFAULT 7.00 COMMENT 'Alto en % del plano' AFTER `plano_ancho`,
  ADD COLUMN `forma` varchar(20) NOT NULL DEFAULT 'rectangular' COMMENT 'rectangular, cuadrada, redonda' AFTER `plano_alto`,
  ADD COLUMN `zona` varchar(40) NULL DEFAULT NULL COMMENT 'Etiqueta de zona: salon, jardin, etc.' AFTER `forma`;

CREATE TABLE IF NOT EXISTS `sucursal_plano` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sucursal` int(11) NOT NULL,
  `nombre` varchar(80) NOT NULL DEFAULT 'Plano principal',
  `ancho_referencia` int(11) NOT NULL DEFAULT 100 COMMENT 'Unidades de referencia (ej. cm del dibujo)',
  `alto_referencia` int(11) NOT NULL DEFAULT 100,
  `zonas_json` mediumtext NULL COMMENT 'LEGACY: migrar a sucursal_plano_area; se importa al cargar el plano',
  `activo` char(1) NOT NULL DEFAULT 'S',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_sucursal_plano_sucursal` (`sucursal`),
  CONSTRAINT `fk_sucursal_plano_sucursal` FOREIGN KEY (`sucursal`) REFERENCES `sucursal` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Áreas del plano configurables por sucursal (cocina, terraza, VIP, etc.)
CREATE TABLE IF NOT EXISTS `sucursal_plano_area` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sucursal` int(11) NOT NULL,
  `codigo` varchar(40) NOT NULL COMMENT 'Slug único por sucursal: cocina, salon, terraza',
  `nombre` varchar(80) NOT NULL,
  `color` varchar(20) NOT NULL DEFAULT '#e9ecef',
  `plano_x` decimal(6,2) NULL DEFAULT NULL COMMENT 'Posición X en % (NULL = no dibujada aún)',
  `plano_y` decimal(6,2) NULL DEFAULT NULL,
  `plano_ancho` decimal(6,2) NULL DEFAULT NULL,
  `plano_alto` decimal(6,2) NULL DEFAULT NULL,
  `orden` int(11) NOT NULL DEFAULT 0,
  `activo` char(1) NOT NULL DEFAULT 'S' COMMENT 'S=activa, N=oculta en plano',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_plano_area_sucursal_codigo` (`sucursal`, `codigo`),
  KEY `idx_plano_area_sucursal` (`sucursal`),
  CONSTRAINT `fk_plano_area_sucursal` FOREIGN KEY (`sucursal`) REFERENCES `sucursal` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `vista` (`id`, `titulo`, `ruta`, `tipo`, `codigo_grupo`, `orden`, `peso_general`, `codigo_pantalla`, `icon`, `inactivo`) VALUES
(NULL, 'Plano de mesas', 'mobiliario/mesas/plano', 'M', 'mobiliarioGen', 2, 18, 'mesasPlano', '', 0);

-- Mismo acceso que "Administrar Mesas" para cada rol
INSERT INTO `menu` (`rol`, `vista`)
SELECT m.`rol`, v_plano.`id`
FROM `menu` m
INNER JOIN `vista` v_admin ON v_admin.`codigo_pantalla` = 'mesasAdmin' AND v_admin.`id` = m.`vista`
INNER JOIN `vista` v_plano ON v_plano.`codigo_pantalla` = 'mesasPlano'
WHERE NOT EXISTS (
  SELECT 1 FROM `menu` m2 WHERE m2.`rol` = m.`rol` AND m2.`vista` = v_plano.`id`
);

-- POS Barra: ver database/scripts/16.pos_barra.sql
