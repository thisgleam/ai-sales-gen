<?php

namespace App\Support;

use App\Models\SalesPage;
use Illuminate\Support\Facades\Storage;

class SalesPageMedia
{
    private const MEDIA_KEYS = [
        'media_url',
        'image_url',
        'media_path',
    ];

    public static function publicPathsFor(SalesPage $salesPage, ?array $generatedContent = null): array
    {
        $paths = [];
        $generatedContent ??= $salesPage->generated_content ?? [];

        foreach (self::MEDIA_KEYS as $key) {
            $paths[] = self::publicPathFromValue($salesPage->getAttribute($key));

            if (is_array($generatedContent) && array_key_exists($key, $generatedContent)) {
                $paths[] = self::publicPathFromValue($generatedContent[$key]);
            }
        }

        return array_values(array_unique(array_filter($paths)));
    }

    public static function deletePublicPaths(array $paths): void
    {
        foreach (array_unique($paths) as $path) {
            Storage::disk('public')->delete($path);
        }
    }

    public static function publicPathFromValue(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $parts = parse_url($value);

        if (is_array($parts) && isset($parts['host']) && ! self::isLocalHost($parts['host'])) {
            return null;
        }

        $path = is_array($parts) ? ($parts['path'] ?? $value) : $value;
        $path = str_replace('\\', '/', urldecode($path));
        $path = ltrim($path, '/');

        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        if (str_starts_with($path, 'public/')) {
            $path = substr($path, strlen('public/'));
        }

        if (! str_starts_with($path, 'sales-media/')) {
            return null;
        }

        if (str_contains($path, '..') || str_ends_with($path, '/')) {
            return null;
        }

        return $path;
    }

    private static function isLocalHost(string $host): bool
    {
        $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);

        return $appHost !== null && strcasecmp($host, $appHost) === 0;
    }
}
