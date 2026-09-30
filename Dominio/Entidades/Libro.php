<?php

class Libro
{
    private $id;
    private $titulo;
    private $autor;
    private $isbn;
    private $anioPublicacion;
    private $stock;
    private $categoriaId;

    public function __construct(
        $id,
        $titulo,
        $autor,
        $isbn,
        $anioPublicacion,
        $stock,
        $categoriaId
    ) {
        $this->id = $id;
        $this->titulo = $titulo;
        $this->autor = $autor;
        $this->isbn = $isbn;
        $this->anioPublicacion = $anioPublicacion;
        $this->stock = $stock;
        $this->categoriaId = $categoriaId;
    }

    public function getIdLibro()
    {
        return $this->id;
    }

    public function getTitulo()
    {
        return $this->titulo;
    }

    public function getAutor()
    {
        return $this->autor;
    }

    public function getIsbn()
    {
        return $this->isbn;
    }

    public function getAnioPublicacion()
    {
        return $this->anioPublicacion;
    }

    public function getStock()
    {
        return $this->stock;
    }

    public function getCategoriaId()
    {
        return $this->categoriaId;
    }

    public function setTitulo($titulo)
    {
        if (empty(trim($titulo))) {
            throw new Exception("El título del libro es obligatorio.");
        }

        $this->titulo = $titulo;
    }

    public function setStock($stock)
    {
        if ($stock < 0) {
            throw new Exception("El stock no puede ser negativo.");
        }

        $this->stock = $stock;
    }

    public function estaDisponible()
    {
        return $this->stock > 0;
    }
}