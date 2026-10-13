
<?php

require_once __DIR__ . '/../../Dominio/Entidades/Usuario.php';
require_once __DIR__ . '/../../Dominio/Interfaces/IUsuarioRepository.php';

class UsuarioService
{
    private $usuarioRepository;

    public function __construct(IUsuarioRepository $usuarioRepository)
    {
        $this->usuarioRepository = $usuarioRepository;
    }

    public function registrarUsuario(
        $nombre,
        $apellido,
        $correo,
        $telefono = null
    ) {
        // Limpiar los datos recibidos.
        $nombre = trim((string) $nombre);
        $apellido = trim((string) $apellido);
        $correo = strtolower(trim((string) $correo));
        $telefono = trim((string) ($telefono ?? ''));

        // Validar campos obligatorios.
        if (
            $nombre === '' ||
            $apellido === '' ||
            $correo === ''
        ) {
            throw new Exception(
                "Nombre, apellido y correo son obligatorios."
            );
        }

        // Validar longitudes.
        if (mb_strlen($nombre) > 100) {
            throw new Exception(
                "El nombre no puede superar 100 caracteres."
            );
        }

        if (mb_strlen($apellido) > 100) {
            throw new Exception(
                "El apellido no puede superar 100 caracteres."
            );
        }

        if (mb_strlen($correo) > 150) {
            throw new Exception(
                "El correo no puede superar 150 caracteres."
            );
        }

        // Validar formato de correo.
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            throw new Exception(
                "Debe ingresar un correo electrónico válido."
            );
        }

        // Validar teléfono opcional.
        if ($telefono !== '') {

            if (
                mb_strlen($telefono) > 20 ||
                !preg_match('/^[0-9+\s()-]+$/', $telefono)
            ) {
                throw new Exception(
                    "El teléfono contiene caracteres no válidos."
                );
            }

        } else {
            $telefono = null;
        }

        // Comprobar correo duplicado.
        if (
            $this->usuarioRepository->obtenerPorCorreo($correo)
            !== null
        ) {
            throw new Exception(
                "Ya existe un usuario registrado con ese correo."
            );
        }

        // Crear entidad del dominio.
        $usuario = new Usuario(
            null,
            $nombre,
            $apellido,
            $correo,
            $telefono
        );

        return $this->usuarioRepository->registrar($usuario);
    }

    public function obtenerUsuario($idUsuario)
    {
        return $this->usuarioRepository->obtenerPorId($idUsuario);
    }

    public function listarUsuarios()
    {
        return $this->usuarioRepository->listarTodos();
    }

    public function actualizarUsuario($usuario)
    {
        return $this->usuarioRepository->actualizar($usuario);
    }
}
