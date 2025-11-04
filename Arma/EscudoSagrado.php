<?php

declare(strict_types=1);
require_once 'Arma.php';
class EscudoSagrado extends Arma
{
    protected $cantManaReq;
    public function __construct(
        $nombre,
        $material = "acero",
        $fuerza = 24,
        $tipo = Tipos::Defensivo,
        $usa_mana = "S",
        $ventaja_sobre = "Dragon",
        $resistencia = 0,
        int $cantManaReq = 2

    ) {
        $this->cantManaReq = $cantManaReq;
        parent::__construct(
            nombre: $nombre,
            material: $material,
            fuerza: $fuerza,
            tipo: $tipo,
            usaMana: $usa_mana,
            ventajaSobre: $ventaja_sobre,
            resistencia: $resistencia
        );
    }
    public function getCantManaReq(): int
    {
        return $this->cantManaReq;
    }


    public function setCantManaReq(int $cantManaReq): void
    {
        $this->cantManaReq = $cantManaReq;
    }
}
