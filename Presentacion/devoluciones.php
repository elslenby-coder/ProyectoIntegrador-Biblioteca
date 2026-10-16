
<?php
require_once __DIR__ . '/verificar_sesion.php';
require_once __DIR__ . '/../Infraestructura/Repositories/PrestamoRepository.php';
require_once __DIR__ . '/../Infraestructura/Repositories/LibroRepository.php';
require_once __DIR__ . '/../Aplicacion/Services/PrestamoService.php';

// Generar token CSRF para la sesión administrativa.
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$mensaje = "";
$error = "";
$prestamos = [];

try {
    // Repositorios.
    $prestamoRepository = new PrestamoRepository();
    $libroRepository = new LibroRepository();

    // Servicio de aplicación.
    $prestamoService = new PrestamoService(
        $prestamoRepository,
        $libroRepository
    );

    // Registrar devolución.
    if ($_SERVER["REQUEST_METHOD"] === "POST") {

        // Verificar CSRF antes de modificar información.
        $tokenRecibido = $_POST['csrf_token'] ?? '';

        if (
            !is_string($tokenRecibido) ||
            !hash_equals(
                $_SESSION['csrf_token'],
                $tokenRecibido
            )
        ) {
            throw new Exception(
                "Solicitud no autorizada."
            );
        }

        $idPrestamo = $_POST["prestamo_id"] ?? "";

        if (
            !is_scalar($idPrestamo) ||
            filter_var(
                $idPrestamo,
                FILTER_VALIDATE_INT,
                ["options" => ["min_range" => 1]]
            ) === false
        ) {
            throw new Exception(
                "Debe seleccionar un préstamo válido."
            );
        }

        $idPrestamo = (int) $idPrestamo;
        $fechaDevolucion = date("Y-m-d");

        // Obtener préstamo antes de devolverlo.
        $prestamo = $prestamoService->obtenerPrestamo(
            $idPrestamo
        );

        if ($prestamo === null) {
            throw new Exception(
                "El préstamo seleccionado no existe."
            );
        }

        $libroId = $prestamo->getIdLibro();

        // Registrar devolución utilizando el servicio.
        // La transacción y el inventario se manejan
        // desde la capa de infraestructura.
        $prestamoService->registrarDevolucion(
            $idPrestamo,
            $fechaDevolucion
        );

        // Consultar inventario actualizado.
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

    // Consultar todos los préstamos.
    $prestamos = $prestamoService->listarPrestamos();

} catch (Throwable $e) {

    error_log(
        "Error en devoluciones.php: " . $e->getMessage()
    );

    if ($e instanceof PDOException) {
        $error = "Ocurrió un error al procesar la devolución.";
    } elseif ($e instanceof Exception) {
        $error = $e->getMessage();
    } else {
        $error = "No se pudo completar la operación.";
    }

    // Mantener visible la tabla si ocurre un error.
    if (isset($prestamoService)) {
        try {
            $prestamos = $prestamoService->listarPrestamos();
        } catch (Throwable $ex) {
            error_log(
                "Error al listar préstamos: "
                . $ex->getMessage()
            );
            $prestamos = [];
        }
    }
}

// Escapar datos antes de mostrarlos en HTML.
function escapar($valor)
{
    return htmlspecialchars(
        (string) ($valor ?? ""),
        ENT_QUOTES,
        'UTF-8'
    );
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

        .tabla-scroll {
            overflow-x: auto;
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
            <?= escapar($mensaje) ?>
        </div>
    <?php endif; ?>

    <?php if ($error !== ""): ?>
        <div class="error">
            <?= escapar($error) ?>
        </div>
    <?php endif; ?>

    <section class="panel">

        <h2>Registrar devolución</h2>

        <form method="POST">

            <!-- Protección CSRF -->
            <input
                type="hidden"
                name="csrf_token"
                value="<?= escapar($_SESSION['csrf_token']) ?>"
            >

            <div class="campo">

                <label for="prestamo_id">
                    Préstamo pendiente
                </label>

                <select
                    id="prestamo_id"
                    name="prestamo_id"
                    required
                >
                    <option value="">
                        Seleccione un préstamo
                    </option>

                    <?php foreach ($prestamos as $prestamo): ?>

                        <?php if (
                            $prestamo->getEstado() === "PRESTADO"
                        ): ?>

                            <option
                                value="<?= escapar(
                                    $prestamo->getIdPrestamo()
                                ) ?>"
                            >
                                <?= escapar(
                                    "Préstamo ID "
                                    . $prestamo->getIdPrestamo()
                                    . " - Usuario "
                                    . $prestamo->getIdUsuario()
                                    . " - Libro "
                                    . $prestamo->getIdLibro()
                                    . " - Cantidad "
                                    . $prestamo->getCantidad()
                                ) ?>
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

        <div class="tabla-scroll">

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
                                <?= escapar(
                                    $prestamo->getIdPrestamo()
                                ) ?>
                            </td>

                            <td>
                                <?= escapar(
                                    $prestamo->getIdUsuario()
                                ) ?>
                            </td>

                            <td>
                                <?= escapar(
                                    $prestamo->getIdLibro()
                                ) ?>
                            </td>

                            <td>
                                <?= escapar(
                                    $prestamo->getFechaPrestamo()
                                ) ?>
                            </td>

                            <td>
                                <?= escapar(
                                    $prestamo->getFechaDevolucion()
                                    ?? "Pendiente"
                                ) ?>
                            </td>

                            <td class="<?=
                                $prestamo->getEstado() === 'DEVUELTO'
                                    ? 'devuelto'
                                    : 'prestado'
                            ?>">
                                <?= escapar(
                                    $prestamo->getEstado()
                                ) ?>
                            </td>

                            <td>
                                <?= escapar(
                                    $prestamo->getCantidad()
                                ) ?>
                            </td>
                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    </section>

</div>

</body>
</html>