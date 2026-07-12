<?php

namespace App\Http\Controllers\Saas;

use App\Http\Controllers\Controller;

use App\Enums\Status;
use App\Models\Blog;
use App\Models\MasterFeature;
use Illuminate\Support\Facades\Cache;

class SeoController extends Controller
{
    public function sitemap()
    {
        $xml = Cache::remember('saas_sitemap', now()->addHours(6), function () {

            $urls = [];

            /*
            |--------------------------------------------------------------------------
            | Static Pages
            |--------------------------------------------------------------------------
            */

            $pages = [
                [
                    'route' => 'saas.index',
                    'priority' => '1.0',
                    'changefreq' => 'daily',
                ],
                [
                    'route' => 'saas.feature.list',
                    'priority' => '0.9',
                    'changefreq' => 'weekly',
                ],
                [
                    'route' => 'saas.blog.list',
                    'priority' => '0.9',
                    'changefreq' => 'weekly',
                ],
                [
                    'route' => 'saas.faq.list',
                    'priority' => '0.7',
                    'changefreq' => 'monthly',
                ],
                [
                    'route' => 'saas.package.list',
                    'priority' => '0.8',
                    'changefreq' => 'weekly',
                ],
                [
                    'route' => 'saas.contact',
                    'priority' => '0.6',
                    'changefreq' => 'monthly',
                ],
            ];

            foreach ($pages as $page) {
                $urls[] = [
                    'loc' => route($page['route']),
                    'lastmod' => now(),
                    'changefreq' => $page['changefreq'],
                    'priority' => $page['priority'],
                ];
            }

            /*
            |--------------------------------------------------------------------------
            | Feature Details
            |--------------------------------------------------------------------------
            */

            MasterFeature::where('status', Status::Active->value)
                ->select('slug', 'updated_at')
                ->get()
                ->each(function ($feature) use (&$urls) {

                    $urls[] = [
                        'loc' => route('saas.feature.details', $feature->slug),
                        'lastmod' => $feature->updated_at,
                        'changefreq' => 'monthly',
                        'priority' => '0.8',
                    ];

                });

            /*
            |--------------------------------------------------------------------------
            | Blog Details
            |--------------------------------------------------------------------------
            */

            Blog::withoutCompanyScope()
                ->where('status', Status::Active->value)
                ->select('slug', 'updated_at')
                ->get()
                ->each(function ($blog) use (&$urls) {

                    $urls[] = [
                        'loc' => route('saas.blog.details', $blog->slug),
                        'lastmod' => $blog->updated_at,
                        'changefreq' => 'monthly',
                        'priority' => '0.8',
                    ];

                });

            return view('sitemap.sitemap', compact('urls'))->render();

        });

        return response($xml)
            ->header('Content-Type', 'application/xml');
    }

    public function robots()
    {
        $robots = Cache::remember('robots_txt', now()->addHours(6), function () {

            return implode("\n", [
                'User-agent: *',
                'Disallow: /',
                '',
                'Disallow: /admin',
                'Disallow: /login',
                '',
                'Sitemap: ' . route('sitemap.saas.index'),
            ]);

        });

        return response($robots)
            ->header('Content-Type', 'text/plain');
    }
}
 
