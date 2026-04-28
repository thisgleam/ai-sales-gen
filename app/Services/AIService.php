<?php

namespace App\Services;

use App\Data\SalesPageContentSchema;
use GuzzleHttp\Client;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class AIService
{
    private const OPENROUTER_URL = 'https://openrouter.ai/api/v1/chat/completions';

    private const MODEL = 'openai/gpt-oss-120b:free';

    private LlmResponseHandler $responseHandler;

    private string $systemPrompt = <<<'PROMPT'
You are a world-class direct-response copywriter and marketing strategist.
Your task is to analyze product data and generate a complete, high-converting sales page structure.

CRITICAL RULES:
- Return ONLY valid JSON. No markdown. No explanations. No code blocks.
- All text values must be plain strings. No HTML tags.
- The JSON must exactly match the schema below.

SCHEMA:
{
  "headline": "A bold, benefit-driven headline (max 12 words)",
  "sub_headline": "A clarifying statement that expands on the headline (max 20 words)",
  "product_description": "2-3 sentences describing the product and the problem it solves",
  "benefits": [
    "Benefit statement 1 (start with a verb, e.g. Save, Boost, Eliminate)",
    "Benefit statement 2",
    "Benefit statement 3",
    "Benefit statement 4"
  ],
  "features": [
    {"name": "Feature Name", "description": "1 sentence description of what this feature does"},
    {"name": "Feature Name", "description": "1 sentence description"}
  ],
  "social_proof_placeholder": "A realistic, plausible testimonial quote attributed to a fictional user",
  "pricing_display": "A compelling pricing statement, e.g. 'Start free. Then $49/mo.'",
  "call_to_action": "A short, action-oriented CTA button text (max 5 words)"
}
PROMPT;

    public function __construct(?LlmResponseHandler $responseHandler = null)
    {
        $this->responseHandler = $responseHandler ?? new LlmResponseHandler;
    }

    /**
     * Generate structured sales page content from raw product data.
     *
     * @return array The decoded JSON content
     *
     * @throws RuntimeException on API failure or invalid JSON
     */
    public function generateSalesPage(array $productData): array
    {
        $userMessage = $this->buildUserMessage($productData);

        return $this->requestValidatedJson(
            [
                ['role' => 'system', 'content' => $this->systemPrompt],
                ['role' => 'user', 'content' => $userMessage],
            ],
            SalesPageContentSchema::full(),
            0.8,
            90
        );
    }

    public function streamSalesPage(array $productData, callable $onChunk): array
    {
        $userMessage = $this->buildUserMessage($productData);

        return $this->requestValidatedJson(
            [
                ['role' => 'system', 'content' => $this->systemPrompt],
                ['role' => 'user', 'content' => $userMessage],
            ],
            SalesPageContentSchema::full(),
            0.8,
            90,
            $onChunk
        );
    }

    /**
     * Regenerate a specific section of the sales page.
     */
    public function regenerateSection(array $productData, string $sectionKey): mixed
    {
        $structures = [
            'features' => 'an array of objects, each with "name" and "description" keys',
            'benefits' => 'an array of strings',
            'headline' => 'a short, punchy string',
            'sub_headline' => 'a clarifying string',
            'product_description' => 'a paragraph string',
            'social_proof_placeholder' => 'a testimonial string',
            'pricing_display' => 'a pricing string',
            'call_to_action' => 'a short button text string',
        ];

        $structure = $structures[$sectionKey] ?? 'a string';
        $userMessage = $this->buildUserMessage($productData);
        $userMessage .= "\n\nREGENERATE ONLY THE '$sectionKey' FIELD. It MUST be $structure.";

        $decoded = $this->requestValidatedJson(
            [
                ['role' => 'system', 'content' => "You are a copywriter. Return ONLY valid JSON. You MUST use '$sectionKey' as the ONLY root key. The value MUST be $structure."],
                ['role' => 'user', 'content' => $userMessage],
            ],
            SalesPageContentSchema::section($sectionKey),
            0.9,
            60
        );

        return $decoded[$sectionKey] ?? throw new RuntimeException('Field not found in AI response.');
    }

    private function requestValidatedJson(array $messages, array $schema, float $temperature, int $timeout, ?callable $onChunk = null): array
    {
        $lastException = null;

        foreach (range(0, 2) as $attempt) {
            $attemptMessages = $this->messagesForAttempt($messages, $attempt);
            $attemptTemperature = $attempt === 2 ? 0 : $temperature;

            try {
                if ($onChunk) {
                    $rawContent = $this->postStreamingChatCompletion(
                        $attemptMessages,
                        $attemptTemperature,
                        $timeout,
                        fn (string $chunk) => $onChunk($chunk, $attempt + 1)
                    );
                } else {
                    $response = $this->postChatCompletion($attemptMessages, $attemptTemperature, $timeout);

                    if ($response->failed()) {
                        throw new RuntimeException('OpenRouter API request failed: '.$response->body());
                    }

                    $rawContent = $response->json('choices.0.message.content');
                }

                if (! is_string($rawContent) || trim($rawContent) === '') {
                    throw new RuntimeException('Empty response from AI model.');
                }

                return $this->responseHandler->validateJson($rawContent, $schema);
            } catch (RuntimeException $exception) {
                $lastException = $exception;

                Log::error('AI JSON validation retry triggered', [
                    'attempt' => $attempt + 1,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        throw new RuntimeException('AI returned invalid JSON after 3 attempts: '.$lastException?->getMessage(), 0, $lastException);
    }

    private function postChatCompletion(array $messages, int|float $temperature, int $timeout): Response
    {
        return Http::withToken(config('services.openrouter.key'))
            ->withHeaders([
                'HTTP-Referer' => config('app.url'),
                'X-OpenRouter-Title' => config('app.name'),
                'Content-Type' => 'application/json',
            ])
            ->timeout($timeout)
            ->post(self::OPENROUTER_URL, [
                'model' => self::MODEL,
                'messages' => $messages,
                'response_format' => ['type' => 'json_object'],
                'temperature' => $temperature,
            ]);
    }

    private function postStreamingChatCompletion(array $messages, int|float $temperature, int $timeout, callable $onChunk): string
    {
        $client = new Client([
            'timeout' => $timeout,
        ]);

        $response = $client->post(self::OPENROUTER_URL, [
            'headers' => [
                'Authorization' => 'Bearer '.config('services.openrouter.key'),
                'HTTP-Referer' => config('app.url'),
                'X-OpenRouter-Title' => config('app.name'),
                'Content-Type' => 'application/json',
            ],
            'http_errors' => false,
            'json' => [
                'model' => self::MODEL,
                'messages' => $messages,
                'response_format' => ['type' => 'json_object'],
                'temperature' => $temperature,
                'stream' => true,
            ],
            'stream' => true,
        ]);

        if ($response->getStatusCode() >= 400) {
            throw new RuntimeException('OpenRouter API request failed: '.$response->getBody());
        }

        $body = $response->getBody();
        $buffer = '';
        $content = '';
        $done = false;

        while (! $body->eof() && ! $done) {
            $buffer .= $body->read(1024);

            while (($position = strpos($buffer, "\n")) !== false) {
                $line = trim(substr($buffer, 0, $position));
                $buffer = substr($buffer, $position + 1);

                if ($line === '' || str_starts_with($line, ':') || ! str_starts_with($line, 'data:')) {
                    continue;
                }

                $data = trim(substr($line, strlen('data:')));

                if ($data === '[DONE]') {
                    $done = true;
                    break;
                }

                $payload = json_decode($data, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    continue;
                }

                $chunk = $payload['choices'][0]['delta']['content']
                    ?? $payload['choices'][0]['message']['content']
                    ?? '';

                if ($chunk === '') {
                    continue;
                }

                $content .= $chunk;
                $onChunk($chunk);
            }
        }

        return $content;
    }

    private function messagesForAttempt(array $messages, int $attempt): array
    {
        if ($attempt === 0) {
            return $messages;
        }

        foreach (array_reverse(array_keys($messages)) as $index) {
            if (($messages[$index]['role'] ?? null) === 'user') {
                $messages[$index]['content'] .= "\n\nStrict JSON Format: return exactly one valid JSON object matching the requested schema. Do not include markdown, commentary, or missing keys.";
                break;
            }
        }

        if ($attempt === 2) {
            $messages[] = [
                'role' => 'user',
                'content' => 'Use deterministic output and repair any invalid or partial JSON before responding.',
            ];
        }

        return $messages;
    }

    /**
     * Build the user message from product data.
     */
    private function buildUserMessage(array $productData): string
    {
        return sprintf(
            "Generate a sales page for the following product:\n\n".
            "Product Name: %s\n".
            "Description: %s\n".
            "Key Features: %s\n".
            "Unique Selling Point: %s\n".
            "Target Audience: %s\n".
            'Pricing: %s',
            $productData['product_name'] ?? '',
            $productData['description'] ?? '',
            $productData['key_features'] ?? '',
            $productData['usp'] ?? '',
            $productData['target_audience'] ?? '',
            $productData['price'] ?? ''
        );
    }
}
