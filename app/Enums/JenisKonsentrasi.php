<?php

namespace App\Enums;

enum JenisKonsentrasi: string
{
    case PARFUM = 'Parfum/Extrait';
    case EDP    = 'EDP';
    case EDT    = 'EDT';
    case EDC    = 'Cologne/EDC';

    public function label(): string
    {
        return $this->value;
    }

    public function rangeMin(): float
    {
        return match ($this) {
            self::PARFUM => 20,
            self::EDP    => 15,
            self::EDT    => 5,
            self::EDC    => 2,
        };
    }

    public function rangeMax(): float
    {
        return match ($this) {
            self::PARFUM => 30,
            self::EDP    => 20,
            self::EDT    => 15,
            self::EDC    => 5,
        };
    }
}
