<?php

declare(strict_types=1);
require_once "Personaje.php";
class DragonOscuro extends Dragon
{
    protected $elemento, $mana;
    public function __construct(
        $nombre,
        string $elemento = "Oscuridad",
        int $mana = 25,
        $desplazamiento = 2,
        $ataque = 25,
        $vida = 50,
        $habilidades = ["Invocación Oscura", "Fuego Infernal Mortal", "Magna Demoniaco"],
        $debilidades = ["Magia", "Elfo"]
    ) {
        $this->elemento = $elemento;
        $this->mana = $mana;
        parent::__construct(
            nombre: $nombre,
            vida: $vida,
            ataque: $ataque,
            desplazamiento: $desplazamiento,
            debilidades: $debilidades,
            habilidades: $habilidades
        );
    }
}
