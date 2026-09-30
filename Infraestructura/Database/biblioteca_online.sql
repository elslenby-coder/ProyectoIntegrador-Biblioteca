-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 30, 2026 at 10:39 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `biblioteca_online`
--

-- --------------------------------------------------------

--
-- Table structure for table `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categorias`
--

INSERT INTO `categorias` (`id`, `nombre`, `descripcion`) VALUES
(1, 'Tecnología', 'Libros de informática y tecnología'),
(2, 'Literatura', 'Novelas, cuentos y obras literarias'),
(3, 'Ciencia', 'Libros de ciencias y conocimiento científico'),
(4, 'Historia', 'Libros de historia y acontecimientos históricos');

-- --------------------------------------------------------

--
-- Table structure for table `detalle_prestamos`
--

CREATE TABLE `detalle_prestamos` (
  `id` int(11) NOT NULL,
  `prestamo_id` int(11) NOT NULL,
  `libro_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `detalle_prestamos`
--

INSERT INTO `detalle_prestamos` (`id`, `prestamo_id`, `libro_id`, `cantidad`) VALUES
(1, 1, 1, 1),
(2, 2, 1, 1),
(3, 3, 1, 1),
(4, 4, 5, 1),
(5, 5, 5, 1);

-- --------------------------------------------------------

--
-- Table structure for table `libros`
--

CREATE TABLE `libros` (
  `id` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `autor` varchar(150) NOT NULL,
  `isbn` varchar(30) DEFAULT NULL,
  `anio_publicacion` int(11) DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `categoria_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `libros`
--

INSERT INTO `libros` (`id`, `titulo`, `autor`, `isbn`, `anio_publicacion`, `stock`, `categoria_id`) VALUES
(1, 'Introducción a PHP', 'Carlos López', '978000000001', 2024, 4, 1),
(2, 'Don Quijote de la Mancha', 'Miguel de Cervantes', '978000000002', 1605, 4, 2),
(3, 'Introducción a la Ciencia', 'María González', '978000000003', 2023, 6, 3),
(4, 'Historia de Nicaragua', 'José Martínez', '978000000004', 2022, 3, 4),
(5, 'Clean code', 'Robert C. Martin', '9780132350884', 2008, 2, 1);

-- --------------------------------------------------------

--
-- Table structure for table `prestamos`
--

CREATE TABLE `prestamos` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `fecha_prestamo` date NOT NULL,
  `fecha_devolucion` date DEFAULT NULL,
  `estado` varchar(30) NOT NULL DEFAULT 'PRESTADO'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `prestamos`
--

INSERT INTO `prestamos` (`id`, `usuario_id`, `fecha_prestamo`, `fecha_devolucion`, `estado`) VALUES
(1, 1, '2026-09-28', NULL, 'PRESTADO'),
(2, 1, '2026-09-30', '2026-09-30', 'DEVUELTO'),
(3, 1, '2026-09-30', '2026-09-30', 'DEVUELTO'),
(4, 5, '2026-09-30', '2026-09-30', 'DEVUELTO'),
(5, 5, '2026-09-30', NULL, 'PRESTADO');

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `correo` varchar(150) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `apellido`, `correo`, `telefono`, `fecha_registro`) VALUES
(1, 'Ana', 'López', 'ana.lopez@gmail.com', '8888-1111', '2026-09-28 23:49:28'),
(2, 'Carlos', 'Martínez', 'carlos.martinez@gmail.com', '8888-2222', '2026-09-28 23:49:28'),
(3, 'María', 'González', 'maria.gonzalez@gmail.com', '8888-3333', '2026-09-28 23:49:28'),
(5, 'Pedro', 'Ramírez', 'pedro.ramirez@gmail.com', '8888-4444', '2026-09-30 02:47:41');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `detalle_prestamos`
--
ALTER TABLE `detalle_prestamos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_detalle_prestamo` (`prestamo_id`),
  ADD KEY `fk_detalle_libro` (`libro_id`);

--
-- Indexes for table `libros`
--
ALTER TABLE `libros`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `isbn` (`isbn`),
  ADD KEY `fk_libros_categorias` (`categoria_id`);

--
-- Indexes for table `prestamos`
--
ALTER TABLE `prestamos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_prestamos_usuarios` (`usuario_id`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `detalle_prestamos`
--
ALTER TABLE `detalle_prestamos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `libros`
--
ALTER TABLE `libros`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `prestamos`
--
ALTER TABLE `prestamos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detalle_prestamos`
--
ALTER TABLE `detalle_prestamos`
  ADD CONSTRAINT `fk_detalle_libro` FOREIGN KEY (`libro_id`) REFERENCES `libros` (`id`),
  ADD CONSTRAINT `fk_detalle_prestamo` FOREIGN KEY (`prestamo_id`) REFERENCES `prestamos` (`id`);

--
-- Constraints for table `libros`
--
ALTER TABLE `libros`
  ADD CONSTRAINT `fk_libros_categorias` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`);

--
-- Constraints for table `prestamos`
--
ALTER TABLE `prestamos`
  ADD CONSTRAINT `fk_prestamos_usuarios` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
