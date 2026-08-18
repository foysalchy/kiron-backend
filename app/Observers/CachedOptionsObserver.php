<?php

namespace App\Observers;

use App\Models\Page;
use App\Models\LandingPage;
use App\Models\Blog;
use App\Models\DomainSetup;
use App\Models\MasterFeature;

class CachedOptionsObserver
{
    // CachedOptionsObserver.php

    public function saved($model)
    {
        if (method_exists($model, 'clearOptionsCache')) {
            $model::clearOptionsCache($model->company_id);
        }

        if (method_exists($model, 'clearHomepageCache')) {
            $model::clearHomepageCache($model->company_id);
        }

        if (method_exists($model, 'clearGlobalLayoutCache') && method_exists($model, 'globalLayoutSections')) {
            foreach ($model::globalLayoutSections() as $section) {
                $model::clearGlobalLayoutCache($model->company_id, $section);
            }
        }

        // এখানে fix - শুধু company_id null হলেই SaaS cache clear হবে
        if (
            is_null($model->company_id)
            && method_exists($model, 'clearSaasCache')
            && method_exists($model, 'saasCacheKeys')
        ) {
            foreach ($model::saasCacheKeys() as $key) {
                $model::clearSaasCache($key);
            }
        }

        if (is_null($model->company_id) && method_exists($model, 'clearPaginatedCache')) {
            $model::clearPaginatedCache();
        }

        // Slug-based cache - company-scoped
        if ($model instanceof \App\Models\Page) {
            \App\Models\Page::clearSlugCache($model->company_id, $model->slug, 'page_content');
        }

        if ($model instanceof \App\Models\LandingPage) {
            \App\Models\LandingPage::clearSlugCache($model->company_id, $model->slug, 'landing_page_view');
        }

        if ($model instanceof \App\Models\Blog) {
            \App\Models\Blog::clearSlugCache($model->company_id, $model->slug, 'blog_single');

            // SaaS blog details - শুধু company_id null হলে
            if (is_null($model->company_id)) {
                \App\Models\Blog::clearSlugCache(null, $model->slug, 'saas_blog_details');
            }
        }

        // Slug-based cache - global/SaaS-only
        if ($model instanceof \App\Models\MasterFeature) {
            \App\Models\MasterFeature::clearSlugCache(null, $model->slug, 'saas_feature_details');
        }

        if ($model instanceof \App\Models\DomainSetup) {
            \App\Models\DomainSetup::clearSubdomainCache($model->sub_domain);
        }
    }
    public function updating($model)
    {
        if ($model instanceof DomainSetup && $model->isDirty('sub_domain')) {
            DomainSetup::clearSubdomainCache($model->getOriginal('sub_domain'));
        }
    }


    public function deleted($model)
    {
        $this->saved($model);
    }

    public function restored($model)
    {
        $this->saved($model);
    }
}
