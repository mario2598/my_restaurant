CREATE TABLE `extra_generico`
 (`id` INT NOT NULL AUTO_INCREMENT , `descripcion`
  VARCHAR(500) NOT NULL , `precio`
   DOUBLE NOT NULL DEFAULT '0' , 
   `dsc_grupo` VARCHAR(500) NOT NULL ,
    `es_requerido` INT NOT NULL DEFAULT '0' ,
     `multiple` INT NOT NULL DEFAULT '0' ,
      `materia_prima` INT NULL DEFAULT NULL , 
      `cant_mp` DOUBLE NULL DEFAULT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;

INSERT INTO `vista` (`id`, `titulo`, `ruta`, `tipo`, `codigo_grupo`, `orden`, `peso_general`, `codigo_pantalla`, `icon`, `inactivo`) VALUES
(92, 'Extras Genericos', 'materiaPrima/extras-generico', 'M', 'mt_prod', 7, 12, 'mt_extras_generico', '', 0);
