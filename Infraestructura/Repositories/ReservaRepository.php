
<?php

require_once __DIR__ . '/../../Dominio/Interfaces/IReservaRepository.php';
require_once __DIR__ . '/../../Dominio/Entidades/Reserva.php';
require_once __DIR__ . '/../Database/Conexion.php';

class ReservaRepository implements IReservaRepository
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

    public function registrar($reserva)
    {
        if (!($reserva instanceof Reserva)) {
            throw new InvalidArgumentException(
                "Los datos de la reserva son inválidos."
            );
        }

        $usuarioId = $reserva->getIdUsuario();
        $libroId = $reserva->getIdLibro();

        try {
            $this->conexion->beginTransaction();

            $stmtLibro = $this->conexion->prepare(
                "SELECT id FROM libros
                 WHERE id = :id FOR UPDATE"
            );
            $stmtLibro->execute([':id' => $libroId]);

            if (!$stmtLibro->fetch(PDO::FETCH_ASSOC)) {
                throw new Exception("El libro no existe.");
            }

            $stmtUsuario = $this->conexion->prepare(
                "SELECT id FROM usuarios WHERE id = :id"
            );
            $stmtUsuario->execute([':id' => $usuarioId]);

            if (!$stmtUsuario->fetch(PDO::FETCH_ASSOC)) {
                throw new Exception("El usuario no existe.");
            }

            $stmtDuplicado = $this->conexion->prepare(
                "SELECT id FROM reservas
                 WHERE usuario_id = :usuario_id
                   AND libro_id = :libro_id
                   AND estado IN ('PENDIENTE', 'DISPONIBLE')
                 LIMIT 1"
            );
            $stmtDuplicado->execute([
                ':usuario_id' => $usuarioId,
                ':libro_id' => $libroId
            ]);

            if ($stmtDuplicado->fetch(PDO::FETCH_ASSOC)) {
                throw new Exception(
                    "El usuario ya tiene una reserva activa de este libro."
                );
            }

            $stmt = $this->conexion->prepare(
                "INSERT INTO reservas
                 (usuario_id, libro_id, fecha_reserva,
                  fecha_expiracion, estado)
                 VALUES
                 (:usuario_id, :libro_id, :fecha_reserva,
                  :fecha_expiracion, :estado)"
            );
            $stmt->execute([
                ':usuario_id' => $usuarioId,
                ':libro_id' => $libroId,
                ':fecha_reserva' => $reserva->getFechaReserva(),
                ':fecha_expiracion' => $reserva->getFechaExpiracion(),
                ':estado' => $reserva->getEstado()
            ]);

            $id = (int) $this->conexion->lastInsertId();
            $this->conexion->commit();

            return $id;

        } catch (Throwable $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            throw $e;
        }
    }

    public function obtenerPorId($id)
    {
        $stmt = $this->conexion->prepare(
            "SELECT * FROM reservas WHERE id = :id"
        );
        $stmt->execute([':id' => $id]);

        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        return $fila ? $this->crearReserva($fila) : null;
    }

    public function listar()
    {
        $stmt = $this->conexion->query(
            "SELECT * FROM reservas
             ORDER BY fecha_reserva ASC, id ASC"
        );

        $reservas = [];

        while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $reservas[] = $this->crearReserva($fila);
        }

        return $reservas;
    }

    public function listarPorUsuario($usuarioId)
    {
        $stmt = $this->conexion->prepare(
            "SELECT * FROM reservas
             WHERE usuario_id = :usuario_id
             ORDER BY fecha_reserva DESC, id DESC"
        );
        $stmt->execute([':usuario_id' => $usuarioId]);

        $reservas = [];

        while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $reservas[] = $this->crearReserva($fila);
        }

        return $reservas;
    }

    public function obtenerPrimeraPendiente($libroId)
    {
        $stmt = $this->conexion->prepare(
            "SELECT * FROM reservas
             WHERE libro_id = :libro_id
               AND estado = 'PENDIENTE'
             ORDER BY fecha_reserva ASC, id ASC
             LIMIT 1"
        );
        $stmt->execute([':libro_id' => $libroId]);

        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        return $fila ? $this->crearReserva($fila) : null;
    }

    public function existeReservaActiva($usuarioId, $libroId)
    {
        $stmt = $this->conexion->prepare(
            "SELECT COUNT(*) FROM reservas
             WHERE usuario_id = :usuario_id
               AND libro_id = :libro_id
               AND estado IN ('PENDIENTE', 'DISPONIBLE')"
        );
        $stmt->execute([
            ':usuario_id' => $usuarioId,
            ':libro_id' => $libroId
        ]);

        return (int) $stmt->fetchColumn() > 0;
    }

    public function actualizarEstado(
        $reservaId,
        $estado,
        $fechaExpiracion = null
    ) {
        $estadosPermitidos = [
            Reserva::PENDIENTE,
            Reserva::DISPONIBLE,
            Reserva::COMPLETADA,
            Reserva::CANCELADA,
            Reserva::EXPIRADA
        ];

        if (!in_array($estado, $estadosPermitidos, true)) {
            throw new InvalidArgumentException(
                "Estado de reserva inválido."
            );
        }

        if ($estado !== Reserva::CANCELADA) {
            throw new Exception(
                "Esta transición requiere el servicio de reservas."
            );
        }

        $stmt = $this->conexion->prepare(
            "UPDATE reservas
             SET estado = 'CANCELADA'
             WHERE id = :id
               AND estado IN ('PENDIENTE', 'DISPONIBLE')"
        );
        $stmt->execute([':id' => $reservaId]);

        return $stmt->rowCount() === 1;
    }

    public function retirarReserva($reservaId)
    {
        $reservaId = filter_var(
            $reservaId,
            FILTER_VALIDATE_INT
        );

        if ($reservaId === false || $reservaId <= 0) {
            throw new Exception("ID de reserva inválido.");
        }

        try {
            $this->conexion->beginTransaction();

            $stmtInicial = $this->conexion->prepare(
                "SELECT libro_id FROM reservas WHERE id = :id"
            );
            $stmtInicial->execute([':id' => $reservaId]);

            $libroId = $stmtInicial->fetchColumn();

            if ($libroId === false) {
                throw new Exception("La reserva no existe.");
            }

            $stmtLibro = $this->conexion->prepare(
                "SELECT stock FROM libros
                 WHERE id = :id FOR UPDATE"
            );
            $stmtLibro->execute([':id' => $libroId]);

            $libro = $stmtLibro->fetch(PDO::FETCH_ASSOC);

            if (!$libro) {
                throw new Exception("El libro no existe.");
            }

            $stmtReserva = $this->conexion->prepare(
                "SELECT id, usuario_id, libro_id,
                        estado, fecha_expiracion
                 FROM reservas
                 WHERE id = :id FOR UPDATE"
            );
            $stmtReserva->execute([':id' => $reservaId]);

            $reserva = $stmtReserva->fetch(PDO::FETCH_ASSOC);

            if (!$reserva) {
                throw new Exception("La reserva no existe.");
            }

            if ((int) $reserva['libro_id'] !== (int) $libroId) {
                throw new Exception(
                    "La reserva cambió durante la operación."
                );
            }

            if ($reserva['estado'] !== Reserva::DISPONIBLE) {
                throw new Exception(
                    "La reserva no está disponible para retiro."
                );
            }

            // Una sola aparición del parámetro nombrado.
            $stmtTiempo = $this->conexion->prepare(
                "SELECT CASE
                    WHEN :fecha_expiracion > NOW()
                    THEN 1 ELSE 0
                 END"
            );
            $stmtTiempo->execute([
                ':fecha_expiracion' =>
                    $reserva['fecha_expiracion']
            ]);

            if ((int) $stmtTiempo->fetchColumn() !== 1) {
                throw new Exception(
                    "La reserva ya venció y no puede retirarse."
                );
            }

            if ((int) $libro['stock'] < 1) {
                throw new Exception(
                    "No hay inventario para entregar esta reserva."
                );
            }

            $stmtStock = $this->conexion->prepare(
                "UPDATE libros
                 SET stock = stock - 1
                 WHERE id = :id AND stock >= 1"
            );
            $stmtStock->execute([':id' => $libroId]);

            if ($stmtStock->rowCount() !== 1) {
                throw new Exception(
                    "No se pudo descontar el inventario."
                );
            }

            $stmtPrestamo = $this->conexion->prepare(
                "INSERT INTO prestamos
                 (usuario_id, fecha_prestamo,
                  fecha_devolucion, estado)
                 VALUES (:usuario_id, CURDATE(),
                         NULL, 'PRESTADO')"
            );
            $stmtPrestamo->execute([
                ':usuario_id' => $reserva['usuario_id']
            ]);

            $prestamoId = (int) $this->conexion->lastInsertId();

            $stmtDetalle = $this->conexion->prepare(
                "INSERT INTO detalle_prestamos
                 (prestamo_id, libro_id, cantidad)
                 VALUES (:prestamo_id, :libro_id, 1)"
            );
            $stmtDetalle->execute([
                ':prestamo_id' => $prestamoId,
                ':libro_id' => $libroId
            ]);

            $stmtCompletar = $this->conexion->prepare(
                "UPDATE reservas
                 SET estado = 'COMPLETADA'
                 WHERE id = :id
                   AND estado = 'DISPONIBLE'
                   AND fecha_expiracion > NOW()"
            );
            $stmtCompletar->execute([':id' => $reservaId]);

            if ($stmtCompletar->rowCount() !== 1) {
                throw new Exception(
                    "No se pudo completar la reserva."
                );
            }

            $this->conexion->commit();

            return $prestamoId;

        } catch (Throwable $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            throw $e;
        }
    }

    // NUEVO: Expirar reservas y reasignar ejemplares.
    public function expirarReservasVencidas()
    {
        // Obtener libros con reservas vencidas.
        // Se procesan como máximo 100 libros por ejecución.
        $stmtLibros = $this->conexion->query(
            "SELECT DISTINCT libro_id
             FROM reservas
             WHERE estado = 'DISPONIBLE'
               AND fecha_expiracion <= NOW()
             ORDER BY libro_id ASC
             LIMIT 100"
        );

        $libroIds = $stmtLibros->fetchAll(
            PDO::FETCH_COLUMN
        );

        $totalExpiradas = 0;
        $totalReasignadas = 0;

        foreach ($libroIds as $libroId) {

            try {
                $this->conexion->beginTransaction();

                // Bloquear primero el libro.
                $stmtLibro = $this->conexion->prepare(
                    "SELECT stock FROM libros
                     WHERE id = :id FOR UPDATE"
                );
                $stmtLibro->execute([
                    ':id' => $libroId
                ]);

                $libro = $stmtLibro->fetch(PDO::FETCH_ASSOC);

                if (!$libro) {
                    throw new Exception(
                        "No existe el libro ID " . $libroId
                    );
                }

                // Bloquear las reservas vencidas de este libro.
                $stmtVencidas = $this->conexion->prepare(
                    "SELECT id FROM reservas
                     WHERE libro_id = :libro_id
                       AND estado = 'DISPONIBLE'
                       AND fecha_expiracion <= NOW()
                     ORDER BY id ASC
                     FOR UPDATE"
                );
                $stmtVencidas->execute([
                    ':libro_id' => $libroId
                ]);

                $idsVencidos = $stmtVencidas->fetchAll(
                    PDO::FETCH_COLUMN
                );

                $expiradasLibro = 0;

                // Expirar solamente las reservas verificadas.
                $stmtExpirar = $this->conexion->prepare(
                    "UPDATE reservas
                     SET estado = 'EXPIRADA'
                     WHERE id = :id
                       AND estado = 'DISPONIBLE'
                       AND fecha_expiracion <= NOW()"
                );

                foreach ($idsVencidos as $idVencido) {
                    $stmtExpirar->execute([
                        ':id' => $idVencido
                    ]);

                    $expiradasLibro += $stmtExpirar->rowCount();
                }

                // Contar los ejemplares que siguen apartados.
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

                // El stock ya incluye ejemplares reservados.
                // No se incrementa al expirar una reserva.
                $libres = max(
                    0,
                    (int) $libro['stock'] - $apartados
                );

                $reasignadasLibro = 0;

                if ($libres > 0 && $expiradasLibro > 0) {

                    // Seleccionar por antigüedad las pendientes.
                    $stmtPendientes = $this->conexion->prepare(
                        "SELECT id FROM reservas
                         WHERE libro_id = :libro_id
                           AND estado = 'PENDIENTE'
                         ORDER BY fecha_reserva ASC, id ASC
                         LIMIT " . (int) $libres . "
                         FOR UPDATE"
                    );
                    $stmtPendientes->execute([
                        ':libro_id' => $libroId
                    ]);

                    $idsPendientes = $stmtPendientes->fetchAll(
                        PDO::FETCH_COLUMN
                    );

                    $stmtActivar = $this->conexion->prepare(
                        "UPDATE reservas
                         SET estado = 'DISPONIBLE',
                             fecha_expiracion =
                                 DATE_ADD(NOW(), INTERVAL 48 HOUR)
                         WHERE id = :id
                           AND estado = 'PENDIENTE'"
                    );

                    foreach ($idsPendientes as $idPendiente) {
                        $stmtActivar->execute([
                            ':id' => $idPendiente
                        ]);

                        $reasignadasLibro +=
                            $stmtActivar->rowCount();
                    }
                }

                $this->conexion->commit();

                $totalExpiradas += $expiradasLibro;
                $totalReasignadas += $reasignadasLibro;

            } catch (Throwable $e) {

                if ($this->conexion->inTransaction()) {
                    $this->conexion->rollBack();
                }

                throw $e;
            }
        }

        return [
            'expiradas' => $totalExpiradas,
            'reasignadas' => $totalReasignadas
        ];
    }

    private function crearReserva($fila)
    {
        return new Reserva(
            $fila['id'],
            $fila['usuario_id'],
            $fila['libro_id'],
            $fila['fecha_reserva'],
            $fila['fecha_expiracion'],
            $fila['estado']
        );
    }
}
