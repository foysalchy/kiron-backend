<?php

namespace App\Services\AI;

interface AiProviderInterface
{
    /**
     * Set the configuration for the provider.
     */
    public function setConfig(string $apiKey, string $model, ?string $instructions = null): self;

    /**
     * Generate content based on a final compiled prompt.
     */
    public function generate(string $prompt, array $options = []): string;

    /**
     * Test the connection to the provider.
     */
    public function testConnection(): bool;
}
