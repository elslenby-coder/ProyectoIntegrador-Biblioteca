
<?php

interface IReservaRepository
{
    public function registrar($reserva);

    public function obtenerPorId($id);

    public function listar();

    public function listarPorUsuario($usuarioId);

    public function obtenerPrimeraPendiente($libroId);

    public function existeReservaActiva($usuarioId, $libroId);

    public function actualizarEstado(
        $reservaId,
        $estado,
        $fechaExpiracion = null
    );

    public function retirarReserva($reservaId);

    public function expirarReservasVencidas();
}
