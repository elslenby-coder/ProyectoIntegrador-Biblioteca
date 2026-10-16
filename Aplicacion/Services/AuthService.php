
<?php

require_once __DIR__ . '/../../Infraestructura/Database/Conexion.php';

class AuthService
{
    private PDO $conexion;

    public function __construct()
    {
        $database = new Conexion();
        $this->conexion = $database->conectar();
    }

    public function autenticar(string $correo, string $password): ?array
    {
        $correo = trim($correo);

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL) || $password === '') {
            return null;
        }

        $sql = "SELECT id, nombre, correo, password_hash
                FROM administradores
                WHERE correo = :correo AND activo = 1
                LIMIT 1";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([':correo' => $correo]);

        $administrador = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$administrador ||
            !password_verify($password, $administrador['password_hash'])) {
            return null;
        }

        return [
            'id' => (int) $administrador['id'],
            'nombre' => $administrador['nombre'],
            'correo' => $administrador['correo']
        ];
    }
}
