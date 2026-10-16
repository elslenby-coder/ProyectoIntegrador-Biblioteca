
<?php
require_once __DIR__ . '/verificar_sesion.php';
require_once __DIR__ . '/../Infraestructura/Repositories/UsuarioRepository.php';
require_once __DIR__ . '/../Aplicacion/Services/UsuarioService.php';

// Generar token CSRF asociado a la sesión administrativa.
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$mensaje = "";
$error = "";
$usuarios = [];

function escapar($valor)
{
    return htmlspecialchars(
        (string) ($valor ?? ""),
        ENT_QUOTES,
        'UTF-8'
    );
}

try {
    $usuarioRepository = new UsuarioRepository();
    $usuarioService = new UsuarioService($usuarioRepository);

    if ($_SERVER["REQUEST_METHOD"] === "POST") {

        // Verificar token CSRF antes de realizar operaciones.
        $tokenRecibido = $_POST['csrf_token'] ?? '';

        if (
            !is_string($tokenRecibido) ||
            !hash_equals($_SESSION['csrf_token'], $tokenRecibido)
        ) {
            throw new Exception("Solicitud no autorizada.");
        }

        $nombre = trim((string) ($_POST["nombre"] ?? ""));
        $apellido = trim((string) ($_POST["apellido"] ?? ""));
        $correo = trim((string) ($_POST["correo"] ?? ""));
        $telefono = trim((string) ($_POST["telefono"] ?? ""));

        // El servicio valida los datos antes de guardarlos.
        $usuarioService->registrarUsuario(
            $nombre,
            $apellido,
            $correo,
            $telefono
        );

        $mensaje = "Usuario registrado correctamente.";
    }

    $usuarios = $usuarioService->listarUsuarios();

} catch (Throwable $e) {

    // Registrar detalles técnicos en el log de PHP.
    error_log("Error en usuarios.php: " . $e->getMessage());

    if ($e instanceof PDOException) {

        if ((string) $e->getCode() === "23000") {
            $error = "No se pudo registrar el usuario. "
                   . "Verifique que el correo no esté duplicado.";
        } else {
            $error = "Ocurrió un error al guardar el usuario.";
        }

    } elseif (
        $e instanceof Exception &&
        get_class($e) === 'Exception' &&
        $e->getPrevious() === null &&
        in_array($e->getMessage(), [
            "Nombre, apellido y correo son obligatorios.",
            "El nombre no puede superar 100 caracteres.",
            "El apellido no puede superar 100 caracteres.",
            "El correo no puede superar 150 caracteres.",
            "Debe ingresar un correo electrónico válido.",
            "El teléfono contiene caracteres no válidos.",
            "Ya existe un usuario registrado con ese correo.",
            "Solicitud no autorizada."
        ], true)
    ) {

        $error = $e->getMessage();

    } else {
        $error = "No se pudo completar la operación.";
    }

    // Mantener la tabla visible cuando falle el registro.
    if (isset($usuarioService)) {
        try {
            $usuarios = $usuarioService->listarUsuarios();
        } catch (Throwable $ex) {
            error_log(
                "Error al consultar usuarios: " . $ex->getMessage()
            );
            $usuarios = [];
        }
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

            table {
                font-size: 12px;
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
                <?= escapar($mensaje) ?>
            </div>
        <?php endif; ?>

        <?php if ($error !== ""): ?>
            <div class="error">
                <?= escapar($error) ?>
            </div>
        <?php endif; ?>

        <section class="panel">

            <h2>Registrar usuario</h2>

            <form method="POST">

                <!-- Protección CSRF -->
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= escapar($_SESSION['csrf_token']) ?>"
                >

                <div class="fila">

                    <div class="campo">
                        <label>Nombre</label>
                        <input
                            type="text"
                            name="nombre"
                            maxlength="100"
                            required
                        >
                    </div>

                    <div class="campo">
                        <label>Apellido</label>
                        <input
                            type="text"
                            name="apellido"
                            maxlength="100"
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
                            maxlength="150"
                            required
                        >
                    </div>

                    <div class="campo">
                        <label>Teléfono</label>
                        <input
                            type="tel"
                            name="telefono"
                            maxlength="20"
                            placeholder="Ej. 8888-8888"
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
                            <td><?= escapar($usuario->getIdUsuario()) ?></td>
                            <td><?= escapar($usuario->getNombre()) ?></td>
                            <td><?= escapar($usuario->getApellido()) ?></td>
                            <td><?= escapar($usuario->getCorreo()) ?></td>
                            <td><?= escapar($usuario->getTelefono()) ?></td>
                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </section>

    </div>

</body>
</html>