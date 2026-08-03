CREATE TABLE IF NOT EXISTS `historial_articulos` (
  `id` int(11) NOT NULL,
  `inventario_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `nota` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `historial_articulos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inventario_id` (`inventario_id`);

ALTER TABLE `historial_articulos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `historial_articulos`
  ADD CONSTRAINT `historial_articulos_ibfk_1` FOREIGN KEY (`inventario_id`) REFERENCES `inventario` (`id`) ON DELETE CASCADE;