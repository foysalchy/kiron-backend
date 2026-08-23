<?php

namespace App\Services\AI\Providers;

use App\Services\AI\AiProviderInterface;
use Illuminate\Support\Facades\Http;
use Exception;

class GeminiProvider implements AiProviderInterface
{
    protected string $apiKey;
    protected string $model;
    protected ?string $instructions;

    public function setConfig(string $apiKey, string $model, ?string $instructions = null): self
    {
        $this->apiKey = $apiKey;
        $this->model = $model;
        $this->instructions = $instructions;
        return $this;
    }

    public function generate(string $prompt, array $options = []): string
    {
        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ]
        ];

        if ($this->instructions) {
            $payload['systemInstruction'] = [
                'parts' => [
                    ['text' => $this->instructions]
                ]
            ];
        }

        $generationConfig = [];
        if (isset($options['max_tokens'])) {
            $generationConfig['maxOutputTokens'] = $options['max_tokens'];
        }
        if (isset($options['temperature'])) {
            $generationConfig['temperature'] = $options['temperature'];
        }

        if (!empty($generationConfig)) {
            $payload['generationConfig'] = $generationConfig;
        }

        $response = Http::withHeaders(['Content-Type' => 'application/json'])
            ->timeout(60)
            ->post("https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}", $payload);

        if ($response->failed()) {
            $errorMsg = $response->json('error.message') ?? $response->body();
            throw new Exception("Gemini API Error: " . $errorMsg);
        }

        return $response->json('candidates.0.content.parts.0.text') ?? '';
    }

    public function testConnection(): bool
    {
        $response = Http::withHeaders(['Content-Type' => 'application/json'])
            ->timeout(10)
            ->post("https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}", [
                'contents' => [['parts' => [['text' => 'Hello']]]]
            ]);

        if ($response->successful()) {
            return true;
        }

        $errorMsg = $response->json('error.message') ?? $response->body();
        throw new Exception("Gemini Error: " . $errorMsg);
    }
}
