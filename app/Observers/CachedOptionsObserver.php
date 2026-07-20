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
