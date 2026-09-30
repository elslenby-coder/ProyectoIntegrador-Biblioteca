<?php

require_once __DIR__ . '/../../Dominio/Entidades/Prestamo.php';
require_once __DIR__ . '/../../Dominio/Interfaces/IPrestamoRepository.php';
require_once __DIR__ . '/../../Dominio/Interfaces/ILibroRepository.php';

class PrestamoService
{
    private $prestamoRepository;
    private $libroRepository;

    public function __construct(
        IPrestamoRepository $prestamoRepository,
        ILibroRepository $libroRepository
    ) {
        $this->prestamoRepository = $prestamoRepository;
        $this->libroRepository = $libroRepository;
    }

    public function registrarPrestamo(
        $usuarioId,
        $libroId,
        $fechaPrestamo,
        $cantidad = 1
    ) {
        // Regla 1: la cantidad debe ser mayor que cero
        if ($cantidad <= 0) {
            throw new Exception(
                "La cantidad debe ser mayor que cero."
            );
        }

        // Buscar el libro
        $libro = $this->libroRepository->obtenerPorId($libroId);

        // Regla 2: el libro debe existir
        if ($libro === null) {
            throw new Exception("El libro no existe.");
        }

        // Regla 3: debe existir stock disponible
        if (!$libro->estaDisponible()) {
            throw new Exception(
                "El libro no tiene existencias disponibles."
            );
        }

        // Regla 4: no prestar más de lo disponible
        if ($cantidad > $libro->getStock()) {
            throw new Exception(
                "La cantidad solicitada supera el stock disponible."
            );
        }

        // Crear préstamo
        $prestamo = new Prestamo(
            null,
            $usuarioId,
            $libroId,
            $fechaPrestamo,
            null,
            'PRESTADO',
            $cantidad
        );

        // Guardar préstamo y detalle
        $idPrestamo =
            $this->prestamoRepository->registrar($prestamo);

        // Descontar stock
        $nuevoStock =
            $libro->getStock() - $cantidad;

        $libro->setStock($nuevoStock);

        $this->libroRepository->actualizar($libro);

        return $idPrestamo;
    }

    public function obtenerPrestamo($idPrestamo)
    {
        return $this->prestamoRepository
            ->obtenerPorId($idPrestamo);
    }

    public function listarPrestamos()
    {
        return $this->prestamoRepository
            ->listarTodos();
    }

    public function listarPrestamosPorUsuario($usuarioId)
    {
        return $this->prestamoRepository
            ->listarPorUsuario($usuarioId);
    }

    public function registrarDevolucion(
        $idPrestamo,
        $fechaDevolucion
    ) {
        // Buscar préstamo
        $prestamo =
            $this->prestamoRepository
                ->obtenerPorId($idPrestamo);

        // Regla 5: el préstamo debe existir
        if ($prestamo === null) {
            throw new Exception(
                "El préstamo no existe."
            );
        }

        /*
         * Esta llamada también valida que el préstamo
         * no haya sido devuelto anteriormente.
         */
        $prestamo->registrarDevolucion(
            $fechaDevolucion
        );

        // Actualizar estado y fecha del préstamo
        $resultado =
            $this->prestamoRepository
                ->actualizar($prestamo);

        // Buscar el libro correspondiente
        $libro =
            $this->libroRepository
                ->obtenerPorId(
                    $prestamo->getIdLibro()
                );

        if ($libro === null) {
            throw new Exception(
                "No se encontró el libro asociado al préstamo."
            );
        }

        // Devolver las unidades al stock
        $nuevoStock =
            $libro->getStock()
            + $prestamo->getCantidad();

        $libro->setStock($nuevoStock);

        $this->libroRepository
            ->actualizar($libro);

        return $resultado;
    }
}