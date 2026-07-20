<?php

namespace App\Observers;

class CachedOptionsObserver
{
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


        if ($model instanceof \App\Models\Page) {
            \App\Models\Page::clearSlugCache($model->company_id, $model->slug, 'page_content');
        }

        if ($model instanceof \App\Models\LandingPage) {
            \App\Models\LandingPage::clearSlugCache($model->company_id, $model->slug, 'landing_page_view');
        }
        if ($model instanceof \App\Models\Blog) {
            \App\Models\Blog::clearSlugCache($model->company_id, $model->slug, 'blog_single');
        }
    }

    public function deleted($model)
    {
        $this->saved($model);
        if ($model instanceof \App\Models\Page) {
            \App\Models\Page::clearSlugCache($model->company_id, $model->slug, 'page_content');
        }

        if ($model instanceof \App\Models\LandingPage) {
            \App\Models\LandingPage::clearSlugCache($model->company_id, $model->slug, 'landing_page_view');
        }
        if ($model instanceof \App\Models\Blog) {
            \App\Models\Blog::clearSlugCache($model->company_id, $model->slug, 'blog_single');
        }
    }

    public function restored($model)
    {
        $this->saved($model);
        if ($model instanceof \App\Models\Page) {
            \App\Models\Page::clearSlugCache($model->company_id, $model->slug, 'page_content');
        }

        if ($model instanceof \App\Models\LandingPage) {
            \App\Models\LandingPage::clearSlugCache($model->company_id, $model->slug, 'landing_page_view');
        }
        if ($model instanceof \App\Models\Blog) {
            \App\Models\Blog::clearSlugCache($model->company_id, $model->slug, 'blog_single');
        }
    }
}
