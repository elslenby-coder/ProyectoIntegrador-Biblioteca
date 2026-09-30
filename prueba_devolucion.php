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

    // Prestamo que vamos a devolver
    $idPrestamo = 3;
    $fechaDevolucion = date('Y-m-d');

    // Registrar devolución
    $service->registrarDevolucion(
        $idPrestamo,
        $fechaDevolucion
    );

    // Consultar nuevamente el préstamo
    $prestamo = $prestamoRepository->obtenerPorId($idPrestamo);

    // Consultar el libro para verificar el stock
    $libro = $libroRepository->obtenerPorId(
        $prestamo->getIdLibro()
    );

    echo "<h2>Devolucion registrada correctamente</h2>";

    echo "Prestamo ID: "
        . $prestamo->getIdPrestamo()
        . "<br>";

    echo "Fecha de devolucion: "
        . $prestamo->getFechaDevolucion()
        . "<br>";

    echo "Estado: "
        . $prestamo->getEstado()
        . "<br>";

    echo "Cantidad devuelta: "
        . $prestamo->getCantidad()
        . "<br><br>";

    echo "<strong>Stock despues de la devolucion: "
        . $libro->getStock()
        . "</strong>";

} catch (Exception $e) {

    echo "<h2>Error al registrar la devolucion</h2>";

    echo $e->getMessage();
}