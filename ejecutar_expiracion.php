
<?php

// Sistema de Biblioteca en Línea
// Ejecutor de expiración de reservas.
// Uso exclusivo desde la terminal.

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Acceso no permitido.');
}

require_once __DIR__ .
    '/Infraestructura/Repositories/ReservaRepository.php';

require_once __DIR__ .
    '/Aplicacion/Services/ReservaService.php';

try {
    $repositorio = new ReservaRepository();
    $servicio = new ReservaService($repositorio);

    $resultado = $servicio->expirarReservasVencidas();

    echo PHP_EOL;
    echo "=== PROCESAMIENTO DE RESERVAS ===" . PHP_EOL;
    echo "Reservas expiradas: " .
        $resultado['expiradas'] . PHP_EOL;
    echo "Reservas reasignadas: " .
        $resultado['reasignadas'] . PHP_EOL;
    echo "Proceso finalizado correctamente." . PHP_EOL;

} catch (Throwable $e) {
    fwrite(
        STDERR,
        "Error al procesar reservas: " .
        $e->getMessage() . PHP_EOL
    );

    exit(1);
}
