
ALTER TABLE `pago_orden` ADD `cliente` INT NULL AFTER `cod_promocion`;

ALTER TABLE `pago_orden` ADD CONSTRAINT `pago_orden_fk02` FOREIGN KEY (`cliente`)
 REFERENCES `cliente`(`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;