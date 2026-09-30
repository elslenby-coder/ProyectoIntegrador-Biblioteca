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
        if ($this->libroRepository->obtenerPorIsbn($isbn) !== null) {
            throw new Exception("Ya existe un libro registrado con ese ISBN.");
        }

        if ($stock < 0) {
            throw new Exception("El stock no puede ser negativo.");
        }

        $libro = new Libro(
            null,
            $titulo,
            $autor,
            $isbn,
            $anioPublicacion,
            $stock,
            $categoriaId
        );

        $libro->setTitulo($titulo);
        $libro->setStock($stock);

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