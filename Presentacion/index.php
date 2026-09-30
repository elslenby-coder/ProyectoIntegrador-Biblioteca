<?php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Biblioteca en Línea</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            color: #333;
        }

        header {
            background: #243447;
            color: white;
            padding: 25px;
            text-align: center;
        }

        header h1 {
            margin-bottom: 8px;
        }

        .contenedor {
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
        }

        .bienvenida {
            background: white;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .bienvenida h2 {
            margin-bottom: 10px;
        }

        .menu {
            display: grid;
            grid-template-columns: repeat(
                auto-fit,
                minmax(220px, 1fr)
            );
            gap: 20px;
        }

        .tarjeta {
            background: white;
            padding: 30px 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .tarjeta h3 {
            margin-bottom: 10px;
        }

        .tarjeta p {
            margin-bottom: 20px;
            color: #666;
            min-height: 40px;
        }

        .boton {
            display: inline-block;
            padding: 10px 20px;
            background: #243447;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .boton:hover {
            background: #34495e;
        }

        footer {
            margin-top: 50px;
            text-align: center;
            color: #777;
            padding: 20px;
        }
    </style>
</head>

<body>

<header>
    <h1>Sistema de Biblioteca en Línea</h1>
    <p>Gestión de usuarios, libros y préstamos</p>
</header>

<div class="contenedor">

    <section class="bienvenida">
        <h2>Panel principal</h2>

        <p>
            Bienvenido al sistema de gestión de biblioteca.
            Seleccione uno de los módulos para comenzar.
        </p>
    </section>

    <section class="menu">

        <div class="tarjeta">
            <h3>Usuarios</h3>

            <p>
                Registro y consulta de usuarios
                de la biblioteca.
            </p>

            <a class="boton" href="usuarios.php">
                Gestionar usuarios
            </a>
        </div>

        <div class="tarjeta">
            <h3>Libros</h3>

            <p>
                Registro, consulta y control
                de disponibilidad de libros.
            </p>

            <a class="boton" href="libros.php">
                Gestionar libros
            </a>
        </div>

        <div class="tarjeta">
            <h3>Préstamos</h3>

            <p>
                Registro y consulta de préstamos
                realizados.
            </p>

            <a class="boton" href="prestamos.php">
                Gestionar préstamos
            </a>
        </div>

        <div class="tarjeta">
            <h3>Devoluciones</h3>

            <p>
                Registro de devoluciones
                y actualización de existencias.
            </p>

            <a class="boton" href="devoluciones.php">
                Registrar devolución
            </a>
        </div>

    </section>

</div>

<footer>
    Sistema de Biblioteca en Línea
</footer>

</body>
</html>