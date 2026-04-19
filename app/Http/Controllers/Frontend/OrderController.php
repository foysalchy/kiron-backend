<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\Status;
use App\Helpers\FileUploadHelper;
use App\Http\Controllers\Controller;
use App\Models\{CustomerPaymentMethod, Order, Party, ProductReview, ProductVariation, Warehouse};
use App\Services\OrderService;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Hash, Log, Session};

class OrderController extends FrontendController
{
    public function __construct(protected OrderService $orderService) {}

    public function index($store)
    {

        if (Cart::count() == 0) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty!');
        }
        $paymentMethods = CustomerPaymentMethod::where('status', Status::Active->value)
            ->get();

        // Auto-create draft for logged in users
        if (auth('customer')->check()) {
            $this->createDraftOrder($store, [
                'phone'   => auth('customer')->user()->phone,
                'name'    => auth('customer')->user()->name,
                'address' => auth('customer')->user()->address,
            ]);
        }

        $cartContent = Cart::content();
        $subtotal = (float) str_replace(',', '', Cart::subtotal());
        $shipping = session()->get('shipping_cost', 60);
        $discount = session()->has('coupon') ? session('coupon')['discount_amount'] : 0;
        $total = ($subtotal - $discount) + $shipping;
        $shipping_area = session()->get('shipping_area', 'inside');

