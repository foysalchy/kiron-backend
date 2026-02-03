<?php
namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\FileUploadHelper;
use App\Helpers\LogHelper;
use App\Models\SupportTicket;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB,Log};

class SupportTicketService
{
    /**
     * Get all tickets with optional pagination
     */
    public function getAllTickets(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = SupportTicket::with(['supportDepartment', 'user']);

            if (!empty($filters['date_filter'])) {
                $fromDate = null;
                $toDate = now()->format('Y-m-d');

                switch ($filters['date_filter']) {
                    case 'Today':
                        $fromDate = now()->format('Y-m-d');
                        break;
                    case 'Yesterday':
                        $fromDate = now()->subDay()->format('Y-m-d');
                        $toDate = $fromDate;
                        break;
                    case 'Last 7 Days':
                        $fromDate = now()->subDays(7)->format('Y-m-d');
                        break;
                    case 'Last 30 Days':
                        $fromDate = now()->subDays(30)->format('Y-m-d');
                        break;
                    case 'This Month':
                        $fromDate = now()->startOfMonth()->format('Y-m-d');
                        break;
                    case 'Last Month':
                        $fromDate = now()->subMonth()->startOfMonth()->format('Y-m-d');
                        $toDate = now()->subMonth()->endOfMonth()->format('Y-m-d');
                        break;
                    case 'Custom Range':
                        $fromDate = $filters['from_date'] ?? null;
                        $toDate = $filters['to_date'] ?? now()->format('Y-m-d');
                        break;
                }

                if ($fromDate) {
                    $query->whereBetween('created_at', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59']);
                }
            }

            if (isset($filters['status']) && $filters['status'] !== 'all') {
                if ($filters['status'] == Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
            }

            if (!empty($filters['support_department_id']) && $filters['support_department_id'] !== 'all') {
                $query->where('support_department_id', $filters['support_department_id']);
            }

            if (!empty($filters['subject'])) {
                $query->where('subject', 'like', "%{$filters['subject']}%");
            }

            $query->orderBy($filters['sort_by'] ?? 'created_at', $filters['sort_order'] ?? 'desc');

            return $paginate ? $query->paginate($filters['per_page'] ?? 10) : $query->get();
        } catch (\Exception $e) {
            Log::error('Error fetching tickets: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch tickets');
        }
    }

    /**
     * Get ticket by ID
     */
    public function getTicketById(int $id): SupportTicket
    {
        $ticket = SupportTicket::with('supportDepartment','user')->find($id);

        if (!$ticket) {
            throw ApiException::notFound('Support Ticket');
        }

        return $ticket;
    }

    /**
     * Create a new ticket
     */
    public function createTicket(array $data): SupportTicket
    {
        DB::beginTransaction();

        try {
            if (isset($data['image'])) {
                $data['image'] = FileUploadHelper::uploadImage(
                    $data['image'],
                    'tickets/attachments',
                    'public',
                    2048
                );
            }
            $data['user_id'] = auth()->id();
            $ticket = SupportTicket::create($data);
            LogHelper::created('support_ticket', $ticket->id, $ticket->company_id,$ticket->subject);

            DB::commit();
            Log::info('Ticket created successfully', ['ticket_id' => $ticket->id]);

            return $ticket;
        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($data['image'])) {
                FileUploadHelper::delete($data['image']);
            }
            Log::error('Ticket creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create ticket');
        }
    }

    /**
     * Update ticket
     */
    public function updateTicket(int $id, array $data): SupportTicket
    {
        DB::beginTransaction();

        try {
            $ticket = $this->getTicketById($id);

            if (isset($data['image'])) {
                $data['image'] = FileUploadHelper::replace(
                    $data['image'],
                    $ticket->image,
                    'tickets/attachments'
                );
            }

            $ticket->update($data);
            LogHelper::updated('support_ticket', $ticket->id, $ticket->company_id,$ticket->subject);

            DB::commit();
            Log::info('Ticket updated successfully', ['ticket_id' => $ticket->id]);

            return $ticket;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($data['image'])) {
                FileUploadHelper::delete($data['image']);
            }
            Log::error('Ticket update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update ticket');
        }
    }

    /**
     * Delete ticket (soft delete)
     */
    public function deleteTicket(int $id): bool
    {
        DB::beginTransaction();
        try {
            $ticket = $this->getTicketById($id);
            $ticket->delete();
            LogHelper::deleted('support_ticket', $ticket->id, $ticket->company_id,$ticket->subject);
            DB::commit();
            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            throw ApiException::serverError('Failed to delete ticket');
        }
    }

    /**
     * Restore soft deleted ticket
     */
    public function restoreTicket(int $id): SupportTicket
    {
        DB::beginTransaction();
        try {
            $ticket = SupportTicket::withTrashed()->find($id);
            if (!$ticket) throw ApiException::notFound('Support Ticket');

            $ticket->restore();
            LogHelper::restored('support_ticket', $ticket->id, $ticket->company_id);
            DB::commit();
            return $ticket;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            throw ApiException::serverError('Failed to restore ticket');
        }
    }

    /**
     * Permanently delete ticket
     */
    public function forceDeleteTicket(int $id): bool
    {
        DB::beginTransaction();
        try {
            $ticket = SupportTicket::withTrashed()->find($id);
            if (!$ticket) throw ApiException::notFound('Support Ticket');

            if ($ticket->image) {
                FileUploadHelper::delete($ticket->image);
            }

            $ticket->forceDelete();
            LogHelper::forceDeleted('support_ticket', $ticket->id, $ticket->company_id);
            DB::commit();
            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            throw ApiException::serverError('Failed to permanently delete ticket');
        }
    }

    /**
     * Toggle status
     */
    public function toggleStatus(int $id): SupportTicket
    {
        DB::beginTransaction();
        try {
            $ticket = $this->getTicketById($id);
            $ticket->update(['status' => !$ticket->status]);
            LogHelper::statusChanged('support_ticket', $ticket->id, $ticket->company_id);
            DB::commit();
            return $ticket;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            throw ApiException::serverError('Failed to toggle status');
        }
    }
}
