<?php

namespace App\Providers;

use App\Services\AttributeGroupService;
use App\Services\AttributeService;
use App\Services\BrandService;
use App\Services\CompanyService;
use App\Services\ExtraCategoryService;
use App\Services\MegaCategoryService;
use App\Services\MiniCategoryService;
use App\Services\PartyService;
use App\Services\ProductService;
use App\Services\SubCategoryService;
use App\Services\WarehouseService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(CompanyService::class);
        $this->app->bind(PartyService::class);
        $this->app->bind(AttributeGroupService::class);
        $this->app->bind(AttributeService::class);
        $this->app->bind(BrandService::class);
        $this->app->bind(MegaCategoryService::class);
        $this->app->bind(SubCategoryService::class);
        $this->app->bind(MiniCategoryService::class);
        $this->app->bind(ExtraCategoryService::class);
        $this->app->bind(WarehouseService::class);
        $this->app->bind(ProductService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
