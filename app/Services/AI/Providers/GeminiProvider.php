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

    public function generate(string $prompt): string
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

        $response = Http::withHeaders(['Content-Type' => 'application/json'])
            ->timeout(60)
            ->post("https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}", $payload);

        if ($response->failed()) {
            throw new Exception("Gemini API Error: " . $response->body());
        }

        return $response->json('candidates.0.content.parts.0.text') ?? '';
    }

    public function testConnection(): bool
    {
        try {
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->timeout(10)
                ->post("https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}", [
                    'contents' => [['parts' => [['text' => 'Hello']]]]
                ]);

            return $response->successful();
        } catch (\Throwable $th) {
            return false;
        }
    }
}
