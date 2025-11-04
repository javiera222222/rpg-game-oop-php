<?php

declare(strict_types=1);


class Dado
{
    const MIN = 1;
    const MAX  = 6;
    public $cara_visible;

    public function mostrarDado(): int
    {

        $this->cara_visible = rand(self::MIN, self::MAX);
        return $this->cara_visible;
    }
}
