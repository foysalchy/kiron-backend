<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Mail\PasswordChangedMail;
use App\Models\User;
use Illuminate\Support\Facades\{DB, Hash, Log, Mail};
use Illuminate\Support\Str;

class UserPasswordService
{
    public function changePassword(User $user, string $method): array
    {
        DB::beginTransaction();
        try {
            $newPassword = Str::random(10); // e.g. "aZ8kLm2Qtx"

            $user->update([
                'password' => Hash::make($newPassword),
            ]);

            if ($method === 'email') {
                $this->sendViaEmail($user, $newPassword);
            } else {
                $this->sendViaSms($user, $newPassword);
            }

            LogHelper::updated('user', $user->id, $user->company_id, 'Password changed via ' . $method);
            Log::info("Password changed for user ID {$user->id} via {$method}");

            DB::commit();

            return [
                'user_id' => $user->id,
                'method'  => $method,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Password change failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to change password');
        }
    }

    protected function sendViaEmail(User $user, string $newPassword): void
    {
        if (empty($user->email)) {
            throw ApiException::badRequest('User has no email address');
        }

        Mail::to($user->email)->send(new PasswordChangedMail($user, $newPassword));
    }

    protected function sendViaSms(User $user, string $newPassword): void
    {
        if (empty($user->phone)) {
            throw ApiException::badRequest('User has no phone number');
        }

        $message = "Your new password is: {$newPassword}. Please change it after login.";

        app(SmsSendService::class)->sendToGateway([$user->phone], $message);
    }
}
