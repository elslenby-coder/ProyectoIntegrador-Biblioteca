
<?php
require_once __DIR__ . '/verificar_sesion.php';

// Sistema de Biblioteca en Línea
// Módulo de gestión de reservas con protección CSRF.

require_once __DIR__ . '/../Infraestructura/Repositories/ReservaRepository.php';
require_once __DIR__ . '/../Infraestructura/Repositories/LibroRepository.php';
require_once __DIR__ . '/../Infraestructura/Repositories/UsuarioRepository.php';

require_once __DIR__ . '/../Aplicacion/Services/ReservaService.php';
require_once __DIR__ . '/../Aplicacion/Services/LibroService.php';
require_once __DIR__ . '/../Aplicacion/Services/UsuarioService.php';

// Comprobar las clases necesarias.
$clasesNecesarias = [
    'ReservaRepository',
    'LibroRepository',
    'UsuarioRepository',
    'ReservaService',
    'LibroService',
    'UsuarioService'
];

foreach ($clasesNecesarias as $clase) {
    if (!class_exists($clase)) {
        error_log("No se pudo cargar la clase: " . $clase);
        http_response_code(500);
        exit(
            'Error de configuración: no se pudo cargar ' .
            htmlspecialchars($clase, ENT_QUOTES, 'UTF-8') .
            '. Revise los archivos PHP del proyecto.'
        );
    }
}

// Crear token CSRF por sesión.
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$mensaje = '';
$error = '';

$usuarios = [];
$libros = [];
$reservas = [];

try {
    // Inicializar repositorios.
    $reservaRepository = new ReservaRepository();
    $libroRepository = new LibroRepository();
    $usuarioRepository = new UsuarioRepository();

    // Inicializar servicios.
    $reservaService = new ReservaService(
        $reservaRepository
    );

    $libroService = new LibroService(
        $libroRepository
    );

    $usuarioService = new UsuarioService(
        $usuarioRepository
    );

    // Procesar formularios.
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // Validar CSRF antes de cualquier operación.
        $tokenRecibido = $_POST['csrf_token'] ?? '';

        if (
            !is_string($tokenRecibido) ||
            !hash_equals(
                $_SESSION['csrf_token'],
                $tokenRecibido
            )
        ) {
            throw new Exception(
                'Solicitud no autorizada. Token CSRF inválido.'
            );
        }

        $accion = $_POST['accion'] ?? '';

        if ($accion === 'registrar') {

            $usuarioId = $_POST['usuario_id'] ?? '';
            $libroId = $_POST['libro_id'] ?? '';

            if (
                !is_scalar($usuarioId) ||
                !is_scalar($libroId) ||
                filter_var(
                    $usuarioId,
                    FILTER_VALIDATE_INT,
                    ['options' => ['min_range' => 1]]
                ) === false ||
                filter_var(
                    $libroId,
                    FILTER_VALIDATE_INT,
                    ['options' => ['min_range' => 1]]
                ) === false
            ) {
                throw new Exception(
                    'Debe seleccionar un usuario y un libro válidos.'
                );
            }

            $idReserva = $reservaService->registrarReserva(
                (int) $usuarioId,
                (int) $libroId
            );

            $mensaje = 'Reserva registrada correctamente. ID: ' .
                       $idReserva;

        } elseif ($accion === 'cancelar') {

            $reservaId = $_POST['reserva_id'] ?? '';

            if (
                !is_scalar($reservaId) ||
                filter_var(
                    $reservaId,
                    FILTER_VALIDATE_INT,
                    ['options' => ['min_range' => 1]]
                ) === false
            ) {
                throw new Exception(
                    'Debe indicar una reserva válida para cancelar.'
                );
            }

            $reservaService->cancelarReserva(
                (int) $reservaId
            );

            $mensaje = 'Reserva cancelada correctamente.';

        } elseif ($accion === 'retirar') {

            $reservaId = $_POST['reserva_id'] ?? '';

            if (
                !is_scalar($reservaId) ||
                filter_var(
                    $reservaId,
                    FILTER_VALIDATE_INT,
                    ['options' => ['min_range' => 1]]
                ) === false
            ) {
                throw new Exception(
                    'Debe indicar una reserva válida para retirar.'
                );
            }

            $prestamoId = $reservaService->retirarReserva(
                (int) $reservaId
            );

            $mensaje = 'Libro retirado correctamente. ' .
                       'Préstamo generado con ID: ' .
                       $prestamoId .
                       '. La reserva quedó COMPLETADA.';

        } else {
            throw new Exception('Acción no válida.');
        }
    }

} catch (Throwable $e) {

    error_log(
        'Error en el módulo de reservas: ' .
        $e->getMessage()
    );

    if ($e instanceof PDOException) {
        $error = 'Error de base de datos al procesar la reserva.';
    } elseif ($e instanceof Exception) {
        $error = $e->getMessage();
    } else {
        $error = 'No se pudo completar la operación.';
    }
}

