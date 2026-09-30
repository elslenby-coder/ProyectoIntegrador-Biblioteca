<?php

require_once __DIR__ . '/../../Dominio/Interfaces/ILibroRepository.php';
require_once __DIR__ . '/../../Dominio/Entidades/Libro.php';
require_once __DIR__ . '/../Database/Conexion.php';

class LibroRepository implements ILibroRepository
{
    private $conexion;

    public function __construct()
    {
        $database = new Conexion();
        $this->conexion = $database->conectar();
    }

    public function registrar($libro)
    {
        $sql = "INSERT INTO libros
                (titulo, autor, isbn, anio_publicacion, stock, categoria_id)
                VALUES
                (:titulo, :autor, :isbn, :anio, :stock, :categoria)";

        $stmt = $this->conexion->prepare($sql);

        $stmt->execute([
            ':titulo' => $libro->getTitulo(),
            ':autor' => $libro->getAutor(),
            ':isbn' => $libro->getIsbn(),
            ':anio' => $libro->getAnioPublicacion(),
            ':stock' => $libro->getStock(),
            ':categoria' => $libro->getCategoriaId()
        ]);

        return $this->conexion->lastInsertId();
    }

    public function obtenerPorId($idLibro)
    {
        $sql = "SELECT * FROM libros WHERE id = :id";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([
            ':id' => $idLibro
        ]);

        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$fila) {
            return null;
        }

        return $this->crearLibro($fila);
    }

    public function obtenerPorIsbn($isbn)
    {
        $sql = "SELECT * FROM libros WHERE isbn = :isbn";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([
            ':isbn' => $isbn
        ]);

        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$fila) {
            return null;
        }

        return $this->crearLibro($fila);
    }

    public function listarTodos()
    {
        $sql = "SELECT * FROM libros ORDER BY titulo";

        $stmt = $this->conexion->query($sql);

        $libros = [];

        while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $libros[] = $this->crearLibro($fila);
        }

        return $libros;
    }

    public function actualizar($libro)
    {
        $sql = "UPDATE libros
                SET titulo = :titulo,
                    autor = :autor,
                    isbn = :isbn,
                    anio_publicacion = :anio,
                    stock = :stock,
                    categoria_id = :categoria
                WHERE id = :id";

        $stmt = $this->conexion->prepare($sql);

        return $stmt->execute([
            ':titulo' => $libro->getTitulo(),
            ':autor' => $libro->getAutor(),
            ':isbn' => $libro->getIsbn(),
            ':anio' => $libro->getAnioPublicacion(),
            ':stock' => $libro->getStock(),
            ':categoria' => $libro->getCategoriaId(),
            ':id' => $libro->getIdLibro()
        ]);
    }

    private function crearLibro($fila)
    {
        return new Libro(
            $fila['id'],
            $fila['titulo'],
            $fila['autor'],
            $fila['isbn'],
            $fila['anio_publicacion'],
            $fila['stock'],
            $fila['categoria_id']
        );
    }
}