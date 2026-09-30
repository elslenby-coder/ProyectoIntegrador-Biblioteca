<?php

require_once __DIR__ . '/../Infraestructura/Repositories/PrestamoRepository.php';
require_once __DIR__ . '/../Infraestructura/Repositories/LibroRepository.php';
require_once __DIR__ . '/../Infraestructura/Repositories/UsuarioRepository.php';

require_once __DIR__ . '/../Aplicacion/Services/PrestamoService.php';
require_once __DIR__ . '/../Aplicacion/Services/LibroService.php';
require_once __DIR__ . '/../Aplicacion/Services/UsuarioService.php';

$mensaje = "";
$error = "";

try {

    // Repositorios
    $prestamoRepository = new PrestamoRepository();
    $libroRepository = new LibroRepository();
    $usuarioRepository = new UsuarioRepository();

    // Servicios
    $prestamoService = new PrestamoService(
        $prestamoRepository,
        $libroRepository
    );

    $libroService = new LibroService(
        $libroRepository
    );

    $usuarioService = new UsuarioService(
        $usuarioRepository
    );

    // Registrar préstamo
    if ($_SERVER["REQUEST_METHOD"] === "POST") {

        $usuarioId = $_POST["usuario_id"] ?? "";
        $libroId = $_POST["libro_id"] ?? "";
        $cantidad = $_POST["cantidad"] ?? "";
        $fechaPrestamo = date("Y-m-d");

        if (
            $usuarioId === "" ||
            $libroId === "" ||
            $cantidad === ""
        ) {
            throw new Exception(
                "Debe seleccionar usuario, libro y cantidad."
            );
        }

        $idPrestamo = $prestamoService->registrarPrestamo(
            (int)$usuarioId,
            (int)$libroId,
            $fechaPrestamo,
            (int)$cantidad
        );

        $mensaje =
            "Préstamo registrado correctamente. ID: "
            . $idPrestamo;
    }

    // Datos necesarios para la pantalla
    $usuarios = $usuarioService->listarUsuarios();
    $libros = $libroService->listarLibros();
    $prestamos = $prestamoService->listarPrestamos();

} catch (Exception $e) {

    $error = $e->getMessage();

    try {
        $usuarios = $usuarioService->listarUsuarios();
    } catch (Exception $ex) {
        $usuarios = [];
    }

    try {
        $libros = $libroService->listarLibros();
    } catch (Exception $ex) {
        $libros = [];
    }

    try {
        $prestamos = $prestamoService->listarPrestamos();
    } catch (Exception $ex) {
        $prestamos = [];
    }
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

    <title>Préstamos - Biblioteca</title>

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
            max-width: 1100px;
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
            grid-template-columns: 1fr 1fr 1fr;
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

        select,
        input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background: white;
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
            padding: 11px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #243447;
            color: white;
        }

        .estado {
            font-weight: bold;
        }

        @media (max-width: 750px) {

            .fila {
                grid-template-columns: 1fr;
            }

            table {
                font-size: 12px;
            }
        }

    </style>

</head>

<body>

<header>

    <h1>Gestión de Préstamos</h1>

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

        <h2>Registrar préstamo</h2>

        <form method="POST">

            <div class="fila">

                <div class="campo">

                    <label>Usuario</label>

                    <select
                        name="usuario_id"
                        required
                    >

                        <option value="">
                            Seleccione un usuario
                        </option>

                        <?php foreach ($usuarios as $usuario): ?>

                            <option
                                value="<?php
                                    echo $usuario->getIdUsuario();
                                ?>"
                            >
                                <?php
                                    echo htmlspecialchars(
                                        $usuario->getNombre()
                                        . " "
                                        . $usuario->getApellido()
                                    );
                                ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <div class="campo">

                    <label>Libro</label>

                    <select
                        name="libro_id"
                        required
                    >

                        <option value="">
                            Seleccione un libro
                        </option>

                        <?php foreach ($libros as $libro): ?>

                            <option
                                value="<?php
                                    echo $libro->getIdLibro();
                                ?>"
                            >
                                <?php
                                    echo htmlspecialchars(
                                        $libro->getTitulo()
                                        . " - Stock: "
                                        . $libro->getStock()
                                    );
                                ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <div class="campo">

                    <label>Cantidad</label>

                    <input
                        type="number"
                        name="cantidad"
                        value="1"
                        min="1"
                        required
                    >

                </div>

            </div>

            <button type="submit">
                Registrar préstamo
            </button>

        </form>

    </section>

    <section class="panel">

        <h2>Préstamos registrados</h2>

        <table>

            <thead>

                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Libro</th>
                    <th>Fecha préstamo</th>
                    <th>Devolución</th>
                    <th>Estado</th>
                    <th>Cantidad</th>
                </tr>

            </thead>

            <tbody>

            <?php foreach ($prestamos as $prestamo): ?>

                <tr>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $prestamo->getIdPrestamo()
                        );
                        ?>
                    </td>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $prestamo->getIdUsuario()
                        );
                        ?>
                    </td>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $prestamo->getIdLibro()
                        );
                        ?>
                    </td>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $prestamo->getFechaPrestamo()
                        );
                        ?>
                    </td>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $prestamo->getFechaDevolucion()
                            ?? "Pendiente"
                        );
                        ?>
                    </td>

                    <td class="estado">
                        <?php
                        echo htmlspecialchars(
                            $prestamo->getEstado()
                        );
                        ?>
                    </td>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $prestamo->getCantidad()
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