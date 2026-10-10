<?php

namespace App\Services;

use App\Enums\Status;
use App\Models\AccountingSetting;
use App\Models\ChartOfAccount;
use App\Models\Order;
use App\Models\Party;
use App\Models\ProjectRevenue;
use App\Models\Purchase;
use App\Models\TransactionJournal;
use App\Models\TransactionJournalAccount;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoAccountingService
{
    /**
     * Get or initialize accounting setting for a company
     */
    public static function getSetting(int $companyId): ?AccountingSetting
    {
        $setting = AccountingSetting::withoutGlobalScopes()->where('company_id', $companyId)->first();
        if (!$setting) {
            $setting = DefaultAccountingSeederService::seedDefaultAccountsForCompany($companyId);
        }
        return $setting;
    }

    /**
     * Check if advanced auto-journaling is enabled
     */
    public static function isEnabled(int $companyId): bool
    {
        $setting = self::getSetting($companyId);
        return $setting && $setting->accounting_mode === 'advanced' && $setting->auto_journal_posting;
    }

    /**
     * Resolve COGS account
     */
    public static function resolveCogsAccount(int $companyId, ?AccountingSetting $setting): ?int
    {
        if ($setting?->default_cogs_account_id) {
            return $setting->default_cogs_account_id;
        }
        $acc = ChartOfAccount::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('name', 'like', '%Cost of Goods Sold%')
            ->first();
        return $acc?->id;
    }

    /**
     * Resolve Inventory account
     */
    public static function resolveInventoryAccount(int $companyId, ?AccountingSetting $setting): ?int
    {
        if ($setting?->default_inventory_account_id) {
            return $setting->default_inventory_account_id;
        }
        $acc = ChartOfAccount::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where(function ($q) {
                $q->where('name', 'like', '%Stock-in-Hand%')
                  ->orWhere('name', 'like', '%Inventory%');
            })
            ->first();
        return $acc?->id;
    }

    /**
     * Calculate Total Cost of Goods Sold (COGS) for an order
     */
    public static function calculateOrderCogs(Order $order): float
    {
        $order->loadMissing(['orderDetails.product', 'orderDetails.variation']);
        $totalCogs = 0;
        foreach ($order->orderDetails as $detail) {
            $unitCost = (float)($detail->variation->purchase_price ?? $detail->product->purchase_price ?? 0);
            if ($unitCost <= 0) {
                $unitCost = (float)($detail->product->cost_price ?? 0);
            }
            $qty = (float)($detail->quantity ?? 1);
            $totalCogs += ($unitCost * $qty);
        }
        return round($totalCogs, 2);
    }

    /**
     * 1. Post Order / Sales Journal (POS or eCommerce)
     */
    public static function postOrderJournal(Order $order): ?TransactionJournal
    {
        try {
            $companyId = $order->company_id;
            if (!self::isEnabled($companyId)) return null;

            $setting = self::getSetting($companyId);
            if (!$setting) return null;

            $isPos = ($order->type === 'pos') || ($order->type === Order::TYPE_POS);
            $isLandingPage = ($order->channel === 'landing_page') || ($order->source === 'landing_page');

            if ($isPos && $setting->sync_pos_orders === false) return null;
            if ($isLandingPage && $setting->sync_landing_page_orders === false) return null;
            if (!$isPos && !$isLandingPage && $setting->sync_website_orders === false) return null;

            // Prevent duplicate auto-posting for same order
            $existing = TransactionJournal::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->where('source_type', 'order')
                ->where('source_id', $order->id)
                ->first();

            $isPos = ($order->type === 'pos') || ($order->type === Order::TYPE_POS);
            $salesAccountId = $isPos
                ? ($setting->default_pos_sales_account_id ?? $setting->default_web_sales_account_id)
                : ($setting->default_web_sales_account_id ?? $setting->default_pos_sales_account_id);

            $cashAccountId = $setting->default_cash_account_id;
            $receivableAccountId = $setting->default_receivable_account_id;
            $deliveryIncomeAccountId = $setting->default_delivery_income_account_id;
            $vatAccountId = $setting->default_vat_payable_account_id;

            // Discounts Account (find Discount Allowed account if present)
            $discountAccount = ChartOfAccount::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->where('name', 'like', '%Discount%')
                ->first();
            $discountAccountId = $discountAccount ? $discountAccount->id : null;

            $totalAmount = (float)($order->grand_total ?? $order->total_amount ?? 0);
            $paidAmount = (float)($order->payment_amount ?? $order->paid_amount ?? 0);
            $dueAmount = max(0, round($totalAmount - $paidAmount, 2));

            $deliveryCharge = (float)($order->other_charges ?? $order->shipping_charge ?? 0);
            $taxAmount = (float)($order->tax_amount ?? 0);
            $totalDiscount = (float)(($order->discount_on_all ?? 0) + ($order->coupon_discount ?? 0));
            $subtotal = (float)($order->subtotal ?? ($totalAmount - $deliveryCharge - $taxAmount + $totalDiscount));

            $items = [];

            // ── DEBITS ──
            if ($paidAmount > 0 && $cashAccountId) {
                $items[] = ['chart_of_account_id' => $cashAccountId, 'debit' => $paidAmount, 'credit' => 0];
            }
            if ($dueAmount > 0 && $receivableAccountId) {
                $items[] = ['chart_of_account_id' => $receivableAccountId, 'debit' => $dueAmount, 'credit' => 0];
            }
            // If discount tracked explicitly as expense:
            if ($totalDiscount > 0 && $discountAccountId) {
                $items[] = ['chart_of_account_id' => $discountAccountId, 'debit' => $totalDiscount, 'credit' => 0];
            }

            // ── CREDITS ──
            // If discount tracked as expense, Credit Sales with Gross Subtotal; otherwise Credit Sales with Net Subtotal
            $salesCredit = ($totalDiscount > 0 && $discountAccountId)
                ? $subtotal
                : max(0, round($subtotal - $totalDiscount, 2));

            if ($salesCredit > 0 && $salesAccountId) {
                $items[] = ['chart_of_account_id' => $salesAccountId, 'debit' => 0, 'credit' => $salesCredit];
            }
            if ($deliveryCharge > 0 && $deliveryIncomeAccountId) {
                $items[] = ['chart_of_account_id' => $deliveryIncomeAccountId, 'debit' => 0, 'credit' => $deliveryCharge];
            }
            if ($taxAmount > 0 && $vatAccountId) {
                $items[] = ['chart_of_account_id' => $vatAccountId, 'debit' => 0, 'credit' => $taxAmount];
            }

            // ── COGS & INVENTORY (Perpetual Inventory System) ──
            $cogsAccountId = self::resolveCogsAccount($companyId, $setting);
            $inventoryAccountId = self::resolveInventoryAccount($companyId, $setting);
            $totalCogs = self::calculateOrderCogs($order);

            if ($totalCogs > 0 && $cogsAccountId && $inventoryAccountId) {
                // Dr. Cost of Goods Sold (Expense in P&L)
                $items[] = ['chart_of_account_id' => $cogsAccountId, 'debit' => $totalCogs, 'credit' => 0];
                // Cr. Merchandise Inventory (Asset in Balance Sheet)
                $items[] = ['chart_of_account_id' => $inventoryAccountId, 'debit' => 0, 'credit' => $totalCogs];
            }

            if (empty($items)) return null;

            $customerName = 'Walk-in';
            if ($order->customer_id) {
                $cust = Party::withoutGlobalScopes()->find($order->customer_id);
                if ($cust) $customerName = $cust->name;
            }

            $voucherNo = 'SV-' . ($order->order_no ?? $order->id);
            $narration = "Sales Invoice #{$order->order_no} for customer {$customerName}";

            return self::saveVoucher(
                $companyId,
                'sales',
                $voucherNo,
                'order',
                $order->id,
                $order->customer_id ?? null,
                $order->order_date ?? Carbon::now(),
                $narration,
                $items,
                $existing
            );
        } catch (\Exception $e) {
            Log::error('Failed to auto-post order journal: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * 1.1 Post Sales Return (Order Return) Journal
     */
    public static function postOrderReturnJournal(\App\Models\OrderReturn $orderReturn): ?TransactionJournal
    {
        try {
            $companyId = $orderReturn->company_id;
            if (!self::isEnabled($companyId)) return null;

            $setting = self::getSetting($companyId);
            if (!$setting || $setting->sync_sales_returns === false) return null;

            $existing = TransactionJournal::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->where('source_type', 'order_return')
                ->where('source_id', $orderReturn->id)
                ->first();

            $salesAccountId = $setting->default_sales_return_account_id
                ?? ($setting->default_pos_sales_account_id ?? $setting->default_web_sales_account_id);

            $cashAccountId = $setting->default_cash_account_id;
            $receivableAccountId = $setting->default_receivable_account_id;

            $refundAmount = (float)($orderReturn->refund_amount ?? 0);
            if ($refundAmount <= 0) return null;

            $paymentsTotal = (float)$orderReturn->orderReturnPayments()->sum('amount');
            $adjustedDue = max(0, round($refundAmount - $paymentsTotal, 2));

            $items = [];

            // Debit Sales (or Sales Return):
            if ($salesAccountId) {
                $items[] = ['chart_of_account_id' => $salesAccountId, 'debit' => $refundAmount, 'credit' => 0];
            }

            // Credit Cash / Bank (Refund paid back to customer):
            if ($paymentsTotal > 0 && $cashAccountId) {
                $items[] = ['chart_of_account_id' => $cashAccountId, 'debit' => 0, 'credit' => $paymentsTotal];
            }

            // Credit Accounts Receivable (Adjusted customer balance):
            if ($adjustedDue > 0 && $receivableAccountId) {
                $items[] = ['chart_of_account_id' => $receivableAccountId, 'debit' => 0, 'credit' => $adjustedDue];
            }

            // ── COGS & INVENTORY REVERSAL ──
            $cogsAccountId = self::resolveCogsAccount($companyId, $setting);
            $inventoryAccountId = self::resolveInventoryAccount($companyId, $setting);

            $orderReturn->loadMissing(['orderReturnDetails.product', 'orderReturnDetails.variation']);
            $returnCogs = 0;
            foreach ($orderReturn->orderReturnDetails as $retDetail) {
                $unitCost = (float)($retDetail->variation->purchase_price ?? $retDetail->product->purchase_price ?? 0);
                if ($unitCost <= 0) {
                    $unitCost = (float)($retDetail->product->cost_price ?? 0);
                }
                $returnCogs += ($unitCost * (float)($retDetail->quantity ?? 1));
            }
            $returnCogs = round($returnCogs, 2);

            if ($returnCogs > 0 && $cogsAccountId && $inventoryAccountId) {
                // Dr. Inventory (restore asset in Balance Sheet)
                $items[] = ['chart_of_account_id' => $inventoryAccountId, 'debit' => $returnCogs, 'credit' => 0];
                // Cr. COGS (reduce expense in P&L)
                $items[] = ['chart_of_account_id' => $cogsAccountId, 'debit' => 0, 'credit' => $returnCogs];
            }

            $customerName = 'Customer';
            if ($orderReturn->customer_id) {
                $cust = Party::withoutGlobalScopes()->find($orderReturn->customer_id);
                if ($cust) $customerName = $cust->name;
            }

            $voucherNo = 'SRV-' . ($orderReturn->return_no ?? $orderReturn->id);
            $narration = "Sales Return #{$orderReturn->return_no} - Credit Note for customer {$customerName}";

            return self::saveVoucher(
                $companyId,
                'sales_return',
                $voucherNo,
                'order_return',
                $orderReturn->id,
                $orderReturn->customer_id ?? null,
                $orderReturn->return_date ?? Carbon::now(),
                $narration,
                $items,
                $existing
            );
        } catch (\Exception $e) {
            Log::error('Failed to auto-post order return journal: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * 1.2 Post eCommerce Order Cancellation / Courier Return Journal
     */
    public static function postOrderCancellationOrReturnJournal(Order $order, string $newStatus = 'cancelled'): ?TransactionJournal
    {
        try {
            $companyId = $order->company_id;
            if (!self::isEnabled($companyId)) return null;

            $setting = self::getSetting($companyId);
            if (!$setting || $setting->sync_sales_returns === false) return null;

            // Check if there was an original sales journal for this order
            $salesJournal = TransactionJournal::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->where('source_type', 'order')
                ->where('source_id', $order->id)
                ->first();

            // If no sales journal was ever posted, nothing to reverse
            if (!$salesJournal) return null;

            // Check if cancellation/return journal already exists
            $existingReturnJournal = TransactionJournal::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->where('source_type', 'order_cancellation')
                ->where('source_id', $order->id)
                ->first();

            $salesAccountId = $setting->default_sales_return_account_id
                ?? ($setting->default_web_sales_account_id ?? $setting->default_pos_sales_account_id);
            $receivableAccountId = $setting->default_receivable_account_id;
            $cashAccountId = $setting->default_cash_account_id;
            $courierExpenseAccountId = $setting->default_courier_expense_account_id;

            $items = [];
            $totalAmount = (float)($order->grand_total ?? $order->total_amount ?? 0);
            $paidAmount = (float)($order->payment_amount ?? $order->paid_amount ?? 0);
            $dueAmount = max(0, round($totalAmount - $paidAmount, 2));

            // Reverse Sales: Debit Sales Return
            if ($totalAmount > 0 && $salesAccountId) {
                $items[] = ['chart_of_account_id' => $salesAccountId, 'debit' => $totalAmount, 'credit' => 0];
            }

            // Reverse Customer Receivable: Credit Accounts Receivable
            if ($dueAmount > 0 && $receivableAccountId) {
                $items[] = ['chart_of_account_id' => $receivableAccountId, 'debit' => 0, 'credit' => $dueAmount];
            }

            // If customer had made a payment and it is refunded:
            if ($paidAmount > 0 && $cashAccountId) {
                $items[] = ['chart_of_account_id' => $cashAccountId, 'debit' => 0, 'credit' => $paidAmount];
            }

            // Courier Delivery & Return Expense (Merchant's RTO Loss)
            $courierCost = 0;
            if (!empty($order->courier_info)) {
                $cInfo = is_array($order->courier_info) ? $order->courier_info : json_decode($order->courier_info, true);
                $courierCost = (float)($cInfo['courier_charge'] ?? $cInfo['delivery_charge'] ?? $cInfo['delivery_fee'] ?? 0);
            }
            if ($courierCost <= 0 && (float)($order->other_charges ?? 0) > 0) {
                $courierCost = (float)$order->other_charges;
            }

            if ($courierCost > 0 && $courierExpenseAccountId && $cashAccountId) {
                $items[] = ['chart_of_account_id' => $courierExpenseAccountId, 'debit' => $courierCost, 'credit' => 0];
                $items[] = ['chart_of_account_id' => $cashAccountId, 'debit' => 0, 'credit' => $courierCost];
            }

            // ── COGS & INVENTORY REVERSAL ──
            $cogsAccountId = self::resolveCogsAccount($companyId, $setting);
            $inventoryAccountId = self::resolveInventoryAccount($companyId, $setting);
            $totalCogs = self::calculateOrderCogs($order);

            if ($totalCogs > 0 && $cogsAccountId && $inventoryAccountId) {
                // Reversal: Dr. Inventory (goods returned to warehouse)
                $items[] = ['chart_of_account_id' => $inventoryAccountId, 'debit' => $totalCogs, 'credit' => 0];
                // Reversal: Cr. COGS (remove cost from P&L)
                $items[] = ['chart_of_account_id' => $cogsAccountId, 'debit' => 0, 'credit' => $totalCogs];
            }

            if (empty($items)) return null;

            $customerName = 'Walk-in';
            if ($order->customer_id) {
                $cust = Party::withoutGlobalScopes()->find($order->customer_id);
                if ($cust) $customerName = $cust->name;
            }

            $voucherNo = 'CRV-' . ($order->order_no ?? $order->id);
            $narration = "eCommerce Order #{$order->order_no} status: {$newStatus} (Sales reversed & courier return loss adjusted for {$customerName})";

            return self::saveVoucher(
                $companyId,
                'journal',
                $voucherNo,
                'order_cancellation',
                $order->id,
                $order->customer_id ?? null,
                Carbon::now(),
                $narration,
                $items,
                $existingReturnJournal
            );
        } catch (\Exception $e) {
            Log::error('Failed to auto-post order cancellation/return journal: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * 2.1 Post Purchase Return Journal
     */
    public static function postPurchaseReturnJournal(\App\Models\PurchaseReturn $purchaseReturn): ?TransactionJournal
    {
        try {
            $companyId = $purchaseReturn->company_id;
            if (!self::isEnabled($companyId)) return null;

            $setting = self::getSetting($companyId);
            if (!$setting || $setting->sync_purchase_returns === false) return null;

            $existing = TransactionJournal::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->where('source_type', 'purchase_return')
                ->where('source_id', $purchaseReturn->id)
                ->first();

            $returnCreditAccountId = $setting->default_purchase_return_account_id
                ?? ($setting->default_inventory_account_id ?? $setting->default_cogs_account_id);
            $cashAccountId = $setting->default_cash_account_id;
            $payableAccountId = $setting->default_payable_account_id;

            $refundAmount = (float)($purchaseReturn->refund_amount ?? $purchaseReturn->grand_total ?? 0);
            if ($refundAmount <= 0) return null;

            $paymentsTotal = (float)$purchaseReturn->payments()->sum('amount');
            $adjustedSupplierDue = max(0, round($refundAmount - $paymentsTotal, 2));

            $items = [];

            // Debit Cash/Bank (Cash refund from supplier):
            if ($paymentsTotal > 0 && $cashAccountId) {
                $items[] = ['chart_of_account_id' => $cashAccountId, 'debit' => $paymentsTotal, 'credit' => 0];
            }

            // Debit Accounts Payable (Supplier due reduced):
            if ($adjustedSupplierDue > 0 && $payableAccountId) {
                $items[] = ['chart_of_account_id' => $payableAccountId, 'debit' => $adjustedSupplierDue, 'credit' => 0];
            }

            // Credit Stock / Purchase Return Account:
            if ($returnCreditAccountId) {
                $items[] = ['chart_of_account_id' => $returnCreditAccountId, 'debit' => 0, 'credit' => $refundAmount];
            }

            $ref = $purchaseReturn->reference_no ?? ('PR-' . $purchaseReturn->id);
            $voucherNo = 'PRV-' . $ref;
            $narration = "Purchase Return Voucher #{$ref} to supplier";

            return self::saveVoucher(
                $companyId,
                'journal',
                $voucherNo,
                'purchase_return',
                $purchaseReturn->id,
                $purchaseReturn->supplier_id ?? null,
                $purchaseReturn->purchase_date ?? Carbon::now(),
                $narration,
                $items,
                $existing
            );
        } catch (\Exception $e) {
            Log::error('Failed to auto-post purchase return journal: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * 2. Post Purchase Journal
     */
    public static function postPurchaseJournal(Purchase $purchase): ?TransactionJournal
    {
        try {
            $companyId = $purchase->company_id;
            if (!self::isEnabled($companyId)) return null;

            $setting = self::getSetting($companyId);
            if (!$setting || $setting->sync_purchases === false) return null;

            $existing = TransactionJournal::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->where('source_type', 'purchase')
                ->where('source_id', $purchase->id)
                ->first();

            $inventoryAccountId = $setting->default_inventory_account_id;
            $cashAccountId = $setting->default_cash_account_id;
            $payableAccountId = $setting->default_payable_account_id;
            $courierAccountId = $setting->default_courier_expense_account_id;

            $totalAmount = (float)($purchase->grand_total ?? $purchase->total_amount ?? 0);
            $otherCharges = (float)($purchase->other_charges ?? 0);
            
            // If there's an explicit expense account for other charges, separate it out
            if ($courierAccountId && $otherCharges > 0) {
                $inventoryAmount = max(0, round($totalAmount - $otherCharges, 2));
            } else {
                $inventoryAmount = $totalAmount;
                $otherCharges = 0; // Handled within inventory
            }

            $paidAmount = (float)($purchase->payment_amount ?? $purchase->paid_amount ?? 0);
            $dueAmount = max(0, round($totalAmount - $paidAmount, 2));

            $items = [];

            // Debit Inventory:
            if ($inventoryAmount > 0 && $inventoryAccountId) {
                $items[] = ['chart_of_account_id' => $inventoryAccountId, 'debit' => $inventoryAmount, 'credit' => 0];
            }

            // Debit Courier / Freight Expense:
            if ($otherCharges > 0 && $courierAccountId) {
                $items[] = ['chart_of_account_id' => $courierAccountId, 'debit' => $otherCharges, 'credit' => 0];
            }

            // Credit Cash / Bank:
            if ($paidAmount > 0 && $cashAccountId) {
                $items[] = ['chart_of_account_id' => $cashAccountId, 'debit' => 0, 'credit' => $paidAmount];
            }

            // Credit Accounts Payable (Supplier Due):
            if ($dueAmount > 0 && $payableAccountId) {
                $items[] = ['chart_of_account_id' => $payableAccountId, 'debit' => 0, 'credit' => $dueAmount];
            }

            if (empty($items)) return null;

            $supplierName = 'Supplier';
            if ($purchase->supplier_id) {
                $sup = Party::withoutGlobalScopes()->find($purchase->supplier_id);
                if ($sup) $supplierName = $sup->name;
            }

            $ref = $purchase->reference_no ?? ('PB-' . $purchase->id);
            $voucherNo = 'PV-' . $ref;
            $narration = "Purchase Bill #{$ref} from supplier {$supplierName}";

            return self::saveVoucher(
                $companyId,
                'purchase',
                $voucherNo,
                'purchase',
                $purchase->id,
                $purchase->supplier_id ?? null,
                $purchase->purchase_date ?? Carbon::now(),
                $narration,
                $items,
                $existing
            );
        } catch (\Exception $e) {
            Log::error('Failed to auto-post purchase journal: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * 3. Post Payment Collection / Party Due Sync (Payment In & Payment Out)
     */
    public static function postPaymentCollectionJournal(
        int $companyId,
        string $direction, // 'in' | 'out'
        float $amount,
        ?int $partyId,
        string $paymentMethod = 'cash',
        ?string $reference = null,
        ?string $date = null
    ): ?TransactionJournal {
        try {
            if ($amount <= 0 || !self::isEnabled($companyId)) return null;

            $setting = self::getSetting($companyId);
            if (!$setting) return null;

            if ($direction === 'in' && $setting->sync_customer_receipts === false) return null;
            if ($direction === 'out' && $setting->sync_supplier_payments === false) return null;

            $isBank = str_contains(strtolower($paymentMethod), 'bank') || str_contains(strtolower($paymentMethod), 'card');
            $assetAccount = $isBank
                ? ($setting->default_bank_account_id ?? $setting->default_cash_account_id)
                : ($setting->default_cash_account_id ?? $setting->default_bank_account_id);

            $receivableAccount = $setting->default_receivable_account_id;
            $payableAccount = $setting->default_payable_account_id;

            $items = [];
            $party = $partyId ? Party::withoutGlobalScopes()->find($partyId) : null;
            $partyName = $party ? $party->name : 'Party #' . $partyId;

            if ($direction === 'in') {
                // Customer Due Received (Receipt Voucher F6)
                // Dr. Cash/Bank | Cr. Accounts Receivable
                $voucherType = 'receipt';
                $voucherNo = 'RV-' . time() . rand(10, 99);
                $narration = "Receipt from Customer: {$partyName} (" . ($reference ?? 'Due Collection') . ")";

                $items[] = ['chart_of_account_id' => $assetAccount, 'debit' => $amount, 'credit' => 0];
                $items[] = ['chart_of_account_id' => $receivableAccount, 'debit' => 0, 'credit' => $amount];
            } else {
                // Supplier Due Paid (Payment Voucher F5)
                // Dr. Accounts Payable | Cr. Cash/Bank
                $voucherType = 'payment';
                $voucherNo = 'PV-' . time() . rand(10, 99);
                $narration = "Payment to Supplier: {$partyName} (" . ($reference ?? 'Bill Payment') . ")";

                $items[] = ['chart_of_account_id' => $payableAccount, 'debit' => $amount, 'credit' => 0];
                $items[] = ['chart_of_account_id' => $assetAccount, 'debit' => 0, 'credit' => $amount];
            }

            return self::saveVoucher(
                $companyId,
                $voucherType,
                $voucherNo,
                'payment_collection',
                null,
                $partyId,
                $date ?? Carbon::now(),
                $narration,
                $items
            );
        } catch (\Exception $e) {
            Log::error('Failed to auto-post payment collection journal: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * 4. Post Project Revenue / Invoicing Journal
     */
    public static function postProjectRevenueJournal(ProjectRevenue $revenue): ?TransactionJournal
    {
        try {
            $companyId = $revenue->company_id;
            if (!self::isEnabled($companyId)) return null;

            $setting = self::getSetting($companyId);
            if (!$setting || $setting->sync_project_revenue === false) return null;

            $existing = TransactionJournal::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->where('source_type', 'project_revenue')
                ->where('source_id', $revenue->id)
                ->first();

            $cashAccountId = $setting->default_cash_account_id;
            $receivableAccountId = $setting->default_receivable_account_id;
            $projectRevenueAccountId = $setting->default_project_revenue_account_id;

            $totalAmount = (float)($revenue->amount ?? 0);
            $receivedAmount = (float)($revenue->received_amount ?? 0);
            $dueAmount = max(0, $totalAmount - $receivedAmount);

            $items = [];

            if ($receivedAmount > 0 && $cashAccountId) {
                $items[] = ['chart_of_account_id' => $cashAccountId, 'debit' => $receivedAmount, 'credit' => 0];
            }
            if ($dueAmount > 0 && $receivableAccountId) {
                $items[] = ['chart_of_account_id' => $receivableAccountId, 'debit' => $dueAmount, 'credit' => 0];
            }
            if ($totalAmount > 0 && $projectRevenueAccountId) {
                $items[] = ['chart_of_account_id' => $projectRevenueAccountId, 'debit' => 0, 'credit' => $totalAmount];
            }

            if (empty($items)) return null;

            $voucherNo = 'PRJ-INV-' . ($revenue->id);
            $narration = "Project Invoice: {$revenue->title} (Project #{$revenue->project_id})";

            return self::saveVoucher(
                $companyId,
                'sales',
                $voucherNo,
                'project_revenue',
                $revenue->id,
                null,
                $revenue->date ?? Carbon::now(),
                $narration,
                $items,
                $existing
            );
        } catch (\Exception $e) {
            Log::error('Failed to auto-post project revenue journal: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * 5. Post HRM Payroll Journal
     */
    public static function postPayrollJournal(int $companyId, float $totalSalary, string $monthName, ?int $payslipId = null): ?TransactionJournal
    {
        try {
            if ($totalSalary <= 0 || !self::isEnabled($companyId)) return null;

            $setting = self::getSetting($companyId);
            if (!$setting || $setting->sync_payroll === false) return null;

            $salaryAccountId = $setting->default_salary_expense_account_id;
            $bankAccountId = $setting->default_bank_account_id ?? $setting->default_cash_account_id;

            $items = [
                ['chart_of_account_id' => $salaryAccountId, 'debit' => $totalSalary, 'credit' => 0],
                ['chart_of_account_id' => $bankAccountId, 'debit' => 0, 'credit' => $totalSalary],
            ];

            $voucherNo = 'PAY-' . strtoupper(substr($monthName, 0, 3)) . '-' . rand(100, 999);
            $narration = "Employee Salary Disbursed for period: {$monthName}";

            return self::saveVoucher(
                $companyId,
                'payment',
                $voucherNo,
                'payroll',
                $payslipId,
                null,
                Carbon::now(),
                $narration,
                $items
            );
        } catch (\Exception $e) {
            Log::error('Failed to auto-post payroll journal: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * 7. Post Courier Delivery Freight Expense Journal
     */
    public static function postCourierExpenseJournal(Order $order, ?float $customCharge = null): ?TransactionJournal
    {
        try {
            $companyId = $order->company_id;
            if (!self::isEnabled($companyId)) return null;

            $setting = self::getSetting($companyId);
            if (!$setting) return null;

            $courierExpenseAccountId = $setting->default_courier_expense_account_id;
            $payableAccountId = $setting->default_payable_account_id ?? $setting->default_cash_account_id;

            if (!$courierExpenseAccountId || !$payableAccountId) return null;

            // Resolve courier charge from parameter or courier_info
            $charge = $customCharge;
            if ($charge === null && !empty($order->courier_info)) {
                $info = is_array($order->courier_info) ? $order->courier_info : json_decode($order->courier_info, true);
                $charge = (float)($info['courier_charge'] ?? $info['delivery_charge'] ?? $info['delivery_fee'] ?? 0);
            }

            if (!$charge || $charge <= 0) return null;

            $existing = TransactionJournal::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->where('source_type', 'courier_expense')
                ->where('source_id', $order->id)
                ->first();

            $courierName = 'Courier';
            if (!empty($order->courier_info)) {
                $info = is_array($order->courier_info) ? $order->courier_info : json_decode($order->courier_info, true);
                $courierName = $info['courier_name'] ?? 'Courier';
            }

            $items = [
                ['chart_of_account_id' => $courierExpenseAccountId, 'debit' => $charge, 'credit' => 0],
                ['chart_of_account_id' => $payableAccountId,        'debit' => 0,       'credit' => $charge],
            ];

            $voucherNo = 'CV-' . ($order->order_no ?? $order->id);
            $narration = "Courier Delivery Charge for Order #{$order->order_no} via {$courierName}";

            return self::saveVoucher(
                $companyId,
                'payment',
                $voucherNo,
                'courier_expense',
                $order->id,
                null,
                $order->order_date ?? Carbon::now(),
                $narration,
                $items,
                $existing
            );
        } catch (\Exception $e) {
            Log::error('Failed to auto-post courier expense journal: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * 8. Post Direct/Indirect Expense Journal (From Expense Module)
     */
    public static function postExpenseJournal(\App\Models\TransactionExpense $expense): ?TransactionJournal
    {
        try {
            $companyId = $expense->company_id;
            if (!self::isEnabled($companyId)) return null;

            $setting = self::getSetting($companyId);
            if (!$setting || $setting->sync_expense_entries === false) return null;

            $fromAccountId = $expense->expense_from_id;
            if (!$fromAccountId) return null;

            $categories = $expense->categories()->get();
            if ($categories->isEmpty()) return null;

            $items = [];
            $totalAmount = 0;

            foreach ($categories as $cat) {
                $amt = (float)$cat->amount;
                if ($amt > 0 && $cat->chart_of_account_id) {
                    $items[] = ['chart_of_account_id' => $cat->chart_of_account_id, 'debit' => $amt, 'credit' => 0];
                    $totalAmount += $amt;
                }
            }

            if ($totalAmount <= 0) return null;

            // Credit the payment account (Cash/Bank):
            $items[] = ['chart_of_account_id' => $fromAccountId, 'debit' => 0, 'credit' => $totalAmount];

            $existing = TransactionJournal::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->where('source_type', 'transaction_expense')
                ->where('source_id', $expense->id)
                ->first();

            $voucherNo = 'EV-' . ($expense->reference_number ?? $expense->id);
            $narration = "Expense Voucher #{$expense->reference_number}: " . ($expense->note ?? 'Direct Expense');

            return self::saveVoucher(
                $companyId,
                'payment',
                $voucherNo,
                'transaction_expense',
                $expense->id,
                null,
                $expense->date ?? Carbon::now(),
                $narration,
                $items,
                $existing
            );
        } catch (\Exception $e) {
            Log::error('Failed to auto-post expense journal: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * 9. Post Other Income Journal (From Income Module)
     */
    public static function postIncomeJournal(\App\Models\TransactionIncome $income): ?TransactionJournal
    {
        try {
            $companyId = $income->company_id;
            if (!self::isEnabled($companyId)) return null;

            $setting = self::getSetting($companyId);
            if (!$setting || $setting->sync_income_entries === false) return null;

            $toAccountId = $income->income_to_id;
            if (!$toAccountId) return null;

            $categories = $income->categories()->get();
            if ($categories->isEmpty()) return null;

            $items = [];
            $totalAmount = 0;

            foreach ($categories as $cat) {
                $amt = (float)$cat->amount;
                if ($amt > 0 && $cat->chart_of_account_id) {
                    $items[] = ['chart_of_account_id' => $cat->chart_of_account_id, 'debit' => 0, 'credit' => $amt];
                    $totalAmount += $amt;
                }
            }

            if ($totalAmount <= 0) return null;

            // Debit the receiving account (Cash/Bank):
            array_unshift($items, ['chart_of_account_id' => $toAccountId, 'debit' => $totalAmount, 'credit' => 0]);

            $existing = TransactionJournal::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->where('source_type', 'transaction_income')
                ->where('source_id', $income->id)
                ->first();

            $voucherNo = 'IV-' . ($income->reference_number ?? $income->id);
            $narration = "Income Voucher #{$income->reference_number}: " . ($income->note ?? 'Direct Income');

            return self::saveVoucher(
                $companyId,
                'receipt',
                $voucherNo,
                'transaction_income',
                $income->id,
                null,
                $income->date ?? Carbon::now(),
                $narration,
                $items,
                $existing
            );
        } catch (\Exception $e) {
            Log::error('Failed to auto-post income journal: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * 10. Post Internal Transfer Journal (Contra Voucher - Bank to Cash, etc.)
     */
    public static function postTransferJournal(\App\Models\TransactionTransfer $transfer): ?TransactionJournal
    {
        try {
            $companyId = $transfer->company_id;
            if (!self::isEnabled($companyId)) return null;

            $fromAccountId = $transfer->from_account_id;
            if (!$fromAccountId) return null;

            $details = $transfer->details()->get();
            if ($details->isEmpty()) return null;

            $items = [];
            $totalAmount = 0;

            foreach ($details as $d) {
                $amt = (float)$d->amount;
                if ($amt > 0 && $d->transfer_to_id) {
                    $items[] = ['chart_of_account_id' => $d->transfer_to_id, 'debit' => $amt, 'credit' => 0];
                    $totalAmount += $amt;
                }
            }

            if ($totalAmount <= 0) return null;

            // Credit the source account:
            $items[] = ['chart_of_account_id' => $fromAccountId, 'debit' => 0, 'credit' => $totalAmount];

            $existing = TransactionJournal::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->where('source_type', 'transaction_transfer')
                ->where('source_id', $transfer->id)
                ->first();

            $voucherNo = 'TV-' . ($transfer->reference_number ?? $transfer->id);
            $narration = "Contra Transfer #{$transfer->reference_number}: " . ($transfer->note ?? 'Internal Account Transfer');

            return self::saveVoucher(
                $companyId,
                'contra',
                $voucherNo,
                'transaction_transfer',
                $transfer->id,
                null,
                $transfer->date ?? Carbon::now(),
                $narration,
                $items,
                $existing
            );
        } catch (\Exception $e) {
            Log::error('Failed to auto-post transfer journal: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Helper to save a balanced voucher
     */
    private static function saveVoucher(
        int $companyId,
        string $voucherType,
        string $voucherNo,
        string $sourceType,
        ?int $sourceId,
        ?int $partyId,
        $date,
        string $narration,
        array $items,
        ?TransactionJournal $existing = null
    ): TransactionJournal {
        return DB::transaction(function () use (
            $companyId, $voucherType, $voucherNo, $sourceType, $sourceId, $partyId, $date, $narration, $items, $existing
        ) {
            // Balance validation & minor rounding adjustment
            $totalDebit = collect($items)->sum('debit');
            $totalCredit = collect($items)->sum('credit');

            // Parity check: If discrepancy > 0.05, adjust last credit/debit row
            $diff = round($totalDebit - $totalCredit, 2);
            if ($diff != 0 && count($items) > 1) {
                if ($diff > 0) {
                    $items[count($items) - 1]['credit'] += $diff;
                } else {
                    $items[count($items) - 1]['debit'] += abs($diff);
                }
                $totalDebit = collect($items)->sum('debit');
                $totalCredit = collect($items)->sum('credit');
            }

            $journalData = [
                'company_id'       => $companyId,
                'created_by'       => auth()->id() ?? 1,
                'reference_number' => $voucherNo,
                'voucher_type'     => $voucherType,
                'voucher_no'       => $voucherNo,
                'source_type'      => $sourceType,
                'source_id'        => $sourceId,
                'party_id'         => $partyId,
                'date'             => Carbon::parse($date)->format('Y-m-d'),
                'description'      => $narration,
                'narration'        => $narration,
                'total_debit'      => $totalDebit,
                'total_credit'     => $totalCredit,
                'status'           => Status::Approved->value,
            ];

            if ($existing) {
                $existing->update($journalData);
                $existing->accounts()->delete();
                $journal = $existing;
            } else {
                $journal = TransactionJournal::withoutGlobalScopes()->create($journalData);
            }

            foreach ($items as $item) {
                if (($item['debit'] > 0 || $item['credit'] > 0) && !empty($item['chart_of_account_id'])) {
                    TransactionJournalAccount::create([
                        'transaction_journal_id' => $journal->id,
                        'chart_of_account_id'    => $item['chart_of_account_id'],
                        'debit'                  => $item['debit'] ?? 0,
                        'credit'                 => $item['credit'] ?? 0,
                    ]);
                }
            }

            return $journal;
        });
    }
}
