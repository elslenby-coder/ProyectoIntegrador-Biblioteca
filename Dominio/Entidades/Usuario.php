<?php

class Usuario
{
    private $id;
    private $nombre;
    private $apellido;
    private $correo;
    private $telefono;

    public function __construct(
        $id,
        $nombre,
        $apellido,
        $correo,
        $telefono = null
    ) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->correo = $correo;
        $this->telefono = $telefono;
    }

    public function getIdUsuario()
    {
        return $this->id;
    }

    public function getNombre()
    {
        return $this->nombre;
    }

    public function getApellido()
    {
        return $this->apellido;
    }

    public function getCorreo()
    {
        return $this->correo;
    }

    public function getTelefono()
    {
        return $this->telefono;
    }

    public function setNombre($nombre)
    {
        if (empty(trim($nombre))) {
            throw new Exception("El nombre es obligatorio.");
        }

        $this->nombre = $nombre;
    }

    public function setCorreo($correo)
    {
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("El correo electrónico no es válido.");
        }

        $this->correo = $correo;
    }
}