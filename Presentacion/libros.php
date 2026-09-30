<?php

require_once __DIR__ . '/../Infraestructura/Repositories/LibroRepository.php';
require_once __DIR__ . '/../Aplicacion/Services/LibroService.php';

$mensaje = "";
$error = "";

try {

    $libroRepository = new LibroRepository();
    $libroService = new LibroService($libroRepository);

    // Registrar libro
    if ($_SERVER["REQUEST_METHOD"] === "POST") {

        $titulo = trim($_POST["titulo"] ?? "");
        $autor = trim($_POST["autor"] ?? "");
        $isbn = trim($_POST["isbn"] ?? "");
        $anioPublicacion = trim($_POST["anio_publicacion"] ?? "");
        $stock = trim($_POST["stock"] ?? "");
        $categoriaId = trim($_POST["categoria_id"] ?? "");

        if (
            $titulo === "" ||
            $autor === "" ||
            $stock === "" ||
            $categoriaId === ""
        ) {
            throw new Exception(
                "Título, autor, stock y categoría son obligatorios."
            );
        }

        if (!is_numeric($stock) || $stock < 0) {
            throw new Exception(
                "El stock debe ser un número igual o mayor que cero."
            );
        }

        $libroService->registrarLibro(
            $titulo,
            $autor,
            $isbn,
            $anioPublicacion,
            $stock,
            $categoriaId
        );

        $mensaje = "Libro registrado correctamente.";
    }

    // Consultar libros
    $libros = $libroService->listarLibros();

} catch (Exception $e) {

    $error = $e->getMessage();

    try {
        $libros = $libroService->listarLibros();
    } catch (Exception $ex) {
        $libros = [];
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

    <title>Libros - Biblioteca</title>

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
            padding: 11px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #243447;
            color: white;
        }

        .disponible {
            font-weight: bold;
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

    <h1>Gestión de Libros</h1>

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

        <h2>Registrar libro</h2>

        <form method="POST">

            <div class="fila">

                <div class="campo">

                    <label>Título</label>

                    <input
                        type="text"
                        name="titulo"
                        required
                    >

                </div>

                <div class="campo">

                    <label>Autor</label>

                    <input
                        type="text"
                        name="autor"
                        required
                    >

                </div>

            </div>

            <div class="fila">

                <div class="campo">

                    <label>ISBN</label>

                    <input
                        type="text"
                        name="isbn"
                    >

                </div>

                <div class="campo">

                    <label>Año de publicación</label>

                    <input
                        type="number"
                        name="anio_publicacion"
                        min="0"
                    >

                </div>

            </div>

            <div class="fila">

                <div class="campo">

                    <label>Stock</label>

                    <input
                        type="number"
                        name="stock"
                        min="0"
                        required
                    >

                </div>

                <div class="campo">

                    <label>ID de categoría</label>

                    <input
                        type="number"
                        name="categoria_id"
                        min="1"
                        required
                    >

                </div>

            </div>

            <button type="submit">
                Registrar libro
            </button>

        </form>

    </section>

    <section class="panel">

        <h2>Libros registrados</h2>

        <table>

            <thead>

                <tr>
                    <th>ID</th>
                    <th>Título</th>
                    <th>Autor</th>
                    <th>ISBN</th>
                    <th>Año</th>
                    <th>Stock</th>
                    <th>Categoría</th>
                </tr>

            </thead>

            <tbody>

            <?php foreach ($libros as $libro): ?>

                <tr>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $libro->getIdLibro()
                        );
                        ?>
                    </td>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $libro->getTitulo()
                        );
                        ?>
                    </td>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $libro->getAutor()
                        );
                        ?>
                    </td>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $libro->getIsbn()
                        );
                        ?>
                    </td>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $libro->getAnioPublicacion()
                        );
                        ?>
                    </td>

                    <td class="disponible">
                        <?php
                        echo htmlspecialchars(
                            $libro->getStock()
                        );
                        ?>
                    </td>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $libro->getCategoriaId()
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