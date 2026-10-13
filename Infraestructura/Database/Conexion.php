<?php
class Conexion
{
    public function conectar()
    {
        // Archivo privado fuera de htdocs.
        $archivoEnv = 'C:/xampp/config_privada/biblioteca.env';

        if (!is_readable($archivoEnv)) {
            error_log("No se puede leer la configuración de la biblioteca.");
            throw new Exception("No se pudo establecer conexión con la base de datos.");
        }

        $config = parse_ini_file($archivoEnv, false, INI_SCANNER_RAW);

        if ($config === false) {
            throw new Exception("No se pudo cargar la configuración de la base de datos.");
        }

        // Las variables de entorno tienen prioridad si están definidas.
        $host = getenv('DB_HOST') ?: ($config['DB_HOST'] ?? '');
        $baseDatos = getenv('DB_NAME') ?: ($config['DB_NAME'] ?? '');
        $usuario = getenv('DB_USER') ?: ($config['DB_USER'] ?? '');

        $claveEntorno = getenv('DB_PASSWORD');
        $clave = ($claveEntorno !== false)
            ? $claveEntorno
            : ($config['DB_PASSWORD'] ?? '');

        if ($host === '' || $baseDatos === '' || $usuario === '' || $clave === '') {
            throw new Exception("La configuración de la base de datos está incompleta.");
        }

        try {
            return new PDO(
                "mysql:host={$host};dbname={$baseDatos};charset=utf8mb4",
                $usuario,
                $clave,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );

        } catch (PDOException $e) {
            // Los detalles técnicos se guardan en el log.
            error_log("Error de conexión a MySQL: " . $e->getMessage());

            // El usuario solo recibe un mensaje general.
            throw new Exception(
                "No se pudo establecer conexión con la base de datos."
            );
        }
    }
}