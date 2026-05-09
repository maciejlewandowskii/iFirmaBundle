<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Service;

use function count;

use maciejlewandowskii\iFirmaApi\Client\iFirmaClientInterface;
use maciejlewandowskii\iFirmaApi\Exception\ValidationException;
use maciejlewandowskii\iFirmaApi\Serializer\PascalCaseNameConverter;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\BackedEnumNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class AbstractService
{
    private static ?NormalizerInterface $iApiSerializer = null;

    private static ?NormalizerInterface $rawSerializer = null;

    public function __construct(
        protected readonly iFirmaClientInterface $client,
        protected readonly ValidatorInterface $validator,
    ) {
    }

    protected function validate(object $dto): void
    {
        $violations = $this->validator->validate($dto);

        if (count($violations) > 0) {
            throw new ValidationException($violations);
        }
    }

    /**
     * Serializes a DTO to a PascalCase array for the iFirma IAPI endpoints.
     * Property names are converted via PascalCaseNameConverter; properties with
     * #[SerializedName] use their declared name instead. Null values are omitted.
     *
     * @throws ExceptionInterface
     *
     * @return array<string, mixed>
     */
    protected function toArray(object $dto): array
    {
        self::$iApiSerializer ??= $this->buildSerializer(pascalCase: true);

        /** @var array<string, mixed> $result */
        $result = self::$iApiSerializer->normalize($dto, 'array', [
            AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
        ]);

        return $result;
    }

    /**
     * Serializes a DTO preserving the original camelCase property names.
     * Used for the iFirma Hub API endpoints (e.g. orders) which use camelCase.
     *
     * @throws ExceptionInterface
     *
     * @return array<string, mixed>
     */
    protected function toArrayPreservingCase(object $dto): array
    {
        self::$rawSerializer ??= $this->buildSerializer(pascalCase: false);

        /** @var array<string, mixed> $result */
        $result = self::$rawSerializer->normalize($dto, 'array', [
            AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
        ]);

        return $result;
    }

    private function buildSerializer(bool $pascalCase): NormalizerInterface
    {
        $metadataFactory = new ClassMetadataFactory(new AttributeLoader());
        $nameConverter = $pascalCase
            ? new MetadataAwareNameConverter($metadataFactory, new PascalCaseNameConverter())
            : null;

        $propertyInfoExtractor = new PropertyInfoExtractor(
            typeExtractors: [new ReflectionExtractor()],
        );

        return new Serializer([
            new BackedEnumNormalizer(),
            new ObjectNormalizer($metadataFactory, $nameConverter, null, $propertyInfoExtractor),
        ]);
    }
}
