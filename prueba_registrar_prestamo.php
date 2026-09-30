<?php

require_once __DIR__ . '/Infraestructura/Repositories/PrestamoRepository.php';
require_once __DIR__ . '/Infraestructura/Repositories/LibroRepository.php';
require_once __DIR__ . '/Aplicacion/Services/PrestamoService.php';

try {
    $prestamoRepository = new PrestamoRepository();
    $libroRepository = new LibroRepository();

    $service = new PrestamoService(
        $prestamoRepository,
        $libroRepository
    );

    $idPrestamo = $service->registrarPrestamo(
        1,                  // Usuario Ana
        1,                  // Libro Introducción a PHP
        date('Y-m-d'),
        1                   // Cantidad
    );

    echo "<h2>Prestamo registrado correctamente</h2>";
    echo "ID del nuevo prestamo: " . $idPrestamo . "<br>";
    echo "Usuario ID: 1<br>";
    echo "Libro ID: 1<br>";
    echo "Cantidad: 1<br>";

    $libro = $libroRepository->obtenerPorId(1);

    echo "<br><strong>Stock restante: "
        . $libro->getStock()
        . "</strong>";

} catch (Exception $e) {
    echo "<h2>Error al registrar el prestamo</h2>";
    echo $e->getMessage();
}