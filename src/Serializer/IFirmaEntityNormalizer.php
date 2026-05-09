<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Serializer;

use function is_object;

use maciejlewandowskii\iFirmaApi\Attribute\SkipSynchronization;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

final readonly class IFirmaEntityNormalizer implements NormalizerInterface
{
    public function __construct(
        private ObjectNormalizer $objectNormalizer,
    ) {
    }

    /**
     * @param array<string, mixed> $context
     *
     * @throws ReflectionException
     * @throws ExceptionInterface
     *
     * @return array<string, mixed>
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        if (!is_object($data)) {
            return [];
        }

        $reflection = new ReflectionClass($data);
        /** @var list<string> $ignoredAttributes */
        $ignoredAttributes = $context['ignored_attributes'] ?? [];

        foreach ($reflection->getProperties() as $property) {
            if ([] !== $property->getAttributes(SkipSynchronization::class)) {
                $ignoredAttributes[] = $property->getName();
            }
        }

        $context['ignored_attributes'] = array_unique($ignoredAttributes);

        /** @var array<string, mixed> $normalized */
        $normalized = $this->objectNormalizer->normalize($data, $format, $context);

        $result = [];

        foreach ($normalized as $key => $value) {
            $pascalKey = str_replace(' ', '', ucwords(str_replace(['_', '-'], ' ', $key)));
            $result[$pascalKey] = $value;
        }

        return $result;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return is_object($data) && ($context['ifirma_sync'] ?? false) === true;
    }

    /**
     * @return array<string, bool>
     */
    public function getSupportedTypes(?string $format): array
    {
        return [
            'object' => true,
        ];
    }
}
