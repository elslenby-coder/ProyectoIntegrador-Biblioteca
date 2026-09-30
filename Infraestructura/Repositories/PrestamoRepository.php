<?php

require_once __DIR__ . '/../../Dominio/Interfaces/IPrestamoRepository.php';
require_once __DIR__ . '/../../Dominio/Entidades/Prestamo.php';
require_once __DIR__ . '/../Database/Conexion.php';

class PrestamoRepository implements IPrestamoRepository
{
    private $conexion;

    public function __construct()
    {
        $database = new Conexion();
        $this->conexion = $database->conectar();
    }

    public function registrar($prestamo)
    {
        $this->conexion->beginTransaction();

        try {
            $sql = "INSERT INTO prestamos
                    (usuario_id, fecha_prestamo, fecha_devolucion, estado)
                    VALUES
                    (:usuario_id, :fecha_prestamo, :fecha_devolucion, :estado)";

            $stmt = $this->conexion->prepare($sql);

            $stmt->execute([
                ':usuario_id' => $prestamo->getIdUsuario(),
                ':fecha_prestamo' => $prestamo->getFechaPrestamo(),
                ':fecha_devolucion' => $prestamo->getFechaDevolucion(),
                ':estado' => $prestamo->getEstado()
            ]);

            $idPrestamo = $this->conexion->lastInsertId();

            $sqlDetalle = "INSERT INTO detalle_prestamos
                           (prestamo_id, libro_id, cantidad)
                           VALUES
                           (:prestamo_id, :libro_id, :cantidad)";

            $stmtDetalle = $this->conexion->prepare($sqlDetalle);

            $stmtDetalle->execute([
                ':prestamo_id' => $idPrestamo,
                ':libro_id' => $prestamo->getIdLibro(),
                ':cantidad' => $prestamo->getCantidad()
            ]);

            $this->conexion->commit();

            return $idPrestamo;

        } catch (Exception $e) {
            $this->conexion->rollBack();
            throw $e;
        }
    }

    public function obtenerPorId($idPrestamo)
    {
        $sql = "SELECT p.*, dp.libro_id, dp.cantidad
                FROM prestamos p
                INNER JOIN detalle_prestamos dp
                    ON p.id = dp.prestamo_id
                WHERE p.id = :id";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([':id' => $idPrestamo]);

        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$fila) {
            return null;
        }

        return $this->crearPrestamo($fila);
    }

    public function listarTodos()
    {
        $sql = "SELECT p.*, dp.libro_id, dp.cantidad
                FROM prestamos p
                INNER JOIN detalle_prestamos dp
                    ON p.id = dp.prestamo_id
                ORDER BY p.id DESC";

        $stmt = $this->conexion->query($sql);

        $prestamos = [];

        while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $prestamos[] = $this->crearPrestamo($fila);
        }

        return $prestamos;
    }

    public function listarPorUsuario($idUsuario)
    {
        $sql = "SELECT p.*, dp.libro_id, dp.cantidad
                FROM prestamos p
                INNER JOIN detalle_prestamos dp
                    ON p.id = dp.prestamo_id
                WHERE p.usuario_id = :usuario_id
                ORDER BY p.id DESC";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([':usuario_id' => $idUsuario]);

        $prestamos = [];

        while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $prestamos[] = $this->crearPrestamo($fila);
        }

        return $prestamos;
    }

    public function actualizar($prestamo)
    {
        $sql = "UPDATE prestamos
                SET fecha_devolucion = :fecha_devolucion,
                    estado = :estado
                WHERE id = :id";

        $stmt = $this->conexion->prepare($sql);

        return $stmt->execute([
            ':fecha_devolucion' => $prestamo->getFechaDevolucion(),
            ':estado' => $prestamo->getEstado(),
            ':id' => $prestamo->getIdPrestamo()
        ]);
    }

    private function crearPrestamo($fila)
    {
        return new Prestamo(
            $fila['id'],
            $fila['usuario_id'],
            $fila['libro_id'],
            $fila['fecha_prestamo'],
            $fila['fecha_devolucion'],
            $fila['estado'],
            $fila['cantidad']
        );
    }
}