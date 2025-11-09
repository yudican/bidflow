<?php

namespace App\Http\Controllers\Spa;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\PaymentMethod;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class CheckoutAgent extends Controller
{
    public function index($user_id = null)
    {
        return view('spa.spa-index');
    }

    public function getCart()
    {
        $user = auth()->user();

        $carts = Cart::with(['product', 'product.variants', 'variant'])->where('user_id', $user->id);
        return response()->json([
            'carts' => $carts->get()->map(function ($cart) {
                $cart['product']['priceData'] = $cart->variant ? $cart->variant['price'] : $cart->product['price'];
                return $cart;
            }),
            'checkoutData' => [
                'selected_all' => $carts->count() == $carts->where('selected', 1)->count(),
                'shipping_info_url' => getSetting('APP_API_URL') . '/shipping/info',
                'total_weight' => getTotalWeight($carts->where('selected', 1)->get()),
                'checkout_url' => getSetting('APP_API_URL') . '/transaction/guest',
                'user' => [
                    'user_id' => $user->id,
                    'nama' => $user->name,
                    'telepon' => $user->telepon,
                    'brand_id' => $user->brand_id,
                ],
                'loader' => asset('assets/img/loader.gif'),
                'voucher_url' => getSetting('APP_API_URL') . '/transaction/apply-voucher',
                'total_amount' => getSubtotal($carts->where('selected', 1)->get()->map(function ($cart) {
                    $cart['product']['priceData'] = $cart->variant ? $cart->variant['price'] : $cart->product['price'];
                    return $cart;
                })),
                'total_qty' => getTotalQty($carts->where('selected', 1)->get()),
                'redirect_url' => route('transaction.detail', ['transaction_id' => ':transaction_id']),
            ],
            'message' => 'success'
        ]);
    }

    public function deleteCart($cart_id)
    {
        $cart = Cart::find($cart_id);
        $cart->delete();

        return response()->json([
            'message' => 'Data berhasil dihapus dari keranjang'
        ]);
    }

    public function updateChartQty(Request $request, $cart_id)
    {
        $cart = Cart::find($cart_id);
        if ($cart) {
            $cart->update(['qty' => $request->qty]);
        }

        return response()->json([
            'message' => 'Data berhasil diubah'
        ]);
    }


    public function addQty($cart_id)
    {
        $cart = Cart::find($cart_id);
        if ($cart) {
            $cart->increment('qty');
        }

        return response()->json([
            'message' => 'Data berhasil diubah'
        ]);
    }

    public function minusQty($cart_id)
    {
        $cart = Cart::find($cart_id);
        if ($cart) {
            if ($cart->qty > 1) {
                $cart->decrement('qty');
            }
        }

        return response()->json([
            'message' => 'Data berhasil diubah'
        ]);
    }
    public function selectAll()
    {
        $user = auth()->user();
        Cart::where('user_id', $user->id)->update(['selected' => 1]);

        return response()->json([
            'message' => 'Data berhasil diubah'
        ]);
    }
    public function selectItem($cart_id)
    {
        $cart = Cart::find($cart_id);
        if ($cart) {
            $cart->update(['selected' => $cart->selected == 1 ? 0 : 1]);
        }

        return response()->json([
            'message' => 'Data berhasil diubah'
        ]);
    }

    public function selectVariant(Request $request)
    {
        $cart = Cart::find($request->cart_id);
        if ($cart) {
            $cart->update(['product_variant_id' => $request->variant_id]);
        }

        return response()->json([
            'message' => 'Variant Berhasil Diupdate'
        ]);
    }

    public function getPaymentMethod()
    {
        $payements = PaymentMethod::whereNull('parent_id')->whereStatus(1)->with('children')->get();

        return response()->json([
            'payment_methods' => $payements,
            'message' => 'success'
        ]);
    }

    public function getWarehouse()
    {
        $warehouses = Warehouse::where('status', 1)->get();

        return response()->json([
            'warehouses' => $warehouses,
            'message' => 'success '
        ]);
    }

    public function getAddress()
    {
        $user = auth()->user();

        return response()->json([
            'address' => $user->addressUsers,
            'message' => 'success'
        ]);
    }
}
