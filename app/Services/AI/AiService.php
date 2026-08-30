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
        $options = [];
        $prompt = self::buildPrompt($params, $companyId);
        return $provider->generate($prompt, $options);
    }

    public static function testConnection(int $companyId, string $providerName, ?string $apiKey = null, ?string $model = null): bool
    {
        $settings = AiSetting::where('company_id', $companyId)->first();
        if (!$settings) {
            $settings = new AiSetting();
        }

        // If key was provided from frontend, override db value for testing
        if ($apiKey && !str_contains($apiKey, '••••')) {
            if ($providerName === 'openai') {
                $settings->openai_key = $apiKey;
            } else {
                $settings->gemini_key = $apiKey;
            }
        }
        
        if ($model) {
            if ($providerName === 'openai') {
                $settings->openai_model = $model;
            } else {
                $settings->gemini_model = $model;
            }
        }

        $provider = self::resolveProvider($providerName, $settings);
        return $provider->testConnection();
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
                $settings->gemini_model ?? 'gemini-flash-latest',
                $settings->gemini_instructions
            );
        }

        throw new Exception("Unsupported AI provider: {$providerName}");
    }

    protected static function buildPrompt(array $params, int $companyId): string
    {
        $type = $params['type'] ?? 'general';
        $language = $params['language'] ?? 'en';
        
        $shop = \App\Models\SiteSetting::where('company_id', $companyId)->first();
        $shopName = $shop->shop_name ?? 'Our Company';
        
        $prompt = "You are an expert SaaS copywriter writing for the company/shop named: '{$shopName}'.\n";
        
        // Add generation type instructions
        switch ($type) {
            case 'product_description':
                $prompt .= "Task: Generate a professional, persuasive, and SEO-friendly product description. The output MUST be formatted in valid HTML suitable for a WYSIWYG editor (use <p>, <ul>, <li>, <strong>, etc. as appropriate). Do NOT wrap the response in markdown code blocks like ```html.\n";
                break;
            case 'page_content':
                $prompt .= "Task: Generate a comprehensive page content (e.g., Privacy Policy, Terms & Conditions, About Us, etc.). CRITICAL RULE: You MUST mention the company name '{$shopName}' appropriately throughout the content instead of using placeholders. The output MUST be formatted in valid HTML suitable for a WYSIWYG editor. Do NOT wrap the response in markdown code blocks.\n";
                break;
            case 'blog_content':
                $prompt .= "Task: Generate an engaging blog post or article content. The output MUST be formatted in valid HTML suitable for a WYSIWYG editor. Do NOT wrap the response in markdown code blocks.\n";
                break;
            case 'meta_title':
                $prompt .= "Task: Generate an SEO-friendly meta title. CRITICAL RULE: The output MUST be strictly under 60 characters. OUTPUT ONLY THE RAW TITLE TEXT, without quotes, labels, markdown, or explanations.\n";
                break;
            case 'meta_description':
                $prompt .= "Task: Generate an SEO-friendly meta description. CRITICAL RULE: The output MUST be exactly between 120 and 160 characters. OUTPUT ONLY THE RAW DESCRIPTION TEXT, without quotes, labels, markdown, or explanations.\n";
                break;
            case 'short_description':
            case 'blog_short_description':
                $prompt .= "Task: Generate a short, punchy summary (2-3 sentences).\n";
                break;
            case 'features':
                $prompt .= "Task: Extract and generate a bulleted list of key features and benefits.\n";
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
