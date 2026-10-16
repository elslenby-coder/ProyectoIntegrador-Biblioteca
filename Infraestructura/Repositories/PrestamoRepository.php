
<?php

require_once __DIR__ . '/../../Dominio/Interfaces/IPrestamoRepository.php';
require_once __DIR__ . '/../../Dominio/Entidades/Prestamo.php';
require_once __DIR__ . '/../Database/Conexion.php';

class PrestamoRepository implements IPrestamoRepository
{
    private $conexion;

    public function __construct(?PDO $conexion = null)
    {
        if ($conexion !== null) {
            $this->conexion = $conexion;
        } else {
            $database = new Conexion();
            $this->conexion = $database->conectar();
        }
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

            // Bloquear el libro para evitar operaciones
            // simultáneas sobre su inventario.
            $stmtLibro = $this->conexion->prepare(
                "SELECT stock
                 FROM libros
                 WHERE id = :libro_id
                 FOR UPDATE"
            );

            $stmtLibro->execute([
                ':libro_id' => $libroId
            ]);

            $libro = $stmtLibro->fetch(PDO::FETCH_ASSOC);

            if (!$libro) {
                throw new Exception("El libro no existe.");
            }

            // Contar ejemplares apartados por reservas.
            $stmtReservas = $this->conexion->prepare(
                "SELECT COUNT(*)
                 FROM reservas
                 WHERE libro_id = :libro_id
                   AND estado = 'DISPONIBLE'"
            );

            $stmtReservas->execute([
                ':libro_id' => $libroId
            ]);

            $apartados = (int) $stmtReservas->fetchColumn();
            $libres = (int) $libro['stock'] - $apartados;

            if ($cantidad > $libres) {
                throw new Exception(
                    "No hay suficientes ejemplares libres. " .
                    "Algunas unidades están apartadas para reservas."
                );
            }

            // Descontar inventario.
            // Parámetros únicos para evitar SQLSTATE HY093.
            $stmtStock = $this->conexion->prepare(
                "UPDATE libros
                 SET stock = stock - :cantidad_descontar
                 WHERE id = :libro_id
                   AND stock >= :cantidad_minima"
            );

            $stmtStock->execute([
                ':cantidad_descontar' => $cantidad,
                ':libro_id' => $libroId,
                ':cantidad_minima' => $cantidad
            ]);

            if ($stmtStock->rowCount() !== 1) {
                throw new Exception(
                    "No se pudo actualizar el inventario."
                );
            }

            // Registrar préstamo.
            $stmtPrestamo = $this->conexion->prepare(
                "INSERT INTO prestamos
                 (usuario_id, fecha_prestamo,
                  fecha_devolucion, estado)
                 VALUES
                 (:usuario_id, :fecha_prestamo,
                  :fecha_devolucion, :estado)"
            );

            $stmtPrestamo->execute([
                ':usuario_id' => $prestamo->getIdUsuario(),
                ':fecha_prestamo' => $prestamo->getFechaPrestamo(),
                ':fecha_devolucion' => $prestamo->getFechaDevolucion(),
                ':estado' => $prestamo->getEstado()
            ]);

            $idPrestamo = $this->conexion->lastInsertId();

            // Registrar detalle del préstamo.
            $stmtDetalle = $this->conexion->prepare(
                "INSERT INTO detalle_prestamos
                 (prestamo_id, libro_id, cantidad)
                 VALUES
                 (:prestamo_id, :libro_id, :cantidad)"
            );

            $stmtDetalle->execute([
                ':prestamo_id' => $idPrestamo,
                ':libro_id' => $libroId,
                ':cantidad' => $cantidad
            ]);

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

        return $fila ? $this->crearPrestamo($fila) : null;
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

            // Bloquear préstamo para evitar
            // devoluciones duplicadas.
            $stmt = $this->conexion->prepare(
                "SELECT p.estado, dp.libro_id, dp.cantidad
                 FROM prestamos p
                 INNER JOIN detalle_prestamos dp
                     ON p.id = dp.prestamo_id
                 WHERE p.id = :id
                 FOR UPDATE"
            );

            $stmt->execute([
                ':id' => $idPrestamo
            ]);

            $fila = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$fila) {
                throw new Exception("El préstamo no existe.");
            }

