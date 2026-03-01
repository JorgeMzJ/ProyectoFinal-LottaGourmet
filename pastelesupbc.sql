-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 22-11-2025 a las 02:56:48
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `pastelesupbc`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compras`
--

CREATE TABLE `compras` (
  `id_compra` int(11) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `total` decimal(10,2) NOT NULL,
  `nombre_cliente` varchar(100) DEFAULT NULL,
  `email_cliente` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `compras`
--

INSERT INTO `compras` (`id_compra`, `fecha`, `total`, `nombre_cliente`, `email_cliente`) VALUES
(8, '2025-11-08 23:53:40', 420.00, 'Mar??a Gonz??lez', 'maria@email.com'),
(9, '2025-11-08 23:55:35', 420.00, 'Mar??a Gonz??lez', 'maria@email.com'),
(10, '2025-11-09 23:55:35', 640.00, 'Juan P??rez', 'juan@email.com'),
(11, '2025-11-10 23:55:36', 350.00, 'Ana L??pez', 'ana@email.com'),
(12, '2025-11-11 23:55:36', 850.00, 'Carlos Ruiz', 'carlos@email.com'),
(13, '2025-11-12 23:55:36', 820.00, 'Laura Mart??nez', 'laura@email.com'),
(14, '2025-11-13 23:55:36', 380.00, 'Pedro S??nchez', 'pedro@email.com'),
(15, '2025-11-14 23:55:36', 920.00, 'Sofia Torres', 'sofia@email.com'),
(16, '2025-11-15 23:55:36', 560.00, 'Diego Ram??rez', 'diego@email.com'),
(17, '2025-11-16 23:55:36', 410.00, 'Carmen Flores', 'carmen@email.com'),
(18, '2025-11-17 23:55:36', 730.00, 'Roberto Castro', 'roberto@email.com'),
(19, '2025-11-18 23:55:36', 890.00, 'Isabel Moreno', 'isabel@email.com'),
(20, '2025-11-19 23:55:36', 1050.00, 'Fernando Vega', 'fernando@email.com'),
(21, '2025-11-20 23:55:36', 780.00, 'Patricia Ortiz', 'patricia@email.com'),
(22, '2025-11-21 23:55:36', 960.00, 'Miguel Herrera', 'miguel@email.com'),
(23, '2025-11-13 23:57:30', 520.00, 'Ra·l Mendoza', 'raul@email.com'),
(24, '2025-11-22 00:19:16', 405.00, 'Jorge Esteban Méndez Jaime', 'jemj2006@gmail.com'),
(25, '2025-11-22 00:24:16', 320.00, 'admin', 'admin@gmail.com'),
(26, '2025-11-22 01:34:01', 740.00, 'Jorge Esteban Méndez Jaime', 'jemj2006@gmail.com'),
(27, '2025-11-22 01:37:30', 740.00, 'Jorge Esteban Méndez Jaime', 'jemj2006@gmail.com'),
(28, '2025-11-22 01:38:14', 740.00, 'Jorge Esteban Méndez Jaime', 'jemj2006@gmail.com'),
(29, '2025-11-22 01:43:54', 740.00, 'Jorge Esteban Méndez Jaime', 'jemj2006@gmail.com');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compra_detalles`
--

