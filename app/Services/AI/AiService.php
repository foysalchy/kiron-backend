<?php

namespace App\Services\AI;

use App\Models\AiSetting;
use App\Services\AI\Providers\OpenAIProvider;
use App\Services\AI\Providers\GeminiProvider;
use Exception;

class AiService
{
    public static function generate(int $companyId, array $params): string
    {
        $settings = AiSetting::where('company_id', $companyId)->first();

        if (!$settings) {
            throw new Exception("AI provider is not configured.");
        }

        // Determine active provider
        $providerName = $settings->default_provider;
        
        // Fallback logic
        if ($providerName === 'openai' && !$settings->openai_status && $settings->gemini_status) {
            $providerName = 'gemini';
        } elseif ($providerName === 'gemini' && !$settings->gemini_status && $settings->openai_status) {
            $providerName = 'openai';
        }

        if ($providerName === 'openai' && (!$settings->openai_status || !$settings->openai_key)) {
            throw new Exception("OpenAI is not fully configured or enabled.");
        }
        if ($providerName === 'gemini' && (!$settings->gemini_status || !$settings->gemini_key)) {
            throw new Exception("Google Gemini is not fully configured or enabled.");
        }

        $provider = self::resolveProvider($providerName, $settings);
        $prompt = self::buildPrompt($params);

        return $provider->generate($prompt);
    }

    public static function testConnection(int $companyId, string $providerName): bool
    {
        $settings = AiSetting::where('company_id', $companyId)->first();
        if (!$settings) return false;

        try {
            $provider = self::resolveProvider($providerName, $settings);
            return $provider->testConnection();
        } catch (\Throwable $th) {
            return false;
        }
    }

    protected static function resolveProvider(string $providerName, AiSetting $settings): AiProviderInterface
    {
        if ($providerName === 'openai') {
            return (new OpenAIProvider())->setConfig(
                $settings->openai_key,
                $settings->openai_model ?? 'gpt-4o-mini',
                $settings->openai_instructions
            );
        }

        if ($providerName === 'gemini') {
            return (new GeminiProvider())->setConfig(
                $settings->gemini_key,
                $settings->gemini_model ?? 'gemini-1.5-flash',
                $settings->gemini_instructions
            );
        }

        throw new Exception("Unsupported AI provider: {$providerName}");
    }

    protected static function buildPrompt(array $params): string
    {
        $type = $params['type'] ?? 'general';
        $language = $params['language'] ?? 'en';
        
        $prompt = "You are an expert SaaS copywriter.\n";
        
        // Add generation type instructions
        switch ($type) {
            case 'product_description':
                $prompt .= "Task: Generate a professional, persuasive, and SEO-friendly product description.\n";
                break;
            case 'meta_title':
                $prompt .= "Task: Generate an SEO-friendly meta title (max 60 characters).\n";
                break;
            case 'meta_description':
                $prompt .= "Task: Generate an SEO-friendly meta description (150-160 characters).\n";
                break;
            case 'short_description':
                $prompt .= "Task: Generate a short, punchy product summary (2-3 sentences).\n";
                break;
            case 'features':
                $prompt .= "Task: Extract and generate a bulleted list of key product features and benefits.\n";
                break;
            default:
                $prompt .= "Task: Generate high-quality content based on the provided data.\n";
                break;
        }

        $prompt .= "\nRules:\n";
        $prompt .= "- Do not invent information, prices, or unsupported claims.\n";
        $prompt .= "- Focus on customer benefits and professional tone.\n";
        
        if ($language === 'bn') {
            $prompt .= "- Output Language: Bengali (natural, modern conversational Bengali).\n";
        } else {
            $prompt .= "- Output Language: English (professional international English).\n";
        }

        $prompt .= "\nProvided Data:\n";
        
        // Remove structural metadata before rendering context
        unset($params['type'], $params['language']);
        
        foreach ($params as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $value = json_encode($value);
            }
            $prompt .= ucfirst(str_replace('_', ' ', $key)) . ": " . $value . "\n";
        }

        return $prompt;
    }
}
