<?php

namespace App\Services;

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Log;
use JsonSerializable;
use RuntimeException;
use Throwable;

class LlmResponseHandler
{
    public function validateJson(string $response, array|JsonSerializable $schema): array
    {
        $schema = $schema instanceof JsonSerializable ? $schema->jsonSerialize() : $schema;
        $cleanedResponse = $this->cleanJsonResponse($response);

        try {
            $decoded = json_decode($cleanedResponse, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable $exception) {
            $this->logValidationFailure($response, $exception->getMessage());

            throw new RuntimeException('AI JSON validation failed: '.$exception->getMessage(), 0, $exception);
        }

        if (! is_array($decoded)) {
            $message = 'Root JSON value must be an object.';
            $this->logValidationFailure($response, $message);

            throw new RuntimeException('AI JSON validation failed: '.$message);
        }

        try {
            $this->validateValue($decoded, $schema, 'root');
        } catch (RuntimeException $exception) {
            $this->logValidationFailure($cleanedResponse, $exception->getMessage());

            throw new RuntimeException('AI JSON validation failed: '.$exception->getMessage(), 0, $exception);
        }

        return $decoded;
    }

    private function cleanJsonResponse(string $response): string
    {
        $response = trim($response);

        if (str_starts_with($response, '```')) {
            $response = preg_replace('/^```(?:json)?\n?/', '', $response) ?? $response;
            $response = preg_replace('/```$/', '', $response) ?? $response;
        }

        return trim($response);
    }

    private function validateValue(mixed $value, mixed $schema, string $path): void
    {
        if (is_string($schema)) {
            $this->validateScalar($value, $schema, $path);

            return;
        }

        if (! is_array($schema)) {
            throw new RuntimeException("Unsupported schema at {$path}.");
        }

        if (($schema[0] ?? null) === 'array') {
            if (! is_array($value)) {
                throw new RuntimeException("Expected {$path} to be array.");
            }

            if (! array_key_exists('items', $schema)) {
                return;
            }

            foreach ($value as $index => $item) {
                $this->validateValue($item, $schema['items'], "{$path}.{$index}");
            }

            return;
        }

        if (! is_array($value)) {
            throw new RuntimeException("Expected {$path} to be object.");
        }

        foreach ($schema as $key => $childSchema) {
            if (! array_key_exists($key, $value)) {
                throw new RuntimeException("Missing required key {$path}.{$key}.");
            }

            $this->validateValue($value[$key], $childSchema, "{$path}.{$key}");
        }
    }

    private function validateScalar(mixed $value, string $type, string $path): void
    {
        $valid = match ($type) {
            'string' => is_string($value),
            'array' => is_array($value),
            'integer' => is_int($value),
            'number' => is_int($value) || is_float($value),
            'boolean' => is_bool($value),
            'mixed' => true,
            default => false,
        };

        if (! $valid) {
            throw new RuntimeException("Expected {$path} to be {$type}.");
        }
    }

    private function logValidationFailure(string $response, string $reason): void
    {
        try {
            if (! Facade::getFacadeApplication()) {
                return;
            }

            Log::error('AI JSON validation failed', [
                'reason' => $reason,
                'response_preview' => mb_substr($response, 0, 500),
            ]);
        } catch (Throwable) {
            //
        }
    }
}
