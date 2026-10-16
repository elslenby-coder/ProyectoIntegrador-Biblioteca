
<?php

require_once __DIR__ . '/../../Dominio/Entidades/Reserva.php';
require_once __DIR__ . '/../../Dominio/Interfaces/IReservaRepository.php';

class ReservaService
{
    private $reservaRepository;

    public function __construct(
        IReservaRepository $reservaRepository
    ) {
        $this->reservaRepository = $reservaRepository;
    }

    public function registrarReserva($usuarioId, $libroId)
    {
        $usuarioId = filter_var($usuarioId, FILTER_VALIDATE_INT);
        $libroId = filter_var($libroId, FILTER_VALIDATE_INT);

        if (
            $usuarioId === false || $usuarioId <= 0 ||
            $libroId === false || $libroId <= 0
        ) {
            throw new Exception(
                "Debe seleccionar un usuario y un libro válidos."
            );
        }

        if ($this->reservaRepository->existeReservaActiva(
            $usuarioId,
            $libroId
        )) {
            throw new Exception(
                "El usuario ya tiene una reserva activa de este libro."
            );
        }

        $reserva = new Reserva(
            null,
            $usuarioId,
            $libroId,
            date('Y-m-d H:i:s'),
            null,
            Reserva::PENDIENTE
        );

        return $this->reservaRepository->registrar($reserva);
    }

    public function obtenerReserva($id)
    {
        $id = filter_var($id, FILTER_VALIDATE_INT);

        if ($id === false || $id <= 0) {
            throw new Exception("ID de reserva inválido.");
        }

        return $this->reservaRepository->obtenerPorId($id);
    }

    public function listarReservas()
    {
        return $this->reservaRepository->listar();
    }

    public function listarReservasPorUsuario($usuarioId)
    {
        $usuarioId = filter_var(
            $usuarioId,
            FILTER_VALIDATE_INT
        );

        if ($usuarioId === false || $usuarioId <= 0) {
            throw new Exception("ID de usuario inválido.");
        }

        return $this->reservaRepository->listarPorUsuario(
            $usuarioId
        );
    }

    public function obtenerPrimeraReservaPendiente($libroId)
    {
        $libroId = filter_var(
            $libroId,
            FILTER_VALIDATE_INT
        );

        if ($libroId === false || $libroId <= 0) {
            throw new Exception("ID de libro inválido.");
        }

        return $this->reservaRepository->obtenerPrimeraPendiente(
            $libroId
        );
    }

    public function cancelarReserva($reservaId)
    {
        $reserva = $this->obtenerReserva($reservaId);

        if ($reserva === null) {
            throw new Exception("La reserva no existe.");
        }

        $reserva->cancelar();

        $actualizada = $this->reservaRepository->actualizarEstado(
            $reservaId,
            Reserva::CANCELADA
        );

        if (!$actualizada) {
            throw new Exception(
                "No se pudo cancelar la reserva. Puede que su estado haya cambiado."
            );
        }

        return true;
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

        return $this->reservaRepository->retirarReserva(
            $reservaId
        );
    }

    // Procesar reservas que superaron las 48 horas.
    public function expirarReservasVencidas()
    {
        return $this->reservaRepository->expirarReservasVencidas();
    }
}
