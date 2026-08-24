<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Mail\SendEmail;
use App\Models\EmailSend;
use App\Models\EmailTemplate;
use App\Models\Party;
use App\Enums\Status;
use App\Models\SiteSetting;
use App\Models\SocialSetting;
use Illuminate\Support\Facades\{DB, Log, Mail, Storage};

class EmailSendService
{
    /**
     * Get all Email logs
     */
    public function getAllEmailLogs(array $filters)
    {
        try {
            $logs = EmailSend::query()
                ->when(!empty($filters['search']), function ($q) use ($filters) {
                    $q->where('subject', 'like', "%{$filters['search']}%");
                })
                ->orderBy('created_at', 'desc')
                ->paginate($filters['per_page'] ?? 15);

            $allIds = [];

            foreach ($logs as $log) {
                $allIds = array_merge(
                    $allIds,
                    $log->customer_ids ?? [],
                    $log->supplier_ids ?? []
                );
            }

            $allIds = array_unique($allIds);

            $parties = Party::whereIn('id', $allIds)
                ->select('id', 'name', 'type')
                ->get()
                ->groupBy('id');

            foreach ($logs as $log) {
                $customerIds = $log->customer_ids ?? [];
                $supplierIds = $log->supplier_ids ?? [];

                $log->customers = collect($customerIds)
                    ->map(fn($id) => $parties[$id][0] ?? null)
                    ->filter()
                    ->values();

                $log->suppliers = collect($supplierIds)
                    ->map(fn($id) => $parties[$id][0] ?? null)
                    ->filter()
                    ->values();
            }

            return $logs;
        } catch (\Exception $e) {
            Log::error('Error fetching Email logs: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch Email logs');
        }
    }

