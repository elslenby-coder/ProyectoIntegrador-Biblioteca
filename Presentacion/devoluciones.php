<?php

require_once __DIR__ . '/../Infraestructura/Repositories/PrestamoRepository.php';
require_once __DIR__ . '/../Infraestructura/Repositories/LibroRepository.php';
require_once __DIR__ . '/../Aplicacion/Services/PrestamoService.php';

$mensaje = "";
$error = "";

try {

    // Repositorios
    $prestamoRepository = new PrestamoRepository();
    $libroRepository = new LibroRepository();

    // Servicio
    $prestamoService = new PrestamoService(
        $prestamoRepository,
        $libroRepository
    );

    // Registrar devolución
    if ($_SERVER["REQUEST_METHOD"] === "POST") {

        $idPrestamo = $_POST["prestamo_id"] ?? "";

        if ($idPrestamo === "") {
            throw new Exception(
                "Debe seleccionar un préstamo."
            );
        }

        $fechaDevolucion = date("Y-m-d");

        // Obtener préstamo antes de devolverlo
        $prestamo = $prestamoService->obtenerPrestamo(
            (int)$idPrestamo
        );

        if ($prestamo === null) {
            throw new Exception(
                "El préstamo seleccionado no existe."
            );
        }

        $libroId = $prestamo->getIdLibro();

        // Registrar devolución
        $prestamoService->registrarDevolucion(
            (int)$idPrestamo,
            $fechaDevolucion
        );

        // Consultar libro actualizado
        $libro = $libroRepository->obtenerPorId(
            $libroId
        );

        $mensaje =
            "Devolución registrada correctamente. "
            . "Préstamo ID: "
            . $idPrestamo
            . ". Stock actualizado del libro: "
            . $libro->getStock();
    }

    // Consultar todos los préstamos
    $prestamos = $prestamoService->listarPrestamos();

} catch (Exception $e) {

    $error = $e->getMessage();

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

    <title>Devoluciones - Biblioteca</title>

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

        .campo {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
        }

        select {
            width: 100%;
            max-width: 600px;
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

        .prestado {
            font-weight: bold;
        }

        .devuelto {
            font-weight: bold;
        }

        @media (max-width: 750px) {

            table {
                font-size: 12px;
            }

        }

    </style>

</head>

<body>

<header>

    <h1>Gestión de Devoluciones</h1>

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

        <h2>Registrar devolución</h2>

        <form method="POST">

            <div class="campo">

                <label>Préstamo pendiente</label>

                <select
                    name="prestamo_id"
                    required
                >

                    <option value="">
                        Seleccione un préstamo
                    </option>

                    <?php foreach ($prestamos as $prestamo): ?>

                        <?php
                        if (
                            $prestamo->getEstado()
                            === "PRESTADO"
                        ):
                        ?>

                            <option
                                value="<?php
                                    echo $prestamo
                                        ->getIdPrestamo();
                                ?>"
                            >
                                <?php

                                echo htmlspecialchars(
                                    "Préstamo ID "
                                    . $prestamo->getIdPrestamo()
                                    . " - Usuario "
                                    . $prestamo->getIdUsuario()
                                    . " - Libro "
                                    . $prestamo->getIdLibro()
                                    . " - Cantidad "
                                    . $prestamo->getCantidad()
                                );

                                ?>
                            </option>

                        <?php endif; ?>

                    <?php endforeach; ?>

                </select>

            </div>

            <button type="submit">
                Registrar devolución
            </button>

        </form>

    </section>

    <section class="panel">

        <h2>Estado de préstamos</h2>

        <table>

            <thead>

                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Libro</th>
                    <th>Fecha préstamo</th>
                    <th>Fecha devolución</th>
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

                    <td
                        class="<?php
                            echo $prestamo->getEstado()
                            === 'DEVUELTO'
                            ? 'devuelto'
                            : 'prestado';
                        ?>"
                    >
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