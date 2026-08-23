<?php

namespace App\Services\AI\Providers;

use App\Services\AI\AiProviderInterface;
use Illuminate\Support\Facades\Http;
use Exception;

class OpenAIProvider implements AiProviderInterface
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
        $messages = [];
        
        if ($this->instructions) {
            $messages[] = [
                'role' => 'system',
                'content' => $this->instructions
            ];
        }

        $messages[] = [
            'role' => 'user',
            'content' => $prompt
        ];

        $payload = [
            'model' => $this->model,
            'messages' => $messages,
            'temperature' => $options['temperature'] ?? 0.7,
        ];

        if (isset($options['max_tokens'])) {
            $payload['max_tokens'] = $options['max_tokens'];
        }

        $response = Http::withToken($this->apiKey)
            ->timeout(60)
            ->post('https://api.openai.com/v1/chat/completions', $payload);

        if ($response->failed()) {
            $errorMsg = $response->json('error.message') ?? $response->body();
            throw new Exception("OpenAI API Error: " . $errorMsg);
        }

        return $response->json('choices.0.message.content') ?? '';
    }

    public function testConnection(): bool
    {
        $response = Http::withToken($this->apiKey)
            ->timeout(10)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->model,
                'messages' => [['role' => 'user', 'content' => 'Hello']],
                'max_tokens' => 5
            ]);

        if ($response->successful()) {
            return true;
        }

        $errorMsg = $response->json('error.message') ?? $response->body();
        throw new Exception("OpenAI Error: " . $errorMsg);
    }
}