    /**
     * Store record and Send Email (Bulk Email flow — from UI)
     * Subject পাঠানো হয় as-is (কোনো placeholder replace হয় না)।
     * Body-তে শুধু {{customer_name}} personalize হয় প্রতি recipient-এর জন্য।
     */
    public function createEmailSend(array $data): EmailSend
    {
        return DB::transaction(function () use ($data) {
            try {
                $partyIds = array_unique(array_merge(
                    $data['customer_ids'] ?? [],
                    $data['supplier_ids'] ?? []
                ));

                $customEmails = $data['custom_emails'] ?? [];
                $recipients = [];

                // 1. Fetch System parties (Customers & Suppliers) — name সহ
                if (!empty($partyIds)) {
                    $parties = Party::whereIn('id', $partyIds)
                        ->whereNotNull('email')
                        ->where('email', '!=', '')
                        ->get(['id', 'name', 'email']);

                    foreach ($parties as $party) {
                        $recipients[] = [
                            'id'    => $party->id,
                            'name'  => $party->name,
                            'email' => $party->email,
                            'type'  => 'system_party'
                        ];
                    }
                }

                // 2. Format Custom Emails (নাম নেই, তাই personalize হবে না)
                foreach ($customEmails as $email) {
                    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $alreadyExists = collect($recipients)->contains('email', $email);
                        if (!$alreadyExists) {
                            $recipients[] = [
                                'id'    => null,
                                'name'  => null,
                                'email' => $email,
                                'type'  => 'custom'
                            ];
                        }
                    }
                }

                if (empty($recipients)) {
                    throw ApiException::badRequest('No valid email addresses found among the selected recipients.');
                }

                $subject = $data['subject'] ?? '';
                $body    = $data['body'] ?? '';

                if (empty($subject) || empty($body)) {
                    throw ApiException::badRequest('Email subject and body cannot be empty.');
                }

                // supplier_ids/customer_ids/custom_emails কলাম NOT NULL হলে default বসিয়ে দিলাম
                $emailSend = EmailSend::create(array_merge([
                    'customer_ids'   => [],
                    'supplier_ids'   => [],
                    'custom_emails'  => [],
                ], $data));

                $successCount = 0;
                $failedRecipients = [];
                $classToIconMap = [
                    'fa-brands fa-facebook'   => 'facebook',
                    'fa-brands fa-x-twitter'  => 'twitterx',
                    'fa-brands fa-instagram'  => 'instagram',
                    'fa-brands fa-linkedin'   => 'linkedin',
                    'fa-brands fa-youtube'    => 'youtube',
                    'fa-brands fa-tiktok'     => 'tiktok',
                    'fa-brands fa-whatsapp'   => 'whatsapp',
                    'fa-brands fa-telegram'   => 'telegram',
                    'fa-brands fa-pinterest'  => 'pinterest',
                    'fa-brands fa-github'     => 'github',
                    'fa-brands fa-snapchat'   => 'snapchat',
                    'fa-brands fa-discord'    => 'discord',
                    'fa-brands fa-reddit'     => 'reddit',
                    'fa-brands fa-dribbble'   => 'dribbble',
                    'fa-brands fa-behance'    => 'behance',
                    'fa-brands fa-vimeo'      => 'vimeo',
                    'fa-brands fa-skype'      => 'skype',
                    'fa-brands fa-slack'      => 'slack',
                    'fa-brands fa-tumblr'     => 'tumblr',
                ];

                $setting = SiteSetting::first();
                $socials = SocialSetting::where('status', Status::Active->value)
                    ->get()
                    ->map(function ($item) use ($classToIconMap) {
                        $imageUrl = null;
                        $class = trim($item->icon_class);

                        if (!empty($item->icon_image)) {
                            $imageUrl = url($item->icon_image);
                        }
                        elseif (!empty($class) && isset($classToIconMap[$class])) {
                            $iconKey = $classToIconMap[$class];
                            $themeColor = '13565e';
                            $imageUrl = "https://img.icons8.com/ios-filled/48/{$themeColor}/{$iconKey}.png";
                        }

                        return [
                            'icon_name'  => $item->icon_name,
                            'icon_image' => $imageUrl,
                            'link'       => $item->link,
                        ];
                    })
                    ->filter(fn($item) => !empty($item['icon_image'])) 
                    ->toArray();

                $companyName = $setting->shop_name ?? "dorja.io";
                $supportEmail = $setting->email ?? null;
                $logo = $setting?->logo
                    ? Storage::disk('r2')->url($setting->logo)
                    : null;


                $companyId = $setting->company_id;
                foreach ($recipients as $index => $recipient) {
                    try {
                        $personalizedBody = $this->replacePlaceholder(
                            $body,
                            'customer_name',
                            $recipient['name'] ?? 'Customer'
                        );

                        $personalizedBody = $this->replacePlaceholder(
                            $personalizedBody,
                            'company_name',
                            $companyName
                        );
                        Log::info('Dispatching email', ['company_id' => $companyId]);
                        Mail::to($recipient['email'])->later(
                            now()->addSeconds($index * 3),
                            new SendEmail(
                                $subject,
                                $personalizedBody,
                                $companyId,
                                $companyName,
                                $logo,
                                $supportEmail,
                                $socials,
                            )
                        );
                        $successCount++;

                        Log::info('Email queued successfully', [
                            'email_send_id' => $emailSend->id,
                            'company_id'    => $emailSend->company_id,
                            'recipient'     => $recipient['email'],
                            'customer_id'   => $recipient['id'],
                            'type'          => $recipient['type'],
                            'subject'       => $subject,
                            'body'          => $personalizedBody,
                        ]);
                    } catch (\Exception $e) {
                        $failedRecipients[] = [
                            'customer_id' => $recipient['id'],
                            'email'       => $recipient['email'],
                            'reason'      => $e->getMessage(),
                        ];

                        Log::error('Failed to queue email for recipient', [
                            'email_send_id' => $emailSend->id,
                            'company_id'    => $emailSend->company_id,
                            'recipient'     => $recipient['email'],
                            'customer_id'   => $recipient['id'],
                            'error'         => $e->getMessage(),
                        ]);
                    }
                }

                if (!empty($failedRecipients)) {
                    Log::warning('Email batch completed with some failures', [
                        'email_send_id'     => $emailSend->id,
                        'company_id'        => $emailSend->company_id,
                        'total_recipients'  => count($recipients),
                        'success_count'     => $successCount,
                        'failed_count'      => count($failedRecipients),
                        'failed_recipients' => $failedRecipients,
                    ]);
                } else {
                    Log::info('Email batch queued successfully — all recipients', [
                        'email_send_id'    => $emailSend->id,
                        'company_id'       => $emailSend->company_id,
                        'total_recipients' => count($recipients),
                        'success_count'    => $successCount,
                    ]);
                }

                LogHelper::created(
                    'email_send',
                    $emailSend->id,
                    $emailSend->company_id,
                    "Email queued: {$successCount}/" . count($recipients) . " recipients succeeded"
                        . (!empty($failedRecipients) ? ' | ' . count($failedRecipients) . ' failed' : '')
                );

                return $emailSend;
            } catch (ApiException $e) {
                throw $e;
            } catch (\Exception $e) {
                Log::error('Email processing failed entirely', [
                    'error'      => $e->getMessage(),
                    'company_id' => $data['company_id'] ?? null,
                    'subject'    => $data['subject'] ?? null,
                    'trace'      => $e->getTraceAsString(),
                ]);

                throw ApiException::serverError('Failed to process and send emails: ' . $e->getMessage());
            }
        });
    }
    private function getIconUrlFromClass(?string $iconClass): ?string
    {
        if (empty($iconClass)) {
            return null;
        }

        $cleanName = str_replace(['fa-brands', 'fa-solid', 'fa-regular', 'fa-square', 'fa-', '-f', ' '], '', $iconClass);
        $cleanName = trim($cleanName, '- ');

        $mapping = [
            'linkedin-in' => 'linkedin',
            'youtube-play' => 'youtube',
            'paper-plane' => 'telegram',
        ];

        $iconName = $mapping[$cleanName] ?? $cleanName;
        $themeColor = '13565e';

        return "https://img.icons8.com/ios-filled/48/{$themeColor}/{$iconName}.png";
    }
    /**
     * Order status change হলে automated email পাঠানো হয়।
     * Subject as-is যায় (placeholder replace হয় না)।
     * Body-তে order-এর সব data দিয়ে placeholder replace হয়।
     */
    public function sendStatusBasedEmail($order)
    {
        $slugMap = [
            Status::Pending->value   => 'order-place',
            Status::Confirmed->value => 'order-confirm',
            Status::Shipped->value   => 'order-shipped',
            Status::Delivered->value => 'order-delivered',
            Status::Cancelled->value => 'cancel-order',
        ];

        $statusValue = $order->status instanceof Status ? $order->status->value : $order->status;

        $slug = $slugMap[$statusValue] ?? null;
        if (!$slug) return;

        $template = EmailTemplate::where('company_id', $order->company_id)
            ->where('slug', $slug)
            ->where('status', Status::Active->value)
            ->first();

        if (!$template) return;

        if (empty($order->customer?->email)) {
            Log::warning("Order {$order->order_no}: customer has no email, status email skipped");
            return;
        }

        // subject as-is (no placeholder replace)
        $subject = $template->subject;

        // body-তে order data দিয়ে সব placeholder replace
        $body = $this->renderOrderTemplate($template->body, $order);

        return $this->createEmailSend([
            'company_id'   => $order->company_id,
            'customer_ids' => [$order->customer_id],
            'subject'      => $subject,
            'body'         => $body,
        ]);
    }

    /**
     * একটা নির্দিষ্ট {{placeholder}} replace করে template-এ।
     */
    private function replacePlaceholder(string $template, string $key, ?string $value): string
    {
        return str_replace('{{' . $key . '}}', $value ?? '', $template);
    }

    /**
     * Order object থেকে body-এর সব {{placeholder}} replace করে।
     * ORDER_PARAMETERS (frontend)-এর সাথে key মিলিয়ে রাখা হয়েছে।
     */
    private function renderOrderTemplate(string $template, $order): string
    {
        $status = $order->status instanceof Status
            ? $order->status
            : Status::from($order->status);

        $replacements = [
            '{{customer_name}}' => $order->customer?->name ?? 'Customer',
            '{{company_name}}'  => $order->company?->name ?? '',
            '{{order_no}}'      => $order->order_no,
            '{{order_date}}'    => $order->order_date
                ? \Carbon\Carbon::parse($order->order_date)->format('d M Y')
                : '',
            '{{total_amount}}'  => number_format((float) $order->grand_total, 2),
            '{{due_amount}}'    => number_format(
                (float) ($order->grand_total - $order->payment_amount),
                2
            ),
            '{{paid_amount}}'   => number_format((float) $order->payment_amount, 2),
            '{{tracking_no}}'   => $this->getTrackingNo($order),
            '{{status}}'        => $status->label(),
            '{{invoice_id}}'    => $order->order_no ?? $order->id,
            '{{invoice_link}}'  => route('order.invoice', $order->id),
        ];

        return strtr($template, $replacements);
    }

    private function getTrackingNo($order): string
    {
        $courierInfo = $order->courier_info;



        return $courierInfo['tracking_code'] ?? '-';
    }

    public function getEmailLogById(int $id): EmailSend
    {
        $log = EmailSend::find($id);
        if (!$log) throw ApiException::notFound('Email record');

        $allIds = array_merge($log->customer_ids ?? [], $log->supplier_ids ?? []);
        $parties = Party::whereIn('id', $allIds)->select('id', 'name', 'type')->get();

        $log->customers = $parties->where('type', Party::TYPE_CUSTOMER)->values();
        $log->suppliers = $parties->where('type', Party::TYPE_SUPPLIER)->values();

        return $log;
    }
}
