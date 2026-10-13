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
        $cantidad = filter_var(
            $prestamo->getCantidad(),
            FILTER_VALIDATE_INT
        );

        $libroId = filter_var(
            $prestamo->getIdLibro(),
            FILTER_VALIDATE_INT
        );

        if (
            $cantidad === false || $cantidad <= 0 ||
            $libroId === false || $libroId <= 0
        ) {
            throw new Exception("Datos del préstamo inválidos.");
        }

        try {
            $this->conexion->beginTransaction();
            // Descontar existencias de forma atómica.
         // Evita prestar más unidades de las disponibles.
$sqlStock = "UPDATE libros
             SET stock = stock - :cantidad_descontar
             WHERE id = :libro_id
               AND stock >= :cantidad_verificar";

$stmtStock = $this->conexion->prepare($sqlStock);

$stmtStock->execute([
    ':cantidad_descontar' => $cantidad,
    ':libro_id' => $libroId,
    ':cantidad_verificar' => $cantidad
]);

            if ($stmtStock->rowCount() !== 1) {
                throw new Exception(
                    "El libro no existe o no tiene suficientes existencias."
                );
            }

            // Registrar el préstamo.
            $sql = "INSERT INTO prestamos
                    (usuario_id, fecha_prestamo,
                     fecha_devolucion, estado)
                    VALUES
                    (:usuario_id, :fecha_prestamo,
                     :fecha_devolucion, :estado)";

            $stmt = $this->conexion->prepare($sql);

            $stmt->execute([
                ':usuario_id' => $prestamo->getIdUsuario(),
                ':fecha_prestamo' => $prestamo->getFechaPrestamo(),
                ':fecha_devolucion' => $prestamo->getFechaDevolucion(),
                ':estado' => $prestamo->getEstado()
            ]);

            $idPrestamo = $this->conexion->lastInsertId();

            // Registrar detalle del préstamo.
            $sqlDetalle = "INSERT INTO detalle_prestamos
                           (prestamo_id, libro_id, cantidad)
                           VALUES
                           (:prestamo_id, :libro_id, :cantidad)";

            $stmtDetalle = $this->conexion->prepare($sqlDetalle);

            $stmtDetalle->execute([
                ':prestamo_id' => $idPrestamo,
                ':libro_id' => $libroId,
                ':cantidad' => $cantidad
            ]);

            // Confirmar las tres operaciones juntas.
            $this->conexion->commit();

            return $idPrestamo;

        } catch (Throwable $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }

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
        $stmt->execute([
            ':usuario_id' => $idUsuario
        ]);

        $prestamos = [];

        while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $prestamos[] = $this->crearPrestamo($fila);
        }

        return $prestamos;
    }

    public function actualizar($prestamo)
    {
        // Conservamos este método para compatibilidad.
        // Las devoluciones deben usar registrarDevolucion().
        if ($prestamo->getEstado() === 'DEVUELTO') {
            throw new Exception(
                "Utilice registrarDevolucion para devolver libros."
            );
        }

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

    public function registrarDevolucion(
        $idPrestamo,
        $fechaDevolucion
    ) {
        $idPrestamo = filter_var(
            $idPrestamo,
            FILTER_VALIDATE_INT
        );

        if ($idPrestamo === false || $idPrestamo <= 0) {
            throw new Exception("ID de préstamo inválido.");
        }

        try {
            $this->conexion->beginTransaction();

            // Bloquear el préstamo para evitar
            // devoluciones simultáneas.
            $sql = "SELECT p.estado, dp.libro_id, dp.cantidad
                    FROM prestamos p
                    INNER JOIN detalle_prestamos dp
                        ON p.id = dp.prestamo_id
                    WHERE p.id = :id
                    FOR UPDATE";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':id' => $idPrestamo]);

            $fila = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$fila) {
                throw new Exception(
                    "El préstamo no existe."
                );
            }

            if ($fila['estado'] !== 'PRESTADO') {
                throw new Exception(
                    "Este préstamo ya fue devuelto o no está activo."
                );
            }

            if ((int)$fila['cantidad'] <= 0) {
                throw new Exception(
                    "La cantidad del préstamo es inválida."
                );
            }

            // Actualizar el estado del préstamo.
            $sqlActualizar = "UPDATE prestamos
                              SET estado = 'DEVUELTO',
                                  fecha_devolucion = :fecha
                              WHERE id = :id
                                AND estado = 'PRESTADO'";

            $stmtActualizar = $this->conexion->prepare(
                $sqlActualizar
            );

            $stmtActualizar->execute([
                ':fecha' => $fechaDevolucion,
                ':id' => $idPrestamo
            ]);

            if ($stmtActualizar->rowCount() !== 1) {
                throw new Exception(
                    "No se pudo registrar la devolución."
                );
            }

            // Restaurar las existencias del libro.
            $sqlStock = "UPDATE libros
                         SET stock = stock + :cantidad
                         WHERE id = :libro_id";

            $stmtStock = $this->conexion->prepare($sqlStock);

            $stmtStock->execute([
                ':cantidad' => (int)$fila['cantidad'],
                ':libro_id' => (int)$fila['libro_id']
            ]);

            if ($stmtStock->rowCount() !== 1) {
                throw new Exception(
                    "No se pudo restaurar el inventario."
                );
            }

            // Confirmar devolución e inventario juntos.
            $this->conexion->commit();

            return true;

        } catch (Throwable $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }

            throw $e;
        }
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
