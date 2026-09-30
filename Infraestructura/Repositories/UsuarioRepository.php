<?php

require_once __DIR__ . '/../../Dominio/Interfaces/IUsuarioRepository.php';
require_once __DIR__ . '/../../Dominio/Entidades/Usuario.php';
require_once __DIR__ . '/../Database/Conexion.php';

class UsuarioRepository implements IUsuarioRepository
{
    private $conexion;

    public function __construct()
    {
        $database = new Conexion();
        $this->conexion = $database->conectar();
    }

    public function registrar($usuario)
    {
        $sql = "INSERT INTO usuarios (nombre, apellido, correo, telefono)
                VALUES (:nombre, :apellido, :correo, :telefono)";

        $stmt = $this->conexion->prepare($sql);

        $stmt->execute([
            ':nombre' => $usuario->getNombre(),
            ':apellido' => $usuario->getApellido(),
            ':correo' => $usuario->getCorreo(),
            ':telefono' => $usuario->getTelefono()
        ]);

        return $this->conexion->lastInsertId();
    }

    public function obtenerPorId($idUsuario)
    {
        $sql = "SELECT * FROM usuarios WHERE id = :id";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([':id' => $idUsuario]);

        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$fila) {
            return null;
        }

        return $this->crearUsuario($fila);
    }

    public function obtenerPorCorreo($correo)
    {
        $sql = "SELECT * FROM usuarios WHERE correo = :correo";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([':correo' => $correo]);

        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$fila) {
            return null;
        }

        return $this->crearUsuario($fila);
    }

    public function listarTodos()
    {
        $sql = "SELECT * FROM usuarios ORDER BY nombre";

        $stmt = $this->conexion->query($sql);

        $usuarios = [];

        while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $usuarios[] = $this->crearUsuario($fila);
        }

        return $usuarios;
    }

    public function actualizar($usuario)
    {
        $sql = "UPDATE usuarios
                SET nombre = :nombre,
                    apellido = :apellido,
                    correo = :correo,
                    telefono = :telefono
                WHERE id = :id";

        $stmt = $this->conexion->prepare($sql);

        return $stmt->execute([
            ':nombre' => $usuario->getNombre(),
            ':apellido' => $usuario->getApellido(),
            ':correo' => $usuario->getCorreo(),
            ':telefono' => $usuario->getTelefono(),
            ':id' => $usuario->getIdUsuario()
        ]);
    }

    private function crearUsuario($fila)
    {
        return new Usuario(
            $fila['id'],
            $fila['nombre'],
            $fila['apellido'],
            $fila['correo'],
            $fila['telefono']
        );
    }
}