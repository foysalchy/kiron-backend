<?php

namespace App\Services;

use App\Models\Order;
use App\Enums\Status;

class OrderTemplateRenderer
{
    public function render(string $template, Order $order): string
    {
        $replacements = $this->buildReplacements($order);

        return strtr($template, $replacements);
    }

    protected function buildReplacements(Order $order): array
    {
        return [
            '{{customer_name}}'  => $order->customer?->name ?? 'Customer',
            '{{company_name}}'   => $order->company?->name ?? '',
            '{{order_no}}'       => $order->order_no,
            '{{order_date}}'     => $order->order_date
                ? \Carbon\Carbon::parse($order->order_date)->format('d M Y')
                : '',
            '{{total_amount}}'   => number_format((float) $order->grand_total, 2),
            '{{due_amount}}'     => number_format((float) ($order->grand_total - $order->payment_amount), 2),
            '{{paid_amount}}'    => number_format((float) $order->payment_amount, 2),
            '{{tracking_no}}'    => $order->tracking_no ?? '',
            '{{status}}'         => Status::from($order->status)->label(),
            '{{invoice_id}}'     => $order->invoice_id ?? $order->order_no,
            '{{invoice_link}}'   => $this->getInvoiceLink($order),
        ];
    }

    protected function getInvoiceLink(Order $order): string
    {
        // আপনার actual invoice route অনুযায়ী বদলে নিন
        return url("/invoices/{$order->id}");
        // অথবা signed URL চাইলে:
        // return \Illuminate\Support\Facades\URL::signedRoute('invoice.show', ['order' => $order->id]);
    }
}