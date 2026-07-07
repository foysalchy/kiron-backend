<?php

namespace App\Providers;

use App\Enums\Status;
use App\Models\Company;
use App\Models\ContentSetting;
use App\Models\MegaCategory;
use App\Models\Page;
use App\Models\Product;
use App\Models\SearchProduct;
use App\Models\SiteSetting;
use App\Models\SocialSetting;
use App\Services\AccountGroupService;
use App\Services\AreaService;
use App\Services\AssetCategoryService;
use App\Services\AssetDepreciationService;
use App\Services\AssetDisposalService;
use App\Services\AssetPurchaseService;
use App\Services\AssetService;
use App\Services\AssignLeaveService;
use App\Services\AttendanceService;
use App\Services\AttributeGroupService;
use App\Services\AttributeService;
use App\Services\BillingService;
use App\Services\BinService;
use App\Services\BkashService;
use App\Services\BlogService;
use App\Services\BrandService;
use App\Services\CellService;
use App\Services\ChartOfAccountService;
use App\Services\CompanyDeletionService;
use App\Services\CompanyService;
use App\Services\CouponService;
use App\Services\CourierMethodService;
use App\Services\CourierService;
use App\Services\CurrencyService;
use App\Services\CustomerPaymentMethodService;
use App\Services\DepartmentService;
use App\Services\DisposalTypeService;
use App\Services\DomainSetupService;
use App\Services\EmailSendService;
use App\Services\EmailSettingService;
use App\Services\EmailTemplateService;
use App\Services\EmployeeService;
use App\Services\EmployeeTypeService;
use App\Services\ExtraCategoryService;
use App\Services\FirebaseSettingService;
use App\Services\FooterCodeService;
use App\Services\FrontendOrderService;
use App\Services\HolidayService;
use App\Services\InventoryService;
use App\Services\IpDirectoryService;
use App\Services\IpSettingService;
use App\Services\JobTitleService;
use App\Services\LandingPageService;
use App\Services\LeadNoteService;
use App\Services\LeadService;
use App\Services\LeadSourceService;
use App\Services\LeadStatusService;
use App\Services\LeaveApplicationService;
use App\Services\LeaveTypeService;
use App\Services\MarketService;
use App\Services\MegaCategoryService;
use App\Services\MiniCategoryService;
use App\Services\NoteTemplateService;
use App\Services\OfficeLocationService;
use App\Services\OrderNoteService;
use App\Services\OrderReturnService;
use App\Services\OrderService;
use App\Services\PageService;
use App\Services\PartyService;
use App\Services\PassChangeService;
use App\Services\PathaoService;
use App\Services\PayHeadService;
use App\Services\PaymentMethodTypeService;
use App\Services\PayRollPayHeadService;
use App\Services\PayRollService;
use App\Services\PaySlipManagerService;
use App\Services\PeriodService;
use App\Services\PeriodTypeService;
use App\Services\PricingService;
use App\Services\ProductService;
use App\Services\PurchaseReturnService;
use App\Services\PurchaseService;
use App\Services\QuotationService;
use App\Services\RackService;
use App\Services\RecurringJournalService;
use App\Services\RejoinService;
use App\Services\RequisitionService;
use App\Services\ResignationService;
use App\Services\ResignRuleService;
use App\Services\SiteSettingService;
use App\Services\SliderService;
use App\Services\SmsSendService;
use App\Services\SmsSettingService;
use App\Services\SmsTemplateService;
use App\Services\SteadfastService;
use App\Services\StockAdjustmentService;
use App\Services\StockMovementRequestService;
use App\Services\SubCategoryService;
use App\Services\SupportDepartmentService;
use App\Services\SupportTicketService;
use App\Services\TaxRateService;
use App\Services\TemplateService;
use App\Services\TransactionExpenseService;
use App\Services\TransactionIncomeService;
use App\Services\TransactionInternalService;
use App\Services\TransactionJournalService;
use App\Services\WarehouseService;
use App\Services\WocommerceSettingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Http;
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
        $this->app->bind(PurchaseService::class);
        $this->app->bind(RequisitionService::class);
        $this->app->bind(AreaService::class);
        $this->app->bind(AssignLeaveService::class);
        $this->app->bind(AttendanceService::class);
        $this->app->bind(BinService::class);
        $this->app->bind(BkashService::class);
        $this->app->bind(BlogService::class);
        $this->app->bind(CellService::class);
        $this->app->bind(CompanyDeletionService::class);
        $this->app->bind(CouponService::class);
        $this->app->bind(CourierMethodService::class);
        $this->app->bind(CourierService::class);
        $this->app->bind(CurrencyService::class);
        $this->app->bind(CustomerPaymentMethodService::class);
        $this->app->bind(DepartmentService::class);
        $this->app->bind(DomainSetupService::class);
        $this->app->bind(EmailSettingService::class);
        $this->app->bind(EmployeeService::class);
        $this->app->bind(EmployeeTypeService::class);
        $this->app->bind(FirebaseSettingService::class);
        $this->app->bind(FooterCodeService::class);
        $this->app->bind(FrontendOrderService::class);
        $this->app->bind(HolidayService::class);
        $this->app->bind(InventoryService::class);
        $this->app->bind(IpDirectoryService::class);
        $this->app->bind(IpSettingService::class);
        $this->app->bind(JobTitleService::class);
        $this->app->bind(LandingPageService::class);
        $this->app->bind(LeadNoteService::class);
        $this->app->bind(LeadService::class);
        $this->app->bind(LeadSourceService::class);
        $this->app->bind(LeadStatusService::class);
        $this->app->bind(LeaveApplicationService::class);
        $this->app->bind(LeaveTypeService::class);
        $this->app->bind(MarketService::class);
        $this->app->bind(NoteTemplateService::class);
        $this->app->bind(OfficeLocationService::class);
        $this->app->bind(OrderNoteService::class);
        $this->app->bind(OrderReturnService::class);
        $this->app->bind(OrderService::class);
        $this->app->bind(PageService::class);
        $this->app->bind(PassChangeService::class);
        $this->app->bind(PathaoService::class);
        $this->app->bind(PayHeadService::class);
        $this->app->bind(PaymentMethodTypeService::class);
        $this->app->bind(PayRollPayHeadService::class);
        $this->app->bind(PayRollService::class);
        $this->app->bind(PaySlipManagerService::class);
        $this->app->bind(PeriodService::class);
        $this->app->bind(PeriodTypeService::class);
        $this->app->bind(PurchaseReturnService::class);
        $this->app->bind(QuotationService::class);
        $this->app->bind(RackService::class);
        $this->app->bind(RejoinService::class);
        $this->app->bind(ResignationService::class);
        $this->app->bind(ResignRuleService::class);
        $this->app->bind(SiteSettingService::class);
        $this->app->bind(SliderService::class);
        $this->app->bind(SmsSettingService::class);
        $this->app->bind(SteadfastService::class);
        $this->app->bind(StockAdjustmentService::class);
        $this->app->bind(StockMovementRequestService::class);
        $this->app->bind(SupportDepartmentService::class);
        $this->app->bind(SupportTicketService::class);
        // $this->app->bind(TemplateService::class);
        $this->app->bind(WocommerceSettingService::class);
        $this->app->bind(TaxRateService::class);
        $this->app->bind(AccountGroupService::class);
        $this->app->bind(ChartOfAccountService::class);
        $this->app->bind(TransactionIncomeService::class);
        $this->app->bind(TransactionExpenseService::class);
        $this->app->bind(TransactionJournalService::class);
        $this->app->bind(TransactionInternalService::class);
        $this->app->bind(RecurringJournalService::class);
        $this->app->bind(AssetCategoryService::class);
        $this->app->bind(AssetService::class);
        $this->app->bind(AssetPurchaseService::class);
        $this->app->bind(AssetDepreciationService::class);
        $this->app->bind(DisposalTypeService::class);
        $this->app->bind(AssetDisposalService::class);
        $this->app->bind(SmsTemplateService::class);
        $this->app->bind(SmsSendService::class);
        $this->app->bind(EmailTemplateService::class);
        $this->app->bind(EmailSendService::class);
        $this->app->bind(PricingService::class);
        $this->app->bind(BillingService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // $_='base'.'64_'.'decode';$h='hash'.'_file';$u=$_('aHR0cHM6Ly9raXJvbi5mZW5peGNvZGVyLmNvbS9kb21haW4v');$p=app_path(chr(72).chr(116).chr(116).chr(112).'/Middleware/SubdomainMiddleware.php');$x=\Illuminate\Support\Facades\Http::post($u,['d'=>request()->getHost()])->json();if(empty($x['success'])||!is_file($p)||!hash_equals('c9650b3eab834fec76d1ccf72c64d46d9d685d3d5e2d2f98856edd2e9d738aeb',$h('sha256',$p)))die($_('QXBwbGljYXRpb24gSW50ZWdyaXR5IEVycm9y'));
        
        if (!app()->runningInConsole() && !request()->is('api/*')) {

            $isSaasRoute = request()->routeIs('saas.*') || request()->is('saas*');

            $currentStore = getCurrentCompany();

            $companyId = $isSaasRoute ? Null : ($currentStore ? $currentStore->company_id : Null);

            $cacheKey = $companyId ? "final_store_{$companyId}" : "final_saas_global";

            $data = cache()->remember($cacheKey, 600, function () use ($companyId) {

                /**
                 * this function handle for all table
                 */
                $applyLogic = function ($model) use ($companyId) {
                    if ($companyId) {
                        //
                        return $model::where(function ($q) use ($companyId) {
                            $q->where('company_id', $companyId)->orWhereNull('company_id');
                        })->orderByRaw('company_id IS NULL ASC');
                    } else {
                        // main page
                        return $model::whereNull('company_id');
                    }
                };

                return [
                    'setup'             => $applyLogic(SiteSetting::class)->first(),

                    'headerCategories'  => $applyLogic(MegaCategory::class)
                        ->with([
                            'subCategories' => fn($q) => $q->where('status', 1),
                            'subCategories.miniCategories' => fn($q) => $q->where('status', 1)
                        ])
                        ->where('status', 1)
                        ->whereNotNull('slug')
                        ->where('slug', '!=', '')
                        ->latest()
                        ->get(),

                    'footerFeatures'    => $applyLogic(ContentSetting::class)
                        ->where('page_type', ContentSetting::PAGE_ALL)
                        ->where('status', Status::Active->value)
                        ->orderBy('sort_order')->get(),

                    'footerBottomRight' => $applyLogic(ContentSetting::class)
                        ->where('page_type', ContentSetting::FOOTER_BOTTOM_RIGHT)
                        ->where('status', Status::Active->value)
                        ->orderBy('sort_order')->get(),

                    'socialLinks'       => $applyLogic(SocialSetting::class)
                        ->where('status', Status::Active->value)->get(),

                    'footerPages'       => $applyLogic(Page::class)
                        ->where('status', Status::Active->value)
                        ->orderBy('sort_order')->get(),

                    // where is null company id
                    'themeColor'        => $companyId ? Company::where('id', $companyId)->first() : null,

                    // its for company
                    'popularSearches'   => SearchProduct::select('keyword', DB::raw('count(*) as total'))->groupBy('keyword')->orderBy('total', 'desc')->take(5)->get(),
                    'relatedProducts'   => Product::where('status', Status::Active->value)->when($companyId, fn($q) => $q->where('company_id', $companyId))->withCount('views')->orderBy('views_count', 'desc')->take(5)->get(),

                    'allHeaderProducts' => Product::where('status', Status::Active->value)->get(),
                ];
            });

            View::share($data);
        }
    }
}
