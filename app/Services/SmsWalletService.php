<?php

namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\FileUploadHelper;
use App\Models\{SmsWallet, SmsPackage, SmsRecharge, SmsSend, SmsWalletTransaction};
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpParser\Node\Stmt\StaticVar;

class SmsWalletService
{
    public function getOrCreate(): SmsWallet
    {
        return SmsWallet::firstOrCreate(
            [], // ✅ company scope already filter করে
            ['sms_count' => 0]
        );
    }

    // Recharge request create
    public function requestRecharge(array $data): SmsRecharge
    {
        DB::beginTransaction();
        try {
            $package = SmsPackage::findOrFail($data['sms_package_id']);

            // ✅ Screenshot upload
            $screenshotPath = null;
            if (isset($data['screenshot'])) {
                $screenshotPath = FileUploadHelper::uploadImage(
                    $data['screenshot'],
                    'sms_recharges/screenshots'
                );
            }

            $recharge = SmsRecharge::create([
                'sms_package_id' => $package->id,
                'reference_no'   => $this->generateReferenceNo(),
                'sms_count'      => $package->sms_count,
                'price'          => $package->price,
                'rate_per_sms'   => $package->rate_per_sms,
                'payment_method' => $data['payment_method'],
                'transaction_id' => $data['transaction_id'] ?? null,
                'account_number' => $data['account_number'] ?? null,
                'note'           => $data['note'] ?? null,
                'screenshot'     => $screenshotPath,
                'status'         => Status::Pending->value,
            ]);

            DB::commit();
            return $recharge;
        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($screenshotPath)) {
                FileUploadHelper::delete($screenshotPath);
            }
            Log::error('Recharge request failed: ' . $e->getMessage());

            throw ApiException::serverError('Failed to submit recharge request');
        }
    }

    // Super admin approve/reject
    public function processRecharge(int $rechargeId, array $data, int $adminId): SmsRecharge
    {
        DB::beginTransaction();
        try {
            $recharge = SmsRecharge::findOrFail($rechargeId);

            if ($recharge->status != Status::Pending->value) {
                throw ApiException::badRequest('Recharge already processed');
            }

            $status = (int) $data['status'];

            $recharge->update([
                'status'        => $status,
                'reject_reason' => $data['reject_reason'] ?? null,
                'approved_by'   => $adminId,
                'approved_at'   => now(),
            ]);

            // Approve হলে wallet এ add করো 
            if ($status == Status::Approved->value) {
                $wallet = $this->getOrCreate();
                $balanceBefore = $wallet->sms_count;
                $balanceAfter  = $balanceBefore + $recharge->sms_count;

                $wallet->update(['sms_count' => $balanceAfter]);

                // Transaction log
                SmsWalletTransaction::create([
                    'company_id'    => $recharge->company_id,
                    'sms_wallet_id' => $wallet->id,
                    'type'          => 'recharge',
                    'sms_count'     => $recharge->sms_count,
                    'rate_per_sms'  => $recharge->rate_per_sms,
                    'reference_type' => SmsRecharge::class,
                    'reference_id'  => $recharge->id,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balanceAfter,
                    'note'          => 'Recharge approved',
                ]);
            }

            DB::commit();
            return $recharge;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Recharge process failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to process recharge');
        }
    }

    // SMS send এ balance deduct
    public function deductBalance(int $smsCount, int $smsSendId, float $ratePerSms): void
    {
        DB::beginTransaction();
        try {
            $wallet = $this->getOrCreate();

            if (!$wallet->hasSufficientBalance($smsCount)) {
                throw ApiException::badRequest('Insufficient SMS balance');
            }

            $balanceBefore = $wallet->sms_count;
            $balanceAfter  = $balanceBefore - $smsCount;

            $wallet->update(['sms_count' => $balanceAfter]);

            SmsWalletTransaction::create([

                'sms_wallet_id' => $wallet->id,
                'type'          => 'deduct',
                'sms_count'     => $smsCount,
                'rate_per_sms'  => $ratePerSms,
                'reference_type' => SmsSend::class,
                'reference_id'  => $smsSendId,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'note'          => 'SMS sent',
            ]);

            DB::commit();
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SMS balance deduct failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to deduct SMS balance');
        }
    }

    private function generateReferenceNo(): string
    {
        $last = SmsRecharge::withTrashed()->latest()->first();
        $next = $last ? (int) substr($last->reference_no, 4) + 1 : 1;
        return 'SMS-' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
