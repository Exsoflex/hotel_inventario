ALTER TABLE `articulos` ADD COLUMN `estado` enum('nuevo') NOT NULL DEFAULT 'nuevo' AFTER `usa_codigo_barras`;

UPDATE `articulos` SET `estado` = 'nuevo' WHERE `estado` IS NULL OR `estado` = '';