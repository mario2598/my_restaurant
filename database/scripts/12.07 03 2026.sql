ALTER TABLE `sucursal` ADD `url_logo_sistema` VARCHAR(4000) NULL , 
ADD `url_logo_factura` VARCHAR(4000) NULL AFTER `url_logo_sistema`;


INSERT INTO `vista` (`id`, `titulo`, `ruta`, `tipo`, `codigo_grupo`, `orden`, `peso_general`, `codigo_pantalla`, `icon`, `inactivo`) VALUES
(93, 'Panel de Control', 'informes/panelControl', 'M', 'informes', 0, 6, 'panelControl', '', 0);


ALTER TABLE `detalle_orden_comanda` ADD `fecha_hora_entrega` DATETIME NULL AFTER `preparado`;