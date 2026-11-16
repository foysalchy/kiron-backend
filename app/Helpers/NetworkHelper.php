<?php

namespace App\Helpers;

use GeoIp2\Database\Reader;

class NetworkHelper
{
    private static ?Reader $reader = null;

    /**
     * Detect Network Type (WiFi vs Mobile Data) and ISP from IP address.
     *
     * @param string|null $ip
     * @return array{network: string, isp: string|null, asn: string|null, display: string}
     */
    public static function detect(?string $ip): array
    {
        if (empty($ip) || in_array($ip, ['127.0.0.1', '::1'])) {
            return [
                'network' => 'Localhost',
                'isp'     => 'Localhost',
                'asn'     => null,
                'display' => 'Localhost',
            ];
        }

        $dbPath = storage_path('app/geoip/GeoLite2-ASN.mmdb');
        if (!file_exists($dbPath)) {
            return [
                'network' => 'Unknown',
                'isp'     => null,
                'asn'     => null,
                'display' => 'Unknown',
            ];
        }

        try {
            if (!self::$reader) {
                self::$reader = new Reader($dbPath);
            }

            $record = self::$reader->asn($ip);
            $org = trim((string) ($record->autonomousSystemOrganization ?? ''));
            $asn = $record->autonomousSystemNumber ? "AS{$record->autonomousSystemNumber}" : null;

            if (empty($org)) {
                return [
                    'network' => 'Unknown',
                    'isp'     => null,
                    'asn'     => $asn,
                    'display' => 'Unknown',
                ];
            }

            $isMobile = self::isMobileOperator($org);
            $network = $isMobile ? 'Mobile Data' : 'WiFi';
            $display = $org ? "{$network} ({$org})" : $network;

            return [
                'network' => $network,
                'isp'     => $org,
                'asn'     => $asn,
                'display' => $display,
            ];
        } catch (\Exception $e) {
            return [
                'network' => 'Unknown',
                'isp'     => null,
                'asn'     => null,
                'display' => 'Unknown',
            ];
        }
    }

    private static function isMobileOperator(string $org): bool
    {
        $lower = strtolower($org);
        $mobileKeywords = [
            'grameenphone',
            'robi',
            'axiata',
            'banglalink',
            'teletalk',
            'airtel',
            'jio',
            'vodafone',
            'cellular',
            'mobile',
            'wireless',
            'telecom',
        ];

        foreach ($mobileKeywords as $keyword) {
            if (str_contains($lower, $keyword)) {
                return true;
            }
        }

        return false;
    }
}
