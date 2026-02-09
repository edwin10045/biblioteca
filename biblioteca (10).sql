-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 26-07-2025 a las 08:07:10
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
-- Base de datos: `biblioteca`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `autores`
--

CREATE TABLE `autores` (
  `id` int(11) NOT NULL,
  `nombre_completo` varchar(100) NOT NULL,
  `nacionalidad` varchar(50) DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `autores`
--

INSERT INTO `autores` (`id`, `nombre_completo`, `nacionalidad`, `fecha_nacimiento`) VALUES
(1, 'Gabriel García Márquez', 'Colombiana', '1927-03-06'),
(2, 'Isabel Allende', 'Chilena', '1942-08-02'),
(3, 'Mario Vargas Llosa', 'Peruana', '1936-03-28'),
(4, 'Carlos Fuentes', 'Mexicana', '1928-11-11'),
(5, 'Laura Esquivel', 'Mexicana', '1950-09-30'),
(6, 'Octavio Paz', 'Mexicana', '1914-03-31'),
(7, 'Jorge Luis Borges', 'Argentina', '1899-08-24'),
(8, 'Juan Rulfo', 'Mexicana', '1917-05-16'),
(9, 'Rosario Castellanos', 'Mexicana', '1925-05-25'),
(10, 'Ana María Matute', 'Española', '1925-07-26');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id`, `nombre`) VALUES
(1, 'Ficción'),
(2, 'Ciencia Ficción'),
(3, 'Novela Histórica'),
(4, 'Terror'),
(5, 'Romance'),
(6, 'Fantasía'),
(7, 'Misterio'),
(8, 'Biografía'),
(9, 'Ensayo'),
(10, 'Autoayuda');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `editoriales`
--

CREATE TABLE `editoriales` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `sitio_web` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `editoriales`
--

INSERT INTO `editoriales` (`id`, `nombre`, `sitio_web`) VALUES
(1, 'Penguin Random House', 'https://www.penguinrandomhouse.com'),
(2, 'Grupo Planeta', 'https://www.planetadelibros.com'),
(3, 'Alfaguara', 'https://www.megustaleer.com/editoriales/alfaguara'),
(4, 'Ediciones Siruela', 'https://www.siruela.com'),
(5, 'Anagrama', 'https://www.anagrama-ed.es'),
(6, 'Tusquets Editores', 'https://www.tusquetseditores.com'),
(7, 'Editorial Porrúa', 'https://www.porrua.mx'),
(8, 'Editorial SM', 'https://www.grupo-sm.com'),
(9, 'Ediciones Paidós', 'https://www.paidos.com'),
(10, 'Fondo de Cultura Económica', 'https://www.fondodeculturaeconomica.com');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleados`
--

CREATE TABLE `empleados` (
  `id` int(11) NOT NULL,
  `nombre_completo` varchar(100) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `contraseña` varchar(200) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `fecha_contratacion` date DEFAULT NULL,
  `estado` varchar(20) DEFAULT NULL,
  `rol` enum('Administrador','Empleado','Gerente') NOT NULL DEFAULT 'Empleado'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `empleados`
--

INSERT INTO `empleados` (`id`, `nombre_completo`, `usuario`, `contraseña`, `telefono`, `fecha_contratacion`, `estado`, `rol`) VALUES
(3, 'Erick', 'Erick', '$2y$10$E4aRfMbLhXouRmUf80wOdeVKEhVK/uJDbe4enb.d/Ca5CUFTrcgpO', '9989596251', '2025-07-15', 'Activo', 'Empleado'),
(4, 'Edwin', 'edwinempleado', '$2y$10$E4aRfMbLhXouRmUf80wOdeVKEhVK/uJDbe4enb.d/Ca5CUFTrcgpO', '999 234 2012', '2025-07-03', 'Bloqueado', 'Empleado'),
(5, 'Xavier Isai Reyes Martinez', 'xaviadmin', '$2y$10$pZtOpnaFelndBv3RM7a9Wua0W87Zp6dAIw0kBSGKytHVSg0gSEC4C', '999 234 2012', '2025-07-01', 'Activo', 'Administrador'),
(6, 'Isabel Proot', 'Isabel', '$2y$10$ZxvIukxntTOtGALh5.uQs.XPX6lBjZ1y.E3zLjKOMOIFWz.C2/xIa', '998 309 2345', '2025-07-17', 'Activo', 'Empleado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `libros`
--

CREATE TABLE `libros` (
  `id` int(11) NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `isbn` varchar(20) DEFAULT NULL,
  `año_publicacion` int(11) DEFAULT NULL,
  `edicion` varchar(50) DEFAULT NULL,
  `idioma` varchar(50) DEFAULT NULL,
  `sinopsis` text DEFAULT NULL,
  `num_paginas` int(11) DEFAULT NULL,
  `portada_url` varchar(200) DEFAULT NULL,
  `formato` varchar(50) DEFAULT NULL,
  `estado` varchar(20) DEFAULT NULL,
  `precio_venta` decimal(10,2) DEFAULT NULL,
  `precio_alquiler` decimal(10,2) DEFAULT NULL,
  `stock_total` int(11) DEFAULT NULL,
  `stock_disponible` int(11) DEFAULT NULL,
  `editorial_id` int(11) DEFAULT NULL,
  `autor_id` int(11) DEFAULT NULL,
  `categoria_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `libros`
--

INSERT INTO `libros` (`id`, `titulo`, `isbn`, `año_publicacion`, `edicion`, `idioma`, `sinopsis`, `num_paginas`, `portada_url`, `formato`, `estado`, `precio_venta`, `precio_alquiler`, `stock_total`, `stock_disponible`, `editorial_id`, `autor_id`, `categoria_id`) VALUES
(4, 'El nombre del viento', '9788498383621', 2007, 'Primera', 'Español', 'Kvothe narra su vida, desde la niñez hasta convertirse en leyenda.', 672, 'portadas/viento.png', 'Físico', 'Disponible', 320.00, 50.00, 10, 2, 1, 1, 6),
(5, 'Cien años de soledad', '9780307474728', 1967, 'Quinta', 'Español', 'La historia de la familia Buendía en el mítico pueblo de Macondo.', 417, 'portadas/cien_portada.png', 'Físico', 'Disponible', 280.00, 45.00, 8, 0, 2, 1, 1),
(8, 'Pedro Páramo', '978-607-16-1107-3', 1955, '1ª edición', 'Español', 'La novela narra la historia de Juan Preciado que viaja al pueblo de Comala para encontrar a su padre, Pedro Páramo, y se encuentra con un pueblo lleno de fantasmas y recuerdos del pasado. Es una obra fundamental de la literatura mexicana y latinoamericana.', 124, 'portadas/portada_1752730271_3566.jpg', 'Rústica / Tapa blanda', 'Disponible', 150.00, 50.00, 12, 3, 3, 8, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `libros_devueltos`
--

CREATE TABLE `libros_devueltos` (
  `id` int(11) NOT NULL,
  `prestamo_id` int(11) DEFAULT NULL,
  `libro_id` int(11) DEFAULT NULL,
  `fecha_devolucion` date DEFAULT NULL,
  `estado_libro` varchar(50) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `multa_aplicada` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `libros_devueltos`
--

INSERT INTO `libros_devueltos` (`id`, `prestamo_id`, `libro_id`, `fecha_devolucion`, `estado_libro`, `observaciones`, `multa_aplicada`) VALUES
(1, 4, 4, '2025-07-20', 'Bueno', '', 0.00),
(2, 3, 8, '2025-07-20', 'Dañado', '', 0.00),
(3, 5, 4, '2025-07-20', 'Bueno', '', 0.00),
(4, 6, 5, '2025-07-20', 'Perdido', '', 0.00),
(5, 7, 8, '2025-07-20', 'Dañado', '', 0.00),
(6, 8, 8, '2025-07-20', 'Bueno', 'Bien', 0.00),
(7, 9, 8, '2025-07-20', 'Bueno', '', 0.00),
(8, 10, 8, '2025-07-26', 'Dañado', '', 0.00),
(9, 12, 4, '2025-07-26', 'Perdido', '', 25.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prestamos`
--

CREATE TABLE `prestamos` (
  `id` int(11) NOT NULL,
  `cliente_nombre` varchar(100) DEFAULT NULL,
  `cliente_telefono` varchar(20) DEFAULT NULL,
  `empleado_id` int(11) DEFAULT NULL,
  `fecha_prestamo` date DEFAULT NULL,
  `fecha_limite` date DEFAULT NULL,
  `fecha_devolucion` date DEFAULT NULL,
  `multa` decimal(10,2) DEFAULT NULL,
  `estado` varchar(20) DEFAULT NULL,
  `libro_ID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `prestamos`
--

INSERT INTO `prestamos` (`id`, `cliente_nombre`, `cliente_telefono`, `empleado_id`, `fecha_prestamo`, `fecha_limite`, `fecha_devolucion`, `multa`, `estado`, `libro_ID`) VALUES
(3, 'Juan Morales', '9371316373', NULL, '2025-07-20', '2025-07-31', '2025-07-20', 100.00, 'Devuelto', 8),
(4, 'Danny Noeta', '9983742322', NULL, '2025-07-20', '2025-07-28', '2025-07-20', 0.00, 'Devuelto', 4),
(5, 'Peponcio', '9982349983', NULL, '2025-07-20', '2025-07-31', '2025-07-20', 0.00, 'Devuelto', 4),
(6, 'Rogelio Torruco', '9873402034', NULL, '2025-07-20', '2025-07-31', '2025-07-20', 0.00, 'Devuelto', 5),
(7, 'Peponcio', '9982349983', NULL, '2025-07-20', '2025-07-21', '2025-07-20', 0.00, 'Devuelto', 8),
(8, 'Xavier Isai', '9982349983', NULL, '2025-07-20', '2025-07-28', '2025-07-20', 0.00, 'Devuelto', 8),
(9, 'Xavier Isai', '9873402034', NULL, '2025-07-20', '2025-08-08', '2025-07-20', 0.00, 'Devuelto', 8),
(10, 'Jorge', '9873402034', NULL, '2025-07-20', '2025-08-08', '2025-07-26', 0.00, 'Devuelto', 8),
(11, 'Juan Morales', '9983742322', NULL, '2025-07-26', '2025-07-12', NULL, 0.00, 'Devuelto', 4),
(12, 'Juan Morales', '9982349983', NULL, '2025-07-26', '2025-07-21', '2025-07-26', 50.00, 'Retrasado', 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reservas`
--

CREATE TABLE `reservas` (
  `id` int(11) NOT NULL,
  `cliente_nombre` varchar(100) DEFAULT NULL,
  `cliente_telefono` varchar(20) DEFAULT NULL,
  `libro_id` int(11) DEFAULT NULL,
  `fecha_solicitud` date DEFAULT NULL,
  `fecha_disponibilidad` date DEFAULT NULL,
  `estado` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `reservas`
--

INSERT INTO `reservas` (`id`, `cliente_nombre`, `cliente_telefono`, `libro_id`, `fecha_solicitud`, `fecha_disponibilidad`, `estado`) VALUES
(3, 'Juan Morales', '9982349983', 4, '2025-07-20', NULL, 'Confirmada'),
(6, 'Juan Morales', '9983742322', 8, '2025-07-26', NULL, 'Confirmada'),
(7, 'Ricardo', '9983742322', 8, '2025-07-26', NULL, 'Pendiente'),
(8, 'Danny Noeta', '9999999999', 8, '2025-07-26', NULL, 'Pendiente'),
(9, 'Juan Morales', '9983742322', 4, '2025-07-26', NULL, 'Pendiente'),
(10, 'Danny Noeta', '9999999999', 4, '2025-07-26', NULL, 'Pendiente'),
(11, 'Danny Noeta', '9999999999', 4, '2025-07-26', NULL, 'Pendiente'),
(12, 'Danny Noeta', '9999999999', 4, '2025-07-26', NULL, 'Pendiente'),
(13, 'Peponcio', '9982349983', 4, '2025-07-26', NULL, 'Pendiente');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

CREATE TABLE `ventas` (
  `id` int(11) NOT NULL,
  `cliente_nombre` varchar(100) DEFAULT NULL,
  `cliente_telefono` varchar(20) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `empleado_id` int(11) DEFAULT NULL,
  `fecha_compra` date DEFAULT NULL,
  `total_pagado` decimal(10,2) DEFAULT NULL,
  `metodo_pago` varchar(50) DEFAULT NULL,
  `libro_id` int(11) DEFAULT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ventas`
--

INSERT INTO `ventas` (`id`, `cliente_nombre`, `cliente_telefono`, `correo`, `empleado_id`, `fecha_compra`, `total_pagado`, `metodo_pago`, `libro_id`, `cantidad`) VALUES
(1, 'Daniel ', '555-555-5555', NULL, 6, '2025-07-19', 150.00, 'Efectivo', 8, 1),
(2, 'Alejandro', '111-111-1111', NULL, 5, '2023-11-19', 150.00, 'Efectivo', 8, 1),
(3, 'Jorge', '9983742322', NULL, 3, '2025-07-20', 320.00, 'Transferencia', 4, 1),
(4, 'Pepe', '9983742322', NULL, 3, '2025-07-20', 280.00, 'Tarjeta', 5, 1),
(5, 'Azteca', '9983742322', NULL, 3, '2025-07-20', 150.00, 'Efectivo', 8, 1),
(6, 'Yeye', '9983742322', NULL, 3, '2025-07-20', 1600.00, 'Efectivo', 4, 5),
(7, 'Ricardo', '9999999999', NULL, 3, '2025-07-22', 1400.00, 'Efectivo', 5, 5),
(8, 'edwin', '9980485', NULL, 3, '2025-07-23', 150.00, 'Efectivo', 8, 1),
(9, 'edwin', '9980485', NULL, 3, '2025-07-23', 150.00, 'Efectivo', 8, 1),
(10, 'Edwin', '9980485', NULL, 3, '2025-07-23', 150.00, 'Efectivo', 8, 1),
(11, 'edwin', '', '', 3, '2025-07-23', 150.00, '', 8, 1),
(12, 'edwin', '9989399337', 'diaztece@gmail.com', 3, '2025-07-23', 150.00, 'Efectivo', 8, 1),
(13, 'edwin', '4444', 'juanmolan66@gmail.com', 3, '2025-07-23', 150.00, 'Tarjeta', 8, 1),
(14, '', '', '202400236@upqroo.edu.mx', 3, '2025-07-23', 150.00, '', 8, 1),
(15, '', '', 'diaztecete@gmail.com', 3, '2025-07-23', 150.00, '', 8, 1),
(16, '', '', 'duvadiaztec@gmail.com', 3, '2025-07-23', 320.00, '', 4, 1),
(17, '', '', 'duvadiaztec@gmail.com', 3, '2025-07-23', 320.00, '', 4, 1),
(18, '', '', 'duvadiaztec@gmail.com', 3, '2025-07-23', 320.00, '', 4, 1),
(19, '', '', 'duvadiaztec@gmail.com', 3, '2025-07-23', 320.00, '', 4, 1),
(20, '', '', 'duvadiaztec@gmail.com', 3, '2025-07-23', 320.00, '', 4, 1),
(21, '', '', 'duvadiaztec@gmail.com', 3, '2025-07-23', 320.00, '', 4, 1),
(22, '', '', 'duvadiaztec@gmail.com', 3, '2025-07-23', 320.00, '', 4, 1),
(23, '', '', '', 3, '2025-07-23', 320.00, '', 4, 1),
(24, '', '', '', 3, '2025-07-23', 320.00, '', 4, 1),
(25, '', '', 'duvadiaztec@gmail.com', 3, '2025-07-23', 320.00, '', 4, 1),
(26, '', '', 'duvadiaztec@gmail.com', 3, '2025-07-23', 320.00, '', 4, 1),
(27, '', '', 'duvadiaztec@gmail.com', 3, '2025-07-23', 320.00, '', 4, 1),
(28, '', '', 'diaztece@gmail.com', 3, '2025-07-23', 320.00, '', 4, 1),
(29, '', '', 'diaztece@gmail.com', 3, '2025-07-23', 320.00, '', 4, 1),
(30, 'Edwin', '9980485', 'diaztecete@gmail.com', 3, '2025-07-23', 320.00, 'Efectivo', 4, 1),
(31, '', '', '202400236@upqroo.edu.mx', 3, '2025-07-23', 320.00, '', 4, 1),
(32, 'Edwin', '9980485', 'diaztecete@gmail.com', 3, '2025-07-23', 320.00, 'Efectivo', 4, 1),
(33, '', '', 'juanmolan66@gmail.com', 3, '2025-07-23', 320.00, '', 4, 1),
(34, '', '', 'juanmolan66@gmail.com', 3, '2025-07-23', 320.00, 'Efectivo', 4, 1),
(35, 'nicolas', '9938188', 'nicolasdiazalvaro08@gmail.com', 3, '2025-07-23', 320.00, 'Tarjeta', 4, 1),
(36, '', '', 'diaztecete@gmail.com', 3, '2025-07-23', 320.00, 'Efectivo', 4, 1),
(37, '', '', 'diaztece@gmail.com', 3, '2025-07-23', 320.00, '', 4, 1),
(38, '', '', 'nicolasdiazalvaro08@gmail.com', 3, '2025-07-23', 320.00, 'Efectivo', 4, 1),
(39, '', '', 'nicolasdiazalvaro08@gmail.com', 3, '2025-07-23', 320.00, '', 4, 1),
(40, '', '', 'nicolasdiazalvaro08@gmail.com', 3, '2025-07-23', 320.00, '', 4, 1),
(41, '', '', 'nicolasdiazalvaro08@gmail.com', 3, '2025-07-23', 320.00, '', 4, 1),
(42, '', '', 'juanmolan66@gmail.com', 3, '2025-07-23', 320.00, '', 4, 1),
(43, 'edwin', '9980485', 'diaztece@gmail.com', 3, '2025-07-23', 320.00, 'Tarjeta', 4, 1),
(44, '', '', 'nicolasdiazalvaro08@gmail.com', 3, '2025-07-23', 320.00, '', 4, 1),
(45, 'edwin', '9980485', 'diaztece@gmail.com', 3, '2025-07-23', 320.00, 'Efectivo', 4, 1),
(46, 'edwin', '9980485', 'duvadiaztec@gmail.com', 3, '2025-07-23', 320.00, 'Transferencia', 4, 1),
(47, 'johan', '', 'diaztecete@gmail.com', 3, '2025-07-23', 320.00, '', 4, 1),
(48, 'johan', '111111', 'diaztecete@gmail.com', 3, NULL, NULL, NULL, 4, 1),
(49, 'eddddddddddddddd', 'ddd', 'diaztece@gmail.com', 3, '2025-07-23', 320.00, 'Tarjeta', 4, 1),
(50, 'aaaaa', '', 'diaztecete@gmail.com', 3, '2025-07-23', 320.00, '', 4, 1),
(51, 'edwin', '9980485', '202400236@upqroo.edu.mx', 3, '2025-07-23', 320.00, 'Transferencia', 4, 1),
(52, 'juan', '33333', 'juanmolan66@gmail.com', 3, '2025-07-23', 320.00, 'Efectivo', 4, 1),
(53, 'juan', '33333', 'juanmolan66@gmail.com', 3, '2025-07-23', 320.00, 'Efectivo', 4, 1),
(54, 'edwin', '9980485', '202400236@upqroo.edu.mx', 3, '2025-07-23', 320.00, 'Efectivo', 4, 1),
(55, '', '', '', 3, '2025-07-23', 320.00, '', 4, 1),
(56, '', '', '', 3, '2025-07-23', 320.00, '', 4, 1),
(57, '', '', '', 3, '2025-07-23', 320.00, '', 4, 1),
(58, '', '', '', 3, '2025-07-23', 320.00, '', 4, 1),
(59, '', '', '', 3, '2025-07-23', 320.00, '', 4, 1),
(60, '', '', '', 3, '2025-07-23', 320.00, '', 4, 1),
(61, 'Juan Morales', '9983742322', 'juanmolan66@gmail.com', 3, '2025-07-23', 320.00, 'Efectivo', 4, 1),
(62, 'Juan Morales', '9983742322', 'juanmolan66@gmail.com', 3, '2025-07-23', 320.00, 'Efectivo', 4, 1),
(63, 'Juan', '938383838', 'juanmolan66@gmail.com', 3, '2025-07-23', 320.00, 'Tarjeta', 4, 1),
(64, 'Juan', '938383838', 'juanmolan66@gmail.com', 3, '2025-07-23', 320.00, 'Tarjeta', 4, 1),
(65, 'Juan Morales', '9983742322', 'juanmolan66@gmail.com', 3, '2025-07-23', 320.00, 'Tarjeta', 4, 1),
(66, 'Ricardo', '9983742322', 'juanmolan66@gmail.com', 3, '2025-07-23', 320.00, 'Transferencia', 4, 1),
(67, 'Ricardo', '9983742322', 'juanmolan66@gmail.com', 3, '2025-07-23', 320.00, 'Transferencia', 4, 1),
(68, 'Danny Noeta', '9999999999', 'juanmolan66@gmail.com', 3, '2025-07-23', 2880.00, 'Efectivo', 4, 9),
(69, 'Juan Morales', '9999999999', 'juanmolan66@gmail.com', 3, '2025-07-23', 960.00, 'Tarjeta', 4, 3),
(70, 'Juan Morales', '9999999999', 'juanmolan66@gmail.com', 3, '2025-07-23', 960.00, 'Tarjeta', 4, 3),
(71, 'Juan Morales', '9983742322', 'juanmolan66@gmail.com', 3, '2025-07-23', 320.00, 'Efectivo', 4, 1),
(72, 'Juan', '938383838', 'juanmolan66@gmail.com', 5, '2025-07-23', 320.00, 'Efectivo', 4, 1),
(73, 'Juan', '938383838', 'juanmolan66@gmail.com', 5, '2025-07-23', 320.00, 'Efectivo', 4, 1),
(74, 'Juan', '938383838', 'juanmolan66@gmail.com', 5, '2025-07-23', 960.00, 'Efectivo', 4, 3),
(75, 'Juan Morales', '9983742322', 'juanmolan66@gmail.com', 3, '2025-07-23', 640.00, 'Tarjeta', 4, 2),
(76, 'Juan', '938383838', 'juanmolan66@gmail.com', 5, '2025-07-23', 640.00, 'Transferencia', 4, 2),
(77, 'Juan', '938383838', 'juanmolan66@gmail.com', 5, '2025-07-23', 640.00, 'Transferencia', 4, 2),
(78, 'Juan', '938383838', 'juanmolan66@gmail.com', 5, '2025-07-23', 640.00, 'Transferencia', 4, 2),
(79, 'Juan Morales', '9983742322', 'juanmolan66@gmail.com', 3, '2025-07-23', 640.00, 'Tarjeta', 4, 2),
(80, 'Ricardo', '9999999999', 'juanmolan66@gmail.com', 3, '2025-07-23', 320.00, 'Tarjeta', 4, 1),
(81, 'Ricardo', '9999999999', 'juanmolan66@gmail.com', 3, '2025-07-23', 320.00, 'Tarjeta', 4, 1),
(82, 'Juan Morales', '9371316373', 'juanmolan66@gmail.com', 3, '2025-07-23', 640.00, 'Tarjeta', 4, 2),
(83, 'Juan Morales', '9371316373', 'juanmolan66@gmail.com', 3, '2025-07-23', 640.00, 'Tarjeta', 4, 2),
(84, 'Juan Morales', '9371316373', 'juanmolan66@gmail.com', 3, '2025-07-23', 640.00, 'Tarjeta', 4, 2),
(85, 'Juan Morales', '9371316373', 'juanmolan66@gmail.com', 3, '2025-07-23', 640.00, 'Tarjeta', 4, 2),
(86, 'Juan Morales', '9371316373', 'juanmolan66@gmail.com', 3, '2025-07-23', 640.00, 'Tarjeta', 4, 2),
(87, 'Juan Morales', '9371316373', 'juanmolan66@gmail.com', 3, '2025-07-23', 640.00, 'Tarjeta', 4, 2),
(88, 'Juan Morales', '9371316373', 'juanmolan66@gmail.com', 3, '2025-07-23', 640.00, 'Tarjeta', 4, 2),
(89, 'Juan Morales', '9371316373', 'juanmolan66@gmail.com', 3, '2025-07-23', 640.00, 'Tarjeta', 4, 2),
(90, 'Juan Morales', '9371316373', 'juanmolan66@gmail.com', 3, '2025-07-23', 640.00, 'Tarjeta', 4, 2),
(91, 'Juan Morales', '9371316373', 'juanmolan66@gmail.com', 3, '2025-07-23', 640.00, 'Tarjeta', 4, 2),
(92, '', '', '', 5, '2025-07-23', 320.00, '', 4, 1),
(93, '', '', '', 5, '2025-07-23', 320.00, '', 4, 1),
(94, 'Juan Morales', '9983742322', 'juanmolan66@gmail.com', 3, '2025-07-23', 640.00, 'Efectivo', 4, 2),
(95, '', '', '', 5, '2025-07-23', 320.00, '', 4, 1),
(96, '', '', '', 3, '2025-07-23', 320.00, '', 4, 1),
(97, '', '', '', 3, '2025-07-23', 320.00, '', 4, 1),
(98, '', '', '', 3, '2025-07-23', 320.00, '', 4, 1),
(99, '', '', '', 3, '2025-07-23', 320.00, '', 4, 1),
(100, '', '', '', 5, '2025-07-23', 320.00, '', 4, 1),
(101, '', '', '', 5, '2025-07-23', 320.00, '', 4, 1),
(102, '', '', '', 5, '2025-07-23', 320.00, '', 4, 1),
(103, '', '', '', 5, '2025-07-23', 320.00, '', 4, 1),
(104, 'Juan Morales', '9983742322', 'juanmolan66@gmail.com', 5, '2025-07-26', 640.00, 'Efectivo', 4, 2);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `autores`
--
ALTER TABLE `autores`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `editoriales`
--
ALTER TABLE `editoriales`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `empleados`
--
ALTER TABLE `empleados`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- Indices de la tabla `libros`
--
ALTER TABLE `libros`
  ADD PRIMARY KEY (`id`),
  ADD KEY `editorial_id` (`editorial_id`),
  ADD KEY `autor_id` (`autor_id`),
  ADD KEY `categoria_id` (`categoria_id`);

--
-- Indices de la tabla `libros_devueltos`
--
ALTER TABLE `libros_devueltos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `prestamo_id` (`prestamo_id`),
  ADD KEY `libro_id` (`libro_id`);

--
-- Indices de la tabla `prestamos`
--
ALTER TABLE `prestamos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `empleado_id` (`empleado_id`),
  ADD KEY `fk_prestamo_libro` (`libro_ID`);

--
-- Indices de la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `libro_id` (`libro_id`);

--
-- Indices de la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `empleado_id` (`empleado_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `autores`
--
ALTER TABLE `autores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `editoriales`
--
ALTER TABLE `editoriales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `empleados`
--
ALTER TABLE `empleados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `libros`
--
ALTER TABLE `libros`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `libros_devueltos`
--
ALTER TABLE `libros_devueltos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `prestamos`
--
ALTER TABLE `prestamos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `reservas`
--
ALTER TABLE `reservas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=105;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `libros`
--
ALTER TABLE `libros`
  ADD CONSTRAINT `libros_ibfk_1` FOREIGN KEY (`editorial_id`) REFERENCES `editoriales` (`id`),
  ADD CONSTRAINT `libros_ibfk_2` FOREIGN KEY (`autor_id`) REFERENCES `autores` (`id`),
  ADD CONSTRAINT `libros_ibfk_3` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`);

--
-- Filtros para la tabla `libros_devueltos`
--
ALTER TABLE `libros_devueltos`
  ADD CONSTRAINT `libros_devueltos_ibfk_1` FOREIGN KEY (`prestamo_id`) REFERENCES `prestamos` (`id`),
  ADD CONSTRAINT `libros_devueltos_ibfk_2` FOREIGN KEY (`libro_id`) REFERENCES `libros` (`id`);

--
-- Filtros para la tabla `prestamos`
--
ALTER TABLE `prestamos`
  ADD CONSTRAINT `fk_prestamo_libro` FOREIGN KEY (`libro_ID`) REFERENCES `libros` (`id`),
  ADD CONSTRAINT `prestamos_ibfk_1` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`);

--
-- Filtros para la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD CONSTRAINT `reservas_ibfk_1` FOREIGN KEY (`libro_id`) REFERENCES `libros` (`id`);

--
-- Filtros para la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD CONSTRAINT `ventas_ibfk_1` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
