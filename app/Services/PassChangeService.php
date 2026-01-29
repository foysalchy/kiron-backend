<?php
namespace App\Services;

use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\PassChange;
use Illuminate\Support\Facades\{DB, Hash, Log};

class PassChangeService
{
    /**
     * Update Password (ID-less approach)
     */
    public function updatePass(array $data):PassChange
    {
        DB::beginTransaction();
        try {
            $password = PassChange::first();

            $hashedData = $data;
            $hashedData['new_password'] = Hash::make($data['new_password']);

            if (!$password) {
                $password = PassChange::create($hashedData);
            } else {
                if (!Hash::check($data['current_password'], $password->new_password)) {
                    throw ApiException::forbidden('Current password does not match.');
                }
                $password->update($hashedData);
            }

            LogHelper::updated('password', $password->id, $password->company_id, 'Password updated');
            DB::commit();
            return $password;

        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Actual Error: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update password');
        }
    }
}
