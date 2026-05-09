<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Serializer;

use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

/**
 * Default name converter for the iFirma IAPI endpoints.
 * Maps camelCase PHP property names to PascalCase API field names (e.g. kodPocztowy → KodPocztowy).
 * Properties whose names contain abbreviations (NIP, GTU, PKWiU …) declare
 * an explicit #[SerializedName] attribute which takes precedence over this converter.
 */
final class PascalCaseNameConverter implements NameConverterInterface
{
    public function normalize(string $propertyName): string
    {
        return ucfirst($propertyName);
    }

    public function denormalize(string $propertyName): string
    {
        return lcfirst($propertyName);
    }
}
