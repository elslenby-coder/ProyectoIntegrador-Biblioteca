
# Sistema de Biblioteca en Línea

**Proyecto Final**

## 1. Descripción del proyecto

El Sistema de Biblioteca en Línea es una aplicación web desarrollada para gestionar los procesos administrativos de una biblioteca.

Permite administrar usuarios, libros, préstamos, devoluciones y reservas, facilitando el control de ejemplares y la disponibilidad del material bibliográfico.

El proyecto está desarrollado con PHP y MySQL, siguiendo una arquitectura de cuatro capas que permite separar responsabilidades, organizar el código y facilitar su mantenimiento.

## 2. Objetivo general

Desarrollar un sistema web para gestionar las operaciones de una biblioteca mediante herramientas de programación, bases de datos relacionales y principios de arquitectura de software.

## 3. Funcionalidades principales

### Gestión de usuarios
- Registro y administración de usuarios.
- Consulta de información de usuarios.

### Gestión de libros
- Administración del catálogo bibliográfico.
- Consulta de disponibilidad de ejemplares.

### Préstamos
- Registro de préstamos.
- Validación de disponibilidad.
- Actualización de existencias.

### Devoluciones
- Registro de devoluciones.
- Actualización de ejemplares disponibles.
- Asignación de ejemplares a reservas pendientes.

### Reservas
- Registro de reservas.
- Administración de estados: PENDIENTE, DISPONIBLE, COMPLETADA, CANCELADA y EXPIRADA.
- Asignación automática de libros disponibles.
- Control de vencimiento de reservas.
- Procesamiento de retiros de libros reservados.

### Administración
- Inicio de sesión para administradores.
- Protección de páginas mediante sesiones PHP.
- Cierre de sesión.

## 4. Tecnologías utilizadas

| Tecnología | Uso |
|---|---|
| PHP | Desarrollo del sistema |
| MySQL | Base de datos relacional |
| XAMPP | Servidor y entorno local |
| Apache | Servidor web |
| HTML | Estructura de las interfaces |
| CSS | Presentación visual |
| Visual Studio Code | Desarrollo |
| Git y GitHub | Control de versiones |

## 5. Arquitectura del sistema

El sistema está organizado en cuatro capas:

### Dominio

Contiene las entidades del sistema y las interfaces que definen contratos para el acceso a datos.

Directorio: `Dominio/`

### Aplicación

Contiene los servicios responsables de ejecutar los procesos del sistema y coordinar las reglas de negocio.

Directorio: `Aplicacion/`

### Infraestructura

Contiene la conexión a MySQL y las implementaciones de los repositorios.

Directorio: `Infraestructura/`

### Presentación

Contiene las páginas PHP y las interfaces utilizadas por el administrador.

Directorio: `Presentacion/`

### Organización general

```text
ProyectoIntegrador-Biblioteca/
|
|-- Dominio/
|   |-- Entidades/
|   |-- Interfaces/
|
|-- Aplicacion/
|   |-- Services/
|
|-- Infraestructura/
|   |-- Database/
|   |-- Repositories/
|
|-- Presentacion/
|   |-- index.php
|   |-- login.php
|   |-- logout.php
|   |-- verificar_sesion.php
|   |-- usuarios.php
|   |-- libros.php
|   |-- prestamos.php
|   |-- devoluciones.php
|   |-- reservas.php
|
|-- ejecutar_expiracion.php
|-- README.md
```

## 6. Base de datos

El sistema utiliza una base de datos MySQL denominada:

`biblioteca_online`

Entre las tablas principales se encuentran las relacionadas con:

- Usuarios.
- Administradores.
- Libros.
- Préstamos.
- Detalles de préstamos.
- Reservas.
- Información bibliográfica complementaria.

La base de datos utiliza claves primarias y foráneas para establecer relaciones y mantener la integridad referencial.

## 7. Instalación y ejecución

### Requisitos

- XAMPP con Apache y MySQL.
- PHP compatible con el proyecto.
- Navegador web.
- Archivo SQL de instalación de la base de datos.

### Instalación

