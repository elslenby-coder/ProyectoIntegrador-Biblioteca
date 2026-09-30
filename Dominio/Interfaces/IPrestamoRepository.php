<?php

interface IPrestamoRepository
{
    public function registrar($prestamo);

    public function obtenerPorId($idPrestamo);

    public function listarTodos();

    public function listarPorUsuario($idUsuario);

    public function actualizar($prestamo);
}