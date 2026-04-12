<!DOCTYPE html>
<html lang="bn">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - #{{ $order->order_no }}</title>

    <!-- Google Font: Outfit -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet" />
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #F8FAFC;
            color: #1E293B;
        }

        .font-black {
            font-weight: 900;
        }

        @media print {
            @page {
                size: auto;
                margin: .5in;
            }

            body {
                background-color: white !important;
            }

            .print\:hidden {
                display: none !important;
            }

            .print\:p-0 {
                padding: 0 !important;
            }

            .print\:shadow-none {
                box-shadow: none !important;
            }

            .print\:border-none {
                border: none !important;
            }

            .container {
                max-width: 100% !important;
                width: 100% !important;
            }

            .invoice-card {
                border: 1px solid #e5e7eb !important;
                padding: 20px !important;
            }

            .p-10 {
                padding: 1.5rem !important;
            }

            .md\:p-16 {
                padding: 1.5rem !important;
            }
        }
    </style>
</head>

<body>

    <!-- Top Toolbar -->
    <div class="bg-white border-b border-gray-100 shadow-sm sticky top-0 z-50 print:hidden">
        <div class="container mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-6">
                <a href="{{ route('user.order.details', $order->id) }}"
                    class="flex items-center gap-2 px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm font-bold text-gray-700 hover:bg-gray-100 transition-all">
                    <i class="fas fa-arrow-left text-xs"></i> Back
                </a>
                <h1 class="text-xl font-bold text-gray-800">Invoice</h1>
            </div>
            <div class="flex gap-3">
                <button onclick="window.print()"
                    class="cursor-pointer px-5 py-2 bg-white border border-gray-200 rounded-lg text-sm font-bold text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                    <i class="fas fa-print"></i> Print
                </button>
                <button onclick="downloadInvoicePDF()"
                    class="cursor-pointer px-4 py-2 bg-[#1D2128] text-white rounded-lg text-sm font-bold hover:bg-black transition-all flex items-center gap-2">
                    <i class="fas fa-download"></i> Download PDF
                </button>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-10 print:p-0">
        <div id="invoice-content"
            class="max-w-4xl mx-auto bg-white rounded-2xl border border-gray-100 shadow-xl print:shadow-none print:border-none overflow-hidden invoice-card">
            <div class="p-10 md:p-16">

                <!-- Header: Logo & Shop Info -->
                <div class="flex flex-col md:flex-row justify-between items-start gap-8 mb-12">
                    <div>
                        <div class="flex items-center gap-3 mb-5">
                            @if ($setup->logo)
                                <img src="{{ $setup->logo_url }}" crossorigin="anonymous"
                                    class="h-12 w-auto object-contain">
                            @else
                                <div class="bg-[#FF6A00] w-12 h-12 flex items-center justify-center rounded-xl">
                                    <span
                                        class="text-white text-2xl font-black">{{ substr($setup->shop_name, 0, 1) }}</span>
                                </div>
                            @endif
                            <span
                                class="text-3xl font-bold tracking-tight text-[#1D2128]">{{ $setup->shop_name }}</span>
                        </div>
                        <div class="text-[13px] text-gray-500 space-y-1 font-medium leading-relaxed">
                            <p>{{ $setup->address }}</p>
                            <p>Phone: {{ $setup->phone }}</p>
                            <p>Email: {{ $setup->email }}</p>
                            <p>Website: www.{{ strtolower(str_replace(' ', '', $setup->shop_name)) }}.com</p>
                        </div>
                    </div>
                    <div class="md:text-right">
                        <h2 class="text-4xl font-bold text-gray-900 mb-6 uppercase tracking-tighter">Invoice</h2>
                        <div class="text-[13px] space-y-1.5 font-medium">
                            <p class="text-gray-500">Invoice no: <span
                                    class="text-gray-900">#{{ $order->order_no }}</span></p>
                            <p class="text-gray-500">Order no: <span class="text-gray-900">#{{ $order->id }}</span>
                            </p>
                            <p class="text-gray-500">Date: <span
                                    class="text-gray-900">{{ $order->created_at->format('d F, Y') }}</span></p>
                            <p class="text-gray-500">Payment Status:
                                <span class="font-bold {{ $order->payment_status_color }}">
                                    {{ $order->payment_status_label }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Billing & Payment Details -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-12 mb-16">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 uppercase mb-4 border-b border-gray-100 pb-2">Bill
                            To:</h3>
                        <div class="text-[14px] text-gray-600 space-y-1 font-medium">
                            <p class="text-gray-900 font-bold text-base">{{ $order->customer->name }}</p>
                            <p>{{ $order->customer->address }}</p>
                            <p>Phone: {{ $order->customer->phone }}</p>
                            <p>Email: {{ $order->customer->email ?? 'N/A' }}</p>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 uppercase mb-4 border-b border-gray-100 pb-2">Payment
                            Information:</h3>
                        <div class="text-[14px] text-gray-600 space-y-1 font-medium">
                            <p><span class="font-bold">Payment Method:</span>
                                {{ strtoupper(str_replace('_', ' ', $order->payment_method ?? 'Cash on Delivery')) }}
                            </p>
                            <p><span class="font-bold">Payment Status:</span> <span
                                    class="{{ $order->payment_status_color }}">{{ $order->payment_status_label }}</span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Product Table -->
                <div class="mb-12">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-y-2 border-gray-100 text-gray-900">
                                <th class="text-left py-5 font-bold uppercase tracking-wider">Product Description</th>
                                <th class="text-center py-5 font-bold uppercase tracking-wider">Qty</th>
                                <th class="text-right py-5 font-bold uppercase tracking-wider">Price</th>
                                <th class="text-right py-5 font-bold uppercase tracking-wider">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 border-b border-gray-100">
                            @foreach ($order->orderDetails as $item)
                                <tr>
                                    <td class="py-6 pr-4">
                                        <p class="text-gray-900 font-bold text-base">
                                            {{ $item->product->title ?? 'N/A' }}</p>
                                        @if ($item->variation)
                                            <p class="text-[11px] text-gray-500 font-bold uppercase mt-1">
                                                {{ $item->variation->display_name }}
                                            </p>
                                        @endif
                                    </td>
                                    <td class="py-6 text-center text-gray-700 font-bold">{{ $item->quantity }}</td>
                                    <td class="py-6 text-right text-gray-700">
                                        ৳{{ number_format($item->unit_price, 0) }}</td>
                                    <td class="py-6 text-right font-bold text-gray-900">
                                        ৳{{ number_format($item->total, 0) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Summary Section -->
                <div class="flex justify-end mb-16">
                    <div class="w-full max-w-[300px] space-y-3 font-medium">
                        <div class="flex justify-between text-gray-500">
                            <span>Subtotal:</span>
                            <span class="text-gray-900">৳{{ number_format($order->subtotal, 0) }}</span>
                        </div>
                        <div class="flex justify-between text-gray-500">
                            <span>Delivery Charge:</span>
                            <span class="text-gray-900">৳{{ number_format($order->other_charges, 0) }}</span>
                        </div>
                        @if ($order->coupon_discount > 0)
                            <div class="flex justify-between text-green-600">
                                <span>Coupon Discount:</span>
                                <span>- ৳{{ number_format($order->coupon_discount, 0) }}</span>
                            </div>
                        @endif
                        <div class="h-px bg-gray-100 my-2"></div>
                        <div class="flex justify-between items-center pt-2">
                            <span class="text-lg font-bold text-gray-900">Total Paid:</span>
                            <span
                                class="text-2xl font-bold text-[#FF6A00]">৳{{ number_format($order->grand_total, 0) }}</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-12 border-t border-gray-100 pt-10 mb-10">
                    <div>
                        <h4 class="text-[13px] font-bold text-gray-900 uppercase mb-3">Terms & Conditions:</h4>
                        <ul class="text-[12px] text-gray-500 space-y-1.5 font-medium leading-relaxed">
                            <li>• Contact us within 7 days for eligible product returns.</li>
                            <li>• 1-year service warranty applies to manufacturing defects.</li>
                            <li>• All prices are listed in Bangladeshi Taka (BDT).</li>
                        </ul>
                    </div>
                    <div class="md:text-right">
                        <p class="text-[12px] text-gray-500 mb-2">For any questions, please contact us:</p>
                        <p class="text-[13px] font-bold text-gray-800">Support: {{ $setup->phone }}</p>
                        <p class="text-[13px] font-bold text-gray-800">Email: {{ $setup->email }}</p>
                    </div>
                </div>

                <!-- Thank You Message -->
                <div class="text-center pt-8 border-t border-gray-50">
                    <p class="text-lg font-bold text-gray-900">Thank you for your order!</p>
                    <p class="text-sm text-gray-400 font-medium mt-1">Thank you for shopping with
                        {{ $setup->shop_name }}.</p>
                </div>

            </div>
        </div>
    </div>

    <!-- Library for Download -->
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
