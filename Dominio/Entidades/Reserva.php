
<?php

class Reserva
{
    private $id;
    private $usuarioId;
    private $libroId;
    private $fechaReserva;
    private $fechaExpiracion;
    private $estado;

    const PENDIENTE = 'PENDIENTE';
    const DISPONIBLE = 'DISPONIBLE';
    const COMPLETADA = 'COMPLETADA';
    const CANCELADA = 'CANCELADA';
    const EXPIRADA = 'EXPIRADA';

    public function __construct(
        $id,
        $usuarioId,
        $libroId,
        $fechaReserva,
        $fechaExpiracion = null,
        $estado = self::PENDIENTE
    ) {
        if (filter_var($usuarioId, FILTER_VALIDATE_INT) === false ||
            (int) $usuarioId <= 0 ||
            filter_var($libroId, FILTER_VALIDATE_INT) === false ||
            (int) $libroId <= 0) {
            throw new InvalidArgumentException(
                "Los datos de la reserva son inválidos."
            );
        }

        if (!in_array($estado, [
            self::PENDIENTE,
            self::DISPONIBLE,
            self::COMPLETADA,
            self::CANCELADA,
            self::EXPIRADA
        ], true)) {
            throw new InvalidArgumentException(
                "El estado de la reserva no es válido."
            );
        }

        $this->id = $id;
        $this->usuarioId = (int) $usuarioId;
        $this->libroId = (int) $libroId;
        $this->fechaReserva = $fechaReserva;
        $this->fechaExpiracion = $fechaExpiracion;
        $this->estado = $estado;
    }

    public function getIdReserva()
    {
        return $this->id;
    }

    public function getIdUsuario()
    {
        return $this->usuarioId;
    }

    public function getIdLibro()
    {
        return $this->libroId;
    }

    public function getFechaReserva()
    {
        return $this->fechaReserva;
    }

    public function getFechaExpiracion()
    {
        return $this->fechaExpiracion;
    }

    public function getEstado()
    {
        return $this->estado;
    }

    public function cancelar()
    {
        if (
            $this->estado !== self::PENDIENTE &&
            $this->estado !== self::DISPONIBLE
        ) {
            throw new Exception(
                "Esta reserva no se puede cancelar."
            );
        }

        $this->estado = self::CANCELADA;
    }

    public function marcarDisponible($fechaExpiracion)
    {
        if ($this->estado !== self::PENDIENTE) {
            throw new Exception(
                "La reserva no está pendiente."
            );
        }

        if ($fechaExpiracion === null || $fechaExpiracion === '') {
            throw new InvalidArgumentException(
                "La fecha de expiración es obligatoria."
            );
        }

        $this->fechaExpiracion = $fechaExpiracion;
        $this->estado = self::DISPONIBLE;
    }

    public function completar()
    {
        if ($this->estado !== self::DISPONIBLE) {
            throw new Exception(
                "La reserva no está disponible para retiro."
            );
        }

        $this->estado = self::COMPLETADA;
    }

    public function expirar()
    {
        if ($this->estado !== self::DISPONIBLE) {
            throw new Exception(
                "Solo puede expirar una reserva disponible."
            );
        }

        $this->estado = self::EXPIRADA;
    }
}