1. Instalar XAMPP.
2. Copiar el proyecto a la carpeta `htdocs` de XAMPP.
3. Iniciar Apache y MySQL desde el panel de XAMPP.
4. Abrir phpMyAdmin.
5. Crear la base de datos `biblioteca_online`.
6. Importar el archivo SQL correspondiente.
7. Configurar los parámetros de conexión en `Infraestructura/Database/Conexion.php`.
8. Configurar una cuenta de administrador mediante un procedimiento seguro.
9. Abrir el sistema desde el navegador.

### Dirección local utilizada durante el desarrollo

```text
http://localhost:8080/ProyectoIntegrador-Biblioteca/Presentacion/index.php
```

La dirección puede variar según la configuración del servidor Apache.

**Nota:** la cuenta administrativa, las credenciales de conexión y los parámetros específicos del entorno deben configurarse de manera segura. No deben publicarse contraseñas reales en el repositorio.

## 8. Seguridad e integridad de datos

El proyecto incorpora mecanismos orientados a proteger las operaciones y preservar la consistencia de la información:

- Autenticación administrativa.
- Almacenamiento de contraseñas mediante hash.
- Validación de sesiones.
- Protección de páginas administrativas.
- Transacciones de base de datos.
- Uso de rollback ante errores en operaciones transaccionales.
- Validaciones de disponibilidad de libros.
- Control de concurrencia en operaciones críticas.

Estas medidas forman parte del desarrollo y las pruebas del proyecto. No representan una auditoría de seguridad completa.

## 9. Sistema de reservas automáticas

El módulo de reservas administra los siguientes estados:

| Estado | Descripción |
|---|---|
| PENDIENTE | Reserva en espera de disponibilidad |
| DISPONIBLE | Ejemplar asignado para retiro |
| COMPLETADA | Reserva retirada y convertida en préstamo |
| CANCELADA | Reserva cancelada |
| EXPIRADA | Reserva cuyo plazo de retiro finalizó |

Cuando se devuelve un ejemplar, el sistema puede asignarlo a una reserva pendiente.

Las reservas disponibles cuentan con un plazo de retiro de 48 horas.

El procesamiento de vencimientos se realiza mediante el archivo:

`ejecutar_expiracion.php`

Durante el desarrollo se configuró una tarea programada de Windows para ejecutar periódicamente este proceso utilizando PHP desde la línea de comandos.

Para instalar el sistema en otro equipo, es necesario configurar nuevamente dicha tarea o implementar un mecanismo equivalente.

## 10. Pruebas realizadas

Durante el desarrollo se efectuaron pruebas de:

- Conexión con MySQL.
- Registro y consulta de reservas.
- Registro de préstamos y devoluciones.
- Actualización de disponibilidad.
- Cancelación y expiración de reservas.
- Asignación automática de ejemplares.
- Manejo de errores y rollback.
- Concurrencia en el retiro de reservas.
- Autenticación y cierre de sesión.

En una prueba de concurrencia, dos procesos intentaron retirar simultáneamente una misma reserva. Solo uno completó la operación y generó el préstamo correspondiente, mientras el segundo fue rechazado.

Este resultado permitió verificar el comportamiento esperado del control de concurrencia en ese escenario.

## 11. Entorno de desarrollo

El proyecto fue desarrollado y probado localmente utilizando:

- Windows.
- Visual Studio Code.
- XAMPP.
- Apache.
- PHP.
- MySQL.
- phpMyAdmin.

## 12. Consideraciones para la entrega

Antes de distribuir o publicar el proyecto se deben verificar:

- La eliminación o protección de scripts de prueba.
- La exclusión de credenciales y datos sensibles.
- La configuración de la base de datos.
- La disponibilidad del archivo SQL de instalación.
- La ejecución correcta de los módulos.
- La configuración del procesamiento automático de reservas.
- La documentación y evidencias de pruebas.

## 13. Conclusión

El Sistema de Biblioteca en Línea integra funcionalidades de administración bibliotecaria con principios de programación orientada a objetos, arquitectura por capas y gestión de bases de datos relacionales.

Su desarrollo permitió implementar operaciones transaccionales, controles de disponibilidad, mecanismos de autenticación y automatización de reservas.

El proyecto constituye una aplicación funcional desarrollada con fines académicos y una base para futuras ampliaciones.

---

**Sistema de Biblioteca en Línea — Proyecto Final**
