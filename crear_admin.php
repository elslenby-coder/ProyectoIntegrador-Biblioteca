
<?php

require_once __DIR__ . '/Infraestructura/Database/Conexion.php';

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Este archivo solo puede ejecutarse desde la terminal.');
}

try {
    $conexion = (new Conexion())->conectar();

    echo "=== CREAR ADMINISTRADOR ===" . PHP_EOL;

    $nombre = trim(readline("Nombre: "));
    $correo = trim(readline("Correo: "));
    $password = readline("Contrasena (minimo 12 caracteres): ");

    if ($nombre === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Nombre o correo invalido.");
    }

    if (strlen($password) < 12) {
        throw new Exception("La contrasena debe tener al menos 12 caracteres.");
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO administradores
            (nombre, correo, password_hash, activo)
            VALUES (:nombre, :correo, :password_hash, 1)";

    $stmt = $conexion->prepare($sql);

    $stmt->execute([
        ':nombre' => $nombre,
        ':correo' => $correo,
        ':password_hash' => $hash
    ]);

    echo PHP_EOL . "Administrador creado correctamente." . PHP_EOL;

} catch (Throwable $e) {
    fwrite(STDERR, "Error: " . $e->getMessage() . PHP_EOL);
    exit(1);
}
