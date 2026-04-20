<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Permission::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $permissionsToInsert = [];

        $addCrud = function ($prefix, $groupName, $feature) use (&$permissionsToInsert) {
            foreach (['view', 'create', 'edit', 'delete'] as $action) {
                $permissionsToInsert[] = [
                    'name'               => "{$prefix}.{$action}",
                    'group_name'         => $groupName,
                    'feature_dependency' => $feature,
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ];
            }
        };

        $addViewOnly = function ($prefix, $groupName, $feature) use (&$permissionsToInsert) {
            $permissionsToInsert[] = [
                'name'               => "{$prefix}.view",
                'group_name'         => $groupName,
                'feature_dependency' => $feature,
                'created_at'         => now(),
                'updated_at'         => now(),
            ];
        };

        // ==========================================
        // 1. Parties
        // ==========================================
        $addCrud('customers',             'Customers',                'customers');
        $addCrud('suppliers',             'Suppliers',                'suppliers');
        $addCrud('customer_groups',       'Customer Groups',          'customer_groups');
        $addCrud('parties_import_export', 'Parties Import & Export',  'parties_import_export');

        // ==========================================
        // 2. Purchase
        // ==========================================
        $addCrud('purchases',             'Purchases',                'purchases');
        $addCrud('requisitions',          'Requisitions',             'requisitions');
        $addCrud('purchase_returns',      'Purchase Returns',         'purchase_returns');

        // ==========================================
        // 3. Orders
        // ==========================================
        $addCrud('orders',                'Orders',                   'orders');
        $addCrud('sales_returns',         'Sales Returns',            'sales_returns');
        $addCrud('quotations',            'Quotations',               'quotations');
        $addViewOnly('incomplete_orders', 'Incomplete Orders',        'incomplete_orders');
        $addCrud('pos',                   'POS',                      'pos_module');

        // ==========================================
        // 4. Products
        // ==========================================
        $addCrud('products',              'Products',                 'products');
        $addCrud('product_groups',        'Product Groups',           'product_groups');
        $addCrud('brands',                'Brands',                   'brands');

        // -- Categories --
        $addCrud('mega_categories',       'Mega Categories',          'mega_categories');
        $addCrud('sub_categories',        'Sub Categories',           'sub_categories');
        $addCrud('mini_categories',       'Mini Categories',          'mini_categories');
        $addCrud('extra_categories',      'Extra Categories',         'extra_categories');

        // -- Attributes --
        $addCrud('attribute_groups',      'Attribute Groups',         'attribute_groups');
        $addCrud('attribute_values',      'Attribute Values',         'attribute_values');

        // ==========================================
        // 5. Warehouses
        // ==========================================
        $addCrud('warehouses',            'Warehouses',               'multi_warehouse');
        $addCrud('warehouse_areas',       'Warehouse Areas',          'multi_warehouse');
        $addCrud('warehouse_racks',       'Warehouse Racks',          'multi_warehouse');
        $addCrud('warehouse_cells',       'Warehouse Cells',          'multi_warehouse');
        $addCrud('warehouse_bins',        'Warehouse Bins',           'multi_warehouse');

        // ==========================================
        // 6. Inventory
        // ==========================================
        $addViewOnly('inventory_overview',  'Inventory Overview',       'inventory_overview');
        $addCrud('inventory_movements',     'Inventory Movements',      'inventory_movements');
        $addCrud('inventory_adjustments',   'Inventory Adjustments',    'inventory_adjustments');
        $addCrud('inventory_audits',        'Inventory Audits',         'inventory_audits');

        // ==========================================
        // 7. Landing Page
        // ==========================================
        $addCrud('landing_page',            'Landing Page',             'landing_page_builder');

        // ==========================================
        // 8. Pricing
        // ==========================================
        $addCrud('pricing_packages',        'Pricing Packages',         'pricing_packages');

        // ==========================================
        // 9. Marketing
        // ==========================================
        $addCrud('marketing_coupons',       'Marketing Coupons',        'marketing_coupons');
        $addCrud('marketing_email',         'Marketing Email',          'email_marketing');
        $addCrud('marketing_sms',           'Marketing SMS',            'sms_marketing');

        // ==========================================
        // 10. CMS
        // ==========================================
        $addCrud('cms_pages',               'CMS Pages',                'cms_pages');
        $addCrud('cms_sliders',             'CMS Sliders',              'cms_sliders');
        $addCrud('cms_blogs',               'CMS Blogs',                'cms_blogs');
        $addCrud('social_settings',         'Social Settings',          'social_settings');

        // ==========================================
        // 11. Accounting
        // ==========================================
        $addCrud('account_groups',          'Account Groups',           'accounting_module');
        $addCrud('account_charts',          'Account Charts',           'accounting_module');
        $addCrud('account_expenses',        'Account Expenses',         'accounting_module');
        $addCrud('account_incomes',         'Account Incomes',          'accounting_module');
        $addCrud('account_transfers',       'Account Transfers',        'accounting_module');

        // ==========================================
        // 12. HRM
        // ==========================================
        $addCrud('hrm_departments',         'HRM Departments',          'hrm_module');
        $addCrud('hrm_employees',           'HRM Employees',            'hrm_module');
        $addCrud('hrm_attendances',         'HRM Attendances',          'hrm_module');
        $addCrud('hrm_holidays',            'HRM Holidays',             'hrm_module');
        $addCrud('hrm_holidays',            'HRM Holidays',             'hrm_module');
        $addCrud('hrm_resign_rules',        'HRM Resign Rules',         'hrm_module');
        $addCrud('hrm_salaries',            'HRM Salaries',             'hrm_module');
        $addCrud('hrm_payroll',             'HRM Payroll',              'hrm_module');
        $addCrud('hrm_payslips',            'HRM Payslips',             'hrm_module');

        // ==========================================
        // 13. Reports
        // ==========================================
        $addViewOnly('report_sales',        'Report Sales',             'report_sales');
        $addViewOnly('report_pos',          'Report POS',               'pos_module');
        $addViewOnly('report_customers',    'Report Customers',         'report_customers');
        $addViewOnly('report_suppliers',    'Report Suppliers',         'report_suppliers');
        $addViewOnly('report_purchase',     'Report Purchase',          'report_purchase');
        $addViewOnly('report_stock',        'Report Stock',             'report_stock');
        $addViewOnly('report_profit_loss',  'Report Profit & Loss',     'accounting_module');
        $addViewOnly('report_audit_logs',   'Report Audit Logs',        'report_audit_logs');

        // ==========================================
        // 14. Support Desk
        // ==========================================
        $addCrud('support_departments',     'Support Departments',      'support_module');
        $addCrud('support_tickets',         'Support Tickets',          'support_module');
        $addCrud('support_kb',              'Support Knowledge Base',   'support_module');

        // ==========================================
        // 15. Assets
        // ==========================================
        $addCrud('assets',                  'Assets',                   'asset_management');
        $addCrud('asset_disposal',          'Asset Disposal',           'asset_management');

        // ==========================================
        // 16. Users
        // ==========================================
        $addCrud('users',                   'Users',                    'users');

        // ==========================================
        // 17. Security
        // ==========================================
        $addViewOnly('security_login_history', 'Security Login History', 'security_login_history');
        $addViewOnly('security_activity_logs', 'Security Activity Logs', 'security_activity_logs');

        // ==========================================
        // 18. Tax & VAT
        // ==========================================
        $addCrud('tax_rates',               'Tax Rates',                'tax_rates');
        $addCrud('tax_groups',              'Tax Groups',               'tax_groups');

        // ==========================================
        // 19. Leads (CRM)
        // ==========================================
        $addCrud('lead_status',             'Lead Status',              'crm_module');
        $addCrud('lead_sources',            'Lead Sources',             'crm_module');
        $addCrud('leads',                   'Leads',                    'crm_module');

        // ==========================================
        // 20. Settings
        // ==========================================
        $addCrud('site_settings',           'Site Settings',            'site_settings');
        $addCrud('settings_courier',        'Courier Settings',         'settings_courier');
        $addCrud('settings_payment',        'Payment Settings',         'settings_payment');
        $addCrud('settings_roles',          'Role Settings',            'settings_roles');
        $addCrud('settings_domain',         'Domain Settings',          'custom_domain');
        $addCrud('settings_ip',             'IP Restriction Settings',  'ip_restriction');
        $addCrud('woocommerce_integration', 'WooCommerce Integration',  'woocommerce_sync');
        $addCrud('settings_sms',            'SMS Settings',             'settings_sms');
        $addCrud('settings_templates',      'Template Settings',        'settings_templates');

        Permission::insert($permissionsToInsert);
    }
}
