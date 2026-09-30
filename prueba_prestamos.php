<?php

require_once __DIR__ . '/Infraestructura/Repositories/PrestamoRepository.php';

try {
    $repository = new PrestamoRepository();
    $prestamos = $repository->listarTodos();

    echo "<h2>Prestamos registrados</h2>";

    if (empty($prestamos)) {
        echo "No hay prestamos registrados.";
    }

    foreach ($prestamos as $prestamo) {
        echo "ID Prestamo: " . $prestamo->getIdPrestamo() . "<br>";
        echo "ID Usuario: " . $prestamo->getIdUsuario() . "<br>";
        echo "ID Libro: " . $prestamo->getIdLibro() . "<br>";
        echo "Fecha Prestamo: " . $prestamo->getFechaPrestamo() . "<br>";

        echo "Fecha Devolucion: " .
            ($prestamo->getFechaDevolucion() ?? "Pendiente") . "<br>";

        echo "Estado: " . $prestamo->getEstado() . "<br>";
        echo "Cantidad: " . $prestamo->getCantidad() . "<br>";
        echo "<hr>";
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}