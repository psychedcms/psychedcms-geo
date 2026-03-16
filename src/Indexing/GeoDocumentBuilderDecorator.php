<?php

declare(strict_types=1);

namespace PsychedCms\Geo\Indexing;

use Doctrine\ORM\EntityManagerInterface;
use PsychedCms\Elasticsearch\Indexing\DocumentBuilder;
use PsychedCms\Elasticsearch\Indexing\EntityMetadataReader;
use PsychedCms\Geo\Attribute\GeolocationField;

final class GeoDocumentBuilderDecorator extends DocumentBuilder
{
    public function __construct(
        private readonly DocumentBuilder $decorated,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function build(object $entity, string $locale, string $defaultLocale): array
    {
        $document = $this->decorated->build($entity, $locale, $defaultLocale);

        $geolocationFields = $this->getGeolocationFields($entity::class);

        foreach ($geolocationFields as $propertyName) {
            if (!isset($document[$propertyName]) || !\is_array($document[$propertyName])) {
                continue;
            }

            $document[$propertyName] = $this->restructureGeolocation($document[$propertyName]);
        }

        return $document;
    }

    /**
     * @return list<string>
     */
    private function getGeolocationFields(string $entityClass): array
    {
        $fields = [];
        $reflectionClass = new \ReflectionClass($entityClass);

        foreach ($reflectionClass->getProperties() as $property) {
            $attributes = $property->getAttributes(GeolocationField::class);
            if ($attributes !== []) {
                $fields[] = $property->getName();
            }
        }

        return $fields;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function restructureGeolocation(array $data): array
    {
        $lat = $data['lat'] ?? null;
        $lng = $data['lng'] ?? null;

        $result = [];

        if (\is_numeric($lat) && \is_numeric($lng)) {
            $result['location'] = [
                'lat' => (float) $lat,
                'lon' => (float) $lng,
            ];
        }

        if (isset($data['address'])) {
            $result['address'] = $data['address'];
        }

        if (isset($data['city'])) {
            $result['city'] = $data['city'];
        }

        if (isset($data['country'])) {
            $result['country'] = $data['country'];
        }

        if (isset($data['zipCode'])) {
            $result['zipCode'] = $data['zipCode'];
        }

        return $result;
    }
}
