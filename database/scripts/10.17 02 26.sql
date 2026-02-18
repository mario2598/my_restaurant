-- Estructura de tabla para la tabla `pm_x_s_horarios`
-- Esta tabla permite configurar horarios específicos en los que un producto debe mostrarse en una sucursal
--
CREATE TABLE `pm_x_s_horarios` (
  `id` int(11) NOT NULL,
  `pm_x_sucursal` int(11) NOT NULL,
  `dia_semana` tinyint(1) NOT NULL COMMENT '1=Lunes, 2=Martes, 3=Miércoles, 4=Jueves, 5=Viernes, 6=Sábado, 7=Domingo',
  `hora_inicio` time NOT NULL COMMENT 'Hora de inicio en formato HH:MM:SS',
  `hora_fin` time NOT NULL COMMENT 'Hora de fin en formato HH:MM:SS',
  `activo` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=Activo, 0=Inactivo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Indices de la tabla `pm_x_s_horarios`
--
ALTER TABLE `pm_x_s_horarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pm_horarios_fk01` (`pm_x_sucursal`);

-- --------------------------------------------------------

--
-- AUTO_INCREMENT de la tabla `pm_x_s_horarios`
--
ALTER TABLE `pm_x_s_horarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- Filtros para la tabla `pm_x_s_horarios`
--
ALTER TABLE `pm_x_s_horarios`
  ADD CONSTRAINT `pm_horarios_fk01` FOREIGN KEY (`pm_x_sucursal`) REFERENCES `pm_x_sucursal` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;