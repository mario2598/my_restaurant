-- Crear clase para estados de respuesta de Hacienda
INSERT INTO `sis_clase` (`id`, `nombre`, `cod_general`) VALUES
(12, 'Estados de Respuesta de Hacienda/FactuX', 'EST_RESPUESTA_HACIENDA');

-- Crear estados de respuesta de Hacienda
INSERT INTO `sis_estado` (`id`, `nombre`, `clase`, `cod_general`) VALUES
(50, 'Pendiente de validación', 12, 'HACIENDA_PENDIENTE'),
(51, 'Aceptado por Hacienda', 12, 'HACIENDA_ACEPTADO'),
(52, 'Rechazado por Hacienda', 12, 'HACIENDA_RECHAZADO');

-- Agregar campos a la tabla fe_info para almacenar JSON y estado de Hacienda
ALTER TABLE `fe_info`
  ADD COLUMN `json_envio` TEXT NULL COMMENT 'JSON enviado a FactuX/Hacienda',
  ADD COLUMN `json_respuesta` TEXT NULL COMMENT 'JSON de respuesta de FactuX/Hacienda',
  ADD COLUMN `estado_hacienda` INT(11) NULL DEFAULT NULL COMMENT 'ID del estado del comprobante en Hacienda (FK a sis_estado)',
  ADD COLUMN `url_consulta_estado` VARCHAR(500) NULL DEFAULT NULL COMMENT 'URL para consultar el estado del comprobante en FactuX',
  ADD CONSTRAINT `fk_fe_info_estado_hacienda` FOREIGN KEY (`estado_hacienda`) REFERENCES `sis_estado` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

