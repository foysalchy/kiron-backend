<?php

namespace App\Observers;

class CachedOptionsObserver
{
    public function saved($model)
    {
        $model::clearOptionsCache($model->company_id);
    }

    public function deleted($model)
    {
        $model::clearOptionsCache($model->company_id);
    }

    public function restored($model)
    {
        $model::clearOptionsCache($model->company_id);
    }
}
