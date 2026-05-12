<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Enum;

enum FlatRateTax: string
{
    case ThreePercent = '0.03';
    case FivePointFivePercent = '0.055';
    case EightPointFivePercent = '0.085';
    case TenPercent = '0.10';
    case TwelvePercent = '0.12';
    case TwelvePointFivePercent = '0.125';
    case FourteenPercent = '0.14';
    case FifteenPercent = '0.15';
    case SeventeenPercent = '0.17';

    public function toFloat(): float
    {
        return (float) $this->value;
    }
}
