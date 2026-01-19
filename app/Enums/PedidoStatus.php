<?php

namespace App\Enums;

enum PedidoStatus: string
{
    case SOLICITADO = 'solicitado';
    case APROVADO = 'aprovado';
    case CANCELADO = 'cancelado';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::SOLICITADO => 'Solicitado',
            self::APROVADO => 'Aprovado',
            self::CANCELADO => 'Cancelado',
        };
    }
}
