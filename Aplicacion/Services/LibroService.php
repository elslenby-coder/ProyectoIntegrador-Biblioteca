
<?php

require_once __DIR__ . '/../../Dominio/Entidades/Libro.php';
require_once __DIR__ . '/../../Dominio/Interfaces/ILibroRepository.php';

class LibroService
{
    private $libroRepository;

    public function __construct(ILibroRepository $libroRepository)
    {
        $this->libroRepository = $libroRepository;
    }

    public function registrarLibro(
        $titulo,
        $autor,
        $isbn,
        $anioPublicacion,
        $stock,
        $categoriaId
    ) {
        // Limpiar los campos de texto.
        $titulo = trim((string) $titulo);
        $autor = trim((string) $autor);
        $isbn = trim((string) $isbn);

        // Validar campos obligatorios.
        if ($titulo === '' || $autor === '') {
            throw new Exception(
                "El título y el autor son obligatorios."
            );
        }

        // Validar longitudes según la base de datos.
        if (mb_strlen($titulo) > 150) {
            throw new Exception(
                "El título no puede superar 150 caracteres."
            );
        }

        if (mb_strlen($autor) > 150) {
            throw new Exception(
                "El autor no puede superar 150 caracteres."
            );
        }

        if (mb_strlen($isbn) > 30) {
            throw new Exception(
                "El ISBN no puede superar 30 caracteres."
            );
        }

        // Validar stock como entero no negativo.
        $stockValidado = filter_var(
            $stock,
            FILTER_VALIDATE_INT
        );

        if ($stockValidado === false || $stockValidado < 0) {
            throw new Exception(
                "El stock debe ser un número entero igual o mayor que cero."
            );
        }

        // Validar categoría.
        $categoriaValidada = filter_var(
            $categoriaId,
            FILTER_VALIDATE_INT
        );

        if (
            $categoriaValidada === false ||
            $categoriaValidada <= 0
        ) {
            throw new Exception(
                "Debe indicar una categoría válida."
            );
        }

        // Validar año de publicación cuando se proporciona.
        if ($anioPublicacion === '' || $anioPublicacion === null) {
            $anioValidado = null;
        } else {
            $anioValidado = filter_var(
                $anioPublicacion,
                FILTER_VALIDATE_INT
            );

            if (
                $anioValidado === false ||
                $anioValidado < 1 ||
                $anioValidado > (int) date('Y')
            ) {
                throw new Exception(
                    "El año de publicación no es válido."
                );
            }
        }

        // Comprobar que el ISBN no esté duplicado.
        // Un ISBN vacío se guarda como NULL.
        if ($isbn === '') {
            $isbn = null;
        } elseif (
            $this->libroRepository->obtenerPorIsbn($isbn) !== null
        ) {
            throw new Exception(
                "Ya existe un libro registrado con ese ISBN."
            );
        }

        // Crear la entidad del dominio.
        $libro = new Libro(
            null,
            $titulo,
            $autor,
            $isbn,
            $anioValidado,
            $stockValidado,
            $categoriaValidada
        );

        return $this->libroRepository->registrar($libro);
    }

    public function obtenerLibro($idLibro)
    {
        return $this->libroRepository->obtenerPorId($idLibro);
    }

    public function listarLibros()
    {
        return $this->libroRepository->listarTodos();
    }

    public function actualizarLibro($libro)
    {
        return $this->libroRepository->actualizar($libro);
    }
}
