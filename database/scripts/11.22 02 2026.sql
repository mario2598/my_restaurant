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
  `codigo_descuento` int(11) NOT NULL,
  `codigo` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `det_incidente_orden_fk01` (`orden`),
  KEY `det_incidente_orden_fk02` (`codigo_descuento`),
  KEY `det_incidente_orden_fk03` (`usuario`),
  KEY `det_incidente_orden_fk04` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `det_incidente_orden`
  ADD CONSTRAINT `det_incidente_orden_fk01` FOREIGN KEY (`orden`) REFERENCES `orden` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `det_incidente_orden_fk02` FOREIGN KEY (`codigo_descuento`) REFERENCES `codigo_descuento` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `det_incidente_orden_fk03` FOREIGN KEY (`usuario`) REFERENCES `usuario` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `det_incidente_orden_fk04` FOREIGN KEY (`codigo`) REFERENCES `codigo_descuento` (`codigo`) ON DELETE NO ACTION ON UPDATE NO ACTION;
