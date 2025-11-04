<?php

declare(strict_types=1);

class Jugador
{
    private $id;
    private $nombre_apellido;
    private $puntaje;

    public function __construct(int $id, string $nombre_apellido, int $puntaje)
    {
        $this->id = $id;
        $this->nombre_apellido = $nombre_apellido;
        $this->puntaje = $puntaje;
    }



    public function  getID(): int
    {
        return $this->id;
    }
    public function getNombreApellido(): string
    {

        return $this->nombre_apellido;
    }
    public function getPuntaje(): int
    {
        return $this->puntaje;
    }
    public function __toString(): string
    {
        return "El ID del jugador es: " . $this->id . "\n Su nombre es: " . $this->nombre_apellido . "\n Su puntaj es: " . $this->puntaje;
    }
}
