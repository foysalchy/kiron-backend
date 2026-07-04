<?php

namespace App\Enums;

class SystemPageType
{
    const HOME            = 'home';
    const BLOG_LIST        = 'blog_list';
    const PRICING          = 'pricing';        
    const FAQ              = 'faq';           
    const CONTACT_US       = 'contact_us';
    const CART             = 'cart';
    const CHECKOUT         = 'checkout';
    const LOGIN            = 'login';          
    const REGISTER         = 'register';       
    const FEATURE          = 'feature';
    const BRAND_LIST       = 'brand_list';
    const SHOP             = 'shop';

    public static function all(): array
    {
        return [
            self::HOME,
            self::BLOG_LIST,
            self::PRICING,
            self::FAQ,
            self::CONTACT_US,
            self::CART,
            self::CHECKOUT,
            self::LOGIN,
            self::REGISTER,
            self::FEATURE,
            self::BRAND_LIST,
            self::SHOP,
        ];
    }

    public static function superAdminScoped(): array
    {
        return array_values(array_diff(self::all(), [self::CART, self::CHECKOUT,self::BRAND_LIST, self::SHOP]));
    }

    public static function companyScoped(): array
    {
        return array_values(array_diff(self::all(), [self::PRICING, self::FAQ,self::FEATURE]));
    }

    public static function labels(): array
    {
        return [
            self::HOME           => 'Home Page',
            self::BLOG_LIST       => 'Blog List Page',
            self::PRICING         => 'Pricing Page',
            self::FAQ             => 'FAQ Page',
            self::CONTACT_US      => 'Contact Us Page',
            self::CART            => 'Cart Page',
            self::CHECKOUT        => 'Checkout Page',
            self::LOGIN           => 'Login Page',
            self::REGISTER        => 'Register Page',
            self::FEATURE    => 'Features Page',
            self::BRAND_LIST   => 'Brand List Page',
            self::SHOP         => 'Shop/Product Page',
        ];
    }
}