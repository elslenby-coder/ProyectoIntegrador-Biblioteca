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

    public function registrarUsuario($nombre, $apellido, $correo, $telefono = null)
    {
        if ($this->usuarioRepository->obtenerPorCorreo($correo) !== null) {
            throw new Exception("Ya existe un usuario registrado con ese correo.");
        }

        $usuario = new Usuario(
            null,
            $nombre,
            $apellido,
            $correo,
            $telefono
        );

        $usuario->setNombre($nombre);
        $usuario->setCorreo($correo);

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