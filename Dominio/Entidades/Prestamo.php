<?php

class Prestamo
{
    private $id;
    private $usuarioId;
    private $libroId;
    private $fechaPrestamo;
    private $fechaDevolucion;
    private $estado;
    private $cantidad;

    public function __construct(
        $id,
        $usuarioId,
        $libroId,
        $fechaPrestamo,
        $fechaDevolucion = null,
        $estado = 'PRESTADO',
        $cantidad = 1
    ) {
        $this->id = $id;
        $this->usuarioId = $usuarioId;
        $this->libroId = $libroId;
        $this->fechaPrestamo = $fechaPrestamo;
        $this->fechaDevolucion = $fechaDevolucion;
        $this->estado = $estado;
        $this->cantidad = $cantidad;
    }

    public function getIdPrestamo()
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

    public function getFechaPrestamo()
    {
        return $this->fechaPrestamo;
    }

    public function getFechaDevolucion()
    {
        return $this->fechaDevolucion;
    }

    public function getEstado()
    {
        return $this->estado;
    }

    public function getCantidad()
    {
        return $this->cantidad;
    }

    public function registrarDevolucion($fechaDevolucion)
    {
        if ($this->estado === 'DEVUELTO') {
            throw new Exception("El préstamo ya fue devuelto.");
        }

        $this->fechaDevolucion = $fechaDevolucion;
        $this->estado = 'DEVUELTO';
    }
}