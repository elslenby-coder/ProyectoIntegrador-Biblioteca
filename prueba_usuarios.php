<?php

require_once __DIR__ . '/Infraestructura/Repositories/UsuarioRepository.php';

try {
    $repository = new UsuarioRepository();
    $usuarios = $repository->listarTodos();

    echo "<h2>Usuarios registrados</h2>";

    foreach ($usuarios as $usuario) {
        echo "ID: " . $usuario->getIdUsuario() . "<br>";
        echo "Nombre: " . $usuario->getNombre() . "<br>";
        echo "Apellido: " . $usuario->getApellido() . "<br>";
        echo "Correo: " . $usuario->getCorreo() . "<br>";
        echo "Telefono: " . $usuario->getTelefono() . "<br>";
        echo "<hr>";
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}