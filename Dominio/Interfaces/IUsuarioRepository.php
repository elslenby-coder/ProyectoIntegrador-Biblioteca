<?php

interface IUsuarioRepository
{
    public function registrar($usuario);

    public function obtenerPorId($idUsuario);

    public function obtenerPorCorreo($correo);

    public function listarTodos();

    public function actualizar($usuario);
}