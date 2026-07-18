<?php

namespace App\Enums;

enum MaterialType: string
{
    case AROMACHEMICAL = 'Aromachemical';
    case ESSENTIAL_OIL = 'Essential Oil';
    case ABSOLUTE      = 'Absolute';
    case ACCORD        = 'Accord';

    public function typeSlug(): string
    {
        return match ($this) {
            self::AROMACHEMICAL => 'aromachemical',
            self::ESSENTIAL_OIL => 'essential_oil',
            self::ABSOLUTE      => 'absolute',
            self::ACCORD        => 'accord',
        };
    }

    public static function slug(string $tipe): ?string
    {
        foreach (self::cases() as $case) {
            if ($case->value === $tipe) {
                return $case->typeSlug();
            }
        }
        return null;
    }
}
