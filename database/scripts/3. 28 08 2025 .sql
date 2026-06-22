--Agregar campos a la tabla sucursal
ALTER TABLE `sucursal`
  ADD COLUMN `id_emisor_fe` int(11) NOT NULL,
  ADD COLUMN `id_sucursal_fe` int(11) NOT NULL,
  ADD COLUMN `tipo_identificacion_emisor` varchar(5) DEFAULT NULL;

//Agregar vista de clientes
INSERT INTO `vista` (`id`, `titulo`, `ruta`, `tipo`, `codigo_grupo`, `orden`, `peso_general`, `codigo_pantalla`, `icon`, `inactivo`) VALUES
(null, 'Clientes', 'mant/clientes', 'M', 'mant', 17, 1, 'mantClientes', '', 0);

