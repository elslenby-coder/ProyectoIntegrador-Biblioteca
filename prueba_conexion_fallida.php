
<?php

require_once __DIR__ . '/Infraestructura/Database/Conexion.php';

// Simular una conexión con credenciales incorrectas.
// No modificamos la conexión real del proyecto.
putenv('DB_USER=usuario_prueba_inexistente');
putenv('DB_PASSWORD=clave_incorrecta');

try {
    $conexion = new Conexion();
    $conexion->conectar();

    echo "<h2>Prueba fallida</h2>";
    echo "La conexión fue aceptada inesperadamente.";

} catch (Throwable $e) {

    echo "<h2>Prueba de conexión fallida</h2>";

    if (
        $e->getMessage() ===
        "No se pudo establecer conexión con la base de datos."
    ) {
        echo "<p>La conexión fue rechazada correctamente.</p>";
        echo "<h3>PRUEBA APROBADA</h3>";
        echo "<p>No se mostraron detalles técnicos de MySQL.</p>";
    } else {
        echo "<p>Se recibió un mensaje inesperado.</p>";
        echo "<h3>REVISAR PRUEBA</h3>";
    }
}
