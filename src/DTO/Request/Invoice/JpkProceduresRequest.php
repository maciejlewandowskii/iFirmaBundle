<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\DTO\Request\Invoice;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class JpkProceduresRequest
{
    public function __construct(
        #[SerializedName('TP')]
        public ?bool $tp = null,

        #[SerializedName('IED')]
        public ?bool $ied = null,

        #[SerializedName('EE')]
        public ?bool $ee = null,

        #[SerializedName('BSPV')]
        public ?bool $bspv = null,

        #[SerializedName('BSPVDostawa')]
        public ?bool $bspvDostawa = null,

        #[SerializedName('BMPVProwizja')]
        public ?bool $bmpvProwizja = null,
    ) {
    }
}
