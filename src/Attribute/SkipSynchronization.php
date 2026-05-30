<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class SkipSynchronization
{
}
