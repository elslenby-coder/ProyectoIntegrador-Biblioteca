
<?php

require_once __DIR__ . '/Infraestructura/Repositories/PrestamoRepository.php';
require_once __DIR__ . '/Dominio/Entidades/Prestamo.php';
require_once __DIR__ . '/Infraestructura/Database/Conexion.php';

try {
    $conexion = (new Conexion())->conectar();

    $libroId = 1;

    // Consultar stock inicial.
    $consulta = $conexion->prepare(
        "SELECT stock FROM libros WHERE id = :id"
    );
    $consulta->execute([':id' => $libroId]);
    $stockAntes = (int) $consulta->fetchColumn();

    echo "<h2>Prueba de rollback</h2>";
    echo "Stock inicial: " . $stockAntes . "<br>";

    // Utilizamos un usuario inexistente para provocar
    // un error de clave foránea después del descuento.
    $prestamo = new Prestamo(
        null,
        999999,
        $libroId,
        date('Y-m-d'),
        null,
        'PRESTADO',
        1
    );

    $repositorio = new PrestamoRepository();

    try {
        $repositorio->registrar($prestamo);

        echo "<p>ALERTA: el préstamo se registró inesperadamente.</p>";

    } catch (Throwable $e) {
        echo "<p>El préstamo fue rechazado, como esperábamos.</p>";
    }

    // Consultar stock final con una nueva consulta.
    $consulta->execute([':id' => $libroId]);
    $stockDespues = (int) $consulta->fetchColumn();

    echo "Stock final: " . $stockDespues . "<br>";

    if ($stockAntes === $stockDespues) {
        echo "<h3>PRUEBA APROBADA: el stock no cambió.</h3>";
    } else {
        echo "<h3>PRUEBA FALLIDA: el stock cambió.</h3>";
    }

} catch (Throwable $e) {
    echo "<h3>No se pudo completar la prueba.</h3>";
}