// Consultar usuarios.
if (isset($usuarioService)) {
    try {
        $usuarios = $usuarioService->listarUsuarios();
    } catch (Throwable $e) {
        error_log($e->getMessage());
        $error .= ' Error al cargar usuarios.';
    }
}

// Consultar libros.
if (isset($libroService)) {
    try {
        $libros = $libroService->listarLibros();
    } catch (Throwable $e) {
        error_log($e->getMessage());
        $error .= ' Error al cargar libros.';
    }
}

// Consultar reservas.
if (isset($reservaService)) {
    try {
        $reservas = $reservaService->listarReservas();
    } catch (Throwable $e) {
        error_log($e->getMessage());
        $error .= ' Error al cargar reservas.';
    }
}

// Escapar contenido HTML.
function escapar($valor)
{
    return htmlspecialchars(
        (string) ($valor ?? ''),
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
    <title>Gestión de Reservas - Biblioteca</title>

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

        header h1 {
            margin: 0 0 10px;
        }

        header p {
            margin: 0;
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
            box-shadow: 0 2px 8px rgba(0,0,0,.08);
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

        select {
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
            color: #256029;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .error {
            background: #ffebee;
            color: #a12a2a;
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

        th, td {
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

        .acciones {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .acciones form {
            margin: 0;
        }

        .cancelar {
            background: #9f3030;
            padding: 8px 12px;
        }

        .cancelar:hover {
            background: #7c2424;
        }

        .retirar {
            background: #237747;
            padding: 8px 12px;
        }

        .retirar:hover {
            background: #195b35;
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
    <h1>Gestión de Reservas</h1>
    <p>Sistema de Biblioteca en Línea</p>
</header>

<div class="contenedor">

    <a class="volver" href="index.php">
        ← Volver al panel principal
    </a>

    <?php if ($mensaje !== ''): ?>
        <div class="mensaje">
            <?= escapar($mensaje) ?>
        </div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="error">
            <?= escapar($error) ?>
        </div>
    <?php endif; ?>

    <section class="panel">

        <h2>Registrar reserva</h2>

        <form method="POST">

            <!-- Protección CSRF -->
            <input
                type="hidden"
                name="csrf_token"
                value="<?= escapar($_SESSION['csrf_token']) ?>"
            >

            <input
                type="hidden"
                name="accion"
                value="registrar"
            >

            <div class="fila">

                <div class="campo">

                    <label for="usuario_id">
                        Usuario
                    </label>

                    <select
                        id="usuario_id"
                        name="usuario_id"
                        required
                    >
                        <option value="">
                            Seleccione un usuario
                        </option>

                        <?php foreach ($usuarios as $usuario): ?>

                            <option value="<?= escapar(
                                $usuario->getIdUsuario()
                            ) ?>">
                                <?= escapar(
                                    $usuario->getNombre() .
                                    ' ' .
                                    $usuario->getApellido()
                                ) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <div class="campo">

                    <label for="libro_id">
                        Libro
                    </label>

                    <select
                        id="libro_id"
                        name="libro_id"
                        required
                    >
                        <option value="">
                            Seleccione un libro
                        </option>

                        <?php foreach ($libros as $libro): ?>

                            <option value="<?= escapar(
                                $libro->getIdLibro()
                            ) ?>">
                                <?= escapar(
                                    $libro->getTitulo() .
                                    ' - Stock: ' .
                                    $libro->getStock()
                                ) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

            </div>

            <button type="submit">
                Registrar reserva
            </button>

        </form>

    </section>

    <section class="panel">

        <h2>Reservas registradas</h2>

        <div class="tabla-scroll">

            <table>

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Libro</th>
                        <th>Fecha reserva</th>
                        <th>Expiración</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>

                <tbody>

                <?php foreach ($reservas as $reserva): ?>

                    <tr>

                        <td>
                            <?= escapar(
                                $reserva->getIdReserva()
                            ) ?>
                        </td>

                        <td>
                            <?= escapar(
                                $reserva->getIdUsuario()
                            ) ?>
                        </td>

                        <td>
                            <?= escapar(
                                $reserva->getIdLibro()
                            ) ?>
                        </td>

                        <td>
                            <?= escapar(
                                $reserva->getFechaReserva()
                            ) ?>
                        </td>

                        <td>
                            <?= escapar(
                                $reserva->getFechaExpiracion() ?? '—'
                            ) ?>
                        </td>

                        <td class="estado">
                            <?= escapar(
                                $reserva->getEstado()
                            ) ?>
                        </td>

                        <td>

                            <div class="acciones">

                                <?php if (
                                    $reserva->getEstado() ===
                                    Reserva::DISPONIBLE
                                ): ?>

                                    <form
                                        method="POST"
                                        onsubmit="return confirm('¿Confirmar el retiro de este libro? Se registrará un préstamo y se descontará una unidad del inventario.');"
                                    >

                                        <!-- Token CSRF -->
                                        <input
                                            type="hidden"
                                            name="csrf_token"
                                            value="<?= escapar(
                                                $_SESSION['csrf_token']
                                            ) ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="accion"
                                            value="retirar"
                                        >

                                        <input
                                            type="hidden"
                                            name="reserva_id"
                                            value="<?= escapar(
                                                $reserva->getIdReserva()
                                            ) ?>"
                                        >

                                        <button
                                            class="retirar"
                                            type="submit"
                                        >
                                            Retirar libro
                                        </button>

                                    </form>

                                <?php endif; ?>

                                <?php if (in_array(
                                    $reserva->getEstado(),
                                    [
                                        Reserva::PENDIENTE,
                                        Reserva::DISPONIBLE
                                    ],
                                    true
                                )): ?>

                                    <form
                                        method="POST"
                                        onsubmit="return confirm('¿Cancelar esta reserva?');"
                                    >

                                        <!-- Token CSRF -->
                                        <input
                                            type="hidden"
                                            name="csrf_token"
                                            value="<?= escapar(
                                                $_SESSION['csrf_token']
                                            ) ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="accion"
                                            value="cancelar"
                                        >

                                        <input
                                            type="hidden"
                                            name="reserva_id"
                                            value="<?= escapar(
                                                $reserva->getIdReserva()
                                            ) ?>"
                                        >

                                        <button
                                            class="cancelar"
                                            type="submit"
                                        >
                                            Cancelar
                                        </button>

                                    </form>

                                <?php endif; ?>

                                <?php if (!in_array(
                                    $reserva->getEstado(),
                                    [
                                        Reserva::PENDIENTE,
                                        Reserva::DISPONIBLE
                                    ],
                                    true
                                )): ?>

                                    —

                                <?php endif; ?>

                            </div>

                        </td>

                    </tr>

                <?php endforeach; ?>

                <?php if (count($reservas) === 0): ?>

                    <tr>
                        <td colspan="7">
                            No hay reservas registradas.
                        </td>
                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </section>

</div>

</body>
</html>