<?php

namespace App\Console\Commands;

use App\Mail\SubscriptionExpiringMail;
use App\Models\CompanySubscription;
use App\Models\ReminderLog;
use App\Models\ReminderSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class SendSubscriptionExpiryReminders extends Command
{
    protected $signature = 'subscriptions:send-expiry-reminders';
    protected $description = 'Send reminder emails based on configured reminder_settings thresholds';

    public function handle(): void
    {
        $settings = ReminderSetting::where('status', true)->get();

        if ($settings->isEmpty()) {
            $this->warn('No active reminder settings found.');
            return;
        }

        foreach ($settings as $setting) {
            $targetDate = Carbon::today()->addDays($setting->days_before)->toDateString();

            $subscriptions = $setting->type === 'trial'
                ? $this->getTrialSubscriptions($targetDate)
                : $this->getExpiringSubscriptions($targetDate);

            $this->processReminders($subscriptions, $setting);
        }
    }

    protected function getTrialSubscriptions(string $targetDate)
    {
        return CompanySubscription::query()
            ->whereDate('trial_ends_at', $targetDate)
            ->with('company')
            ->get()
            ->filter->isOnTrial();
    }

    protected function getExpiringSubscriptions(string $targetDate)
    {
        return CompanySubscription::query()
            ->whereDate('ends_at', $targetDate)
            ->with('company')
            ->get()
            ->filter->isActive();
    }

    protected function processReminders($subscriptions, ReminderSetting $setting): void
    {
        foreach ($subscriptions as $subscription) {

            // Ei threshold-e already mail gele skip koro
            $alreadySent = ReminderLog::where('company_subscription_id', $subscription->id)
                ->where('reminder_setting_id', $setting->id)
                ->exists();

            if ($alreadySent) {
                continue;
            }

            $email = $subscription->company?->email;

            if (! $email) {
                $this->warn("Skipped subscription #{$subscription->id} — no company email.");
                continue;
            }

            try {
                Mail::to($email)->send(
                    new SubscriptionExpiringMail($subscription, $setting->type, $setting->days_before)
                );

                ReminderLog::create([
                    'company_subscription_id' => $subscription->id,
                    'reminder_setting_id'     => $setting->id,
                    'sent_at'                 => now(),
                ]);

                $this->info("Reminder ({$setting->type}, {$setting->days_before} days) sent to {$email}");
            } catch (\Throwable $e) {
                $this->error("Failed for {$email}: {$e->getMessage()}");
            }
        }
    }
}