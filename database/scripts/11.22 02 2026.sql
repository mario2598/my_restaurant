ALTER TABLE `usuario`
  ADD COLUMN `llave_maestra` VARCHAR(100) NULL,
  ADD COLUMN `ind_llave_maestra_activa` TINYINT(1) NOT NULL DEFAULT 0;

CREATE TABLE `det_incidente_orden` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orden` int(11) NOT NULL,
  `monto_afectado` double NOT NULL DEFAULT 0,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `usuario` int(11) NOT NULL,
  `descripcion` varchar(2500) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `det_incidente_orden_fk01` (`orden`),
  KEY `det_incidente_orden_fk02` (`usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `det_incidente_orden`
  ADD CONSTRAINT `det_incidente_orden_fk01` FOREIGN KEY (`orden`) REFERENCES `orden` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `det_incidente_orden_fk02` FOREIGN KEY (`usuario`) REFERENCES `usuario` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

-- Si la tabla det_incidente_orden ya existía con codigo_descuento y codigo, ejecutar antes:
-- ALTER TABLE `det_incidente_orden` DROP FOREIGN KEY `det_incidente_orden_fk02`, DROP FOREIGN KEY `det_incidente_orden_fk04`;
-- ALTER TABLE `det_incidente_orden` DROP COLUMN `codigo_descuento`, DROP COLUMN `codigo`;
