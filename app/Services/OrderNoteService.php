<?php

namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\Area;
use App\Models\OrderNote;
use App\Models\Warehouse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderNoteService
{
    /**
     * Get all order note with optional pagination
     */
    public function getAllOrderNote(array $filters, bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = OrderNote::query();


            if (isset($filters['status'])) {
                if ($filters['status'] == Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
            }

            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('note', 'like', "%{$search}%");
                });
            }

            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate ? $query->paginate($filters['per_page'] ?? 15) : $query->get();
        } catch (\Throwable $e) {
            Log::error('Error fetching areas: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch areas');
        }
    }

    /**
     * Get order note by ID
     */
    public function getOrderNoteById(int $id): OrderNote
    {
        $note = OrderNote::find($id);
        if (!$note) {
            throw ApiException::notFound('order note');
        }
        return $note;
    }

    /**
     * Create a new order note
     */
    public function createOrderNote(array $data): OrderNote
    {
        \Log::info($data);
        DB::beginTransaction();
        try {
            $note = OrderNote::create($data);
            LogHelper::created('order_note', $note->id, $note->company_id, $note->name);
            DB::commit();
            Log::info('Order note created successfully', ['order_note_id' => $note->id]);

            return $note;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order Note creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create order note');
        }
    }


    /**
     * Update order note
     */
    public function updateOrderNote(int $id, array $data): OrderNote
    {
        DB::beginTransaction();
        try {
            $note = $this->getOrderNoteById($id);

            $note->update($data);

            LogHelper::updated('order_note', $note->id, $note->company_id, $note->name);
            DB::commit();
            Log::info('Area Updated Successfully', ['order_note_id' => $note->id]);

            return $note;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order note update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update order note');
        }
    }
    /**
     * Delete order note (soft delete)
     */
    public function deleteOrderNote(int $id): bool
    {
        DB::beginTransaction();
        try {
            $note = $this->getOrderNoteById($id);

            $note->delete();

            LogHelper::deleted('order_note', $note->id, $note->company_id, $note->name);
            DB::commit();
            Log::info('Order Note deleted successfully', ['area_id' => $id]);

            return true;
        } catch (ApiException $e) {
            throw $e;
            DB::rollBack();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order Not  deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete order Note');
        }
    }
}