            if ($fila['estado'] !== 'PRESTADO') {
                throw new Exception(
                    "Este préstamo ya fue devuelto o no está activo."
                );
            }

            $cantidad = (int) $fila['cantidad'];
            $libroId = (int) $fila['libro_id'];

            if ($cantidad <= 0) {
                throw new Exception(
                    "La cantidad del préstamo es inválida."
                );
            }

            // Bloquear también el libro.
            $stmtLibro = $this->conexion->prepare(
                "SELECT id
                 FROM libros
                 WHERE id = :libro_id
                 FOR UPDATE"
            );

            $stmtLibro->execute([
                ':libro_id' => $libroId
            ]);

            if (!$stmtLibro->fetch(PDO::FETCH_ASSOC)) {
                throw new Exception("El libro no existe.");
            }

            // Actualizar préstamo.
            $stmtActualizar = $this->conexion->prepare(
                "UPDATE prestamos
                 SET estado = 'DEVUELTO',
                     fecha_devolucion = :fecha
                 WHERE id = :id
                   AND estado = 'PRESTADO'"
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

            // Restaurar inventario.
            $stmtStock = $this->conexion->prepare(
                "UPDATE libros
                 SET stock = stock + :cantidad
                 WHERE id = :libro_id"
            );

            $stmtStock->execute([
                ':cantidad' => $cantidad,
                ':libro_id' => $libroId
            ]);

            if ($stmtStock->rowCount() !== 1) {
                throw new Exception(
                    "No se pudo restaurar el inventario."
                );
            }

            // Contar unidades que ya están apartadas.
            $stmtApartados = $this->conexion->prepare(
                "SELECT COUNT(*)
                 FROM reservas
                 WHERE libro_id = :libro_id
                   AND estado = 'DISPONIBLE'"
            );

            $stmtApartados->execute([
                ':libro_id' => $libroId
            ]);

            $apartados = (int) $stmtApartados->fetchColumn();

            // Consultar el stock actualizado.
            $stmtStockActual = $this->conexion->prepare(
                "SELECT stock
                 FROM libros
                 WHERE id = :libro_id"
            );

            $stmtStockActual->execute([
                ':libro_id' => $libroId
            ]);

            $stockActual = (int) $stmtStockActual->fetchColumn();

            $libres = $stockActual - $apartados;

            // Activar reservas pendientes según disponibilidad.
            // Se atienden por fecha e ID, de la más antigua
            // a la más reciente.
            if ($libres > 0) {
                $stmtPendientes = $this->conexion->prepare(
                    "SELECT id
                     FROM reservas
                     WHERE libro_id = :libro_id
                       AND estado = 'PENDIENTE'
                     ORDER BY fecha_reserva ASC, id ASC
                     LIMIT :limite
                     FOR UPDATE"
                );

                $stmtPendientes->bindValue(
                    ':libro_id',
                    $libroId,
                    PDO::PARAM_INT
                );

                $stmtPendientes->bindValue(
                    ':limite',
                    $libres,
                    PDO::PARAM_INT
                );

                $stmtPendientes->execute();

                $reservasPendientes = $stmtPendientes->fetchAll(
                    PDO::FETCH_ASSOC
                );

                $stmtActivar = $this->conexion->prepare(
                    "UPDATE reservas
                     SET estado = 'DISPONIBLE',
                         fecha_expiracion = DATE_ADD(
                             NOW(), INTERVAL 48 HOUR
                         )
                     WHERE id = :id
                       AND estado = 'PENDIENTE'"
                );

                foreach ($reservasPendientes as $reserva) {
                    $stmtActivar->execute([
                        ':id' => $reserva['id']
                    ]);

                    if ($stmtActivar->rowCount() !== 1) {
                        throw new Exception(
                            "No se pudo activar una reserva."
                        );
                    }
                }
            }

            // Confirmar devolución, inventario y reservas
            // dentro de la misma transacción.
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
