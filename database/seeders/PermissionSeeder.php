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

        $addCrud = function ($prefix, $groupName, $feature = null) use (&$permissionsToInsert) {
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

        $addViewOnly = function ($prefix, $groupName, $feature = null) use (&$permissionsToInsert) {
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
        $addCrud('customers',             'Customers');
        $addCrud('suppliers',             'Suppliers');
        $addCrud('customer_groups',       'Customer Groups');
        $addCrud('parties_import_export', 'Parties Import & Export');

        // ==========================================
        // 2. Purchase
        // ==========================================
        $addCrud('purchases',             'Purchases');
        $addCrud('requisitions',          'Requisitions');
        $addCrud('purchase_returns',      'Purchase Returns');

        // ==========================================
        // 3. Orders
        // ==========================================
        $addCrud('orders',                'Orders');
        $addCrud('sales_returns',         'Sales Returns');
        $addCrud('quotations',            'Quotations');
        $addViewOnly('incomplete_orders', 'Incomplete Orders');
        $addCrud('pos',                   'POS',                      'pos_module');

        // ==========================================
        // 4. Products
        // ==========================================
        $addCrud('products',              'Products');
        $addCrud('product_groups',        'Product Groups');
        $addCrud('brands',                'Brands');

        // -- Categories --
        $addCrud('mega_categories',       'Mega Categories');
        $addCrud('sub_categories',        'Sub Categories');
        $addCrud('mini_categories',       'Mini Categories');
        $addCrud('extra_categories',      'Extra Categories');

        // -- Attributes --
        $addCrud('attribute_groups',      'Attribute Groups');
        $addCrud('attribute_values',      'Attribute Values');

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
        $addViewOnly('inventory_overview',  'Inventory Overview');
        $addCrud('inventory_movements',     'Inventory Movements');
        $addCrud('inventory_adjustments',   'Inventory Adjustments');
        $addCrud('inventory_audits',        'Inventory Audits');

        // ==========================================
        // 7. Landing Page
        // ==========================================
        $addCrud('landing_page',            'Landing Page',             'landing_page_builder');

        // ==========================================
        // 8. Pricing
        // ==========================================
        $addCrud('pricing_packages',        'Pricing Packages');

        // ==========================================
        // 9. Marketing
        // ==========================================
        $addCrud('marketing_coupons',       'Marketing Coupons');
        $addCrud('marketing_email',         'Marketing Email',          'email_marketing');
        $addCrud('marketing_sms',           'Marketing SMS',            'sms_marketing');

        // ==========================================
        // 10. CMS
        // ==========================================
        $addCrud('cms_pages',               'CMS Pages');
        $addCrud('cms_sliders',             'CMS Sliders');
        $addCrud('cms_blogs',               'CMS Blogs');
        $addCrud('social_settings',         'Social Settings');

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
        $addCrud('hrm_resign_rules',        'HRM Resign Rules',         'hrm_module');

        // -- Payroll --
        $addCrud('hrm_salaries',            'HRM Salaries',             'hrm_module');
        $addCrud('hrm_payroll',             'HRM Payroll',              'hrm_module');
        $addCrud('hrm_payslips',            'HRM Payslips',             'hrm_module');

        // ==========================================
        // 13. Reports
        // ==========================================
        $addViewOnly('report_sales',        'Report Sales');
        $addViewOnly('report_pos',          'Report POS',               'pos_module');
        $addViewOnly('report_customers',    'Report Customers');
        $addViewOnly('report_suppliers',    'Report Suppliers');
        $addViewOnly('report_purchase',     'Report Purchase');
        $addViewOnly('report_stock',        'Report Stock');
        $addViewOnly('report_profit_loss',  'Report Profit & Loss',     'accounting_module');
        $addViewOnly('report_audit_logs',   'Report Audit Logs');

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
        $addCrud('users',                   'Users');

        // ==========================================
        // 17. Security
        // ==========================================
        $addViewOnly('security_login_history', 'Security Login History');
        $addViewOnly('security_activity_logs', 'Security Activity Logs');

        // ==========================================
        // 18. Tax & VAT
        // ==========================================
        $addCrud('tax_rates',               'Tax Rates');
        $addCrud('tax_groups',              'Tax Groups');

        // ==========================================
        // 19. Leads (CRM)
        // ==========================================
        $addCrud('lead_status',             'Lead Status',              'crm_module');
        $addCrud('lead_sources',            'Lead Sources',             'crm_module');
        $addCrud('leads',                   'Leads',                    'crm_module');

        // ==========================================
        // 20. Settings
        // ==========================================
        $addCrud('site_settings',           'Site Settings');
        $addCrud('settings_courier',        'Courier Settings');
        $addCrud('settings_payment',        'Payment Settings');
        $addCrud('settings_roles',          'Role Settings');
        $addCrud('settings_domain',         'Domain Settings',          'custom_domain');
        $addCrud('settings_ip',             'IP Restriction Settings',  'ip_restriction');
        $addCrud('woocommerce_integration', 'WooCommerce Integration',  'woocommerce_sync');
        $addCrud('settings_sms',            'SMS Settings');
        $addCrud('settings_templates',      'Template Settings');

        // Insert all permissions at once
        Permission::insert($permissionsToInsert);
    }
}