CREATE TABLE `compra_detalles` (
  `id_detalle` int(11) NOT NULL,
  `id_compra` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `compra_detalles`
--

INSERT INTO `compra_detalles` (`id_detalle`, `id_compra`, `id_producto`, `cantidad`, `precio_unitario`) VALUES
(14, 9, 1, 2, 180.00),
(15, 9, 11, 1, 60.00),
(16, 10, 11, 2, 170.00),
(17, 10, 3, 2, 150.00),
(18, 11, 14, 2, 175.00),
(19, 12, 1, 3, 180.00),
(20, 12, 15, 2, 145.00),
(21, 13, 11, 3, 170.00),
(22, 13, 6, 2, 165.00),
(23, 14, 7, 2, 190.00),
(24, 15, 1, 2, 180.00),
(25, 15, 11, 2, 170.00),
(26, 15, 16, 2, 195.00),
(27, 16, 3, 2, 150.00),
(28, 16, 14, 2, 130.00),
(29, 17, 15, 2, 145.00),
(30, 17, 9, 1, 120.00),
(31, 18, 1, 2, 180.00),
(32, 18, 6, 2, 165.00),
(33, 18, 7, 1, 40.00),
(34, 19, 11, 3, 170.00),
(35, 19, 16, 2, 195.00),
(36, 19, 14, 1, 155.00),
(37, 20, 1, 3, 180.00),
(38, 20, 11, 2, 170.00),
(39, 20, 6, 2, 165.00),
(40, 21, 3, 2, 150.00),
(41, 21, 14, 2, 130.00),
(42, 21, 9, 3, 150.00),
(43, 22, 1, 2, 180.00),
(44, 22, 11, 3, 170.00),
(45, 22, 16, 1, 195.00),
(46, 22, 15, 1, 145.00),
(47, 23, 11, 2, 170.00),
(48, 23, 1, 1, 180.00),
(49, 24, 14, 1, 210.00),
(50, 24, 16, 1, 195.00),
(51, 25, 23, 2, 160.00),
(52, 26, 11, 1, 200.00),
(53, 26, 1, 1, 180.00),
(54, 27, 11, 1, 200.00),
(55, 27, 1, 1, 180.00),
(56, 28, 11, 1, 200.00),
(57, 28, 1, 1, 180.00),
(58, 29, 11, 1, 200.00),
(59, 29, 1, 3, 180.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_pedidos`
--

CREATE TABLE `detalle_pedidos` (
  `id_detalle` int(11) NOT NULL,
  `id_pedido` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalle_pedidos`
--

INSERT INTO `detalle_pedidos` (`id_detalle`, `id_pedido`, `id_producto`, `cantidad`, `precio_unitario`) VALUES
(44, 19, 1, 5, 180.00),
(45, 19, 11, 5, 170.00),
(46, 20, 3, 4, 150.00),
(47, 20, 14, 4, 130.00),
(48, 21, 1, 3, 180.00),
(49, 21, 6, 3, 165.00),
(50, 22, 15, 5, 146.67),
(51, 22, 17, 5, 146.67),
(52, 22, 14, 5, 146.67),
(53, 23, 15, 7, 160.00),
(54, 23, 10, 6, 160.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `paquetes_eventos`
--

CREATE TABLE `paquetes_eventos` (
  `id_paquete` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `tipo_evento` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL,
  `cantidad_postres` int(11) NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `paquetes_eventos`
--

INSERT INTO `paquetes_eventos` (`id_paquete`, `nombre`, `tipo_evento`, `descripcion`, `precio`, `cantidad_postres`, `activo`) VALUES
(1, 'Paquete Cumpleaños Básico', 'Cumpleaños', 'Perfecto para fiestas pequeñas con 10 postres variados: carlota de fresa, vainilla y chocolate.', 1400.00, 10, 1),
(2, 'Paquete Cumpleaños Mediano', 'Cumpleaños', 'Ideal para celebraciones con 15 postres gourmet incluyendo red velvet, moka y selva negra.', 2200.00, 15, 1),
(3, 'Paquete Boda Básico', 'Boda', 'Para bodas íntimas: 20 postres elegantes con variedad de sabores premium.', 3200.00, 20, 1),
(4, 'Paquete Boda Mediano', 'Boda', 'Celebración especial: 30 postres seleccionados con carlota de frambuesa, red velvet y tres leches.', 4500.00, 30, 1),
(5, 'Paquete Corporativo Pequeño', 'Corporativo', 'Perfecto para reuniones: 12 postres de café moka, chocolate y vainilla.', 1600.00, 12, 1),
(6, 'Paquete Corporativo Mediano', 'Corporativo', 'Ideal para eventos de oficina: 20 postres premium variados.', 2800.00, 20, 1),
(7, 'Paquete Evento Social', 'Otro', 'Paquete versátil para cualquier evento: 15 postres con mezcla de carlota y pasteles.', 2000.00, 15, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `paquete_productos`
--

CREATE TABLE `paquete_productos` (
  `id` int(11) NOT NULL,
  `id_paquete` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `paquete_productos`
--

INSERT INTO `paquete_productos` (`id`, `id_paquete`, `id_producto`, `cantidad`) VALUES
(27, 1, 7, 3),
(28, 1, 11, 3),
(29, 2, 15, 5),
(30, 2, 17, 5),
(31, 2, 14, 5),
(33, 3, 15, 7),
(34, 3, 10, 6),
(36, 4, 15, 10),
(37, 4, 10, 7),
(38, 4, 14, 5),
(39, 5, 9, 4),
(40, 5, 17, 4),
(41, 5, 11, 4),
(42, 6, 9, 5),
(43, 6, 17, 6),
(44, 6, 15, 5),
(45, 6, 14, 4),
(48, 7, 11, 5);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id_pedido` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `fecha` datetime DEFAULT current_timestamp(),
  `tipoEvento` varchar(100) DEFAULT NULL,
  `fechaEvento` date DEFAULT NULL,
  `notas` text DEFAULT NULL,
  `id_paquete` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pedidos`
--

INSERT INTO `pedidos` (`id_pedido`, `id_usuario`, `fecha`, `tipoEvento`, `fechaEvento`, `notas`, `id_paquete`) VALUES
(19, 3, '2025-11-11 15:55:36', 'Boda', '2025-12-06', 'Decoraci??n especial', 1),
(20, 3, '2025-11-16 15:55:36', 'Cumplea??os', '2025-11-29', 'Tema infantil', 2),
(21, 3, '2025-11-19 15:55:36', 'Corporativo', '2025-11-26', 'Logo de empresa', NULL),
(22, 3, '2025-11-21 16:21:00', 'Cumpleaños', '2025-11-28', 'que no tengan nueces', 2),
(23, 2, '2025-11-21 16:24:36', 'Boda', '2025-12-04', '', 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id_producto` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `en_promocion` tinyint(1) NOT NULL DEFAULT 0,
  `precio_oferta` decimal(10,2) DEFAULT NULL
) ;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id_producto`, `nombre`, `descripcion`, `precio`, `stock`, `imagen`, `en_promocion`, `precio_oferta`) VALUES
(1, 'Carlota de Fresa', 'Deliciosa carlota con fresas frescas, crema pastelera y bizcocho esponjoso. Perfecta para cualquier celebración.', 180.00, 15, 'carlota_fresa.jpg', 0, NULL),
(3, 'Carlota de Limón', 'Carlota ácida y deliciosa con limón siciliano, mousse ligero y base de vainilla. Toque cítrico irresistible.', 175.00, 10, 'carlota_limon.jpg', 0, NULL),
(6, 'Carlota de Piña', 'Tropical carlota con trozos de piña natural, crema de coco y bizcocho húmedo. Sabor del paraíso.', 165.00, 11, 'carlota_pina.jpg', 0, NULL),
(7, 'Carlota de Vainilla', 'Clásica carlota de vainilla con crema pastelera, bizcocho esponjoso y decoración de chispas de chocolate.', 160.00, 18, 'carlota_vainilla.jpg', 0, NULL),
(9, 'Carlota de Café', 'Carlota para los amantes del café con espresso italiano, crema de café y galletitas de chocolate.', 170.00, 13, 'carlota_cafe.jpg', 0, NULL),
(10, 'Carlota de Tres Leches', 'Carlota tipo tres leches con leche condensada, leche evaporada y crema fresca. Clásico reinventado.', 175.00, 11, 'carlota_tres_leches.jpg', 0, NULL),
(11, 'Pastel de Chocolate', 'Esponjoso pastel de chocolate con ganache de chocolate oscuro y cobertura de cacao. Irresistible para chocolateros.', 220.00, 16, 'pastel_chocolate.jpg', 1, 200.00),
(12, 'Pastel de Zanahoria', 'Pastel de zanahoria con nueces, canela y frosting de queso crema. Saludable y delicioso.', 200.00, 10, 'pastel_zanahoria.jpg', 0, NULL),
(13, 'Pastel de Vainilla', 'Suave pastel de vainilla con relleno de crema pastelera y cobertura de buttercream. Perfecto para cualquier ocasión.', 190.00, 20, 'pastel_vainilla.jpg', 0, NULL),
(14, 'Pastel Selva Negra', 'Pastel multicapas con chocolate, cerezas en almíbar, crema chantilly y virutas de chocolate. Clásico elegante.', 240.00, 7, 'pastel_selva_negra.jpg', 1, 210.00),
(15, 'Pastel de Red Velvet', 'Pastel rojo terciopelado con frosting de queso crema, frambuesas y decoración de pétalos comestibles.', 210.00, 9, 'pastel_red_velvet.jpg', 0, NULL),
(16, 'Tarta de Limón', 'Tarta cítrica con limón fresco, buttercream de limón y merengue. Refrescante y aromática.', 195.00, 12, 'pastel_limon.jpg', 0, NULL),
(17, 'Pastel de Café Moca', 'Pastel capas con café, chocolate y crema mocha. Ideal para después de comidas.', 215.00, 11, 'pastel_moca.jpg', 0, NULL),
(19, 'Tarta Frutos Rojos', 'Tarta con capas de bizcocho vainilla, crema chantilly y mezcla de frutos rojos frescos. Colorido y delicioso.', 220.00, 10, 'pastel_frutos_rojos.jpg', 0, NULL),
(20, 'Pastel de Dulce de Leche', 'Pastel clásico relleno de dulce de leche casero, merengue y crocante de nueces. Caramelo puro.', 200.00, 15, 'pastel_dulce_leche.jpg', 0, NULL),
(21, 'Carlota de Frambuesa', 'Elegante carlota con frambuesas frescas, mousse de frambuesa y base de vainilla. Sabor sofisticado y delicado.', 195.00, 9, 'carlota_frambuesa.jpg', 1, 155.00),
(22, 'Carlota de Mango', 'Refrescante carlota elaborada con pulpa de mango, crema chantilly y galletas María. Ideal para días calurosos.', 170.00, 12, 'carlota_mango.jpg', 0, NULL),
(23, 'Carlota de Maracuyá', 'Exótica carlota con maracuyá fresco, mousse ligero y bizcocho de vainilla. Sabor tropical irresistible.', 185.00, 10, 'carlota_maracuya.jpg', 1, 160.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `es_admin` tinyint(1) DEFAULT 0,
  `creado_en` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `email`, `telefono`, `password_hash`, `es_admin`, `creado_en`) VALUES
(2, 'admin', 'admin@gmail.com', '12343567890', '$2y$10$l5D33pnJq5hA6vXWMRcHkO2FKynvm4I9uLbEgqG.qtuZKtZpTPJGy', 1, '2025-11-20 22:02:23'),
(3, 'Jorge Esteban Méndez Jaime', 'jemj2006@gmail.com', '6864728875', '$2y$10$eC6ZBI7mLZVHk/5qygCNVutTUxmfwsmT3/TICtr6Ox/g8H9RPdyNi', 0, '2025-11-21 00:46:09');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `compras`
--
ALTER TABLE `compras`
  ADD PRIMARY KEY (`id_compra`);

--
-- Indices de la tabla `compra_detalles`
--
ALTER TABLE `compra_detalles`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `fk_compra` (`id_compra`),
  ADD KEY `fk_producto` (`id_producto`);

--
-- Indices de la tabla `detalle_pedidos`
--
ALTER TABLE `detalle_pedidos`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `id_pedido` (`id_pedido`),
  ADD KEY `id_producto` (`id_producto`);

--
-- Indices de la tabla `paquetes_eventos`
--
ALTER TABLE `paquetes_eventos`
  ADD PRIMARY KEY (`id_paquete`);

--
-- Indices de la tabla `paquete_productos`
--
ALTER TABLE `paquete_productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_paquete` (`id_paquete`),
  ADD KEY `id_producto` (`id_producto`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id_pedido`),
  ADD KEY `id_cliente` (`id_usuario`),
  ADD KEY `id_paquete` (`id_paquete`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id_producto`),
  ADD KEY `idx_productos_en_promocion` (`en_promocion`),
  ADD KEY `idx_productos_precio` (`precio`),
  ADD KEY `idx_productos_stock` (`stock`);
ALTER TABLE `productos` ADD FULLTEXT KEY `ft_productos_nombre_descripcion` (`nombre`,`descripcion`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `compras`
--
ALTER TABLE `compras`
  MODIFY `id_compra` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT de la tabla `compra_detalles`
--
ALTER TABLE `compra_detalles`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT de la tabla `detalle_pedidos`
--
ALTER TABLE `detalle_pedidos`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT de la tabla `paquetes_eventos`
--
ALTER TABLE `paquetes_eventos`
  MODIFY `id_paquete` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `paquete_productos`
--
ALTER TABLE `paquete_productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id_pedido` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id_producto` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `compra_detalles`
--
ALTER TABLE `compra_detalles`
  ADD CONSTRAINT `fk_compra` FOREIGN KEY (`id_compra`) REFERENCES `compras` (`id_compra`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_producto` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `detalle_pedidos`
--
ALTER TABLE `detalle_pedidos`
  ADD CONSTRAINT `detalle_pedidos_ibfk_1` FOREIGN KEY (`id_pedido`) REFERENCES `pedidos` (`id_pedido`),
  ADD CONSTRAINT `detalle_pedidos_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`);

--
-- Filtros para la tabla `paquete_productos`
--
ALTER TABLE `paquete_productos`
  ADD CONSTRAINT `fk_paquete_productos_paquete` FOREIGN KEY (`id_paquete`) REFERENCES `paquetes_eventos` (`id_paquete`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_paquete_productos_producto` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `fk_pedidos_usuarios` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
