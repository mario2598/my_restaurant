-- Si ya ejecutó el script 15 antes de añadir sucursal_plano_area, ejecute solo este archivo.
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `sucursal_plano_area` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sucursal` int(11) NOT NULL,
  `codigo` varchar(40) NOT NULL COMMENT 'Slug único por sucursal: cocina, salon, terraza',
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
