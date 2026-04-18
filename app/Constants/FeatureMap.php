<?php

namespace App\Constants;

class FeatureMap
{
    /**
     * Pricing package feature label → permission feature_dependency key
     */
    public const MAP = [
        'POS Sales'                              => 'pos_module',
        'Warehouses'                             => 'multi_warehouse',
        'Create Landing Page'                    => 'landing_page_builder',
        'Email Campaigns'                        => 'email_marketing',
        'SMS Campaigns'                          => 'sms_marketing',
        'Chart of Accounts'                      => 'accounting_module',
        'Employees'                              => 'hrm_module',
        'Ticket System'                          => 'support_module',
        'Support Departments'                    => 'support_module',
        'Asset Tracking'                         => 'asset_management',
        'Disposal'                               => 'asset_management',
        'Login History'                          => null, // global
        'Roles & Permissions'                    => null, // global
        'User Management'                        => null, // global
        'Customers'                              => null, // global
        'Customer Groups'                        => null, // global
        'Orders / Invoice'                       => null, // global
        'Product CRUD'                           => null, // global
        'Product Groups'                         => null, // global
        'Categories (Mega / Sub / Mini / Extra)' => null, // global
        'Brands'                                 => null, // global
        'Attributes & Variants'                  => null, // global
        'Stock Overview'                         => null, // global
        'Sales Report'                           => null, // global
        'Coupons / Discounts'                    => null, // global
        'Pages'                                  => null, // global
        'Blog / News'                            => null, // global
        'Sliders'                                => null, // global
        'VAT Rates'                              => null, // global
        'VAT Groups'                             => null, // global
        'Payment Gateway'                        => null, // global
        'Courier / Shipping Integration'         => null, // global
    ];

    /**
     * Convert pricing package feature labels → permission dependency keys
     */
    public static function resolve(array $featureLabels): array
    {
        return collect($featureLabels)
            ->map(fn($label) => self::MAP[$label] ?? null)
            ->filter()          // remove nulls (global perms always included)
            ->unique()
            ->values()
            ->toArray();
    }
}
