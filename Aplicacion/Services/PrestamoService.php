
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
        // Validar la cantidad solicitada.
        $cantidad = filter_var(
            $cantidad,
            FILTER_VALIDATE_INT
        );

        if ($cantidad === false || $cantidad <= 0) {
            throw new Exception(
                "La cantidad debe ser un número entero mayor que cero."
            );
        }

        // Verificar que el libro exista.
        $libro = $this->libroRepository->obtenerPorId($libroId);

        if ($libro === null) {
            throw new Exception("El libro no existe.");
        }

        // Comprobar la disponibilidad.
        if (!$libro->estaDisponible()) {
            throw new Exception(
                "El libro no tiene existencias disponibles."
            );
        }

        if ($cantidad > $libro->getStock()) {
            throw new Exception(
                "La cantidad solicitada supera el stock disponible."
            );
        }

        // Crear la entidad del préstamo.
        $prestamo = new Prestamo(
            null,
            $usuarioId,
            $libroId,
            $fechaPrestamo,
            null,
            'PRESTADO',
            $cantidad
        );

        // El repositorio registra el préstamo,
        // guarda su detalle y descuenta el stock
        // dentro de una sola transacción.
        return $this->prestamoRepository->registrar($prestamo);
    }

    public function obtenerPrestamo($idPrestamo)
    {
        return $this->prestamoRepository->obtenerPorId(
            $idPrestamo
        );
    }

    public function listarPrestamos()
    {
        return $this->prestamoRepository->listarTodos();
    }

    public function listarPrestamosPorUsuario($usuarioId)
    {
        return $this->prestamoRepository->listarPorUsuario(
            $usuarioId
        );
    }

    public function registrarDevolucion(
        $idPrestamo,
        $fechaDevolucion
    ) {
        // Verificar que el préstamo exista.
        $prestamo = $this->prestamoRepository->obtenerPorId(
            $idPrestamo
        );

        if ($prestamo === null) {
            throw new Exception("El préstamo no existe.");
        }

        // Aplicar las reglas de negocio de la entidad:
        // estado y validación de devolución duplicada.
        $prestamo->registrarDevolucion($fechaDevolucion);

        // El repositorio vuelve a comprobar el estado
        // con bloqueo de fila y realiza la devolución
        // junto con la restauración del inventario.
        return $this->prestamoRepository->registrarDevolucion(
            $idPrestamo,
            $fechaDevolucion
        );
    }
}
