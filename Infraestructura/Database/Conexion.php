<?php

class Conexion
{
    private $host = "localhost";
    private $baseDatos = "biblioteca_online";
    private $usuario = "root";
    private $clave = "";

    public function conectar()
    {
        try {
            $conexion = new PDO(
                "mysql:host={$this->host};dbname={$this->baseDatos};charset=utf8mb4",
                $this->usuario,
                $this->clave
            );

            $conexion->setAttribute(
                PDO::ATTR_ERRMODE,
                PDO::ERRMODE_EXCEPTION
            );

            return $conexion;

        } catch (PDOException $e) {
            throw new Exception(
                "Error al conectar con la base de datos: " . $e->getMessage()
            );
        }
    }
}