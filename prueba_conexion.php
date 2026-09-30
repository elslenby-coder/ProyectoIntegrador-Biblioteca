<?php

require_once __DIR__ . '/Infraestructura/Database/Conexion.php';

try {
    $database = new Conexion();
    $conexion = $database->conectar();

    echo "CONEXION EXITOSA A biblioteca_online";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}