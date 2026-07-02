<?php

namespace App\Console\Commands;

use App\Mail\SubscriptionExpiringMail;
use App\Models\CompanySubscription;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class SendSubscriptionExpiryReminders extends Command
{
    protected $signature = 'subscriptions:send-expiry-reminders';
    protected $description = 'Send reminder emails for subscriptions/trials expiring in N days';

    public function handle(): void
    {
        $reminderDays = (int) config('subscription.reminder_days_before_expiry', 2);
        $targetDate   = Carbon::today()->addDays($reminderDays)->toDateString();

        $this->sendTrialReminders($targetDate, $reminderDays);
        $this->sendSubscriptionReminders($targetDate, $reminderDays);
    }

    protected function sendTrialReminders(string $targetDate, int $reminderDays): void
    {
        $subscriptions = CompanySubscription::query()
            ->whereDate('trial_ends_at', $targetDate)
            ->whereNull('reminder_sent_at')
            ->with('company')
            ->get()
            ->filter->isOnTrial();

        $this->dispatchReminders($subscriptions, 'trial', $reminderDays);
    }

    protected function sendSubscriptionReminders(string $targetDate, int $reminderDays): void
    {
        $subscriptions = CompanySubscription::query()
            ->whereDate('ends_at', $targetDate)
            ->whereNull('reminder_sent_at')
            ->with('company')
            ->get()
            ->filter->isActive(); // model-er existing helper

        $this->dispatchReminders($subscriptions, 'subscription', $reminderDays);
    }

    protected function dispatchReminders($subscriptions, string $type, int $reminderDays): void
    {
        foreach ($subscriptions as $subscription) {
            $email = $subscription->company?->email;

            if (! $email) {
                $this->warn("Skipped subscription #{$subscription->id} — no company email.");
                continue;
            }

            try {
                Mail::to($email)->send(
                    new SubscriptionExpiringMail($subscription, $type, $reminderDays)
                );

                $subscription->update(['reminder_sent_at' => now()]);

                $this->info("Reminder ({$type}) sent to {$email}");
            } catch (\Throwable $e) {
                $this->error("Failed for {$email}: {$e->getMessage()}");
            }
        }

        $this->info(ucfirst($type)." reminders processed: {$subscriptions->count()}");
    }
}