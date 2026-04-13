<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Models\{Order, Party, ProductVariation, Warehouse};
use App\Services\OrderService;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Hash, Log, Session};

class OrderController extends Controller
{
    public function __construct(protected OrderService $orderService) {}

    public function index($store)
    {
        $company = getCurrentCompany();
        $template = $company->template_name;

        if (Cart::count() == 0) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty!');
        }

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

        return view($template . '.frontend.checkout', compact(
            'cartContent',
            'subtotal',
            'discount',
            'shipping',
            'total',
            'shipping_area'
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
        $company = getCurrentCompany();
        $cartContent = Cart::content();
        if ($cartContent->isEmpty()) return ['success' => false, 'error' => 'Cart is empty'];

        // 1. Strict Warehouse Check
        $warehouseQuery = Warehouse::where('company_id', $company->id)->active();
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
                ['company_id' => $company->id, 'phone' => $data['phone'], 'type' => Party::TYPE_CUSTOMER],
                ['name' => $data['name'] ?? 'Guest', 'password' => Hash::make('12345678'), 'status' => true]
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
                'company_id'    => $company->id,
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
        $company = getCurrentCompany();

        // 1. Try to find the draft order
        $orderId = Session::get('current_draft_order_id');
        $order = Order::where('company_id', $company->id)->where('status', Status::Draft->value)->find($orderId);

        // 2. If session failed, try to find by phone
        if (!$order) {
            $customer = Party::where('company_id', $company->id)->where('phone', $request->phone)->first();
            if ($customer) {
                $order = Order::where('customer_id', $customer->id)->where('status', Status::Draft->value)->latest()->first();
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
        $company = getCurrentCompany();
        $template = $company->template_name;

        $order = Order::where('company_id', $company->id)
            ->with([
                'customer',
                'orderDetails.product',
                'orderDetails.variation.attributes.attributeValue',
                'orderDetails.variation.attributes.attributeGroup'
            ])
            ->find($id);

        if (!$order) {
            abort(404);
        }
        if (auth('customer')->check() && $order->customer_id !== auth('customer')->id()) {
            abort(403, 'এই অর্ডারটি দেখার অনুমতি আপনার নেই।');
        }

        return view($template . '.frontend.orderDetails', compact('order'));
    }
    public function invoice($store, $id)
    {
        $company = getCurrentCompany();
        $template = $company->template_name;

        $order = Order::where('company_id', $company->id)
            ->with(['customer', 'orderDetails.product', 'company'])
            ->findOrFail($id);

        return view($template . '.frontend.invoice', compact('order'));
    }
    public function trackOrder(Request $request)
    {
        $company = getCurrentCompany();
        $template = $company->template_name;
        $order = null;

        if ($request->filled('order_no')) {
            $order = Order::where('company_id', $company->id)
                ->where('order_no', $request->order_no)
                ->first();

            if (!$order) {
                return back()->with('error', 'Order not found with the provided order number.');
            }
        }

        return view($template . '.frontend.product-track', compact('order'));
    }
}
