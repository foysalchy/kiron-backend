<?php 
namespace App\Services;

use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\FirebaseSetting;
use Illuminate\Support\Facades\{DB,Log};

class FirebaseSettingService
{
    /**
     * Get Firebase settings for the current company
     */
    public function getFirebaseSettings(): ?FirebaseSetting
    {
        try {
            return FirebaseSetting::first();
        } catch (\Exception $e) {
            Log::error('Error fetching firebase settings: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch firebase settings');        }
    }
    /**
     * Save or Update Firebase configuration
     */
    public function saveFirebaseSettings(array $data): FirebaseSetting
    {
        DB::beginTransaction();
        try {
            $firebaseSetting = FirebaseSetting::updateOrCreate(
                [],
                [
                    'email'               => $data['email'],
                    'api_key'             => $data['api_key'],
                    'auth_domain'         => $data['auth_domain'],
                    'project_id'          => $data['project_id'],
                    'storage_bucket'      => $data['storage_bucket'],
                    'messaging_sender_id' => $data['messaging_sender_id'],
                    'app_id'              => $data['app_id'],
                    'measurement_id'      => $data['measurement_id'] ?? null,
                    'google_auth'         => $data['google_auth'] ?? 0,
                    'facebook_auth'       => $data['facebook_auth'] ?? 0,
                    'status'              => $data['status'] ?? 1,
                ]
            );

            $action = $firebaseSetting->wasRecentlyCreated ? 'created' : 'updated';
            LogHelper::$action('firebase_setting', $firebaseSetting->id, $firebaseSetting->company_id, 'Firebase SDK ' . $action);

            Log::info("Firebase settings {$action} successfully for company ID: " . $firebaseSetting->company_id);

            DB::commit();
            return $firebaseSetting->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Firebase settings save failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to save firebase settings');
        }
    }
}