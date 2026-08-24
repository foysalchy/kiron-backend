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
 public function updatePass(array $data)
{
    DB::beginTransaction();
    try {
        $user = auth()->user();

        if (!Hash::check($data['current_password'], $user->password)) {
            throw ApiException::forbidden('Current password does not match.');
        }

        $user->update([
            'password' => Hash::make($data['new_password']),
        ]);

        LogHelper::updated('user', $user->id, $user->company_id, 'Password updated');

        DB::commit();
        return $user;

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
