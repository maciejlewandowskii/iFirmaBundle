<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi;

use function dirname;

use Override;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/** @api */
final class IFirmaApiBundle extends Bundle
{
    #[Override]
    public function getPath(): string
    {
        return dirname(__DIR__);
    }
}
