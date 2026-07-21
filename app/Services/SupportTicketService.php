<?php

namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\FileUploadHelper;
use App\Helpers\LogHelper;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB, Log};

class SupportTicketService
{
    /**
     * Get all tickets with optional pagination
     */
    public function getAllTickets(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $user  = auth()->user();
            $query = SupportTicket::with(['supportDepartment', 'company', 'user', 'assignedUser']);
            if ($user->is_super_admin) {
                $query->withoutGlobalScope('company');
            }
            // ── Visibility Filter ───────────────────────────────────────
            $hasRole = $user->roles()->exists();

            if ($hasRole) {
                if ($user->is_super_admin) {
                    $query->where('assigned_to', $user->id);
                } else {
                    $query->where('user_id', $user->id);
                }
            }
            // ────────────────────────────────────────────────────────────


            if (!empty($filters['date_filter'])) {
                $fromDate = null;
                $toDate   = now()->format('Y-m-d');

                switch ($filters['date_filter']) {
                    case 'Today':
                        $fromDate = now()->format('Y-m-d');
                        break;
                    case 'Yesterday':
                        $fromDate = now()->subDay()->format('Y-m-d');
                        $toDate   = $fromDate;
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
                        $toDate   = now()->subMonth()->endOfMonth()->format('Y-m-d');
                        break;
                    case 'Custom Range':
                        $fromDate = $filters['from_date'] ?? null;
                        $toDate   = $filters['to_date'] ?? now()->format('Y-m-d');
                        break;
                }

                if ($fromDate) {
                    $query->whereBetween('created_at', [
                        $fromDate . ' 00:00:00',
                        $toDate   . ' 23:59:59',
                    ]);
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

            return $paginate
                ? $query->paginate($filters['per_page'] ?? 10)
                : $query->get();
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
        $user = auth()->user();

        $query = SupportTicket::with(['supportDepartment', 'user', 'replies.user']);

        if ($user->is_super_admin) {
            $query->withoutGlobalScope('company');
        }

        $ticket = $query->find($id);

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

                );
            }
            $data['user_id'] = auth()->id();
            $ticket = SupportTicket::create($data);
            LogHelper::created('support_ticket', $ticket->id, $ticket->company_id, $ticket->subject);

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
            LogHelper::updated('support_ticket', $ticket->id, $ticket->company_id, $ticket->subject);

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
            LogHelper::deleted('support_ticket', $ticket->id, $ticket->company_id, $ticket->subject);
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
 public function toggleStatus(int $id, string $newStatus): SupportTicket
{
    DB::beginTransaction();
    try {
        $ticket = $this->getTicketById($id);

        $updateData = ['status' => $newStatus];

        // Close হলে response_status null করো
        if ($newStatus == Status::Closed->value) {
            $updateData['response_status'] = null;
        }

        $ticket->update($updateData);
        LogHelper::statusChanged('support_ticket', $ticket->id, $ticket->company_id);

        DB::commit();
        return $ticket;
    } catch (ApiException $e) {
        DB::rollBack();
        throw $e; // original message/status code preserve
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Failed to toggle status: ' . $e->getMessage(), [
            'ticket_id' => $id,
            'new_status' => $newStatus,
        ]);
        throw ApiException::serverError('Failed to toggle status');
    }
}
    /**
     * Store a ticket reply and update status automatically
     */
    public function storeReply(array $data): SupportTicketReply
    {
        DB::beginTransaction();

        try {
            $authUser = auth()->user();

            $ticketQuery = SupportTicket::query();
            if ($authUser->is_super_admin) {
                $ticketQuery->withoutGlobalScope('company');
            }
            $ticket = $ticketQuery->findOrFail($data['support_ticket_id']);

            if ($ticket->status === Status::Closed->value) {
                throw ApiException::badRequest('Cannot reply to a closed ticket.');
            }

            if (isset($data['image'])) {
                $data['image'] = FileUploadHelper::uploadImage(
                    $data['image'],
                    'tickets/replies',
                );
            }

            $data['user_id'] = $authUser->id;
            $reply = SupportTicketReply::create($data);

            $isStaff = $authUser->is_super_admin || $authUser->roles()->exists();

            $ticket->update([
                'response_status' => $isStaff
                    ? Status::WaitingForClientResponse->value
                    : Status::WaitForResponse->value,
            ]);

            LogHelper::updated('support_ticket_reply', $reply->id, $ticket->company_id, 'New reply added');

            DB::commit();
            Log::info('Ticket reply stored successfully', ['reply_id' => $reply->id, 'ticket_id' => $ticket->id]);

            return $reply->load('user');
        } catch (ApiException $e) {
            DB::rollBack();
            if (isset($data['image']) && is_string($data['image'])) {
                FileUploadHelper::delete($data['image']);
            }
            throw $e; // original message/status code rakho
        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($data['image']) && is_string($data['image'])) {
                FileUploadHelper::delete($data['image']);
            }
            Log::error('Ticket reply creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to send reply');
        }
    }

    public function assignUser(int $ticketId, array $data): SupportTicket
    {
        DB::beginTransaction();
        try {
            $ticket = $this->getTicketById($ticketId);
            $ticket->assigned_to = $data['user_id'];
            $ticket->save();

            DB::commit();
            return $ticket;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to assign user to ticket: ' . $e->getMessage());
            throw ApiException::serverError('Failed to assign user');
        }
    }
}
