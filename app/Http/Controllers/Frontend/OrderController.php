<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Models\{Order, Party, Warehouse};
use App\Services\OrderService;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Hash, Session};

class OrderController extends Controller
{
    public function __construct(protected OrderService $orderService) {}

    public function index($store)
    {
        $company = getCurrentCompany();
        $template = $company->template_name;

        if (Cart::count() == 0) {
            return redirect()->route('cart.index')->with('error', 'আপনার কার্টটি খালি!');
        }

        // for login user
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
            'cartContent', 'subtotal', 'discount', 'shipping', 'total', 'shipping_area'
        ));
    }

    // for guest user
    public function partialSave($store, Request $request)
    {
        $company = getCurrentCompany();
        $cartContent = Cart::content();
        if ($cartContent->isEmpty()) return response()->json(['success' => false]);

        // "Exact Warehouse"
        $warehouseQuery = Warehouse::where('company_id', $company->id)->active();
        foreach ($cartContent as $item) {
            $variationId = $item->options->variation_id ?? null;
            $requestedQty = $item->qty;
            $warehouseQuery->whereHas('stocks', function($query) use ($variationId, $requestedQty) {
                if ($variationId) $query->where('product_variation_id', $variationId);
                $query->where('quantity', '>=', $requestedQty);
            });
        }
        $warehouseId = $warehouseQuery->first()?->id ?: null;

        try {
            DB::beginTransaction();

            // custimer create or get
            if (auth('customer')->check()) {
                $customer = auth('customer')->user();
            } else {
                $customer = Party::updateOrCreate(
                    ['company_id' => $company->id, 'phone' => $request->phone, 'type' => Party::TYPE_CUSTOMER],
                    ['name' => $request->name ?? 'Guest', 'password' => Hash::make('12345678'), 'status' => true]
                );
            }

            //
            $items = [];
            foreach ($cartContent as $item) {
                $variationId = $item->options->variation_id ?? null;

                if ($variationId) {
                    // for varient product
                    $variation = \App\Models\ProductVariation::find($variationId);
                    $actualProductId = $variation ? $variation->product_id : null;
                } else {
                    $actualProductId = (int) $item->id;
                }

                $items[] = [
                    'product_id'   => $actualProductId,
                    'variation_id' => $variationId,
                    'quantity'     => (int) $item->qty,
                    'unit_price'   => (float) $item->price,
                ];
            }

            $orderData = [
                'company_id'    => $company->id,
                'warehouse_id'  => $warehouseId,
                'customer_id'   => $customer->id,
                'items'         => $items,
                'status'        => Status::Draft->value,
                'order_date'    => now(),
                'coupon_code'   => session('coupon')['coupon_code'] ?? null,
                'other_charges' => session()->get('shipping_cost', 60),
            ];

            if (Session::has('current_draft_order_id')) {
                Order::where('id', Session::get('current_draft_order_id'))->delete();
            }

            $order = $this->orderService->createSalesOrder($orderData);
            Session::put('current_draft_order_id', $order->id);

            DB::commit();
            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Draft Order
     */
    private function createDraftOrder($store, $data)
    {
        $company = getCurrentCompany();
        $cartContent = Cart::content();
        if ($cartContent->isEmpty()) return ['success' => false];

        // ১. "Exact Warehouse"
        $warehouseQuery = Warehouse::where('company_id', $company->id)->active();
        foreach ($cartContent as $item) {
            $variationId = $item->options->variation_id ?? null;
            $requestedQty = $item->qty;

            $warehouseQuery->whereHas('stocks', function($query) use ($variationId, $requestedQty) {
                if ($variationId) $query->where('product_variation_id', $variationId);
                $query->where('quantity', '>=', $requestedQty);
            });
        }

        $exactWarehouse = $warehouseQuery->first();
        $warehouseId = $exactWarehouse ? $exactWarehouse->id : null;

        try {
            DB::beginTransaction();

            $customer = Party::updateOrCreate(
                ['company_id' => $company->id, 'phone' => $data['phone'], 'type' => Party::TYPE_CUSTOMER],
                ['name' => $data['name'] ?? 'Guest', 'password' => Hash::make('12345678'), 'status' => true]
            );

            $items = [];
            foreach ($cartContent as $item) {
                $items[] = [
                    'product_id'   => (int) str_replace('var_', '', $item->id),
                    'variation_id' => $item->options->variation_id ?? null,
                    'quantity'     => (int) $item->qty,
                    'unit_price'   => (float) $item->price,
                ];
            }

            $orderData = [
                'company_id'    => $company->id,
                'warehouse_id'  => $warehouseId,
                'customer_id'   => $customer->id,
                'items'         => $items,
                'status'        => Status::Draft->value,
                'order_date'    => now(),
                'coupon_code'   => session('coupon')['coupon_code'] ?? null,
                'other_charges' => session()->get('shipping_cost', 60),
            ];

            if (Session::has('current_draft_order_id')) {
                Order::where('id', Session::get('current_draft_order_id'))->delete();
            }

            $order = $this->orderService->createSalesOrder($orderData);
            Session::put('current_draft_order_id', $order->id);

            DB::commit();
            return ['success' => true];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Order store
     */
    public function storeOrder($store, Request $request)
    {
        $request->validate(['name' => 'required', 'phone' => 'required', 'address' => 'required']);
        $company = getCurrentCompany();

        $orderId = Session::get('current_draft_order_id');
        $order = Order::where('company_id', $company->id)->where('status', Status::Draft->value)->find($orderId);

        if (!$order) {
            $customer = Party::where('phone', $request->phone)->first();
            $order = Order::where('customer_id', $customer?->id)->where('status', Status::Draft->value)->latest()->first();
        }

        if ($order) {
            try {
                DB::beginTransaction();

                $order->customer->update(['name' => $request->name, 'address' => $request->address]);

                $this->orderService->changeStatus($order->id, Status::Pending->value);

                DB::commit();
                Cart::destroy();
                Session::forget(['coupon', 'current_draft_order_id']);
                return redirect('/invoice')->with('success', 'আপনার অর্ডারটি সফল হয়েছে!');
            } catch (\Exception $e) {
                DB::rollBack();
                return back()->with('error', 'সমস্যা: ' . $e->getMessage());
            }
        }
        return back()->with('error', 'অর্ডার সেশন পাওয়া যায়নি। আবার চেষ্টা করুন।');
    }
}
