<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Enum;

enum VatRate: string
{
    case Zero = '0.00';
    case Five = '0.05';
    case Eight = '0.08';
    case TwentyThree = '0.23';
    case Exempt = 'null';

    public function toFloat(): ?float
    {
        return self::Exempt === $this ? null : (float) $this->value;
    }
}
