<?php

require_once __DIR__ . '/../Infraestructura/Repositories/UsuarioRepository.php';
require_once __DIR__ . '/../Aplicacion/Services/UsuarioService.php';

$mensaje = "";
$error = "";

try {
    $usuarioRepository = new UsuarioRepository();
    $usuarioService = new UsuarioService($usuarioRepository);

    // Registrar usuario
    if ($_SERVER["REQUEST_METHOD"] === "POST") {

        $nombre = trim($_POST["nombre"] ?? "");
        $apellido = trim($_POST["apellido"] ?? "");
        $correo = trim($_POST["correo"] ?? "");
        $telefono = trim($_POST["telefono"] ?? "");

        if (
            $nombre === "" ||
            $apellido === "" ||
            $correo === ""
        ) {
            throw new Exception(
                "Nombre, apellido y correo son obligatorios."
            );
        }

        $usuarioService->registrarUsuario(
            $nombre,
            $apellido,
            $correo,
            $telefono
        );

        $mensaje = "Usuario registrado correctamente.";
    }

    // Consultar usuarios
    $usuarios = $usuarioService->listarUsuarios();

} catch (Exception $e) {
    $error = $e->getMessage();
    $usuarios = [];
}

?>
<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Usuarios - Biblioteca</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            color: #333;
        }

        header {
            background: #243447;
            color: white;
            padding: 22px;
            text-align: center;
        }

        .contenedor {
            max-width: 1000px;
            margin: 30px auto;
            padding: 20px;
        }

        .volver {
            display: inline-block;
            margin-bottom: 20px;
            color: #243447;
            text-decoration: none;
            font-weight: bold;
        }

        .panel {
            background: white;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        h2 {
            margin-top: 0;
        }

        .fila {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .campo {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            background: #243447;
            color: white;
            border: none;
            padding: 11px 22px;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background: #34495e;
        }

        .mensaje {
            background: #e8f5e9;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .error {
            background: #ffebee;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #243447;
            color: white;
        }

        @media (max-width: 700px) {

            .fila {
                grid-template-columns: 1fr;
            }

        }

    </style>

</head>

<body>

<header>

    <h1>Gestión de Usuarios</h1>

    <p>Sistema de Biblioteca en Línea</p>

</header>

<div class="contenedor">

    <a class="volver" href="index.php">
        ← Volver al panel principal
    </a>

    <?php if ($mensaje !== ""): ?>

        <div class="mensaje">
            <?php echo htmlspecialchars($mensaje); ?>
        </div>

    <?php endif; ?>

    <?php if ($error !== ""): ?>

        <div class="error">
            <?php echo htmlspecialchars($error); ?>
        </div>

    <?php endif; ?>

    <section class="panel">

        <h2>Registrar usuario</h2>

        <form method="POST">

            <div class="fila">

                <div class="campo">

                    <label>Nombre</label>

                    <input
                        type="text"
                        name="nombre"
                        required
                    >

                </div>

                <div class="campo">

                    <label>Apellido</label>

                    <input
                        type="text"
                        name="apellido"
                        required
                    >

                </div>

            </div>

            <div class="fila">

                <div class="campo">

                    <label>Correo electrónico</label>

                    <input
                        type="email"
                        name="correo"
                        required
                    >

                </div>

                <div class="campo">

                    <label>Teléfono</label>

                    <input
                        type="text"
                        name="telefono"
                    >

                </div>

            </div>

            <button type="submit">
                Registrar usuario
            </button>

        </form>

    </section>

    <section class="panel">

        <h2>Usuarios registrados</h2>

        <table>

            <thead>

                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Correo</th>
                    <th>Teléfono</th>
                </tr>

            </thead>

            <tbody>

            <?php foreach ($usuarios as $usuario): ?>

                <tr>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $usuario->getIdUsuario()
                        );
                        ?>
                    </td>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $usuario->getNombre()
                        );
                        ?>
                    </td>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $usuario->getApellido()
                        );
                        ?>
                    </td>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $usuario->getCorreo()
                        );
                        ?>
                    </td>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $usuario->getTelefono()
                        );
                        ?>
                    </td>

                </tr>

            <?php endforeach; ?>

            </tbody>

        </table>

    </section>

</div>

</body>

</html>