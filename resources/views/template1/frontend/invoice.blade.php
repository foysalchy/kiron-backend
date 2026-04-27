<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - #{{ $order->order_no }}</title>
    <link rel="icon" type="image/x-icon" href="{{ $setup->favicon_url }}">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: #F8FAFC;
            color: #1E293B;
            font-size: 14px;
        }

        /* ── Toolbar ── */
        .toolbar {
            background: #fff;
            border-bottom: 1px solid #f0f0f0;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .06);
            position: sticky;
            top: 0;
            z-index: 50;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .toolbar-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .toolbar-title {
            font-size: 18px;
            font-weight: 700;
            color: #1D2128;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            color: #374151;
            text-decoration: none;
            cursor: pointer;
        }

        .btn-back:hover {
            background: #f3f4f6;
        }

        .toolbar-right {
            display: flex;
            gap: 10px;
        }

        .btn-print {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 18px;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            color: #374151;
            cursor: pointer;
        }

        .btn-print:hover {
            background: #f9fafb;
        }

        .btn-download {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            background: #1D2128;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            color: #fff;
            cursor: pointer;
        }

        .btn-download:hover {
            background: #000;
        }

        /* ── Page Wrapper ── */
        .page-wrapper {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px 60px;
        }

        /* ── Invoice Card ── */
        .invoice-card {
            background: #fff;
            border-radius: 16px;
            border: 1px solid #e9ecef;
            box-shadow: 0 8px 40px rgba(0, 0, 0, .08);
            overflow: hidden;
        }

        .invoice-inner {
            padding: 60px;
        }

        /* ── Header ── */
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 30px;
            margin-bottom: 48px;
        }

        .shop-logo-row {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 18px;
        }

        .shop-logo-row img {
            height: 48px;
            width: auto;
            object-fit: contain;
        }

        .logo-placeholder {
            width: 48px;
            height: 48px;
            background: #FF6A00;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo-placeholder span {
            color: #fff;
            font-size: 22px;
            font-weight: 900;
        }

        .shop-name {
            font-size: 26px;
            font-weight: 700;
            color: #1D2128;
            letter-spacing: -.5px;
        }

        .shop-info p {
            font-size: 13px;
            color: #6b7280;
            font-weight: 500;
            line-height: 1.7;
        }

        .invoice-meta {
            text-align: right;
        }

        .invoice-title {
            font-size: 38px;
            font-weight: 700;
            color: #111827;
            text-transform: uppercase;
            letter-spacing: -1px;
            margin-bottom: 20px;
        }

        .invoice-meta-info p {
            font-size: 13px;
            color: #6b7280;
            font-weight: 500;
            line-height: 1.8;
        }

        .invoice-meta-info span {
            color: #1D2128;
            font-weight: 600;
        }

        /* ── Billing Row ── */
        .billing-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 48px;
            margin-bottom: 48px;
        }

        .billing-section h3 {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .8px;
            color: #111827;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 8px;
            margin-bottom: 14px;
        }

        .billing-section .customer-name {
            font-size: 15px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 4px;
        }

        .billing-section p {
            font-size: 13px;
            color: #4b5563;
            font-weight: 500;
            line-height: 1.8;
        }

        /* ── Product Table ── */
        .product-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }

        .product-table thead tr {
            border-top: 2px solid #f1f5f9;
            border-bottom: 2px solid #f1f5f9;
        }

        .product-table th {
            padding: 16px 8px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .6px;
            color: #111827;
        }

        .product-table th:first-child {
            text-align: left;
            padding-left: 0;
        }

        .product-table th:nth-child(2) {
            text-align: center;
        }

        .product-table th:nth-child(3),
        .product-table th:last-child {
            text-align: right;
            padding-right: 0;
        }

        .product-table tbody tr {
            border-bottom: 1px solid #f8fafc;
        }

        .product-table td {
            padding: 20px 8px;
            vertical-align: top;
        }

        .product-table td:first-child {
            padding-left: 0;
        }

        .product-table td:last-child {
            padding-right: 0;
        }

        .product-table {
            page-break-inside: auto;
        }

        .product-table tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        .product-title {
            font-size: 14px;
            font-weight: 700;
            color: #111827;
        }

        .product-variant {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: #9ca3af;
            margin-top: 4px;
        }

        .td-center {
            text-align: center;
            font-weight: 700;
            color: #374151;
        }

        .td-right {
            text-align: right;
            color: #374151;
        }

        .td-right-bold {
            text-align: right;
            font-weight: 700;
            color: #111827;
        }

        /* ── Summary ── */
        .summary-wrapper {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 56px;
        }

        .summary-box {
            width: 300px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            font-weight: 500;
            color: #6b7280;
            padding: 6px 0;
        }

        .summary-row span:last-child {
            color: #111827;
        }

        .summary-row.discount {
            color: #16a34a;
        }

        .summary-row.discount span:last-child {
            color: #16a34a;
        }

        .summary-divider {
            height: 1px;
            background: #f1f5f9;
            margin: 8px 0;
        }

        .summary-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 10px;
        }

        .summary-total .label {
            font-size: 16px;
            font-weight: 700;
            color: #111827;
        }

        .summary-total .amount {
            font-size: 24px;
            font-weight: 700;
            color: #FF6A00;
        }

        /* ── Footer Info ── */
        .footer-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 48px;
            border-top: 1px solid #f1f5f9;
            padding-top: 36px;
            margin-bottom: 36px;
            page-break-inside: avoid;
        }

        .footer-row h4 {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .6px;
            color: #111827;
            margin-bottom: 12px;
        }

        .footer-row ul {
            list-style: none;
        }

        .footer-row ul li {
            font-size: 12px;
            color: #6b7280;
            font-weight: 500;
            line-height: 2;
        }

        .footer-contact {
            text-align: right;
        }

        .footer-contact p {
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 6px;
        }

        .footer-contact .contact-info {
            font-size: 13px;
            font-weight: 700;
            color: #1D2128;
            line-height: 1.8;
        }

        /* ── Thank You ── */
        .thankyou {
            text-align: center;
            padding-top: 28px;
            border-top: 1px solid #f8fafc;
            page-break-inside: avoid;
        }

        .invoice-footer-section {
            page-break-inside: avoid;
        }

        .thankyou p:first-child {
            font-size: 17px;
            font-weight: 700;
            color: #111827;
        }

        .thankyou p:last-child {
            font-size: 13px;
            color: #9ca3af;
            font-weight: 500;
            margin-top: 4px;
        }

        /* ── PRINT ── */
        @media print {
            @page {
                size: A4;
                margin: .6in .4in;
            }

            body {
                background: #fff !important;
            }

            .toolbar {
                display: none !important;
            }

            .page-wrapper {
                margin: 0;
                padding: 0;
                max-width: 100%;
            }

            .invoice-card {
                border: 1px solid #e5e7eb !important;
                box-shadow: none !important;
                border-radius: 0 !important;
            }

            .invoice-inner {
                padding: 30px !important;
            }

            .invoice-header {
                margin-bottom: 28px !important;
            }

            .billing-row {
                margin-bottom: 28px !important;
                gap: 24px !important;
            }

            .summary-wrapper {
                margin-bottom: 28px !important;
            }

            .summary-wrapper,
            .footer-row,
            .thankyou {
                page-break-inside: avoid;
            }

            /* Summary + Footer  */
            .summary-wrapper+div {
                page-break-before: avoid;
            }

            .footer-row {
                padding-top: 20px !important;
                margin-bottom: 20px !important;
            }
        }
    </style>
