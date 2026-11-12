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

        // Fallback models if primary model is overloaded (503 High Demand)
        $modelsToTry = [$this->model];
        $fallbacks = ['gemini-3.5-flash', 'gemini-3.5-flash-lite', 'gemini-3-flash-preview'];
        foreach ($fallbacks as $fb) {
            if (!in_array($fb, $modelsToTry)) {
                $modelsToTry[] = $fb;
            }
        }

        $lastError = 'Unknown Gemini API Error';
        foreach ($modelsToTry as $modelName) {
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->timeout(60)
                ->post("https://generativelanguage.googleapis.com/v1beta/models/{$modelName}:generateContent?key={$this->apiKey}", $payload);

            if ($response->successful()) {
                return $response->json('candidates.0.content.parts.0.text') ?? '';
            }

            $lastError = $response->json('error.message') ?? $response->body();
            // If error is high demand / 503 / 429 / quota exceeded, try next fallback model
            $isTemporary = str_contains(strtolower($lastError), 'high demand') 
                || str_contains(strtolower($lastError), 'temporarily')
                || str_contains(strtolower($lastError), 'quota')
                || str_contains(strtolower($lastError), 'rate_limit')
                || $response->status() === 503
                || $response->status() === 429;

            if (!$isTemporary) {
                break;
            }
        }

        throw new Exception("Gemini API Error: " . $lastError);
    }

    public function testConnection(): bool
    {
        $response = Http::withHeaders(['Content-Type' => 'application/json'])
            ->timeout(30)
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
