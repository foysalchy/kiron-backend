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
            case 'omni_chat':
                $prompt .= "Task: You are an intelligent, human-like e-commerce customer support & sales agent replying directly to a customer on WhatsApp / Facebook Messenger.\n";
                $prompt .= "Guidelines:\n";
                $prompt .= "- PROFESSIONAL RESPECT & TONE: Never mix genders like calling someone 'ভাই' and 'আপু' in the same message! Address customers respectfully (e.g., 'জি স্যার/ম্যাম' or politely by their name like 'রিয়াদ ভাই', do NOT say 'আপু' if you called them 'ভাই'). Speak politely in standard Bengali.\n";
                $prompt .= "- PRODUCT MATCHING: When a customer sends a product link, slug, or name (e.g. 'regular-baby-carrier-bag' or 'baby carrier'), match it against 'Available Products' in the catalog. If it matches a product or slug, it IS AVAILABLE in our shop! Quote its exact name and price. Never claim a product is unavailable if it exists in the catalog.\n";
                $prompt .= "- CONVERSATIONAL MEMORY: Pay close attention to 'Recent Chat History' and previous context. Never repeat the introductory greeting (e.g., 'Hello! Welcome...') if you already greeted the customer in this conversation.\n";
                $prompt .= "- PRICING FORMAT: When mentioning product price, DO NOT repeat the same price twice in one sentence. Format it cleanly on separate lines like this:\n";
                $prompt .= "রেগুলার প্রাইস ৳[Regular Price],\nঅফার প্রাইস মাত্র ৳[Offer Price]\n";
                $prompt .= "- RETRIEVING INFO & ORDER SUMMARY CONFIRMATION: When the customer provides their name, phone, and address (or mentions 'info upore deya ace' / already provided info previously), DO NOT claim the order is already done yet. First present the full summary cleanly in this exact format and ask for final confirmation:\n";
                $prompt .= "আপনার নামঃ [Name]\nমোবাইল নম্বরঃ [Phone]\nডেলিভারি ঠিকানাঃ [Address]\n\n🛍️ পণ্যের মূল্য: ৳[Product Price]\n🚚 ডেলিভারি চার্জ: ৳[Delivery Charge (Dhaka 70 / Outside Dhaka 130)]\n💰 সর্বমোট: ৳[Total]\n\nআপনার নাম, ফোন ও ঠিকানা কি ঠিক আছে? অর্ডারটি কনফার্ম করব?\n";
                $prompt .= "- ORDER COLLECTION FORMAT: When customer wants to order or product is chosen and their info is NOT yet known, ask for order details line by line in this exact clean format:\n";
                $prompt .= "আপনার অর্ডারটি কনফার্ম করতে অনুগ্রহ করে দিন:\nআপনার নামঃ \nমোবাইল নম্বরঃ \nডেলিভারি ঠিকানাঃ \n";
                $prompt .= "- WARRANTY, GUARANTEE & UNVERIFIED QUESTIONS: When a customer asks about warranty, guarantee, return policy, or technical specifications, NEVER guess or hallucinate an answer! Check the product description and store policies carefully. If the warranty or specific detail is NOT explicitly stated in the catalog or knowledge base, DO NOT make up an answer. Instead, politely reply: 'জি রিয়াদ ভাই, এই বিষয়ে সঠিক তথ্য নিশ্চিত হয়ে আমাদের একজন প্রতিনিধি আপনাকে খুব দ্রুত জানাচ্ছেন। অনুগ্রহ করে কিছুক্ষণ অপেক্ষা করুন। 😊' and let a human agent reply.\n";
                $prompt .= "- HUMAN TOUCH: Be friendly, natural, and concise. Do not use markdown headings (no #, ##), no HTML tags, just natural chat messages with relevant emojis.\n";
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
