<?php

namespace App\Helpers;

class DeviceHelper
{
    /**
     * Detect device type, platform/OS, and browser from User-Agent string.
     *
     * @param string|null $userAgent
     * @return array{device: string, platform: string, browser: string}
     */
    public static function detect(?string $userAgent = null): array
    {
        $ua = $userAgent ?? request()->userAgent() ?? '';

        // Device type
        $device = 'Desktop';
        if (preg_match('/(tablet|ipad|playbook|silk)|(android(?!.*mobi))/i', $ua)) {
            $device = 'Tablet';
        } elseif (preg_match('/(mobile|phone|ipod|iphone|android|blackberry|iemobile|kindle|silk-accelerated|opera mini)/i', $ua)) {
            $device = 'Mobile';
        }

        // Platform / Operating System
        $platform = 'Unknown OS';
        if (preg_match('/windows nt 10/i', $ua)) {
            $platform = 'Windows 10/11';
        } elseif (preg_match('/windows nt 6\.3/i', $ua)) {
            $platform = 'Windows 8.1';
        } elseif (preg_match('/windows nt 6\.2/i', $ua)) {
            $platform = 'Windows 8';
        } elseif (preg_match('/windows nt 6\.1/i', $ua)) {
            $platform = 'Windows 7';
        } elseif (preg_match('/windows nt/i', $ua)) {
            $platform = 'Windows';
        } elseif (preg_match('/iphone/i', $ua)) {
            $platform = 'iOS (iPhone)';
        } elseif (preg_match('/ipad/i', $ua)) {
            $platform = 'iPadOS';
        } elseif (preg_match('/android/i', $ua)) {
            $platform = 'Android';
        } elseif (preg_match('/macintosh|mac os x/i', $ua)) {
            $platform = 'macOS';
        } elseif (preg_match('/cros/i', $ua)) {
            $platform = 'Chrome OS';
        } elseif (preg_match('/linux/i', $ua)) {
            $platform = 'Linux';
        }

        // Browser detection (ordered by specificity)
        $browser = 'Unknown Browser';
        if (preg_match('/edg\/([0-9\.]+)/i', $ua)) {
            $browser = 'Edge';
        } elseif (preg_match('/(opr|opera)\/([0-9\.]+)/i', $ua)) {
            $browser = 'Opera';
        } elseif (preg_match('/samsungbrowser\/([0-9\.]+)/i', $ua)) {
            $browser = 'Samsung Internet';
        } elseif (preg_match('/ucbrowser\/([0-9\.]+)/i', $ua)) {
            $browser = 'UC Browser';
        } elseif (preg_match('/chrome\/([0-9\.]+)/i', $ua)) {
            $browser = 'Chrome';
        } elseif (preg_match('/firefox\/([0-9\.]+)/i', $ua)) {
            $browser = 'Firefox';
        } elseif (preg_match('/version\/([0-9\.]+).*safari/i', $ua) || preg_match('/safari/i', $ua)) {
            $browser = 'Safari';
        }

        return [
            'device'   => $device,
            'platform' => $platform,
            'browser'  => $browser,
        ];
    }
}
