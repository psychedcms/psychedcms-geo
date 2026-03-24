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

        $document = $this->restructureNestedGeolocations($document, $geolocationFields);

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

    /**
     * Recursively walk the document and restructure any nested geolocation objects.
     * Skips root-level fields already handled by getGeolocationFields().
     *
     * @param array<string, mixed> $data
     * @param list<string> $skipKeys Root-level keys already processed
     * @return array<string, mixed>
     */
    private function restructureNestedGeolocations(array $data, array $skipKeys = []): array
    {
        foreach ($data as $key => $value) {
            if (\in_array($key, $skipKeys, true)) {
                continue;
            }

            if (!\is_array($value)) {
                continue;
            }

            // If this array has lat/lng keys, it's a geolocation object - restructure it
            if (isset($value['lat']) && isset($value['lng'])) {
                $data[$key] = $this->restructureGeolocation($value);
                continue;
            }

            // Otherwise recurse into sub-arrays
            $data[$key] = $this->restructureNestedGeolocations($value);
        }

        return $data;
    }
}