        return $this->view('frontend.checkout', compact(
            'cartContent',
            'subtotal',
            'discount',
            'shipping',
            'total',
            'shipping_area',
            'paymentMethods',
        ));
    }

    public function partialSave($store, Request $request)
    {
        if (!$request->phone || strlen($request->phone) < 11) {
            return response()->json(['success' => false, 'message' => 'Invalid phone number']);
        }
        $res = $this->createDraftOrder($store, $request->all());
        return response()->json($res);
    }

    private function createDraftOrder($store, $data)
    {

        $cartContent = Cart::content();
        if ($cartContent->isEmpty()) return ['success' => false, 'error' => 'Cart is empty'];

        // 1. Strict Warehouse Check
        $warehouseQuery = Warehouse::active();
        foreach ($cartContent as $item) {
            $variationId = $item->options->variation_id ?? null;
            $warehouseQuery->whereHas('stocks', function ($query) use ($variationId, $item) {
                if ($variationId) $query->where('product_variation_id', $variationId);
                $query->where('quantity', '>=', $item->qty);
            });
        }
        $exactWarehouse = $warehouseQuery->first();

        // If no stock in any exact warehouse, return error
        if (!$exactWarehouse) {
            return ['success' => false, 'error' => 'One or more items are out of stock in our warehouses.'];
        }

        try {
            DB::beginTransaction();

            $customer = auth('customer')->check() ? auth('customer')->user() : Party::updateOrCreate(
                ['phone' => $data['phone'], 'type' => Party::TYPE_CUSTOMER],
                ['name' => $data['name'] ?? 'Guest', 'password' => Hash::make('12345678'), 'status' => Status::Pending->value]
            );

            $items = [];
            foreach ($cartContent as $item) {
                $variationId = $item->options->variation_id ?? null;
                $actualProductId = $variationId ? ProductVariation::find($variationId)?->product_id : (int) $item->id;
                $items[] = [
                    'product_id'   => $actualProductId,
                    'variation_id' => $variationId,
                    'quantity'     => (int) $item->qty,
                    'unit_price'   => (float) $item->price,
                ];
            }
            $shippingAddress = [
                'name'    => $data['name'] ?? $customer->name,
                'phone'   => $data['phone'] ?? $customer->phone,
                'address' => $data['address'] ?? $customer->address ?? 'N/A',
            ];

            $orderData = [
                'warehouse_id'  => $exactWarehouse->id,
                'customer_id'   => $customer->id,
                'items'         => $items,
                'status'        => Status::Draft->value,
                'order_date'    => now(),
                'coupon_code'   => session('coupon')['coupon_code'] ?? null,
                'other_charges' => session()->get('shipping_cost', 60),
                'shipping_address' => $shippingAddress,
            ];

            // Cleanup old drafts
            $oldId = Session::get('current_draft_order_id');
            if ($oldId) Order::where('id', $oldId)->where('status', Status::Draft->value)->delete();

            $order = $this->orderService->createSalesOrder($orderData);
            Session::put('current_draft_order_id', $order->id);

            DB::commit();
            return ['success' => true];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function storeOrder($store, Request $request)
    {
        $request->validate(['name' => 'required', 'phone' => 'required', 'address' => 'required']);

        // 1. Try to find the draft order
        $orderId = Session::get('current_draft_order_id');
        $order = Order::where('status', Status::Draft->value)->find($orderId);

        // 2. If session failed, try to find by phone
        if (!$order) {
            $customer = Party::where('phone', $request->phone)->first();
            if ($customer) {
                $order = Order::where('status', Status::Draft->value)->latest()->first();
            }
        }

        // 3. CRITICAL FALLBACK: If still no draft, try to create it NOW
        if (!$order) {
            $draftResult = $this->createDraftOrder($store, $request->all());
            if ($draftResult['success']) {
                $order = Order::find(Session::get('current_draft_order_id'));
            } else {
                // This means the "Order session not found" was actually a "Stock issue"
                return back()->with('error', 'Order failed: ' . $draftResult['error']);
            }
        }

        // 4. Confirm the Order
        try {
            DB::beginTransaction();
            $finalAddress = [
                'name'    => $request->name,
                'phone'   => $request->phone,
                'address' => $request->address,
            ];
            $order->update(['shipping_address' => $finalAddress]);
            $order->customer->update(['name' => $request->name, 'address' => $request->address]);

            // This will deduct stock from the Exact Warehouse assigned in the draft
            $this->orderService->changeStatus($order->id, Status::Pending->value);

            DB::commit();
            Cart::destroy();
            Session::forget(['coupon', 'current_draft_order_id']);
            return redirect()->route('order.invoice', $order->id)->with('success', 'Your order has been successfully placed.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Final Confirmation Error: ' . $e->getMessage());
        }
    }
    public function orderDetails($store, $id)
    {
        $order = Order::with([
            'customer',
            'orderDetails.product' => function ($query) {
                $query->withTrashed(); // This loads deleted products
            },
            'orderDetails.variation.attributes.attributeValue',
            'orderDetails.variation.attributes.attributeGroup'
        ])
            ->find($id);

        if (!$order) {
            abort(404);
        }

        // Authorization check
        if (auth('customer')->check() && $order->customer_id !== auth('customer')->id()) {
            abort(403, 'Unauthorized access to this order details.');
        }

        return $this->view('frontend.orderDetails', compact('order'));
    }
    public function invoice($store, $id)
    {
        $order = Order::with([
            'customer',
            'orderDetails.product' => function ($q) {
                $q->withTrashed();
            }, // Add this
            'company'
        ])
            ->findOrFail($id);

        return $this->view('frontend.invoice', compact('order'));
    }
    public function trackOrder(Request $request)
    {
        $order = null;

        if ($request->filled('order_no')) {
            $order = Order::where('order_no', $request->order_no)
                ->first();

            if (!$order) {
                return back()->with('error', 'Order not found with the provided order number.');
            }
        }

        return $this->view('frontend.product-track', compact('order'));
    }
    public function storeReview(Request $request)
    {
        // 1. Added variation_id to validation
        $request->validate([
            'product_id'   => 'required|exists:products,id',
            'variation_id' => 'nullable',
            'rating'       => 'required|integer|min:1|max:5',
            'comment'      => 'nullable|string|max:1000',
            'images'       => 'nullable|array|max:5',
            'images.*'     => 'nullable|image|max:2048'
        ], [
            // Custom error message
            'images.max'   => 'You can only upload a maximum of 5 images.',
        ]);

        if (!auth('customer')->check()) {
            return back()->with('error', 'Please login to review.');
        }

        try {
            $imagePaths = [];

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = FileUploadHelper::uploadImage(
                        $image,
                        'reviews',
                        'public',
                        2048
                    );
                    $imagePaths[] = $path;
                }
            }

            ProductReview::create([
                'company_id'   => getCurrentCompany()->id,
                'product_id'   => $request->product_id,
                'variation_id' => $request->filled('variation_id') ? (int)$request->variation_id : null,
                'customer_id'  => auth('customer')->id(),
                'rating'       => $request->rating,
                'comment'      => $request->comment,
                // FIXED: Removed json_encode because of the 'array' cast in the model
                'images'       => !empty($imagePaths) ? $imagePaths : null,
                'status'       => Status::Pending->value,
            ]);

            return back()->with('success', 'Review submitted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
    //return order
    public function requestReturn(Request $request, $store, $id)
    {
        $request->validate([
            'reason'   => 'required|string|max:1000',
            'images.*' => 'nullable|image|max:2048'
        ]);

        $order = Order::findOrFail($id);

        // Prevent duplicate requests
        if ($order->status == Status::ReturnRequest->value) {
            return back()->with('error', 'Return request already submitted.');
        }

        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePaths[] = FileUploadHelper::uploadImage($image, 'returns', 'public', 2048);
            }
        }

        $order->update([
            'status' => Status::ReturnRequest->value,
            'return_info' => [
                'reason'     => $request->reason,
                'images'     => $imagePaths,
                'request_at' => now()->toDateTimeString(),
            ]
        ]);

        return back()->with('success', 'Your return request has been submitted.');
    }
}
