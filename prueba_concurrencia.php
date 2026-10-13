
<?php

// PRUEBA DE CONCURRENCIA
// Ejecutar únicamente desde la terminal de VS Code.
// Crea registros temporales en la base de datos.

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Acceso no permitido.');
}

require_once __DIR__ . '/Infraestructura/Database/Conexion.php';

$php = PHP_BINARY;
$worker = __DIR__ . '/prueba_concurrencia_worker.php';

try {
    $conexion = (new Conexion())->conectar();

    // Buscar un usuario existente para la prueba.
    $usuarioId = $conexion->query(
        "SELECT id FROM usuarios ORDER BY id LIMIT 1"
    )->fetchColumn();

    // Buscar una categoría existente.
    $categoriaId = $conexion->query(
        "SELECT id FROM categorias ORDER BY id LIMIT 1"
    )->fetchColumn();

    if (!$usuarioId || !$categoriaId) {
        throw new Exception(
            "Se necesita al menos un usuario y una categoría."
        );
    }

    // Crear un libro temporal con una sola existencia.
    $isbn = 'TEST-' . bin2hex(random_bytes(6));

    $stmt = $conexion->prepare(
        "INSERT INTO libros
         (titulo, autor, isbn, anio_publicacion, stock, categoria_id)
         VALUES
         (:titulo, :autor, :isbn, :anio, 1, :categoria)"
    );

    $stmt->execute([
        ':titulo' => 'Prueba temporal de concurrencia',
        ':autor' => 'Sistema de pruebas',
        ':isbn' => $isbn,
        ':anio' => (int) date('Y'),
        ':categoria' => $categoriaId
    ]);

    $libroId = (int) $conexion->lastInsertId();

    echo "=== PRUEBA DE CONCURRENCIA ===\n";
    echo "Libro temporal ID: {$libroId}\n";
    echo "Stock inicial: 1\n\n";

    // Ambos procesos intentarán comenzar dentro de 2 segundos.
    $inicio = microtime(true) + 2;

    $comando = escapeshellarg($php) . ' ' .
        escapeshellarg($worker) . ' ' .
        escapeshellarg((string) $usuarioId) . ' ' .
        escapeshellarg((string) $libroId) . ' ' .
        escapeshellarg((string) $inicio);

    $procesos = [];

    for ($i = 0; $i < 2; $i++) {
        $descriptores = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w']
        ];

        $proceso = proc_open(
            $comando,
            $descriptores,
            $pipes,
            __DIR__
        );

        if (!is_resource($proceso)) {
            throw new Exception(
                "No se pudo iniciar el proceso de prueba."
            );
        }

        fclose($pipes[0]);

        $procesos[] = [
            'proceso' => $proceso,
            'salida' => $pipes[1],
            'error' => $pipes[2]
        ];
    }

    // Recoger los resultados de ambos procesos.
    $exitos = 0;
    $rechazos = 0;

    foreach ($procesos as $indice => $item) {
        $salida = trim(stream_get_contents($item['salida']));
        $error = trim(stream_get_contents($item['error']));

        fclose($item['salida']);
        fclose($item['error']);

        $codigo = proc_close($item['proceso']);

        echo "Proceso " . ($indice + 1) . ": ";
        echo $salida . "\n";

        if ($error !== '') {
            echo "Error técnico: {$error}\n";
        }

        if (str_starts_with($salida, 'EXITO:')) {
            $exitos++;
        } elseif (str_starts_with($salida, 'RECHAZADO:')) {
            $rechazos++;
        }

        if ($codigo !== 0) {
            echo "Código de salida: {$codigo}\n";
        }
    }

    // Verificar el inventario y los préstamos registrados.
    $stmt = $conexion->prepare(
        "SELECT stock FROM libros WHERE id = :id"
    );
    $stmt->execute([':id' => $libroId]);
    $stockFinal = (int) $stmt->fetchColumn();

    $stmt = $conexion->prepare(
        "SELECT COUNT(*)
         FROM detalle_prestamos
         WHERE libro_id = :id"
    );
    $stmt->execute([':id' => $libroId]);
    $prestamosRegistrados = (int) $stmt->fetchColumn();

    echo "\nStock final: {$stockFinal}\n";
    echo "Préstamos registrados: {$prestamosRegistrados}\n";
    echo "Procesos exitosos: {$exitos}\n";
    echo "Procesos rechazados: {$rechazos}\n\n";

    if (
        $stockFinal === 0 &&
        $prestamosRegistrados === 1 &&
        $exitos === 1 &&
        $rechazos === 1
    ) {
        echo "PRUEBA APROBADA: no hubo sobrepréstamo.\n";
    } else {
        echo "PRUEBA NO APROBADA: revisar resultados.\n";
    }

    echo "\nIMPORTANTE: se conservaron los registros ";
    echo "temporales para revisar la evidencia.\n";
    echo "Libro de prueba ID: {$libroId}\n";

} catch (Throwable $e) {
    echo "ERROR EN LA PRUEBA: " . $e->getMessage() . "\n";
}
