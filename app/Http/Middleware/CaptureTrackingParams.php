<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CaptureTrackingParams
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $session = $request->session();

        // 1. UTM Parameters
        if ($request->filled('utm_source')) {
            $session->put('marketing_source', strtolower($request->query('utm_source')));
            $session->put('utm_source', $request->query('utm_source'));
            if ($request->filled('utm_medium')) $session->put('utm_medium', $request->query('utm_medium'));
            if ($request->filled('utm_campaign')) $session->put('utm_campaign', $request->query('utm_campaign'));
            if ($request->filled('utm_content')) $session->put('utm_content', $request->query('utm_content'));
        }

        // 2. Facebook Click ID (fbclid)
        if ($request->filled('fbclid')) {
            $fbclid = $request->query('fbclid');
            $timestamp = time() * 1000;
            $fbc = "fb.1.{$timestamp}.{$fbclid}";
            $session->put('_fbc', $fbc);
            $session->put('fbclid', $fbclid);

            if (!$session->has('marketing_source')) {
                $session->put('marketing_source', 'Facebook Ads');
            }
        }

        // 3. TikTok Click ID (ttclid)
        if ($request->filled('ttclid')) {
            $session->put('_ttp', $request->query('ttclid'));
            if (!$session->has('marketing_source')) {
                $session->put('marketing_source', 'TikTok Ads');
            }
        }

        // 4. Referrer detection (fallback)
        if (!$session->has('marketing_source')) {
            $referer = $request->headers->get('referer');
            if ($referer) {
                $host = parse_url($referer, PHP_URL_HOST);
                if ($host) {
                    if (str_contains($host, 'facebook.com') || str_contains($host, 'fb.com')) {
                        $session->put('marketing_source', 'Facebook');
                    } elseif (str_contains($host, 'instagram.com')) {
                        $session->put('marketing_source', 'Instagram');
                    } elseif (str_contains($host, 'google.com')) {
                        $session->put('marketing_source', 'Google');
                    } elseif (str_contains($host, 'tiktok.com')) {
                        $session->put('marketing_source', 'TikTok');
                    } elseif (str_contains($host, 'youtube.com')) {
                        $session->put('marketing_source', 'YouTube');
                    }
                }
            }
        }

        // 5. Landing URL
        if (!$session->has('marketing_landing_url')) {
            $session->put('marketing_landing_url', $request->fullUrl());
        }

        return $next($request);
    }
}
