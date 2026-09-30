<?php

interface ILibroRepository
{
    public function registrar($libro);

    public function obtenerPorId($idLibro);

    public function obtenerPorIsbn($isbn);

    public function listarTodos();

    public function actualizar($libro);
}