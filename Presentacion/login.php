
<?php

ini_set('session.use_strict_mode', '1');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Aplicacion/Services/AuthService.php';

// Si ya inició sesión, enviar al panel principal.
if (isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$correo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $correo = $_POST['correo'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!is_string($correo) || !is_string($password)) {
        $error = 'Datos inválidos.';
        $correo = '';
    } else {
        $correo = trim($correo);

        if ($correo === '' || $password === '') {
            $error = 'Debes ingresar tu correo y contraseña.';
        } else {
            try {
                $auth = new AuthService();

                $administrador = $auth->autenticar(
                    $correo,
                    $password
                );

                if ($administrador !== null) {

                    session_regenerate_id(true);

                    $_SESSION['admin_id'] = $administrador['id'];
                    $_SESSION['admin_nombre'] = $administrador['nombre'];
                    $_SESSION['admin_correo'] = $administrador['correo'];

                    header('Location: index.php');
                    exit;
                }

                $error = 'Correo o contraseña incorrectos.';

            } catch (Throwable $e) {
                error_log($e->getMessage());

                $error = 'No se pudo iniciar sesión. Intenta nuevamente.';
            }
        }
    }
}

function escapar($valor)
{
    return htmlspecialchars(
        (string) $valor,
        ENT_QUOTES,
        'UTF-8'
    );
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Iniciar sesión | Biblioteca en Línea</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #f2f4f7;
            font-family: Arial, sans-serif;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            background: white;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.10);
        }

        .icono {
            text-align: center;
            font-size: 45px;
            margin-bottom: 10px;
        }

        h1 {
            text-align: center;
            color: #243449;
            font-size: 26px;
            margin-bottom: 8px;
        }

        .subtitulo {
            text-align: center;
            color: #666;
            font-size: 14px;
            margin-bottom: 30px;
        }

        label {
            display: block;
            font-weight: bold;
            color: #333;
            margin-bottom: 8px;
            margin-top: 18px;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
        }

        input:focus {
            outline: none;
            border-color: #243449;
        }

        button {
            width: 100%;
            margin-top: 25px;
            padding: 13px;
            border: none;
            border-radius: 6px;
            background: #243449;
            color: white;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background: #344d69;
        }

        .error {
            background: #fde8e8;
            color: #a51d1d;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 18px;
            font-size: 14px;
        }

        .footer {
            text-align: center;
            margin-top: 25px;
            font-size: 12px;
            color: #888;
        }
    </style>
</head>

<body>

    <main class="login-container">

        <div class="icono">📚</div>

        <h1>Biblioteca en Línea</h1>

        <p class="subtitulo">
            Sistema de Gestión de Biblioteca
            <br>
            Acceso administrativo
        </p>

        <?php if ($error !== ''): ?>
            <div class="error" role="alert">
                <?= escapar($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">

            <label for="correo">
                Correo electrónico
            </label>

            <input
                type="email"
                id="correo"
                name="correo"
                value="<?= escapar($correo) ?>"
                placeholder="Ingresa tu correo"
                autocomplete="username"
                maxlength="150"
                required
            >

            <label for="password">
                Contraseña
            </label>

            <input
                type="password"
                id="password"
                name="password"
                placeholder="Ingresa tu contraseña"
                autocomplete="current-password"
                required
            >

            <button type="submit">
                Iniciar sesión
            </button>

        </form>

        <div class="footer">
            Sistema de Biblioteca en Línea
            <br>
            Panel de Administración
        </div>

    </main>

</body>
</html>
