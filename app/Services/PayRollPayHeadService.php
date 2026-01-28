<?php

namespace App\Services;

use App\Models\PayRollPayHead;
use App\Models\PayHead;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use Illuminate\Support\Facades\{DB, Log};

class PayRollPayHeadService
{
    /**
     * Get all payroll pay heads with calculations based on UI
     */
    public function getPayRollSummary(int $payRollId): array
    {
        try {
            $items = PayRollPayHead::with(['payHead', 'payRoll'])
                ->where('pay_roll_id', $payRollId)
                ->get();

            $totalAddition = 0;
            $totalDeduction = 0;

            foreach ($items as $item) {
                if ($item->payHead && $item->payHead->type === 'addition') {
                    $totalAddition += (float) $item->amount;
                } elseif ($item->payHead && $item->payHead->type === 'deduction') {
                    $totalDeduction += (float) $item->amount;
                }
            }

            return [
                'items'           => $items,
                'total_addition'  => $totalAddition,
                'total_deduction' => $totalDeduction,
                'grand_total'     => $totalAddition - $totalDeduction,
            ];
        } catch (\Exception $e) {
            Log::error('Error fetching summary: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch summary');
        }
    }

    /**
     * Get Record by ID
     */
    public function getById(int $id): PayRollPayHead
    {

        $record = PayRollPayHead::find($id);

        if (!$record) {
            throw ApiException::notFound('Payroll Pay Head Record');
        }

        return $record;
    }

    /**
     * Create/Assign a new Pay Head using Name conversion
     */
    public function create(array $data): PayRollPayHead
    {
        DB::beginTransaction();
        try {
            $payHead = PayHead::where('name', $data['pay_head_name'])->first();

            if (!$payHead) {
                throw ApiException::notFound('Pay Head with this name');
            }

            $data['pay_head_id'] = $payHead->id;
            unset($data['pay_head_name']);

            $record = PayRollPayHead::create($data);
            LogHelper::created('payRollPayHead', $record->id, $record->company_id,$record->type . ' amount '. $record->amount);

            DB::commit();
            return $record->load('payHead', 'payRoll');
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Assigning failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to assign pay head');
        }
    }

    /**
     * Update Assigned Pay Head
     */
    public function update(int $id, array $data): PayRollPayHead
    {
        DB::beginTransaction();
        try {
            $record = $this->getById($id);

            if (isset($data['pay_head_name'])) {
                $payHead = PayHead::where('name', $data['pay_head_name'])
                    ->where('company_id', $record->company_id)
                    ->first();

                if ($payHead) {
                    $data['pay_head_id'] = $payHead->id;
                }
                unset($data['pay_head_name']);
            }

            $record->update($data);
            LogHelper::updated('payRollPayHead', $record->id, $record->company_id,$record->type . ' amount '. $record->amount);

            DB::commit();
            return $record->fresh(['payHead', 'payRoll']);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update record');
        }
    }

    /**
     * Soft Delete
     */
    public function delete(int $id): bool
    {
        DB::beginTransaction();
        try {
            $record = $this->getById($id);
            $record->delete();
            LogHelper::deleted('payRollPayHead', $record->id, $record->company_id,$record->type . ' amount '. $record->amount);

            DB::commit();
            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to remove pay head');
        }
    }

    /**
     * Restore Soft Deleted Record
     */
    public function restore(int $id): PayRollPayHead
    {
        DB::beginTransaction();
        try {
            $record = $this->getById($id);
            $record->restore();
            LogHelper::restored('payRollPayHead', $record->id, $record->company_id,$record->type . ' amount '. $record->amount);

            DB::commit();
            return $record;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            throw ApiException::serverError('Failed to restore record');
        }
    }

    /**
     * Permanently Delete Record
     */
    public function forceDelete(int $id): bool
    {
        DB::beginTransaction();
        try {
            $record = $this->getById($id);
            $record->forceDelete();
            LogHelper::forceDeleted('payRollPayHead', $id, $record->company_id,$record->type . ' amount '. $record->amount);

            DB::commit();
            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            throw ApiException::serverError('Failed to permanently delete record');
        }
    }
}
