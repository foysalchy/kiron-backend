<?php

namespace App\Services;

use App\Helpers\LogHelper;
use App\Models\Domain;
use App\Models\DomainSetup;
use Illuminate\Support\Facades\{DB, Http, Log};

class DomainSetupService
{
    private string $apiToken   = 'iF9aNbBfaKiT3WtsdZSkUiAEoMEnt2Q6aCFlYWUv';
    private string $zoneId     = 'ac7c72e0460207f900a635fb32ae43cf';
    private string $ipAddress  = '134.209.65.214';
    private string $baseDomain = 'doob.com.bd';

    public function getDomain()
    {
        return DomainSetup::first();
    }
    public function multiDomain()
    {
        return Domain::all();
    }
    public function deleteDomain($id)
    {
        return Domain::find($id)->delete();
    }

    public function saveDomain(array $data)
    {
        if (!empty($data['custom_domain'])) {
            $check = $this->zoneCheck($data['custom_domain']);
            if (!$check) {
                return "{$data['custom_domain']} is not pointing to our server IP ({$this->ipAddress}).";
            }
        }

        DB::beginTransaction();
        try {
            $domain = DomainSetup::updateOrCreate(
                [],
                [
                    'custom_domain' => $data['custom_domain'] ?? null,
                    'sub_domain'    => $data['sub_domain'] ?? null,
                    'status'        => $data['status'] ?? 1,
                ]
            );

            Log::info('Domain saved', ['domain' => $domain]);

            if (!empty($data['sub_domain'])) {
                $cfResult = $this->createCloudflareDnsRecord($data['sub_domain']);
                if (!$cfResult['success']) {
                    DB::rollBack();
                    Log::error('Cloudflare DNS failed', ['errors' => $cfResult['errors'] ?? []]);
                    return 'Failed to create Cloudflare DNS record.';
                }
            }

            $action = $domain->wasRecentlyCreated ? 'created' : 'updated';
            LogHelper::$action('domain_setup', $domain->id, $domain->company_id, 'Domain updated to: ' . $domain->custom_domain);

            DB::commit();
            return $domain;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Domain saving failed: ' . $e->getMessage());
            return 'Failed to save domain settings.';
        }
    }
    public function saveMultiDomain(array $data)
    {
        if (!empty($data['domain'])) {
            $check = $this->zoneCheck($data['domain']);
            if (!$check) {
                return [
                    'success' => false,
                    'message' => "{$data['domain']} is not pointing to our server IP ({$this->ipAddress}).",
                ];
            }
        }

        DB::beginTransaction();
        try {
          

            $domain = Domain::create($data);

            LogHelper::created(
                'domain',
                $domain->id,
                $domain->company_id, 
                'Domain created: ' . $domain->domain             
            );

            DB::commit();

            Log::info('Domain saved', ['domain' => $domain]);

            return [
                'success' => true,
                'data'    => $domain,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Domain saving failed: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to save domain settings.',
            ];
        }
    }

    private function createCloudflareDnsRecord(string $subDomain): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiToken,
            'Content-Type'  => 'application/json',
        ])->post("https://api.cloudflare.com/client/v4/zones/{$this->zoneId}/dns_records", [
            'type'    => 'A',
            'name'    => "{$subDomain}.{$this->baseDomain}",
            'content' => $this->ipAddress,
            'ttl'     => 3600,
            'proxied' => false,
        ]);

        $result = $response->json();
        Log::info('Cloudflare response', ['response' => $result]);

        if (!empty($result['errors'])) {
            foreach ($result['errors'] as $error) {
                if ($error['code'] === 81058) {
                    Log::info('Cloudflare DNS record already exists, skipping.');
                    return ['success' => true];
                }
            }
        }

        return $result ?? ['success' => false, 'errors' => ['No response from Cloudflare']];
    }

    private function zoneCheck(string $domain): bool
    {
        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
            ])->get("https://dns.google/resolve", [
                'name' => $domain,
                'type' => 'A',
            ]);

            $result = $response->json();

            Log::info('DNS Check', [
                'domain'   => $domain,
                'result'   => $result,
                'targetIp' => $this->ipAddress,
            ]);

            if (empty($result['Answer'])) {
                return false;
            }

            foreach ($result['Answer'] as $record) {
                if ($record['type'] === 1 && $record['data'] === $this->ipAddress) {
                    return true;
                }
            }

            return false;
        } catch (\Exception $e) {
            Log::error('DNS Check failed: ' . $e->getMessage());
            return false;
        }
    }
}
