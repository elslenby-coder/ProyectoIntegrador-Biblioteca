
<?php

// Archivo auxiliar para probar préstamos concurrentes.
// Ejecutar solamente desde la terminal.

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Acceso no permitido.');
}

require_once __DIR__ . '/Infraestructura/Repositories/PrestamoRepository.php';
require_once __DIR__ . '/Infraestructura/Repositories/LibroRepository.php';
require_once __DIR__ . '/Aplicacion/Services/PrestamoService.php';

if ($argc !== 4) {
    exit("Uso: php prueba_concurrencia_worker.php USUARIO_ID LIBRO_ID INICIO\n");
}

$usuarioId = filter_var($argv[1], FILTER_VALIDATE_INT);
$libroId = filter_var($argv[2], FILTER_VALIDATE_INT);
$inicio = filter_var($argv[3], FILTER_VALIDATE_FLOAT);

if (
    $usuarioId === false || $usuarioId <= 0 ||
    $libroId === false || $libroId <= 0 ||
    $inicio === false
) {
    exit("Argumentos inválidos.\n");
}

try {
    $prestamoRepository = new PrestamoRepository();
    $libroRepository = new LibroRepository();

    $prestamoService = new PrestamoService(
        $prestamoRepository,
        $libroRepository
    );

    // Esperar hasta el instante indicado para
    // intentar ejecutar ambas solicitudes juntas.
    while (microtime(true) < $inicio) {
        usleep(1000);
    }

    $idPrestamo = $prestamoService->registrarPrestamo(
        $usuarioId,
        $libroId,
        date('Y-m-d'),
        1
    );

    echo "EXITO: Préstamo ID {$idPrestamo}\n";

} catch (Throwable $e) {
    echo "RECHAZADO: " . $e->getMessage() . "\n";
}
