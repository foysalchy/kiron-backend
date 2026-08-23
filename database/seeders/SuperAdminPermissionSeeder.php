<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SuperAdminPermissionSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Permission::where('type', 'superadmin')->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $permissions = [];

        $addCrud = function ($prefix, $groupName) use (&$permissions) {
            foreach (['view', 'create', 'edit', 'delete'] as $action) {
                $permissions[] = [
                    'name'               => "{$prefix}.{$action}",
                    'group_name'         => $groupName,
                    'feature_dependency' => null,
                    'type'               => 'superadmin',
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ];
            }
        };

        $addViewOnly = function ($prefix, $groupName) use (&$permissions) {
            $permissions[] = [
                'name'               => "{$prefix}.view",
                'group_name'         => $groupName,
                'feature_dependency' => null,
                'type'               => 'superadmin',
                'created_at'         => now(),
                'updated_at'         => now(),
            ];
        };

        // ── Seller Management ──────────────────────────────
        $addCrud('sellers',                  'Seller Management');

        // ── Leads ──────────────────────────────────────────
        $addCrud('lead_status',              'Lead Status');
        $addCrud('lead_sources',             'Lead Sources');
        $addCrud('leads',                    'Leads');

        // ── Support Desk ───────────────────────────────────
        $addCrud('support_departments',      'Support Departments');
        $addCrud('support_tickets',          'Support Tickets');
        $addCrud('support_kb',               'Support Knowledge Base');

        // ── Users ──────────────────────────────────────────
        $addCrud('users',                    'Users');

        // ── HRM & Payroll ──────────────────────────────────
        $addCrud('hrm_departments',          'HRM Departments');
        $addCrud('hrm_employees',            'HRM Employees');
        $addCrud('hrm_attendances',          'HRM Attendances');
        $addCrud('hrm_holidays',             'HRM Holidays');
        $addCrud('hrm_resign_rules',         'HRM Resign Rules');
        $addCrud('hrm_salaries',             'HRM Salaries');
        $addCrud('hrm_payroll',              'HRM Payroll');
        $addCrud('hrm_payslips',             'HRM Payslips');

        // ── CMS / Content ──────────────────────────────────
        $addCrud('cms_pages',                'CMS Pages');
        $addCrud('cms_sliders',              'CMS Sliders');
        $addCrud('cms_blogs',                'CMS Blogs');
        $addCrud('social_settings',          'Social Settings');

        // ── Security ───────────────────────────────────────
        $addViewOnly('security_login_history', 'Login History');
        $addViewOnly('security_activity_logs', 'Activity Logs');

        // ── Pricing ────────────────────────────────────────
        $addCrud('pricing_packages',         'Pricing Packages');
        $addCrud('master_brands',         'Master Brand');
        $addCrud('master_demos',         'Master Demos');
        $addCrud('master_features',         'Master Features');

        $addCrud('omni',                   'Omni Channel');
        $addCrud('omni_setting',                   'Omni Setting');
        $addCrud('omni_channel',                   'Omni Channel');

        // ── Marketing ──────────────────────────────────────
        $addCrud('marketing_coupons',        'Marketing Coupons');
        $addCrud('super_admin_email',          'Marketing Email');
        $addCrud('super_admin_sms',            'Marketing SMS');

        // ── Settings (courier ❌ themes ❌ removed) ─────────
        $addCrud('site_settings',            'Site Settings');
        $addCrud('settings_content',      'Content Settings');
        $addCrud('settings_payment',         'Payment Settings');
        $addCrud('settings_ip',              'IP Restriction Settings');
        $addCrud('settings_theme',           'Theme Settings');
        $addCrud('settings_meta',           'Meta Settings');
        $addCrud('settings_menu',           'Menu Settings');
        $addCrud('settings_marketing',      'Marketing Settings');
        $addCrud('settings_ai',             'AI Integration Settings');

        // ── Roles & Permissions ────────────────────────────
        $addCrud('settings_roles',           'Roles');
        $addViewOnly('permissions',          'Permissions');

        Permission::insert($permissions);
    }
}
