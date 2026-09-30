<?php

require_once __DIR__ . '/Infraestructura/Repositories/LibroRepository.php';

try {
    $repository = new LibroRepository();
    $libros = $repository->listarTodos();

    echo "<h2>Libros registrados</h2>";

    foreach ($libros as $libro) {
        echo "ID: " . $libro->getIdLibro() . "<br>";
        echo "Titulo: " . $libro->getTitulo() . "<br>";
        echo "Autor: " . $libro->getAutor() . "<br>";
        echo "ISBN: " . $libro->getIsbn() . "<br>";
        echo "Año: " . $libro->getAnioPublicacion() . "<br>";
        echo "Stock: " . $libro->getStock() . "<br>";
        echo "Categoria ID: " . $libro->getCategoriaId() . "<br>";
        echo "<hr>";
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}