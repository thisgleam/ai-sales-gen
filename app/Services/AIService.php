<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AIService
{
    private const OPENROUTER_URL = 'https://openrouter.ai/api/v1/chat/completions';
    private const MODEL = 'openai/gpt-oss-120b:free';

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

    /**
     * Generate structured sales page content from raw product data.
     *
     * @param array $productData
     * @return array The decoded JSON content
     * @throws \RuntimeException on API failure or invalid JSON
     */
    public function generateSalesPage(array $productData): array
    {
        $userMessage = $this->buildUserMessage($productData);

        $response = Http::withToken(config('services.openrouter.key'))
            ->withHeaders([
                'HTTP-Referer'     => config('app.url'),
                'X-OpenRouter-Title' => config('app.name'),
                'Content-Type'     => 'application/json',
            ])
            ->timeout(90)
            ->post(self::OPENROUTER_URL, [
                'model'    => self::MODEL,
                'messages' => [
                    ['role' => 'system', 'content' => $this->systemPrompt],
                    ['role' => 'user',   'content' => $userMessage],
                ],
                'response_format' => ['type' => 'json_object'],
                'temperature'     => 0.8,
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('OpenRouter API request failed: ' . $response->body());
        }

        $rawContent = $response->json('choices.0.message.content');

        if (! $rawContent) {
            throw new \RuntimeException('Empty response from AI model.');
        }

        // Clean up markdown code blocks if the AI accidentally includes them
        $rawContent = trim($rawContent);
        if (str_starts_with($rawContent, '```')) {
            $rawContent = preg_replace('/^```(?:json)?\n?/', '', $rawContent);
            $rawContent = preg_replace('/```$/', '', $rawContent);
            $rawContent = trim($rawContent);
        }

        $decoded = json_decode($rawContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('AI returned invalid JSON: ' . json_last_error_msg());
        }

        return $decoded;
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

        $response = Http::withToken(config('services.openrouter.key'))
            ->withHeaders([
                'HTTP-Referer'     => config('app.url'),
                'X-OpenRouter-Title' => config('app.name'),
                'Content-Type'     => 'application/json',
            ])
            ->timeout(60)
            ->post(self::OPENROUTER_URL, [
                'model'    => self::MODEL,
                'messages' => [
                    ['role' => 'system', 'content' => "You are a copywriter. Return ONLY valid JSON. You MUST use '$sectionKey' as the ONLY root key. The value MUST be $structure."],
                    ['role' => 'user',   'content' => $userMessage],
                ],
                'response_format' => ['type' => 'json_object'],
                'temperature'     => 0.9,
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('AI request failed.');
        }

        $rawContent = trim($response->json('choices.0.message.content'));
        
        // Clean up markdown code blocks if the AI accidentally includes them
        if (str_starts_with($rawContent, '```')) {
            $rawContent = preg_replace('/^```(?:json)?\n?/', '', $rawContent);
            $rawContent = preg_replace('/```$/', '', $rawContent);
            $rawContent = trim($rawContent);
        }

        $decoded = json_decode($rawContent, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('AI returned invalid JSON for section regeneration.');
        }

        return $decoded[$sectionKey] ?? throw new \RuntimeException('Field not found in AI response.');
    }

    /**
     * Build the user message from product data.
     */
    private function buildUserMessage(array $productData): string
    {
        return sprintf(
            "Generate a sales page for the following product:\n\n" .
            "Product Name: %s\n" .
            "Description: %s\n" .
            "Key Features: %s\n" .
            "Unique Selling Point: %s\n" .
            "Target Audience: %s\n" .
            "Pricing: %s",
            $productData['product_name']  ?? '',
            $productData['description']   ?? '',
            $productData['key_features']  ?? '',
            $productData['usp']           ?? '',
            $productData['target_audience'] ?? '',
            $productData['price']         ?? ''
        );
    }
}
