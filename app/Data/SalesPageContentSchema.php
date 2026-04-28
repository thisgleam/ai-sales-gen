<?php

namespace App\Data;

use JsonSerializable;

class SalesPageContentSchema implements JsonSerializable
{
    public function jsonSerialize(): array
    {
        return self::full();
    }

    public static function full(): array
    {
        return [
            'headline' => 'string',
            'sub_headline' => 'string',
            'product_description' => 'string',
            'benefits' => ['array', 'items' => 'string'],
            'features' => [
                'array',
                'items' => [
                    'name' => 'string',
                    'description' => 'string',
                ],
            ],
            'social_proof_placeholder' => 'string',
            'pricing_display' => 'string',
            'call_to_action' => 'string',
        ];
    }

    public static function section(string $sectionKey): array
    {
        $schema = self::full();

        return [
            $sectionKey => $schema[$sectionKey] ?? 'string',
        ];
    }
}