</head>

<body>

    <!-- Toolbar -->
    <div class="toolbar">
        <div class="toolbar-left">
            <a href="{{ route('user.order.details', $order->id) }}" class="btn-back">
                <i class="fas fa-arrow-left" style="font-size:11px;"></i> Back
            </a>
            <span class="toolbar-title">Invoice</span>
        </div>
        <div class="toolbar-right">
            <button class="btn-print" onclick="window.print()">
                <i class="fas fa-print"></i> Print
            </button>
            <button class="btn-download" onclick="downloadInvoicePDF()">
                <i class="fas fa-download"></i> Download PDF
            </button>
        </div>
    </div>

    <!-- Page -->
    <div class="page-wrapper">
        <div id="invoice-content" class="invoice-card">
            <div class="invoice-inner">

                <!-- Header -->
                <div class="invoice-header">
                    <div>
                        <div class="shop-logo-row">
                            @if ($setup->logo)
                                <img src="{{ $setup->logo_url }}" crossorigin="anonymous">
                            @else
                                <div class="logo-placeholder">
                                    <span>{{ substr($setup->shop_name, 0, 1) }}</span>
                                </div>
                            @endif
                            <span class="shop-name">{{ $setup->shop_name }}</span>
                        </div>
                        <div class="shop-info">
                            <p>{{ $setup->address }}</p>
                            <p>Phone: {{ $setup->phone }}</p>
                            <p>Email: {{ $setup->email }}</p>
                            <p>Website: www.{{ strtolower(str_replace(' ', '', $setup->shop_name)) }}.com</p>
                        </div>
                    </div>
                    <div class="invoice-meta">
                        <div class="invoice-title">Invoice</div>
                        <div class="invoice-meta-info">
                            <p>Invoice no: <span>#{{ $order->order_no }}</span></p>
                            <p>Order Date: <span>{{ $order->created_at->format('d F, Y') }}</span></p>
                            <p>Payment Status: <span
                                    class="{{ $order->payment_status_color }}">{{ $order->payment_status_label }}</span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Billing -->
                <div class="billing-row">
                    <div class="billing-section">
                        <h3>Bill To:</h3>
                        <p class="customer-name">{{ $order->customer->name }}</p>
                        <p>{{ $order->customer->address }}</p>
                        <p>Phone: {{ $order->customer->phone }}</p>
                        <p>Email: {{ $order->customer->email ?? 'N/A' }}</p>
                    </div>
                    <p><strong>Payment Method:</strong>
                    @if ($order->orderPayments->isNotEmpty())
                        <span style="text-transform: uppercase; font-weight: 700; color: #1D2128;">
                            {{ str_replace(['_', '-'], ' ', $order->orderPayments->last()->payment_method) }}
                        </span>
                    @else
                        Cash on Delivery
                    @endif
                    </p>

                    <p><strong>Payment Status:</strong>
                        <span class="{{ $order->payment_status_color }}" style="font-weight:700;">
                            {{ $order->payment_status_label }}
                        </span>
                    </p>
                </div>

                <!-- Product Table -->
                <div>
                    <table class="product-table">
                        <thead>
                            <tr>
                                <th>Product Description</th>
                                <th style="text-align:center;">Qty</th>
                                <th style="text-align:right;">Price</th>
                                <th style="text-align:right;padding-right:0;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->orderDetails as $item)
                                <tr>
                                    <td style="padding-left:0;">
                                        <p class="product-title">{{ $item->product->title ?? 'N/A' }}</p>
                                        @if ($item->variation)
                                            <p class="product-variant">{{ $item->variation->display_name }}</p>
                                        @endif
                                    </td>
                                    <td class="td-center">{{ $item->quantity }}</td>
                                    <td class="td-right">{{ $setup->currency }}
                                        {{ number_format($item->unit_price, 0) }}</td>
                                    <td class="td-right-bold" style="padding-right:0;">{{ $setup->currency }}
                                        {{ number_format($item->total, 0) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Summary -->
                <div class="summary-wrapper">
                    <div class="summary-box">
                        <div class="summary-row">
                            <span>Subtotal:</span>
                            <span>{{ $setup->currency }} {{ number_format($order->subtotal, 0) }}</span>
                        </div>
                        <div class="summary-row">
                            <span>Delivery Charge:</span>
                            <span>{{ $setup->currency }} {{ number_format($order->other_charges, 0) }}</span>
                        </div>
                        @if ($order->coupon_discount > 0)
                            <div class="summary-row discount">
                                <span>Coupon Discount:</span>
                                <span>- {{ $setup->currency }} {{ number_format($order->coupon_discount, 0) }}</span>
                            </div>
                        @endif
                        <div class="summary-divider"></div>
                        <div class="summary-total">
                            <span class="label">Total Paid:</span>
                            <span class="amount">{{ $setup->currency }}
                                {{ number_format($order->grand_total, 0) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Footer Info -->
                <!-- Footer Info + Thank You wrap -->
                <div style="page-break-inside: avoid;">
                    <!-- Footer Info -->
                    <div class="footer-row">
                        <div>
                            <h4>Terms & Conditions:</h4>
                            <ul>
                                <li>• Contact us within 7 days for eligible product returns.</li>
                                <li>• 1-year service warranty applies to manufacturing defects.</li>
                                <li>• All prices are listed in Bangladeshi Taka (BDT).</li>
                            </ul>
                        </div>
                        <div class="footer-contact">
                            <p>For any questions, please contact us:</p>
                            <p class="contact-info">
                                Support: {{ $setup->phone }}<br>
                                Email: {{ $setup->email }}
                            </p>
                        </div>
                    </div>

                    <!-- Thank You -->
                    <div class="thankyou">
                        <p>Thank you for your order!</p>
                        <p>Thank you for shopping with {{ $setup->shop_name }}.</p>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        function downloadInvoicePDF() {
            const element = document.getElementById('invoice-content');
            const opt = {
                margin: [5, 5],
                filename: 'Invoice-{{ $order->order_no }}.pdf',
                image: {
                    type: 'jpeg',
                    quality: 0.98
                },
                html2canvas: {
                    scale: 2,
                    useCORS: true,
                    letterRendering: true
                },
                jsPDF: {
                    unit: 'mm',
                    format: 'a4',
                    orientation: 'portrait'
                }
            };
            html2pdf().set(opt).from(element).save();
        }
    </script>
</body>

</html>
