<?php
namespace App\Services;

use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\DomainSetup;
use Illuminate\Support\Facades\{DB,Log};

class DomainSetupService
{
    public function getDomain()
    {
        return DomainSetup::first();
    }
    public function saveDomain(array $data): DomainSetup
    {
        DB::beginTransaction();
        try {
            $domain = DomainSetup::updateOrCreate(
                [],
                [
                    'custom_domain' => $data['custom_domain'],
                    'status'        => $data['status'] ?? 1,
                ]
            );

            $action = $domain->wasRecentlyCreated ? 'created' : 'updated';
            LogHelper::$action('domain_setup', $domain->id, $domain->company_id, 'Domain updated to: ' . $domain->custom_domain);

            DB::commit();
            return $domain;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Domain saving failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to save domain settings');
        }
    }
}
