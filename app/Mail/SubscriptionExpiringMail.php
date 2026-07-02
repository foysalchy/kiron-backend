<?php

namespace App\Mail;

use App\Models\CompanySubscription;
use Illuminate\Mail\Mailable;

class SubscriptionExpiringMail extends Mailable
{
  
    public function __construct(
        public CompanySubscription $subscription,
        public string $type,
        public int $daysLeft
    ) {}

    public function build()
    {
        $expiryDate = $this->type === 'trial'
            ? $this->subscription->trial_ends_at
            : $this->subscription->ends_at;

        $subjectLabel = $this->type === 'trial' ? 'trial' : 'subscription';

        return $this->subject("Your {$subjectLabel} expires in {$this->daysLeft} day(s)")
            ->view('emails.subscription-expiring')
            ->with([
                'companyName' => $this->subscription->company->name,
                'type'        => $this->type,
                'daysLeft'    => $this->daysLeft,
                'expiryDate'  => $expiryDate,
            ]);
    }
}