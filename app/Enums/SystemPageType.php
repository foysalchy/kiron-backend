<?php

namespace App\Enums;

class SystemPageType
{
    const HOME           = 'home';
    const BLOG_LIST       = 'blog_list';
    const PRICING_FAQ     = 'pricing_faq';
    const CONTACT_US      = 'contact_us';
    const CART            = 'cart';
    const CHECKOUT        = 'checkout';
    const LOGIN_REGISTER  = 'login_register';

    public static function all(): array
    {
        return [
            self::HOME,
            self::BLOG_LIST,
            self::PRICING_FAQ,
            self::CONTACT_US,
            self::CART,
            self::CHECKOUT,
            self::LOGIN_REGISTER,
        ];
    }

    public static function superAdminScoped(): array
    {
        return array_values(array_diff(self::all(), [self::CART, self::CHECKOUT]));
    }


    public static function companyScoped(): array
    {
        return array_values(array_diff(self::all(), [self::PRICING_FAQ]));
    }

    public static function labels(): array
    {
        return [
            self::HOME           => 'Home Page',
            self::BLOG_LIST       => 'Blog List Page',
            self::PRICING_FAQ     => 'Pricing & FAQ Page',
            self::CONTACT_US      => 'Contact Us Page',
            self::CART            => 'Cart Page',
            self::CHECKOUT        => 'Checkout Page',
            self::LOGIN_REGISTER  => 'Login & Register Page',
        ];
    }
}
