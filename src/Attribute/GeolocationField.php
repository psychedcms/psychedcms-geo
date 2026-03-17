<?php

declare(strict_types=1);

namespace PsychedCms\Geo\Attribute;

use Attribute;
use PsychedCms\Core\Attribute\Field\FieldAttribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class GeolocationField extends FieldAttribute
{
    public function __construct(
        public readonly int $defaultZoom = 13,
        public readonly float $defaultLat = 48.85,
        public readonly float $defaultLng = 2.35,
        ?string $label = null,
        ?string $group = null,
        ?string $placeholder = null,
        ?string $info = null,
        ?string $prefix = null,
        ?string $postfix = null,
        bool $separator = false,
        ?string $class = null,
        mixed $default = null,
        bool $required = false,
        bool $readonly = false,
        ?string $pattern = null,
        bool $index = false,
        bool $searchable = false,
        bool $translatable = false,
        bool $sanitise = true,
        ?bool $allowHtml = null,
        ?bool $listColumn = null,
        ?int $listColumnOrder = null,
        ?string $listDisplayPattern = null,
        bool $listSortable = false,
        bool $listFilterable = false,
        ?string $listFilterType = null,
    ) {
        parent::__construct(
            label: $label,
            group: $group,
            placeholder: $placeholder,
            info: $info,
            prefix: $prefix,
            postfix: $postfix,
            separator: $separator,
            class: $class,
            default: $default,
            required: $required,
            readonly: $readonly,
            pattern: $pattern,
            index: $index,
            searchable: $searchable,
            translatable: $translatable,
            sanitise: $sanitise,
            allowHtml: $allowHtml,
            listColumn: $listColumn,
            listColumnOrder: $listColumnOrder,
            listDisplayPattern: $listDisplayPattern,
            listSortable: $listSortable,
            listFilterable: $listFilterable,
            listFilterType: $listFilterType,
        );
    }

    public function getFieldType(): string
    {
        return 'geolocation';
    }

    /**
     * @return array<string, mixed>
     */
    public function toSchemaArray(): array
    {
        $schema = parent::toSchemaArray();

        if ($this->defaultZoom !== 13) {
            $schema['defaultZoom'] = $this->defaultZoom;
        }

        if ($this->defaultLat !== 48.85) {
            $schema['defaultLat'] = $this->defaultLat;
        }

        if ($this->defaultLng !== 2.35) {
            $schema['defaultLng'] = $this->defaultLng;
        }

        return $schema;
    }
}
