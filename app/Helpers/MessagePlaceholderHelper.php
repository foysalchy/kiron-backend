<?php
namespace App\Helpers;

class MessagePlaceholderHelper
{
    public static function replace(string $template, string $customerName, string $companyName): string
    {
        return str_replace(
            ['{customer_name}', '{company_name}'],
            [$customerName, $companyName],
            $template
        );
    }

    public static function availablePlaceholders(): array
    {
        return [
            ['key' => '{customer_name}', 'label' => 'Customer Name'],
            ['key' => '{company_name}', 'label' => 'Company Name'],
        ];
    }
}